<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

class MakeAccount extends Command
{
    protected $signature = 'make:account';

    protected $description = 'Create a new user or admin account';

    public function handle(): int
    {
        $this->info('=== Create Account ===');

        $name = $this->ask('Full name');

        $email = $this->ask('Email address');
        while (! Validator::make(['email' => $email], ['email' => 'required|email|unique:users,email'])->passes()) {
            if (User::where('email', $email)->exists()) {
                $this->error('This email is already registered.');
            } else {
                $this->error('Invalid email format.');
            }
            $email = $this->ask('Email address');
        }

        $password = $this->secret('Password (min 8 characters)');
        while (strlen($password) < 8) {
            $this->error('Password must be at least 8 characters.');
            $password = $this->secret('Password (min 8 characters)');
        }

        $confirm = $this->secret('Confirm password');
        while ($password !== $confirm) {
            $this->error('Passwords do not match.');
            $confirm = $this->secret('Confirm password');
        }

        $role = $this->choice('Account type', ['user', 'admin'], 0);

        $this->newLine();
        $this->table(
            ['Field', 'Value'],
            [
                ['Name', $name],
                ['Email', $email],
                ['Role', $role === 'admin' ? 'Administrator' : 'Regular User'],
            ]
        );

        if (! $this->confirm('Create this account?', true)) {
            $this->info('Account creation cancelled.');

            return Command::SUCCESS;
        }

        $user = User::create([
            'name' => $name,
            'email' => $email,
            'password' => Hash::make($password),
            'is_admin' => $role === 'admin',
        ]);

        $this->newLine();
        $this->info('✓ Account created successfully!');
        $this->line("   ID:    {$user->id}");
        $this->line("   Name:  {$user->name}");
        $this->line("   Email: {$user->email}");
        $this->line('   Role:  '.($user->is_admin ? 'Administrator' : 'User'));

        return Command::SUCCESS;
    }
}
