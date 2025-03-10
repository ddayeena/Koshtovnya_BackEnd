<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class OrderShippedMail extends Mailable
{
    use Queueable, SerializesModels;
    public $order;

    /**
     * Конструктор, що отримує користувача.
     */
    public function __construct($order)
    {
        $this->order = $order;
    }

    /**
     * Налаштування листа.
     */
    public function build()
    {
        return $this->subject('Ваше замовлення успішно відправлене!')
                    ->view('emails.order_shipped'); 
    }
}
