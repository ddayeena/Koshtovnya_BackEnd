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

    public function createPayment($amount, $order_id, $description)
    {
        $params = [
            'action'         => 'pay',
            'amount'         => $amount,
            'currency'       => 'UAH',
            'description'    => $description,
            'order_id'       => $order_id,
            'version'        => '3',
            'sandbox'        => 1, // 1 - test, 0 - not test
            'server_url'     => 'https://0d24-176-121-4-31.ngrok-free.app/api/liqpay-callback',
            'result_url'     => url('http://localhost:8080/payment-confirmed') // After successfull payment
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
