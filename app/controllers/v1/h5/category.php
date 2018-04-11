<?php
/**
 * 商城首页数据
 */

use Biaoye\Model\ProductCategory;

$app->get('/v1/h5/category/list', function () use ($app) {
    $result = ProductCategory::find([
        'conditions' => 'status=0',
        'columns' => 'id, name, img, parent_id',
    ])->toArray();

    if (empty($result)) return [];

    $ret = [];
    foreach($result as $item) {
        if ($item['parent_id'] == 0) {
            if (!isset($ret[$item['id']])) {
                $ret[$item['id']] = [
                    'id' => $item['id'],
                    'name' => $item['name']
                ];
            }
        } else {
            if (isset($ret[$item['parent_id']])) {
                $ret[$item['parent_id']]['child'][$item['id']] = [
                    'id' => $item['id'],
                    'name' => $item['name'],
                    'img' => $item['img'],
                ];
            } else {
                $ret[$item['parent_id']] = [
                    'child' => [
                        $item['id'] => [
                            'id' => $item['id'],
                            'name' => $item['name'],
                            'img' => $item['img'],
                        ]
                    ]
                ];
            }
        }
    }

    foreach($ret as $key => $value) {
        if (!isset($value['id'])) {
            unset($ret[$key]);
        }
    }

    return $ret;
});