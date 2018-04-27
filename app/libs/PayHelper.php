<?php
use Biaoye\Model\CustomerPay;

class PayHelper
{
    public function handle($app, $payId)
    {
        $payInfo = CustomerPay::findFirst($payId);

        if (!$payInfo) {
            throw new BusinessException(1000, '未找到支付id' . $payId);
        }

        if ($payInfo->pay_type == 0) {
            return $this->handleAlipay($app, $payInfo);
        }

        if ($payInfo->pay_type == 1) {
            return $this->handleWechat($app, $payInfo);
        }
    }

    public function handleAlipay($app, $info) {
        $info->pay_money = 0.01;
        $alipayParams = [
            'subject' => '商城订单',
            'out_trade_no' => $info->out_trade_no,
            'timeout_express' => '30m',
            'total_amount' => $info->pay_money,
            'product_code' => 'QUICK_WAP_WAY'
        ];

        $terminal = $info->terminal;

        $ret = [];
        // 手机支付
        if ($terminal == 'wap') {
            $script = AlipayHelper::wappay($app, $alipayParams);
            $ret['order_id'] = $info->order_id;
            $ret['terminal'] = 'aliwap';
            $ret['script'] = $script;
        }

        return $ret;
    }

    public function handleWechat($app, $info) {
        $info->pay_money = 0.01;
        $wxpayParams = [
            'subject' => '商城订单',
            'out_trade_no' => $info->out_trade_no,
            'total_amount' => $info->pay_money * 100, // 微信以分位单位
            'trade_type' => 'JSAPI'
        ];

        $terminal = $info->terminal;

        if ($terminal == 'wechat') {
            $wxpayParams['openid'] = $info->openid;
        } else {
            $wxpayParams['trade_type'] = 'MWEB';
        }

        $ret = WxpayHelper::pay($app, $wxpayParams);

        if (isset($ret['return_code']) && $ret['return_code'] == 'SUCCESS') {
            // 微信内部浏览器支付
            if ($terminal == 'wechat') {
                $output = [];
                $output['appId'] = $ret['appid'];
                $output['nonceStr'] = $ret['nonce_str'];
                $output['signType'] = 'MD5';
                $output['package'] = "prepay_id=" . $ret['prepay_id'];
                $output['timeStamp'] = time();

                $paySign = WxpayHelper::buildSign($app, $output);
                $output['paySign'] = $paySign;

                return [
                    'params' => $output,
                    'terminal' => 'wechat',
                    'out_trade_no' => $payData['out_trade_no'],
                    'order_id' => $info->order_id,
                ];
            }

            // 外部支付
            if ($terminal == 'wap') {
                $output = [];
                $output['order_id'] = $info->order_id;
                $output['terminal'] = 'wap';
                $output['mweb_url'] = $ret['mweb_url'] . '&redirect_url=' . urlencode('http://qr.guoguojia.vip/home/paymentStatus.html?id=' . $info->order_id . '&out_trade_no=' . $info->out_trade_no);

                return $output;
            }
        }

        return [];
    }
}
