<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class OrderDeliveredMail extends Mailable
{
    use Queueable, SerializesModels;
    public $order;
    public $delivery;

    /**
     * Конструктор, що отримує користувача.
     */
    public function __construct($order, $delivery)
    {
        $this->order = $order;
        $this->delivery = $delivery;
    }

    /**
     * Налаштування листа.
     */
    public function build()
    {
        return $this->subject('Дякуємо Вам за покупки в нашому магазині!')
                    ->view('emails.order_delivered'); 
    }
}
