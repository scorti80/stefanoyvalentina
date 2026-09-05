<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Hash;

class CreateAdmin extends Command
{
    protected $signature = 'wedding:create-admin {--name=} {--email=} {--password=}';

    protected $description = 'Create or update the wedding gallery administrator';

    public function handle(): int
    {
        $name = $this->option('name') ?: $this->ask('Name', 'Administrator');
        $email = $this->option('email') ?: $this->ask('Email');
        $password = $this->option('password') ?: $this->secret('Password');

        if (! filter_var($email, FILTER_VALIDATE_EMAIL) || strlen((string) $password) < 10) {
            $this->error('Use a valid email and a password of at least 10 characters.');

            return self::FAILURE;
        }

        User::query()->updateOrCreate(
            ['email' => $email],
            ['name' => $name, 'password' => Hash::make($password)],
        );

        $this->info("Administrator {$email} is ready.");

        return self::SUCCESS;
    }
}
