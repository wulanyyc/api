<?php
/**
 * 商城首页数据
 */

use Biaoye\Model\CustomerAddress;
use Biaoye\Model\Room;
use Biaoye\Model\School;


$app->get('/v1/h5/address/list', function () use ($app) {
    $customerId = $app->util->getCustomerId($app);

    $data = CustomerAddress::find([
        'conditions' => "status = 0 and customer_id=" . $customerId,
        'order' => 'id desc',
        'columns' => 'id, rec_name, rec_school, rec_room, rec_phone, rec_detail, default_flag',
    ])->toArray();

    if (empty($data)) {
        return [];
    }

    foreach($data as $key => $item) {
        $data[$key]['school'] = School::findFirst($item['rec_school'])->name;
        $data[$key]['room']   = Room::findFirst($item['rec_room'])->name;
        unset($data[$key]['rec_school']);
        unset($data[$key]['rec_room']);
    }

    return $data;
});

$app->get('/v1/h5/address/detail/{id:\d+}', function ($id) use ($app) {
    $customerId = $app->util->getCustomerId($app);

    $data = CustomerAddress::findFirst($id);

    if (!$data) {
        return [];
    }

    $ret['id'] = $id;
    $ret['school'] = School::findFirst($data->rec_school)->name;
    $ret['room']   = Room::findFirst($data->rec_room)->name;
    $ret['rec_school'] = $data->rec_school;
    $ret['rec_room'] = $data->rec_room;
    $ret['rec_name'] = $data->rec_name;
    $ret['rec_phone'] = $data->rec_phone;
    $ret['rec_detail'] = $data->rec_detail;
    $ret['default_flag'] = $data->default_flag;
    $ret['sex'] = $data->sex;

    return $ret;
});

$app->post('/v1/h5/address/add', function () use ($app) {
    $params = $_POST;
    if (empty($params)) {
        throw new BusinessException(1000, '参数不能为空');
    }

    $customerId = $app->util->getCustomerId($app);

    $ar = new CustomerAddress();
    $ar->customer_id = $customerId;
    foreach($params as $key => $value) {
        $ar->$key = $value;
    }

    if ($ar->save()) {
        return 1;
    } else {
        throw new BusinessException(1000, '参数有误');
    }
});

$app->post('/v1/h5/address/edit/{id:\d+}', function ($id) use ($app) {
    $params = $_POST;
    if (empty($params)) {
        throw new BusinessException(1000, '参数不能为空');
    }

    $customerId = $app->util->getCustomerId($app);

    $ar = CustomerAddress::findFirst($id);
    $ar->customer_id = $customerId;
    foreach($params as $key => $value) {
        $ar->$key = $value;
    }

    if ($ar->save()) {
        return 1;
    } else {
        throw new BusinessException(1000, '参数有误');
    }
});

$app->get('/v1/h5/address/del/{id:\d+}', function ($id) use ($app) {
    $customerId = $app->util->getCustomerId($app);

    $ar = CustomerAddress::findFirst($id);
    if ($ar->customer_id != $customerId) {
        throw new BusinessException(1000, '没有操作权限');
    }

    $ar->status = 1;
    if ($ar->save()) {
        return 1;
    } else {
        throw new BusinessException(1000, '删除失败');
    }
});


$app->get('/v1/h5/address/set/default/{id:\d+}', function ($id) use ($app) {
    $customerId = $app->util->getCustomerId($app);

    $up = CustomerAddress::findFirst($id);
    if (!$up) {
        throw new BusinessException(1000, '参数有误');
    }
    $up->default_flag = 1;

    if ($up->save()) {
        $app->db->execute("update customer_address set default_flag = 0 where customer_id= " . $customerId . " and id !=" . $id);
        return 1;
    } else {
        throw new BusinessException(1000, '设置失败');
    }
});