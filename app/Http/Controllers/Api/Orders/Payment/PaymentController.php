<?php

namespace App\Http\Controllers\Api\Orders\Payment;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\Controller;
use App\Mail\OrderDetailsMail;
use App\Models\Delivery;
use App\Models\Order;
use App\Models\Payment;
use App\Services\Order\Payment\LiqPayService;
use Illuminate\Support\Facades\Mail;

class PaymentController extends Controller
{
    protected $liqPayService;

    public function __construct(LiqPayService $liqPayService)
    {
        $this->liqPayService = $liqPayService;
    }

    public function createPayment(Request $request)
    {
        $request->validate([
            'amount' => 'required|numeric|min:1',
            'order_id' => 'required|numeric|min:1',
            'description' => 'required|string',
        ]);
    
        $form = $this->liqPayService->createPayment(
            $request->amount,
            $request->order_id,
            $request->description
        );
    
        return response()->json(['form' => $form]);
    }
    public function callback(Request $request)
    {
        Log::info('LiqPay Callback received', $request->all());  
    
        // Get encoded data
        $dataEncoded = $request->input('data');
        $signatureReceived = $request->input('signature');
    
        // Decode data
        $decodedData = json_decode(base64_decode($dataEncoded), true);
    
        if (!$decodedData) {
            Log::error('Failed to decode LiqPay callback data');
            return response()->json(['message' => 'Invalid callback data'], 400);
        }
    
        Log::info('Decoded data', $decodedData);
    
        //Validate signature
        if (!$this->liqPayService->validateCallbackSignature($dataEncoded, $signatureReceived)) {
            Log::error('Invalid callback signature');
            return response()->json(['message' => 'Invalid callback signature'], 400);
        }
    
        Log::info('Signature is valid');
    
        //Update payment
        $payment = Payment::where('order_id', $decodedData['order_id'])->first();
        if (!$payment) {
            return response()->json(['message' => 'Payment record not found'], 404);
        }
    
        // Define payment status
        $liqpayStatus = $decodedData['status'];
        if ($liqpayStatus === 'success' || $liqpayStatus === 'sandbox') { 
            $payment->status = 'Оплачено';
            $payment->paid_at = now();
        } elseif (in_array($liqpayStatus, ['failure', 'error', 'reversed'])) {
            $payment->status = 'Помилка';
        } else {
            $payment->status = 'В очікуванні';
        }
        
        $payment->transaction_number = $decodedData['transaction_id'];
        $payment->save();

        $order = Order::findOrFail( $decodedData['order_id']);
        $delivery = Delivery::where('order_id',$decodedData['order_id'])->first();
        
        Mail::to($order->user->email)->send(new OrderDetailsMail($order, $delivery, $payment));
    
        return response()->json(['message' => 'Callback processed successfully']);
    }
    
}
