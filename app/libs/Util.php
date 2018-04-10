<?php
use Phalcon\Security\Random;

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

    public static function getToken($app) {
        if (isset($_REQUEST['token'])) {
            $token = $_REQUEST['token'];
        } else {
            $token = $app->request->getHeader('token');
        }

        return $token;
    }

    public static function uuid() {
        $random = new Random();
        return $random->uuid();
    }

    public static function getCustomerId($app) {
        $token = $app->util->getToken($app);
        return $app->redis->hmget($token, 'customer_id');
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
}
