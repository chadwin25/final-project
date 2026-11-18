<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Validator;

class CreateStaff extends Command
{
    protected $signature = 'staff:create';

    protected $description = 'Create a staff account using a private password prompt';

    public function handle(): int
    {
        $username = trim((string) $this->ask('Username'));
        $fullName = trim((string) $this->ask('Full name'));
        $password = (string) $this->secret('Password (at least 12 characters)');
        $confirmation = (string) $this->secret('Confirm password');

        if ($password !== $confirmation) {
            $this->error('The passwords do not match. No account was created.');

            return self::FAILURE;
        }

        $validator = Validator::make(
            [
                'username' => $username,
                'full_name' => $fullName,
                'password' => $password,
            ],
            [
                'username' => ['required', 'string', 'alpha_dash', 'min:3', 'max:50', 'unique:users,username'],
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
            'username' => $username,
            'full_name' => $fullName,
            'password' => $password,
        ]);

        $this->info("Staff account '{$username}' created.");

        return self::SUCCESS;
    }
}