<?php
use Phalcon\Security\Random;
use Biaoye\Model\Customer;
use Biaoye\Model\CustomerCart;
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
            throw new BusinessException(401, 'bad request');
        }

        return $customerId;
    }

    public static function getCustomerInfo($app) {
        $customerId = self::getCustomerId($app);
        return Customer::findFirst($customerId);
    }

    public static function getAgentId($app) {
        $token = $app->util->getToken($app);
        $agentId = $app->redis->hget($token, 'agent_id');

        if (empty($agentId)) {
            throw new BusinessException(401, 'bad request');
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
            
            $school = School::findFirst($info->rec_school);
            $room = Room::findFirst($info->rec_room);

            if ($school) {
                $schoolName = $school->name;
            } else {
                $schoolName = '';
            }

            if ($room) {
                $roomName = $room->name;
            } else {
                $roomName = '';
            }

            $address = $schoolName . $roomName . $info->rec_detail;

            $app->redis->setex($key, 86400, $address);
            return $address;
        }
    }

    public function getDefaultAddress($app) {
        $customerId = self::getCustomerId($app);
        $data = CustomerAddress::findFirst([
            'conditions' => 'default_flag=1 and status=0 and customer_id=' . $customerId,
            'columns' => 'id, rec_name, rec_phone, rec_school, rec_room, rec_detail',
        ]);

        if (!$data) {
            $data = CustomerAddress::findFirst([
                'conditions' => 'status=0 and customer_id=' . $customerId,
                'columns' => 'id, rec_name, rec_phone, rec_school, rec_room, rec_detail',
                'order' => 'id desc'
            ]);
        }

        if (!$data) {
            return [];
        } else {
            return [
                'id' => $data->id,
                'rec_name' => $data->rec_name,
                'rec_phone' => $data->rec_phone,
                'rec_detail' => $data->rec_detail,
                'school' => $app->data->getSchoolName($app, $data->rec_school),
                'room' => $app->data->getRoomName($app, $data->rec_school),
            ];
        }
    }

    public static function getCartId($app) {
        $customerId = self::getCustomerId($app);
        $ar = CustomerCart::findFirst("customer_id=" . $customerId);
        if ($ar) {
            return $ar->id;
        }

        $cart = [];
        $add = new CustomerCart();
        $add->customer_id = $customerId;
        $add->cart = json_encode($cart);
        $add->save();

        return $add->id;
    }

    public static function getCartNum($app) {
        $cartId = self::getCartId($app);
        $ar = CustomerCart::findFirst($cartId);

        $cart = json_decode($ar->cart, true);
        return count($cart);
    }

    public static function sendSms($app, $phones, $content) {
        $api = "http://sms.bamikeji.com:8890/mtPort/mt/normal/send";
        $suffix = "【庆荣科技】";

        $config = [
            'uid' => $app->config->params->sms->id,
            'passwd' => md5($app->config->params->sms->pwd),
            'phonelist' => implode(',', $phones),
            'content' => $content . $suffix,
        ];

        $ret = $app->util->curlRequest($api, http_build_query($config));
        $app->logger->error("sms req:" . json_encode($ret));
        return $ret;
    }

    public static function checkPhoneFormat($phone) {
        $reg ='/^1\d{10}$/';
        if(preg_match($reg, $phone)) {
            return true;
        }

        return false;
    }

    // 添加抢单cache
    public static function setRobCacheKey($app, $orderId) {
        $key = $app->config->params->get_order_prefix . $orderId;
        $app->redis->setex($key, 172800, 0); // 2天
    }

    // 获取夜晚标志
    public static function getSwitchFlag($app) {
        $hour = date('H', time());

        // 晚9点
        if ($hour >= $app->config->params->switch_day_night) {
            return true;
        }

        return false;
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
