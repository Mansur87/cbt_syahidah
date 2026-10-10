<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $email = env('CBT_ADMIN_EMAIL');
        $password = env('CBT_ADMIN_PASSWORD');

        if (! filled($email) || ! filled($password)) {
            return;
        }

        User::query()->firstOrCreate(
            ['email' => $email],
            [
                'name' => env('CBT_ADMIN_NAME', 'Administrator Syahidah'),
                'username' => Str::lower(env('CBT_ADMIN_USERNAME') ?: Str::before($email, '@')),
                'password' => $password,
                'role' => 'admin',
            ],
        );
    }
}
