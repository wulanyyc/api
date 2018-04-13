<?php
namespace Biaoye\Model;

use Phalcon\Mvc\Model;
use Biaoye\Model\Product;

class CustomerCart extends Model
{
    public function getSource() {
        return "customer_cart";
    }

    public static function getCart($id) {
        $cart = self::findFirst($id)->cart;
        $cartArr = json_decode($cart, true);

        $products = [];
        foreach($cartArr as $key => $item) {
            $item['name'] = Product::findFirst($item['id'])->name;
            $item['pid'] = $item['id'];
            unset($item['id']);
            $products[] = $item;
        }

        return $products;
    }
}
