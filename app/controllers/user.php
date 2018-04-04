<?php
use Biaoye\Model\Users;

//用户认证（手机动态登录）
$app->post('/user/add', function () use ($app) {
    $params = json_decode($app->request->getRawBody(), true);

    $resultStr = $app->redis->get($params['session']);
    $result = json_decode($resultStr, true);

    unset($params['session']);

    $ar = new Users();
    $ar->openid  = $result['openid'];
    $ar->unionid = $result['unionid'];

    foreach($params as $key => $value) {
        $ar->$key = $value;
    }

    if ($ar->save() == true) {
        return $ar->id;
    } else {
        return 0;
    }
});


$app->post('/user/phone', function () use ($app) {
    $params = json_decode($app->request->getRawBody(), true);

    $user = $app->util->getUser($app, $params['session']);
    $pc = new WxBizDataCrypt($app->config->wxconfig['appid'], $user['session_key']);
    $errCode = $pc->decryptData($params['encryptedData'], $params['iv'], $data);

    if ($errCode == 0) {
        $ret = json_decode($data, true);
        return $ret['purePhoneNumber'];
    } else {
        return $errCode;
    }
});

// 上传店铺logo
$app->post('/user/upload/logo', function () use ($app) {
    $uploader = new FileUploader($app);
    $result = $uploader->upload();

    if (!empty($result['uploaded'])) {
        return $result['uploaded'];
    } else {
        return [];
    }
});

// 用户上传
$app->post('/user/upload/{id:\d+}', function ($id) use ($app) {
    $uploader = new FileUploader($app);
    $result = $uploader->upload(['uid' => $id]);

    if (!empty($result['uploaded'])) {
        return $result['uploaded'];
    } else {
        return [];
    }
});


// 用户信息
$app->get('/user/qr/{id:\d+}', function ($id) use ($app) {
    $userInfo = Users::findFirst($id);

    return $userInfo->serviceQr;
});

$app->post('/user/qr/edit', function () use ($app) {
    $params = json_decode($app->request->getRawBody(), true);
    $ar = Users::findFirst($params['uid']);
    $ar->serviceQr = $params['serviceQr'];
    $ar->save();

    return $ar->id;
});

// 用户上传
$app->post('/user/upload/{id:\d+}', function ($id) use ($app) {
    $uploader = new FileUploader($app);
    $result = $uploader->upload(['uid' => $id]);

    if (!empty($result['uploaded'])) {
        return $result['uploaded'];
    } else {
        return [];
    }
});

// 用户上传
$app->post('/user/{id:\d+}', function ($id) use ($app) {
    $uploader = new FileUploader($app);
    $result = $uploader->upload(['uid' => $id]);

    if (!empty($result['uploaded'])) {
        return $result['uploaded'];
    } else {
        return [];
    }
});