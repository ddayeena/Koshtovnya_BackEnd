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
        $subject = $this->admin->role === 'admin' 
            ? 'Запрошення стати адміністратором' 
            : 'Запрошення стати менеджером';

        return $this->subject($subject)
                    ->view('emails.admin_invitation');
    }
}
