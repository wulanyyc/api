<?php
use Biaoye\Model\School;
use Biaoye\Model\Room;

$app->get('/v1/school/list', function () use ($app) {
    // TODO cache
    
    $list = School::find([
        'columns' => 'id, name',
        'conditions' => 'status = 0'
    ]);

    return $list->toArray();
});


$app->get('/v1/school/room/{id:\d+}', function ($id) use ($app) {
    // TODO cache

    $list = Room::find([
        'columns' => 'id, name',
        'conditions' => 'status = 0 and school_id = ' . $id
    ]);

    return $list->toArray();
});


