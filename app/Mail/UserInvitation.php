<?php

namespace App\Mail;

use Illuminate\Mail\Mailable;

class UserInvitation extends Mailable
{
    public $user;
    public $password;

    public function __construct($user, $password)
    {
        $this->user = $user;
        $this->password = $password;
    }

    public function build()
    {
        if ($this->user->role === 'manager')   $subject = 'Запрошення стати менеджером';  
        elseif ($this->user->role === 'admin') $subject = 'Запрошення стати адміністратором'; 
        elseif ($this->user->role === 'user')  $subject = 'Запрошення стати користувачем';
        else $subject = 'Запрошення';
    
        return $this->subject($subject)
                    ->view('emails.user_invitation');
    }
    
}
