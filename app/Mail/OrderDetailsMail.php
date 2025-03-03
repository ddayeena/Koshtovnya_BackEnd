<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class OrderDetailsMail extends Mailable
{
    use Queueable, SerializesModels;
    public $order;
    public $delivery;
    public $payment;

    /**
     * Конструктор, що отримує користувача.
     */
    public function __construct($order, $delivery, $payment)
    {
        $this->order = $order;
        $this->delivery = $delivery;
        $this->payment = $payment;
    }

    /**
     * Налаштування листа.
     */
    public function build()
    {
        return $this->subject('Дякуємо за замовлення в нашому магазині!')
                    ->view('emails.order'); // Шаблон для повідомлення
    }
}
