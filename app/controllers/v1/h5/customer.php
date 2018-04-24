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

    $ret = [
        'phone' => $info->phone,
        'unpay' => isset($order[0]) ? $order[0]['status_cnt'] : 0,
        'undeliver' => isset($order[1]) ? $order[1]['status_cnt'] : 0,
        'deliver' => isset($order[2]) ? $order[2]['status_cnt'] : 0,
        'ok' => isset($order[3]) ? $order[3]['status_cnt'] : 0,
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
        'columns' => 'id, title, message, create_time',
        'order' => 'id desc',
    ])->toArray();

    if (!empty($result)) {
        foreach($result as $key => $item) {
            $result[$key]['date'] = date('y/m/d', time());
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
