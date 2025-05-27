<?php

namespace App\Services\Order\Payment;

use LiqPay;

class LiqPayService
{
    protected $liqpay;

    public function __construct()
    {
        $this->liqpay = new LiqPay(env('LIQPAY_PUBLIC_KEY'), env('LIQPAY_PRIVATE_KEY'));
    }

    public function createPayment($amount, $order_id, $description, $currency = 'UAH')
    {
        $params = [
            'action'         => 'pay',
            'amount'         => $amount,
            'currency'       => strtoupper($currency),
            'description'    => $description,
            'order_id'       => $order_id,
            'version'        => '3',
            'sandbox'        => 1,
            'server_url'     => 'https://koshtovnya.api-dev.bmax-edu.website/api/liqpay-callback',
            'result_url'     => url('https://koshtovnya-front-end-33rd.vercel.app/payment-confirmed')
        ];
    
        return $this->liqpay->cnb_form($params);
    }
    
    public function validateCallbackSignature(string $dataEncoded, string $receivedSignature)
    {
        $privateKey = env('LIQPAY_PRIVATE_KEY');

        $calculatedSignature = base64_encode(sha1($privateKey . $dataEncoded . $privateKey, true));

        return $calculatedSignature === $receivedSignature;
    }
}
