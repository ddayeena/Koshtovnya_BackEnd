<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class WelcomeMail extends Mailable
{
    use Queueable, SerializesModels;
    public $user;

    /**
     * Конструктор, що отримує користувача.
     */
    public function __construct($user)
    {
        $this->user = $user;
    }

    public function build()
    {
        return $this->subject('Вітаємо в нашому магазині!')
                    ->view('emails.welcome'); // Шаблон для повідомлення
    }
}
