<?php

namespace App\Mail;

use Illuminate\Mail\Mailable;

class AdminInvitation extends Mailable
{
    public $admin;
    public $password;

    public function __construct($admin, $password)
    {
        $this->admin = $admin;
        $this->password = $password;
    }

    public function build()
    {
        $subject = $this->admin->role === 'manager' 
            ? 'Запрошення стати менеджером' 
            : 'Запрошення стати адміністратором';

        return $this->subject($subject)
                    ->view('emails.admin_invitation');
    }
}
