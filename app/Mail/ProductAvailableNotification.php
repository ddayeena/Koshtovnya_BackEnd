<?php

namespace App\Mail;

use Illuminate\Mail\Mailable;

class ProductAvailableNotification extends Mailable
{
    public $user;
    public $product;

    public function __construct($user, $product)
    {
        $this->user = $user;
        $this->product = $product;
    }

    public function build()
    {
        return $this->subject('Товар знову в наявності!')
                    ->view('emails.product_available_notification');
    }
}
