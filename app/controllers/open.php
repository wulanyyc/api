<?php
use Biaoye\Model\Users;
use Biaoye\Model\Shops;

$app->get('/open/session', function () use ($app) {
    $code     = $app->request->getQuery('code');
    $wxconfig = $app->config['wxconfig'];

    $result = $app->util->getWxSessionId($app, $wxconfig['appid'], $wxconfig['appsecret'], $code);

    if (isset($result['session_key'])) {
        $userInfo = Users::findFirst(['openid' => $result['openid']]);
        $key = md5($result['openid'] . $result['session_key']);
        $app->redis->setex($key, 86400, json_encode($result));

        $ret = [];
        $ret['session'] = $key;
        if (!empty($userInfo)) {
            $ret['uid'] = $userInfo->id;
        }

        return $ret;
    } else {
        return '';
    }
});



$app->get('/open/banks', function () use ($app) {
    return $app->config->banks;
});