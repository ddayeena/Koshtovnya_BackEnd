<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class BanMail extends Mailable
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

    /**
     * Налаштування листа.
     */
    public function build()
    {
        if ($this->user->is_permanently_banned)  $subject = 'Вас назавжди заблоковано';  
        else $subject = 'Вас заблоковано на 3 дня';
        return $this->subject($subject)
                    ->view('emails.ban'); 
    }
}
