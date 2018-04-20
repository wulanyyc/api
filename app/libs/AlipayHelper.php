<?php
/**
 * 基础帮助类
 * @author yangyuncai
 *
 */
class AlipayHelper {
    public static function wappay($app, $params) {
        $aop = new \AopClient;
        $aop->gatewayUrl = $app->config->alipay['gatewayUrl'];
        $aop->appId = $app->config->alipay['app_id'];
        $aop->rsaPrivateKey = $app->config->alipay['merchant_private_key'];
        $aop->format = "json";
        $aop->charset = $app->config->alipay['charset'];
        $aop->signType= $app->config->alipay['sign_type'];
        $aop->alipayrsaPublicKey = $app->config->alipay['alipay_public_key'];

        $request = new \AlipayTradeWapPayRequest ();
        $request->setBizContent(json_encode($params));
        $request->setNotifyUrl($app->config->alipay['notify_url']);
        $request->setReturnUrl($app->config->alipay['return_url']);
        $result = $aop->pageExecute ($request);

        return $result;
    }

    public static function check($app, $data, $terminal) {
        $aop = new \AopClient;
        $aop->alipayrsaPublicKey = $app->config->alipay['alipay_public_key'];

        $result = $aop->rsaCheckV1($data, $app->config->alipay['alipay_public_key'], $app->config->alipay['sign_type']);

        return $result;
    }

    public static function query($app, $data) {
        $aop = new \AopClient;
        $aop->gatewayUrl = $app->config->alipay['gatewayUrl'];
        $aop->appId = $app->config->alipay['app_id'];
        $aop->rsaPrivateKey = $app->config->alipay['merchant_private_key'];
        $aop->format = "json";
        $aop->charset = $app->config->alipay['charset'];
        $aop->signType= $app->config->alipay['sign_type'];
        $aop->alipayrsaPublicKey = $app->config->alipay['alipay_public_key'];

        $params = [];
        $params['out_trade_no'] = $data['out_trade_no'];
        // $params['trade_no'] = $data['trade_no'];

        $request = new \AlipayTradeQueryRequest ();
        $request->setBizContent(json_encode($params));
        $result = $aop->Execute ($request);

        return $result;
    }
}
