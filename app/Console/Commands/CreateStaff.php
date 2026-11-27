<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

class CreateStaff extends Command
{
    protected $signature = 'staff:create';

    protected $description = 'Create a staff account using a private password prompt';

    public function handle(): int
    {
        $email = mb_strtolower(trim((string) $this->ask('Email address')));
        $fullName = trim((string) $this->ask('Full name'));
        $password = (string) $this->secret('Password (at least 12 characters)');
        $confirmation = (string) $this->secret('Confirm password');

        if ($password !== $confirmation) {
            $this->error('The passwords do not match. No account was created.');

            return self::FAILURE;
        }

        $validator = Validator::make(
            [
                'email' => $email,
                'full_name' => $fullName,
                'password' => $password,
            ],
            [
                'email' => ['required', 'email:rfc', 'max:100', 'unique:users,email', 'unique:customers,email'],
                'full_name' => ['required', 'string', 'max:100'],
                'password' => ['required', 'string', 'min:12', 'max:255'],
            ]
        );

        if ($validator->fails()) {
            foreach ($validator->errors()->all() as $message) {
                $this->error($message);
            }

            $this->error('No account was created.');

            return self::FAILURE;
        }

        User::create([
            'username' => (string) Str::uuid(),
            'email' => $email,
            'full_name' => $fullName,
            'password' => $password,
        ]);

        $this->info("Staff account '{$email}' created.");

        return self::SUCCESS;
    }
}
