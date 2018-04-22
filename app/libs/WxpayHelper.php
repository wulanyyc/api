<?php
namespace app\components;

use Yii;
use yii\base\Component;
use app\components\WechatHelper;
use app\components\SiteHelper;


/**
 * 基础帮助类
 * @author yangyuncai
 *
 */
class WxpayHelper extends Component{
    public static $api = 'https://api.mch.weixin.qq.com/pay/unifiedorder';

    public static function pay($params) {
        $data = [];
        $data['appid'] = $app->config->params->wxpay['appid'];
        $data['mch_id'] = $app->config->params->wxpay['mch_id'];
        $data['body'] = $params['subject'];
        $data['nonce_str'] = uniqid();
        $data['out_trade_no'] = $params['out_trade_no'];
        $data['total_fee'] = $params['total_amount'];
        $data['spbill_create_ip'] = self::getClientIp();
        $data['notify_url'] = $app->config->params->wxpay['notify_url'];
        $data['trade_type'] = $params['trade_type'];

        if (!empty($params['openid'])) {
            $data['openid'] = $params['openid'];
        }

        $sign = self::buildSign($data);
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

    public static function buildSign($data) {
        $keys = array_keys($data);
        sort($keys);

        $str = '';
        foreach($keys as $item) {
            $str .= $item . "=" . $data[$item] . "&";
        }

        $str .= "key=" . $app->config->params->wxpay['key'];

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

    public static function refund($params) {
        $api = 'https://api.mch.weixin.qq.com/secapi/pay/refund';

        $data = [];
        $data['appid'] = $app->config->params->wxpay['appid'];
        $data['mch_id'] = $app->config->params->wxpay['mch_id'];
        $data['refund_desc'] = '后台退款';
        $data['nonce_str'] = uniqid();
        $data['out_trade_no'] = $params['out_trade_no'];
        $data['total_fee'] = $params['pay_money'] * 100;
        $data['refund_fee'] = $params['pay_money'] * 100;
        $data['out_refund_no'] = $params['order_id'];

        $sign = self::buildSign($data);
        $data['sign'] = $sign;
        $xml = self::buildXml($data);
        $postData = $xml->asXML();

        $ret = self::curl($postData, $api, true);

        return self::xmlToArray($ret);
    }

    public static function query($params) {
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
}