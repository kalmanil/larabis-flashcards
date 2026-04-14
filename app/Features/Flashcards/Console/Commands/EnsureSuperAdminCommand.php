<?php

namespace App\Features\Flashcards\Console\Commands;

use App\Features\Auth\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class EnsureSuperAdminCommand extends Command
{
    protected $signature = 'flashcards:ensure-superadmin';

    protected $description = 'Create or promote the superadmin from FLASHCARDS_SUPERADMIN_EMAIL (optional FLASHCARDS_SUPERADMIN_PASSWORD)';

    public function handle(): int
    {
        $email = env('FLASHCARDS_SUPERADMIN_EMAIL');
        if (!$email) {
            $this->error('Set FLASHCARDS_SUPERADMIN_EMAIL in the tenant .env (tenants/flashcards/.env).');

            return self::FAILURE;
        }

        $other = User::query()
            ->where('role', User::ROLE_SUPERADMIN)
            ->where('email', '!=', $email)
            ->exists();

        if ($other) {
            $this->error('Another superadmin already exists. Demote them before promoting a different email.');

            return self::FAILURE;
        }

        $existing = User::where('email', $email)->first();
        $plain = env('FLASHCARDS_SUPERADMIN_PASSWORD');

        if ($existing) {
            if ($existing->isSuperAdmin()) {
                $this->info('Superadmin already in place: '.$email);

                return self::SUCCESS;
            }

            $updates = ['role' => User::ROLE_SUPERADMIN];
            if ($plain) {
                $updates['password'] = Hash::make($plain);
            }
            $existing->update($updates);
            $this->info('User promoted to superadmin: '.$email);

            return self::SUCCESS;
        }

        if (!$plain) {
            $plain = Str::password(24);
            $this->warn('Generated password (store securely, not shown again): '.$plain);
        }

        User::create([
            'name' => 'Superadmin',
            'email' => $email,
            'password' => Hash::make($plain),
            'role' => User::ROLE_SUPERADMIN,
        ]);

        $this->info('Created superadmin: '.$email);

        return self::SUCCESS;
    }
}
