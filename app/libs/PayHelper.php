<?php
use Biaoye\Model\CustomerPay;

class PayHelper
{
    public function handle($app, $payId)
    {
        $payInfo = CustomerPay::findFirst($payId);

        if ($payInfo->pay_type == 0) {
            $this->handleAlipay($app, $payInfo);
        }

        if ($payInfo->pay_type == 1) {
            $this->handleWechat($app, $payInfo);
        }
    }

    public function handleAlipay($app, $info) {
        $alipayParams = [
            'subject' => '商城订单',
            'out_trade_no' => $info['out_trade_no'],
            'timeout_express' => '30m',
            'total_amount' => $info['pay_money'],
            'product_code' => 'QUICK_WAP_WAY'
        ];

        $terminal = $info['terminal'];

        $ret = [];
        // 手机支付
        if ($terminal == 'wap') {
            $ret = AlipayHelper::wappay($app, $alipayParams);
        }

        return $ret;
    }

    public function handleWechat($app, $info) {
        $wxpayParams = [
            'subject' => '商城订单',
            'out_trade_no' => $info['out_trade_no'],
            'total_amount' => $info['pay_money'] * 100, // 微信以分位单位
            'trade_type' => 'JSAPI'
        ];

        if ($terminal == 'wechat') {
            $wxpayParams['openid'] = $openid;
        } else {
            $wxpayParams['trade_type'] = 'MWEB';
        }

        $ret = WxpayHelper::pay($wxpayParams);

        if (isset($ret['return_code']) && $ret['return_code'] == 'SUCCESS') {
            // 微信内部浏览器支付
            if ($terminal == 'wechat') {
                $output = [];
                $output['appId'] = $ret['appid'];
                $output['nonceStr'] = $ret['nonce_str'];
                $output['signType'] = 'MD5';
                $output['package'] = "prepay_id=" . $ret['prepay_id'];
                $output['timeStamp'] = time();

                $paySign = WxpayHelper::buildSign($output);
                $output['paySign'] = $paySign;

                return (['data' => $output, 'out_trade_no' => $payData['out_trade_no']]);
            }

            // 外部支付
            if ($terminal == 'wap') {
                $output = [];
                $output['terminal'] = 'wap';
                $output['mweb_url'] = $ret['mweb_url'] . '&redirect_url=' . urlencode('http://guoguojia.vip/pay/?out_trade_no=' . $payData['out_trade_no']);

                return $output;
            }
        }
    }
}