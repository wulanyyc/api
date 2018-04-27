<?php
use Biaoye\Model\Customer;
use Biaoye\Model\School;
use Biaoye\Model\Product;
use Biaoye\Model\ProductListSchool;

class ProductHelper
{   
    public function getProductPrice($id) {
        return Product::findFirst($id)->price;
    }

    // 首页标签商品
    public function getHomeProductByTag($app, $customerId, $tagId, $num) {
        $customerInfo = Customer::findFirst($customerId);

        if (!$app->util->getAssignFlag($app)) {
            // 白天
            return $this->getDayHomeProductByTag($app, $customerInfo, $tagId, $num);
        } else {
            // 晚上
            return $this->getNightHomeProductByTag($app, $customerInfo, $tagId, $num);
        }
    }

    public function getDayHomeProductByTag($app, $customerInfo, $tagId, $num)
    {
        $products = ProductListSchool::find([
            'conditions' => 'status=1 and school_id= ' . $customerInfo->school_id . ' and num > 0 and find_in_set(' . $tagId . ' ,tags)',
            'columns' => 'product_id, name, price, title, slogan, img',
            'limit' => $num,
            'order' => 'product_id desc'
        ]);

        if (!$products) {
            return [];
        }

        $data = $products->toArray();
        foreach($data as $key => $item) {
            $data[$key]['id'] = $item['product_id'];
            unset($data[$key]['product_id']);
        }

        return $data;
    }

    public function getNightHomeProductByTag($app, $customerInfo, $tagId, $num)
    {
        $sql = "select pl.id, pl.name, pl.price, pl.title, pl.slogan, pl.img from agent_inventory as ai left join product_list as pl on ai.product_id = pl.id where ai.school_id = " . $customerInfo->school_id . " and ai.room_id=" . $customerInfo->room_id . " and ai.num > 0 and ai.status = 0 and find_in_set(" . $tagId . ", pl.tags) group by pl.id order by ai.product_id desc limit " . $num;

        $products = $app->db->query($sql)->fetchAll();

        return $products;
    }

    // 获取新品
    public function getNewProduct($app, $num) {
        $customerId = $app->util->getCustomerId($app);
        $info = Customer::findFirst('id=' . $customerId);

        if (!$app->util->getAssignFlag($app)) {
            return $this->getDayNewProduct($app, $info, $num);
        } else {
            return $this->getNightNewProduct($app, $info, $num);
        }
    }

    public function getDayNewProduct($app, $info, $num) {
        $products = ProductListSchool::find([
            'conditions' => 'status=1 and num > 0 and school_id= ' . $info->school_id,
            'columns' => 'product_id, name, title, price, img',
            'limit' => $num,
            'order' => 'product_id desc'
        ]);

        if (!$products) {
            return [];
        }

        $data = $products->toArray();

        if (!empty($data)) {
            foreach($data as $key => $item) {
                $data[$key]['id'] = $item['product_id'];
                unset($data[$key]['product_id']);
            }
        }

        return $data;
    }

    public function getNightNewProduct($app, $info, $num) {
        $sql = "select pl.id, pl.name, pl.price, pl.title, pl.img from agent_inventory as ai left join product_list as pl on ai.product_id = pl.id where ai.school_id = " . $info->school_id . " and ai.num > 0 and ai.status = 0 group by pl.id order by ai.product_id desc limit " . $num;

        $products = $app->db->query($sql)->fetchAll();

        return $products;
    }

    // 根据标签获取商品
    public function getProductByTag($app, $tagId, $num, $page) {
        $customerId = $app->util->getCustomerId($app);
        $customerInfo = Customer::findFirst($customerId);

        if (!$app->util->getAssignFlag($app)) {
            return $this->getDayProductByTag($app, $customerInfo, $tagId, $num, $page);
        } else {
            return $this->getNightProductByTag($app, $customerInfo, $tagId, $num, $page);
        }
    }

    public function getDayProductByTag($app, $customerInfo, $tagId, $num, $page)
    {
        $offset = ($page - 1) * $num;
        $products = ProductListSchool::find([
            'conditions' => 'status=1 and school_id= ' . $customerInfo->school_id . ' and num > 0 and find_in_set(' . $tagId . ' ,tags)',
            'columns' => 'product_id, name, price, title, slogan, img',
            'limit' => $num,
            'order' => 'product_id desc',
            'offset' => $offset,
        ]);

        if (!$products) {
            return [];
        }

        $data = $products->toArray();

        foreach($data as $key => $item) {
            $data[$key]['id'] = $item['product_id'];
            unset($data[$key]['product_id']);
        }

        return $data;
    }

    public function getNightProductByTag($app, $customerInfo, $tagId, $num, $page)
    {
        $offset = ($page - 1) * $num;
        $sql = "select pl.id, pl.name, pl.price, pl.title, pl.slogan, pl.img from agent_inventory as ai left join product_list as pl on ai.product_id = pl.id where ai.school_id = " . $customerInfo->school_id . " and ai.room_id=" . $customerInfo->room_id . " and ai.num > 0 and ai.status = 0 and find_in_set(" . $tagId . ", pl.tags) group by pl.id order by ai.product_id desc limit " . $num . " offset " . $offset;

        $products = $app->db->query($sql)->fetchAll();

        return $products;
    }

    // 根据分类获取商品
    public function getProductByCategory($app, $categoryId, $num, $page, $level = 1) {
        $customerId = $app->util->getCustomerId($app);
        $customerInfo = Customer::findFirst($customerId);

        if (!$app->util->getAssignFlag($app)) {
            return $this->getDayProductByCategory($app, $customerInfo, $categoryId, $num, $page, $level);
        } else {
            return $this->getNightProductByCategory($app, $customerInfo, $categoryId, $num, $page, $level);
        }
    }

    public function getDayProductByCategory($app, $customerInfo, $categoryId, $num, $page, $level)
    {
        $offset = ($page - 1) * $num;

        if ($level == 1) {
            $products = ProductListSchool::find([
                'conditions' => 'status=1 and school_id= ' . $customerInfo->school_id . ' and num > 0 and category=' . $categoryId,
                'columns' => 'product_id, name, price, title, slogan, img',
                'limit' => $num,
                'order' => 'product_id desc',
                'offset' => $offset,
            ]);
        } else {
            $products = ProductListSchool::find([
                'conditions' => 'status=1 and school_id= ' . $customerInfo->school_id . ' and num > 0 and sub_category=' . $categoryId,
                'columns' => 'product_id, name, price, title, slogan, img',
                'limit' => $num,
                'order' => 'product_id desc',
                'offset' => $offset,
            ]);
        }

        if (!$products) {
            return [];
        }

        $data = $products->toArray();
        foreach($data as $key => $item) {
            $data[$key]['id'] = $item['product_id'];
            unset($data[$key]['product_id']);
        }

        return $data;
    }

    public function getNightProductByCategory($app, $customerInfo, $categoryId, $num, $page, $level)
    {
        $offset = ($page - 1) * $num;

        if ($level == 1) {
            $sql = "select pl.id, pl.name, pl.price, pl.title, pl.slogan, pl.img from agent_inventory as ai left join product_list as pl on ai.product_id = pl.id where ai.school_id = " . $customerInfo->school_id . " and ai.room_id=" . $customerInfo->room_id . " and ai.num > 0 and ai.status = 0 and category_id=" . $categoryId . " group by pl.id order by ai.product_id desc limit " . $num . " offset " . $offset;
        } else {
            $sql = "select pl.id, pl.name, pl.price, pl.title, pl.slogan, pl.img from agent_inventory as ai left join product_list as pl on ai.product_id = pl.id where ai.school_id = " . $customerInfo->school_id . " and ai.room_id=" . $customerInfo->room_id . " and ai.num > 0 and ai.status = 0 and sub_category= " . $categoryId . " group by pl.id order by ai.product_id desc limit " . $num . " offset " . $offset;
        }

        $products = $app->db->query($sql)->fetchAll();

        return $products;
    }

    // 首页标签商品
    public function getProductRecom($app, $num) {
        $customerId = $app->util->getCustomerId($app);
        $customerInfo = Customer::findFirst($customerId);

        if (!$app->util->getAssignFlag($app)) {
            // 白天
            return $this->getDayProductRecom($app, $num, $customerInfo);
        } else {
            // 晚上
            return $this->getNightProductRecom($app, $num, $customerInfo);
        }
    }

    public function getDayProductRecom($app, $num, $customerInfo) {
        $products = ProductListSchool::find([
            'conditions' => 'status=1 and school_id= ' . $customerInfo->school_id . ' and num > 0',
            'columns' => 'product_id, name, price, title, slogan, img',
            'limit' => $num,
            'order' => 'sale_num desc',
        ]);

        if (!$products) {
            return [];
        }

        $data = $products->toArray();

        foreach($data as $key => $item) {
            $data[$key]['id'] = $item['product_id'];
            unset($data[$key]['product_id']);
        }

        return $data;
    }

    public function getNightProductRecom($app, $num, $customerInfo) {
        $sql = "select pl.id, pl.name, pl.price, pl.title, pl.slogan, pl.img from agent_inventory as ai left join product_list as pl on ai.product_id = pl.id where ai.school_id = " . $customerInfo->school_id . " and ai.room_id=" . $customerInfo->room_id . " and ai.num > 0 and ai.status = 0 group by pl.id order by pl.sale_num desc limit " . $num;

        $products = $app->db->query($sql)->fetchAll();

        return $products;
    }

    // 搜索文本
    public function getProductSearch($app, $text) {
        $customerId = $app->util->getCustomerId($app);
        $customerInfo = Customer::findFirst($customerId);

        if (!$app->util->getAssignFlag($app)) {
            // 白天
            return $this->getDayProductSearch($app, $text, $customerInfo);
        } else {
            // 晚上
            return $this->getNightProductSearch($app, $text, $customerInfo);
        }
    }

    public function getDayProductSearch($app, $text, $customerInfo) {
        $products = ProductListSchool::find([
            'conditions' => 'status=1 and school_id= ' . $customerInfo->school_id . ' and num > 0 and name like "%' . $text . '%"',
            'columns' => 'product_id, name, price, title, slogan, img',
            'limit' => 10,
            'order' => 'sale_num desc',
        ]);

        if (!$products) {
            return [];
        }

        $data = $products->toArray();

        foreach($data as $key => $item) {
            $data[$key]['id'] = $item['product_id'];
            unset($data[$key]['product_id']);
        }

        return $data;
    }

    public function getNightProductSearch($app, $text, $customerInfo) {
        $sql = "select pl.id, pl.name, pl.price, pl.title, pl.slogan, pl.img from agent_inventory as ai left join product_list as pl on ai.product_id = pl.id where ai.school_id = " . $customerInfo->school_id . " and ai.room_id=" . $customerInfo->room_id . " and ai.num > 0 and ai.status = 0 and pl.name like '%" . $text . "%' group by pl.id order by pl.sale_num desc limit 10";

        $products = $app->db->query($sql)->fetchAll();

        return $products;
    }
}
