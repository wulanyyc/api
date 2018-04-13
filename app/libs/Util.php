<?php
use Phalcon\Security\Random;
use Biaoye\Model\CustomerAddress;
use Biaoye\Model\School;
use Biaoye\Model\Room;

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
        $customerId = $app->redis->hget($token, 'customer_id');

        if (empty($customerId)) {
            throw new BusinessException(400, 'bad request');
        }

        return $customerId;
    }

    public static function getAgentId($app) {
        $token = $app->util->getToken($app);
        $agentId = $app->redis->hget($token, 'agent_id');

        if (empty($agentId)) {
            throw new BusinessException(400, 'bad request');
        }

        return $agentId;
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

    public function getChar($num = 0) {
        if ($num == 0) {
            $num = rand(1, 20);
        }
        
        $b = '';
        for ($i=0; $i<$num; $i++) {
            // 使用chr()函数拼接双字节汉字，前一个chr()为高位字节，后一个为低位字节
            $a = chr(mt_rand(0xB0,0xD0)).chr(mt_rand(0xA1, 0xF0));
            // 转码
            $b .= iconv('GB2312', 'UTF-8', $a);
        }
        return $b;
    }

    public function getAddressInfo($app, $id) {
        $key = "address_" . $id;
        $cacheAddress = $app->redis->get($key);
        if ($cacheAddress) {
            return $cacheAddress;
        } else {
            $info = CustomerAddress::findFirst($id);
            $school = School::findFirst($info->rec_school)->name;
            $room = Room::findFirst($info->rec_room)->name;

            $address = $school . $room . $info->rec_detail;

            $app->redis->setex($key, 86400, $address);
            return $address;
        }
    }
}
