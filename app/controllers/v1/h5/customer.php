<?php
/**
 * 优惠券
 */

use Biaoye\Model\Customer;
use Biaoye\Model\CustomerOrder;
use Biaoye\Model\CustomerFeedback;
use Biaoye\Model\NotifyMessage;
use Biaoye\Model\CustomerSearchHistory;

$app->get('/v1/h5/customer/center', function () use ($app) {
    $customerId = $app->util->getCustomerId($app);
    $info = Customer::findFirst($customerId);

    $sql = "select status, count(*) as status_cnt from customer_order where customer_id = " . $customerId . " group by status";
    $order = $app->db->query($sql)->fetchAll();

    foreach($order as $item) {
        if ($item['status'] == 0) {
            $unpay = $item['status_cnt'];
        }

        if ($item['status'] == 1) {
            $undeliver = $item['status_cnt'];
        }

        if ($item['status'] == 2) {
            $deliver = $item['status_cnt'];
        }

        if ($item['status'] == 3) {
            $ok = $item['status_cnt'];
        }
    }

    $ret = [
        'phone' => $info->phone,
        'unpay' => isset($unpay) ? $unpay : 0,
        'undeliver' => isset($undeliver) ? $undeliver : 0,
        'deliver' => isset($deliver) ? $deliver : 0,
        'ok' => isset($ok) ? $ok : 0,
        'money' => $info->money,
        'score' => $info->score,
    ];

    return $ret;
});

$app->post('/v1/h5/customer/feedback', function () use ($app) {
    $customerId = $app->util->getCustomerId($app);
    $advice = $app->request->getPost("advice");

    $ar = new CustomerFeedback();
    $ar->customer_id = $customerId;
    $ar->advice = $advice;

    if (!$ar->save()) {
        throw new BusinessException(1000, '反馈失败');
    }

    return 1;
});

$app->get('/v1/h5/customer/message', function () use ($app) {
    $date = date('Ymd', time() - 15 * 86400);
    $result = NotifyMessage::find([
        'conditions' => 'terminal = 1 and date >=' . $date,
        'columns' => 'id, title, message, create_time, img',
        'order' => 'id desc',
    ])->toArray();

    if (!empty($result)) {
        foreach($result as $key => $item) {
            $result[$key]['date'] = date('y/m/d', strtotime($result[$key]['create_time']));
            $result[$key]['create_time'] = date('Y/m/d H:i:s', strtotime($result[$key]['create_time']));
        }
    }

    return $result;
});


// 搜索历史
$app->get('/v1/h5/customer/search/history', function () use ($app) {
    $customerId = $app->util->getCustomerId($app);

    $result = CustomerSearchHistory::find([
        'conditions' => 'customer_id = ' . $customerId,
        'columns' => 'search_text',
        'order' => 'id desc',
        'limit' => 6,
    ])->toArray();

    return $result;
});

// 个人信息
$app->get('/v1/h5/customer/info', function () use ($app) {
    $customerId = $app->util->getCustomerId($app);

    $info = Customer::findFirst($customerId)->toArray();
    if (!empty($info)) {
        unset($info['create_time']);
        unset($info['invite_code']);
    }

    return $info;
});


// 修改姓名
$app->post('/v1/h5/customer/sex', function () use ($app) {
    $sex = $app->request->getPost("sex");
    $customerId = $app->util->getCustomerId($app);

    $sex = intval($sex);
    if ($sex > 2) {
        throw new BusinessException(1000, '参数有误');
    }

    $up = Customer::findFirst($customerId);
    $up->sex = $sex;
    if (!$up->save()) {
        throw new BusinessException(1000, '修改失败');
    }

    return 1;
});


// 修改手机号码
$app->post('/v1/h5/customer/phone', function () use ($app) {
    $phone = $app->request->getPost("phone");
    $code = $app->request->getPost("code");
    $customerId = $app->util->getCustomerId($app);

    if (!$app->util->checkPhoneFormat($phone)) {
        throw new BusinessException(1000, '参数有误');
    }

    $key = $phone . '_smscode';
    $vcode = $app->redis->get($key);
    if ($vcode != $code) {
        throw new BusinessException(1000, '验证码有误');
    }

    $up = Customer::findFirst($customerId);
    $up->phone = $phone;
    if (!$up->save()) {
        throw new BusinessException(1000, '修改失败');
    }

    return 1;
});


// 钱包
$app->get('/v1/h5/customer/wallet', function () use ($app) {
    $customerId = $app->util->getCustomerId($app);

    $info = Customer::findFirst($customerId);

    return [
        'money' => $info->money,
        'score' => $info->score,
    ];
});

// 提醒发货
$app->get('/v1/h5/customer/notify/deliver/{id:\d+}', function ($id) use ($app) {
    return 1;
});
