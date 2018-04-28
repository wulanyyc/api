<?php

/**
 * 基础帮助类
 * @author yangyuncai
 *
 */
class WxpayHelper {
    public static $api = 'https://api.mch.weixin.qq.com/pay/unifiedorder';

    public static function pay($app, $params) {
        $data = [];
        $data['appid'] = $app->config->wxpay['appid'];
        $data['mch_id'] = $app->config->wxpay['mch_id'];
        $data['body'] = $params['subject'];
        $data['nonce_str'] = uniqid();
        $data['out_trade_no'] = $params['out_trade_no'];
        $data['total_fee'] = $params['total_amount'];
        $data['spbill_create_ip'] = self::getClientIp();
        $data['notify_url'] = $app->config->wxpay['notify_url'];
        $data['trade_type'] = $params['trade_type'];

        if (!empty($params['openid'])) {
            $data['openid'] = $params['openid'];
        }

        $sign = self::buildSign($app, $data);
        $data['sign'] = $sign;
        $xml = self::buildXml($data);
        $postData = $xml->asXML();

        $ret = self::curlRequest(self::$api, $postData);

        if (!empty($ret)) {
            return self::xmlToArray($ret);
        } else {
            return [];
        }
    }

    public static function buildSign($app, $data) {
        $keys = array_keys($data);
        sort($keys);

        $str = '';
        foreach($keys as $item) {
            $str .= $item . "=" . $data[$item] . "&";
        }

        $str .= "key=" . $app->config->wxpay['key'];

        $sign = md5($str);

        return strtoupper($sign);
    }

    public static function buildXml($data) {
        $xmlObj = new \SimpleXMLElement('<xml></xml>');
        self::arrayToXml($data, $xmlObj);
        return $xmlObj;
    }

    public static function arrayToXml($data, &$xmlObj) {
        foreach($data as $key => $value) {
            if (is_numeric($key)){
                $key = 'item'.$key;
            }

            if (is_array($value)) {
                $subnode = $xmlObj->addChild($key);
                self::arrayToXml($value, $subnode);
            } else {
                $xmlObj->addChild("$key", htmlspecialchars("$value"));
            }
        }
    }

    public static function xmlToArray($xml) {
        $obj  = simplexml_load_string($xml, 'SimpleXMLElement', LIBXML_NOCDATA);
        $json = json_encode($obj);
        $data = json_decode($json, TRUE);

        return $data;
    }

    // public static function refund($params) {
    //     $api = 'https://api.mch.weixin.qq.com/secapi/pay/refund';

    //     $data = [];
    //     $data['appid'] = $app->config->params->wxpay['appid'];
    //     $data['mch_id'] = $app->config->params->wxpay['mch_id'];
    //     $data['refund_desc'] = '后台退款';
    //     $data['nonce_str'] = uniqid();
    //     $data['out_trade_no'] = $params['out_trade_no'];
    //     $data['total_fee'] = $params['pay_money'] * 100;
    //     $data['refund_fee'] = $params['pay_money'] * 100;
    //     $data['out_refund_no'] = $params['order_id'];

    //     $sign = self::buildSign($data);
    //     $data['sign'] = $sign;
    //     $xml = self::buildXml($data);
    //     $postData = $xml->asXML();

    //     $ret = self::curl($postData, $api, true);

    //     return self::xmlToArray($ret);
    // }

    public static function query($app, $params) {
        $api = 'https://api.mch.weixin.qq.com/pay/orderquery';

        $data = [];
        $data['appid'] = $app->config->params->wxpay['appid'];
        $data['mch_id'] = $app->config->params->wxpay['mch_id'];
        $data['nonce_str'] = uniqid();
        $data['out_trade_no'] = $params['out_trade_no'];

        $sign = self::buildSign($data);
        $data['sign'] = $sign;
        $xml = self::buildXml($data);
        $postData = $xml->asXML();

        $ret = self::curlRequest($api, $postData);

        return self::xmlToArray($ret);
    }

    public static function getClientIp() {
        $ip = '';
        if (isset($_SERVER['REMOTE_ADDR'])) {
            $ip = $_SERVER['REMOTE_ADDR'];
        } else if (getenv('REMOTE_ADDR')) {
            $ip = getenv('REMOTE_ADDR');
        }

        return $ip;
    }

    /**
     * 远程调用api
     */
    public static function curlRequest($url, $data = "", $header = array(), $timeout = 30) {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_TIMEOUT, $timeout);

        curl_setopt($ch, CURLOPT_URL, $url);

        if (count($header) > 1) {
            curl_setopt($ch, CURLOPT_HTTPHEADER, $header);
        }
        
        if (!empty($data)) {
            curl_setopt($ch, CURLOPT_POST, 1);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
        }

        curl_setopt($ch,CURLOPT_SSL_VERIFYPEER,false);
        curl_setopt($ch,CURLOPT_SSL_VERIFYHOST,false);

        // curl_setopt($ch,CURLOPT_SSLCERT, Yii::$app->basePath .'/config/wx.pem');

        $response = curl_exec($ch);

        if($response){
            curl_close($ch);
            return $response;
        } else { 
            $error = curl_errno($ch);
            curl_close($ch);
            return false;
        }
    }

    public static function getNoncestr() {
        $str = "";
        $str_pol = "ABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789abcdefghijklmnopqrstuvwxyz";
        $max = strlen($str_pol) - 1;
        for ($i = 0; $i < 16; $i++) {
            $str .= $str_pol[mt_rand(0, $max)];
        }
        return $str;
    }

    public static function getPageAccessToken($app) {
        $key = 'page_access_token';
        $keyRefresh = 'page_refresh_token';

        $cache = $app->redis->get($key);

        if (empty($cache)) {
            $refreshToken = $app->redis->get($keyRefresh);
            if (empty($refreshToken)) {
                return '';
            }

            $config = $app->config->wxpay;
            $url = 'https://api.weixin.qq.com/sns/oauth2/refresh_token?appid='. $config['appid'] .'&grant_type=refresh_token&refresh_token=' . $refreshToken;

            $ret = self::curlRequest($url);
            $data = json_decode($ret, true);

            if (isset($data['access_token'])) {
                $app->redis->setex($key, $data['expires_in'] - 60, $data['access_token']);
                $app->redis->setex($keyRefresh, 30 * 86400 - 3600, $data['refresh_token']);

                return $data['access_token'];
            } else {
                return '';
            }
        } else {
            return $cache;
        }
    }

    public static function buildPageSignature($app, $url, $timestamp, $noncestr) {
        $data = [
            'url' => $url,
            'noncestr' => $noncestr,
            'timestamp' => $timestamp,
            'jsapi_ticket' => self::getJsapiTicket($app),
        ];

        ksort($data, SORT_STRING);

        $str = '';
        foreach($data as $key => $value) {
            $str .= $key . "=" . $value . '&';
        }

        $str = substr($str, 0, -1);

        return sha1($str);
    }

    public static function getJsapiTicket($app) {
        $key = 'jsapi_ticket';

        $cache = $app->redis->get($key);

        if (empty($cache)) {
            $url = 'https://api.weixin.qq.com/cgi-bin/ticket/getticket?access_token=' . self::getAccessToken($app) . '&type=jsapi';

            $ret = self::curlRequest($url);
            $data = json_decode($ret, true);

            if (isset($data['ticket'])) {
                $app->redis->setex($key, $data['expires_in'] - 60, $data['ticket']);
                return $data['ticket'];
            } else {
                return '';
            }
        } else {
            return $cache;
        }
    }

    public static function getAccessToken($app) {
        $key = 'access_token';

        $cache = Yii::$app->redis->get($key);
        if (empty($cache)) {
            $appid = $app->config->wxpay->appid;
            $appsecret = $app->config->wxpay->appsecret;

            $url = 'https://api.weixin.qq.com/cgi-bin/token?grant_type=client_credential&appid='. $appid .'&secret=' . $appsecret;

            $ret = self::curlRequest($url);
            $data = json_decode($ret, true);

            if (isset($data['access_token'])) {
                $app->redis->setex($key, $data['expires_in'] - 60, $data['access_token']);
                return $data['access_token'];
            } else {
                return '';
            }
        } else {
            return $cache;
        }
    }
}
