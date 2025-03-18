<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\User;
use Illuminate\Support\Carbon;

class DeleteUnverifiedUsers extends Command
{
    protected $signature = 'users:delete-unverified';
    protected $description = 'Removes unverified users after code expiration';

    public function handle()
    {
        $deleted = User::whereNull('email_verified_at')
            ->where('verification_expires_at', '<', Carbon::now())
            ->delete();

        $this->info("Видалено $deleted непідтверджених користувачів.");
    }
}
