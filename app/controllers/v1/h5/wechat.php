<?php
use Biaoye\Model\Customer;

$app->get('/v1/h5/wechat/get/openid', function () use ($app) {
    // https://api.weixin.qq.com/sns/oauth2/access_token?appid=APPID&secret=SECRET&code=CODE&grant_type=authorization_code
    $code = $app->request->getQuery("code");
    $customerId = $app->util->getCustomerId($app);

    $key = 'page_access_token';
    $keyRefresh = 'page_refresh_token';

    $config = $app->config->wxpay;
    $url = 'https://api.weixin.qq.com/sns/oauth2/access_token?appid='. $config['appid'] .'&secret=' . $config['appsecret'] . '&code=' . $code . '&grant_type=authorization_code';

    $ret = $app->util->curlRequest($url);
    $data = json_decode($ret, true);

    if (isset($data['access_token'])) {
        $app->redis->setex($key, $data['expires_in'] - 60, $data['access_token']);
        $app->redis->setex($keyRefresh, 30 * 86400 - 3600, $data['refresh_token']);

        $up = Customer::findFirst($customerId);
        $up->openid = $data['openid'];
        $up->save();

        return $data['openid'];
    } else {
        throw new BusinessException(1000, '获取openid失败');
    }
});

// 微信js初始化
$app->post('/v1/h5/wechat/init', function () use ($app) {
    $url = $app->request->getPost("url");
    if (empty($url)) {
        throw new BusinessException(1000, '参数有误');
    }

    $timestamp = time();
    $noncestr  = WxpayHelper::getNoncestr();
    $signature = WxpayHelper::buildPageSignature($app, $url, $timestamp, $noncestr);

    $wechatData = [
        'timestamp' => $timestamp,
        'noncestr'  => $noncestr,
        'signature' => $signature,
        'appid'     => $app->config->wxpay['appid'],
    ];

    return $wechatData;
});