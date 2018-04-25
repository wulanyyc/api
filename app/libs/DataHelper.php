<?php
use Biaoye\Model\School;
use Biaoye\Model\Room;
use Biaoye\Model\Product;
use Biaoye\Model\ProductListSchool;
use Biaoye\Model\ProductCategory;
use Biaoye\Model\Customer;
use Biaoye\Model\CustomerCoupon;
use Biaoye\Model\CustomerCouponUse;
use Biaoye\Model\CustomerOrder;
use Biaoye\Model\CustomerPay;
use Biaoye\Model\CustomerCart;
use Phalcon\Mvc\Model\Transaction\Manager;

class DataHelper
{
    public function getSchoolName($app, $id) {
        $key = "school_" . $id;

        $cache = $app->redis->get($key);

        if ($cache) return $cache;

        $info = School::findFirst($id);

        if ($info) {
            $app->redis->setex($key, 604800, $info->name);
            return $info->name;
        }

        return '';
    }

    public function getRoomName($app, $id) {
        $key = "room_" . $id;

        $cache = $app->redis->get($key);

        if ($cache) return $cache;

        $info = Room::findFirst($id);

        if ($info) {
            $app->redis->setex($key, 604800, $info->name);
            return $info->name;
        }

        return '';
    }

    public function checkCouponStatus($app, $couponId, $products) {
        $couponInfo = CustomerCoupon::findFirst($couponId);
        $config = json_decode($couponInfo->config, true);

        if ($couponInfo->type == 1) {
            if (isset($config['limit_money'])) {
                $total = 0;
                foreach($products as $product) {
                    $money = $product['num'] * $app->product->getProductPrice($product['id']);
                    $total += $money;
                }

                if ($total > $config['limit_money']) {
                    return 1;
                } else {
                    return 0;
                }
            }

            return 1;
        }

        if ($couponInfo->type == 2) {
            $data = [];
            $total = 0;

            foreach($products as $product) {
                $info = Product::findFirst($product['id']);

                $money = $product['num'] * $app->product->getProductPrice($product['id']);

                if (!isset($data['category'][$info->category]['total'])) {
                    $data['category'][$info->category]['total'] = $money;
                } else {
                    $data['category'][$info->category]['total'] += $money;
                }
                
                if (!isset($data['category'][$info->sub_category]['total'])) {
                    $data['category'][$info->sub_category]['total'] = $money;
                } else {
                    $data['category'][$info->sub_category]['total'] += $money;
                }

                $total += $money;
            }

            if (empty($config['category'])) {
                if ($total >= $config['limit_money']) {
                    return 1;
                }
            } else {
                $categoryTotal = 0;
                foreach($config['category'] as $item) {
                    if (isset($data['category'][$item])) {
                        $categoryTotal += $data['category'][$item]['total'];
                    }
                }

                if ($categoryTotal >= $config['limit_money']) {
                    return 1;
                }
            }
        }

        if ($couponInfo->type == 3) {
            $data = [];
            $total = 0;

            foreach($products as $product) {
                $info = Product::findFirst($product['id']);

                $money = $product['num'] * $app->product->getProductPrice($product['id']);

                if (!isset($data['factory'][$info->factory]['total'])) {
                    $data['factory'][$info->factory]['total'] = $money;
                } else {
                    $data['factory'][$info->factory]['total'] += $money;
                }
                
                $total += $money;
            }

            $factoryTotal = 0;
            foreach($config['factory'] as $item) {
                if (isset($data['factory'][$item])) {
                    $factoryTotal += $data['factory'][$item]['total'];
                }
            }

            if ($factoryTotal >= $config['limit_money']) {
                return 1;
            }
        }

        return 0;
    }

    public function checkInventory($app, $products) {
        $ret = [
            'status' => true,
        ];

        foreach($products as $product) {
            $info = ProductListSchool::findFirst($product['id']);
            $inventoryNum = $info->num;

            if ($inventoryNum < $product['num']) {
                $ret['product'] = $info->name;
                $ret['num'] = $info->num;
                $ret['status'] = false;
                break;
            }
        }

        return $ret;
    }

    public function calculateOrderPrice($app, $data) {
        $products = json_decode($data['products'], true);
        $coupons = !empty($data['coupon_ids']) ? explode(',', $data['coupon_ids']) : [];
        $productPrice = 0;

        foreach($products as $product) {
            $productPrice += $product['num'] * $app->product->getProductPrice($product['id']);
        }

        $couponFee = 0;
        if (!empty($coupons)) {
            $customerId = $app->util->getCustomerId($app);
            foreach($coupons as $coupon) {
                $cnt = CustomerCouponUse::count("customer_id=" . $customerId . " and coupon_id=" . $coupon . " and use_status=0");

                if ($cnt > 0) {
                    $status = $app->data->checkCouponStatus($app, $coupon, $products);
                    if ($status == 1) {
                        $couponFee += CustomerCoupon::findFirst($coupon)->money;
                    }
                }
            }
        }

        $expressFee = $app->config->params->express_fee;

        $payMoney = $productPrice + $expressFee - $couponFee;
        if ($payMoney < 0) {
            $payMoney = 0;
        }

        $deliverFee = round($expressFee * $app->config->params->deliver_fee_rate, 2);
        $productSalary = round(($productPrice - $couponFee) * $app->config->params->order_salary_rate, 2);
        $totalSalary = $productSalary + $deliverFee;

        return [
            'product_price' => $productPrice,
            'express_fee' => $expressFee,
            'pay_money' => $payMoney,
            'deliver_fee' => $deliverFee,
            'product_salary' => $productSalary,
            'total_salary' => $totalSalary,
            'coupon_fee' => $couponFee,
        ];
    }

    public function handlePayOkOrder($app, $orderId, $tradeNo) { 
        try {
            $manager = new Manager();
            $transaction = $manager->get();

            // 更新支付表状态
            $up = CustomerPay::findFirst('order_id=' . $orderId);
            $up->setTransaction($transaction);
            $up->trade_no = $tradeNo;
            $up->pay_result = 1;

            if (!$up->save()) {
                $transaction->rollback("update CustomerPay fail " . $orderId);
            }

            // 更新订单支付状态
            $uporder = CustomerOrder::findFirst($orderId);
            $uporder->setTransaction($transaction);
            $uporder->status = 1;

            if (!$uporder->save()) {
                $transaction->rollback("update CustomerOrder fail " . $orderId);
            }

            // 更新余额
            $wallet = $uporder->pay_wallet;
            if ($wallet > 0) {
                $customerUp = Customer::findFirst($uporder->customer_id);
                $customerUp->setTransaction($transaction);
                $customerUp->money = $customerUp->money - $wallet;

                if (!$customerUp->save()) {
                    $transaction->rollback("update Customer money fail " . $uporder->customer_id . "_" . $wallet);
                }
            }

            // 更新券
            $coupons = trim($uporder->coupon_ids);
            if (!empty($coupons)) {
                $couponArr = explode(',', $coupons);
                $couponStr = implode(',', $couponArr)
                $couponUp = CustomerCouponUse::find([
                    "conditions" => "use_status=0 and customer_id=" . $uporder->customer_id . " and coupon_id in (" . $couponStr . ")",
                ]);

                $couponUp->setTransaction($transaction);
                $couponUp->use_status = 1;
                if (!$couponUp->save()) {
                    $transaction->rollback("update CustomerCouponUse fail " . $uporder->customer_id . "_" . $coupons );
                }
            }

            // 更新购物车
            $cartUp = CustomerCart::findFirst($uporder->cart_id);
            if ($cartUp) {
                $cartProducts = json_decode($cartUp->cart, true);
                if (!empty($cartProducts)) {
                    $cartUp->setTransaction($transaction);

                    $products = json_decode($uporder->products, true);
                    foreach($cartProducts as $key => $item) {
                        foreach($products as $product) {
                            if ($item['id'] == $product['id']) {
                                unset($cartProducts[$key]);
                            }
                        }
                    }

                    $cartUp->cart = json_encode($cartProducts);
                    if (!$cartUp->save()) {
                        $transaction->rollback("update CustomerCart fail " . $uporder->cart_id);
                    }
                } 
            }

            $transaction->commit();

            return 1;
        } catch (Phalcon\Mvc\Model\Transaction\Failed $e) {
            $msg = $e->getMessage();
            $app->logger->error("handlePayOkOrder fail: " . $msg);
        
            throw new BusinessException(1000, '更新失败');
        }
    }
}
