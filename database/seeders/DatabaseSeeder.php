<?php

namespace Database\Seeders;

use App\Domain\User\Models\User;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // Default Admin User
        $admin = User::firstOrCreate(
            ['email' => 'admin@comicrealm.test'],
            [
                'name' => 'Admin ComicRealm',
                'username' => 'admin',
                'password' => bcrypt('password123'),
                'role' => 'admin',
                'status' => 'active',
            ]
        );
        $admin->profile()->firstOrCreate([]);

        // Default Regular User
        $user = User::firstOrCreate(
            ['email' => 'user@comicrealm.test'],
            [
                'name' => 'Regular Reader',
                'username' => 'reader',
                'password' => bcrypt('password123'),
                'role' => 'user',
                'status' => 'active',
            ]
        );
        $user->profile()->firstOrCreate([]);

        $this->call([
            GenreSeeder::class,
            ComicSeeder::class,
        ]);
    }
}
