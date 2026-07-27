<?php

namespace Database\Seeders;

use App\Domain\Publisher\Models\PublisherProfile;
use App\Domain\User\Models\User;
use App\Domain\Wallet\Models\PublisherWallet;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class PublisherSeeder extends Seeder
{
    /**
     * Data publisher mencerminkan skenario nyata:
     * - 2 studio approved dengan rekam jejak earning yang masuk akal
     * - 1 studio pending (baru daftar, belum ada earning)
     * - 1 studio rejected (ditolak, belum ada earning)
     * - 1 studio blocked (sudah pernah earning, lalu diblokir)
     *
     * Hardcode yang diperbolehkan:
     * - Email & username (identitas unik untuk testing)
     * - Bank dummy (tidak melibatkan rekening nyata)
     * - Status verifikasi (sesuai skenario masing-masing)
     */
    public function run(): void
    {
        $publishers = [
            // ── Studio 1: Aktif & Produktif ────────────────────────────────────
            [
                'user' => [
                    'email'    => 'publisher@comicrealm.test',
                    'name'     => 'Dani M.',
                    'username' => 'dani',
                    'role'     => 'publisher',
                    'status'   => 'active',
                ],
                'profile' => [
                    'brand_name'          => 'Dani Comic Studio',
                    'slug'                => 'dani-comic-studio',
                    'bio'                 => 'Studio komik indie yang berfokus pada genre action dan fantasy. Berkarya sejak 2022.',
                    'bank_name'           => 'BCA',
                    'bank_account_number' => '1234567890',
                    'bank_account_name'   => 'Dani M',
                    'verification_status' => 'approved',
                    'approved_at'         => now()->subMonths(8),
                ],
                // balance = total_earned - total_withdrawn (dihitung riil dari penjualan bab komik)
                'wallet' => [
                    'balance'         => 0,
                    'total_earned'    => 0,
                    'total_withdrawn' => 0,
                ],
            ],

            // ── Studio 2: Aktif tapi lebih kecil ──────────────────────────────
            [
                'user' => [
                    'email'    => 'realm@comicrealm.test',
                    'name'     => 'Ari Setiawan',
                    'username' => 'arisetiawan',
                    'role'     => 'publisher',
                    'status'   => 'active',
                ],
                'profile' => [
                    'brand_name'          => 'Realm Art Studio',
                    'slug'                => 'realm-art-studio',
                    'bio'                 => 'Kolektif seniman independen yang menggabungkan gaya manhwa dan komik Jepang.',
                    'bank_name'           => 'Mandiri',
                    'bank_account_number' => '0987654321',
                    'bank_account_name'   => 'Ari Setiawan',
                    'verification_status' => 'approved',
                    'approved_at'         => now()->subMonths(5),
                ],
                'wallet' => [
                    'balance'         => 1_200_000,
                    'total_earned'    => 3_500_000,
                    'total_withdrawn' => 2_300_000,
                ],
            ],

            // ── Studio 3: Baru mendaftar, menunggu review ─────────────────────
            [
                'user' => [
                    'email'    => 'kreatif@comicrealm.test',
                    'name'     => 'Putri Nugroho',
                    'username' => 'putrinugroho',
                    'role'     => 'user',     // belum diapprove, masih role user
                    'status'   => 'active',
                ],
                'profile' => [
                    'brand_name'          => 'Kreatif Webtoon Studio',
                    'slug'                => 'kreatif-webtoon-studio',
                    'bio'                 => 'Studio baru yang antusias dengan genre romance dan slice of life.',
                    'bank_name'           => 'BNI',
                    'bank_account_number' => '5554443322',
                    'bank_account_name'   => 'Putri Nugroho',
                    'verification_status' => 'pending',
                    'approved_at'         => null,
                ],
                'wallet' => [
                    'balance'         => 0,
                    'total_earned'    => 0,
                    'total_withdrawn' => 0,
                ],
            ],

            // ── Studio 4: Ditolak (rejection reason diisi) ────────────────────
            [
                'user' => [
                    'email'    => 'rejected@comicrealm.test',
                    'name'     => 'Hendra Wijaya',
                    'username' => 'hendrawijaya',
                    'role'     => 'user',
                    'status'   => 'active',
                ],
                'profile' => [
                    'brand_name'          => 'Midnight Comics',
                    'slug'                => 'midnight-comics',
                    'bio'                 => 'Studio komik bertema dark fantasy.',
                    'bank_name'           => 'BRI',
                    'bank_account_number' => '1122334455',
                    'bank_account_name'   => 'Hendra Wijaya',
                    'verification_status' => 'rejected',
                    'rejection_reason'    => 'Informasi rekening bank tidak dapat diverifikasi. Harap unggah bukti kepemilikan rekening.',
                    'approved_at'         => null,
                ],
                'wallet' => [
                    'balance'         => 0,
                    'total_earned'    => 0,
                    'total_withdrawn' => 0,
                ],
            ],

            // ── Studio 5: Diblokir (punya riwayat earning, lalu melanggar TOS) ─
            [
                'user' => [
                    'email'    => 'blocked@comicrealm.test',
                    'name'     => 'Bimo Saputra',
                    'username' => 'bimosaputra',
                    'role'     => 'publisher',
                    'status'   => 'suspended',
                ],
                'profile' => [
                    'brand_name'          => 'Dark Phoenix Studio',
                    'slug'                => 'dark-phoenix-studio',
                    'bio'                 => 'Studio komik yang sebelumnya aktif, kini dibekukan karena pelanggaran kebijakan konten.',
                    'bank_name'           => 'CIMB Niaga',
                    'bank_account_number' => '7788990011',
                    'bank_account_name'   => 'Bimo Saputra',
                    'verification_status' => 'blocked',
                    'approved_at'         => now()->subYear(),
                ],
                'wallet' => [
                    // Punya saldo tersisa karena diblokir sebelum withdraw semua
                    'balance'         => 500_000,
                    'total_earned'    => 2_000_000,
                    'total_withdrawn' => 1_500_000,
                ],
            ],
        ];

        foreach ($publishers as $data) {
            $user = User::firstOrCreate(
                ['email' => $data['user']['email']],
                array_merge($data['user'], [
                    'password' => Hash::make('password123'),
                ])
            );
            $user->profile()->firstOrCreate([]);

            $profileData = $data['profile'];

            // rejection_reason hanya diisi jika ada
            $profile = PublisherProfile::updateOrCreate(
                ['user_id' => $user->id],
                $profileData
            );

            PublisherWallet::updateOrCreate(
                ['publisher_id' => $profile->id],
                $data['wallet']
            );
        }
    }
}
