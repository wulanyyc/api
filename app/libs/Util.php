<?php
use Shop\Model\Users;

class Util
{
    public static function arrayToObject($array)
    {
        $object = new stdClass();
        foreach ($array as $key => $value) {
            if (is_array($value)) {
                $value = self::arrayToObject($value);
            }
            $object->$key = $value;
        }
        return $object;
    }

    public static function objectToArray($obj)
    {   
        $data = [];
        if (is_object($obj)) {
            foreach ($obj as $key => $value) {
                if (is_object($value)) {
                    $value = self::objectToArray($value);
                }
                $data[$key] = $value;
            }
        }

        return $data;
    }

    public static function getWxSessionId($app, $appid, $secret, $code) {
        $url = 'https://api.weixin.qq.com/sns/jscode2session?appid='. $appid .'&secret='. $secret .'&js_code='. $code .'&grant_type=authorization_code';

        return $app->curl->get($url, 60);
    }

    public static function getUser($app, $session) {
        $result = $app->redis->get($session);
        return json_decode($result, true);
    }

    public static function getAuditFlag($uid) {
        return Users::findFirst($uid)->auditflag;
    }
}
