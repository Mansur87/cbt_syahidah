<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('username', 50)->nullable()->unique()->after('name');
        });

        DB::table('users')
            ->select(['id', 'email'])
            ->orderBy('id')
            ->chunkById(100, function ($users): void {
                foreach ($users as $user) {
                    $baseUsername = Str::limit(Str::slug(Str::before($user->email, '@')), 40, '');
                    $baseUsername = $baseUsername !== '' ? $baseUsername : "user-{$user->id}";
                    $username = $baseUsername;
                    $suffix = 1;

                    while (DB::table('users')->where('username', $username)->exists()) {
                        $suffix++;
                        $username = "{$baseUsername}-{$suffix}";
                    }

                    DB::table('users')
                        ->where('id', $user->id)
                        ->update(['username' => $username]);
                }
            });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropUnique(['username']);
            $table->dropColumn('username');
        });
    }
};
