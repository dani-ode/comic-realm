<?php

namespace Database\Seeders;

use App\Domain\User\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    /**
     * Hardcode yang diperbolehkan:
     * - Email & username akun test yang predictable (untuk login mudah di dev)
     * - Password default development (password)
     * - Role & status awal yang memang sudah ditentukan
     */
    public function run(): void
    {
        // ── Admin ──────────────────────────────────────────────────────────────
        $admin = User::firstOrCreate(
            ['email' => 'admin@comicrealm.test'],
            [
                'name'     => 'Admin ComicRealm',
                'username' => 'admin',
                'password' => Hash::make('password123'),
                'role'     => 'admin',
                'status'   => 'active',
            ]
        );
        $admin->profile()->firstOrCreate([]);

        // ── Regular Reader ─────────────────────────────────────────────────────
        $reader = User::firstOrCreate(
            ['email' => 'reader@comicrealm.test'],
            [
                'name'     => 'Budi Santoso',
                'username' => 'budisantoso',
                'password' => Hash::make('password123'),
                'role'     => 'user',
                'status'   => 'active',
            ]
        );
        $reader->profile()->firstOrCreate([]);

        // ── Readers tambahan untuk interaksi yang beragam ──────────────────────
        $readerNames = [
            ['name' => 'Rina Wijaya',    'username' => 'rinawijaya',    'email' => 'rina@comicrealm.test'],
            ['name' => 'Ahmad Fauzi',    'username' => 'ahmadfauzi',    'email' => 'fauzi@comicrealm.test'],
            ['name' => 'Siti Rahayu',    'username' => 'sitirahayu',    'email' => 'siti@comicrealm.test'],
            ['name' => 'Deni Kurniawan', 'username' => 'denikurniawan', 'email' => 'deni@comicrealm.test'],
        ];

        foreach ($readerNames as $r) {
            $u = User::firstOrCreate(
                ['email' => $r['email']],
                [
                    'name'     => $r['name'],
                    'username' => $r['username'],
                    'password' => Hash::make('password123'),
                    'role'     => 'user',
                    'status'   => 'active',
                ]
            );
            $u->profile()->firstOrCreate([]);
        }

        // ── Seeders terurut ────────────────────────────────────────────────────
        $this->call([
            GenreSeeder::class,
            PublisherSeeder::class,
            ComicSeeder::class,
            InteractionSeeder::class,
            PayoutSeeder::class,
        ]);
    }
}
