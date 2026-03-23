<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Hash;

class VerifyLoginAccounts extends Command
{
    protected $signature = 'verify:login-accounts';
    protected $description = 'Verify that all login accounts exist with correct credentials';

    public function handle()
    {
        $this->info('🔍 Verifying login accounts...');
        $this->newLine();

        $accounts = [
            ['email' => 'admin@millenaire.cm', 'password' => 'Admin@2025!', 'role' => 'admin'],
            ['email' => 'teacher@millenaire.cm', 'password' => 'Teacher@2025!', 'role' => 'teacher'],
            ['email' => 'parent@millenaire.cm', 'password' => 'Parent@2025!', 'role' => 'parent'],
            ['email' => 'student@millenaire.cm', 'password' => 'Student@2025!', 'role' => 'student'],
        ];

        foreach ($accounts as $account) {
            $user = User::where('email', $account['email'])->first();

            if (!$user) {
                $this->error("✗ {$account['email']} - NOT FOUND");
                continue;
            }

            // Check role
            if ($user->role !== $account['role']) {
                $this->warn("⚠ {$account['email']} - Role mismatch (expected: {$account['role']}, got: {$user->role})");
            }

            // Check password
            if (!Hash::check($account['password'], $user->password)) {
                $this->error("✗ {$account['email']} - Password doesn't match!");
                continue;
            }

            // Check if active
            if (!$user->is_active) {
                $this->warn("⚠ {$account['email']} - Account is INACTIVE");
                continue;
            }

            // Check if must change password
            if ($user->must_change_password) {
                $this->warn("⚠ {$account['email']} - Must change password on first login");
            }

            $this->line("✓ {$account['email']} - OK");
        }

        $this->newLine();
        $this->info('Login accounts verification complete!');
    }
}
