<?php

namespace App\Domain\Publisher\Actions;

use App\Domain\Publisher\DTOs\ApplyPublisherData;
use App\Domain\Publisher\Models\PublisherProfile;
use App\Domain\User\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class ApplyPublisher
{
    public function execute(User $user, ApplyPublisherData $data): PublisherProfile
    {
        return DB::transaction(function () use ($user, $data) {
            $slug = Str::slug($data->brand_name);
            $count = PublisherProfile::where('slug', 'like', "{$slug}%")->count();
            if ($count > 0) {
                $slug .= '-' . ($count + 1);
            }

            $profile = PublisherProfile::updateOrCreate(
                ['user_id' => $user->id],
                [
                    'brand_name' => $data->brand_name,
                    'slug' => $slug,
                    'bio' => $data->bio,
                    'bank_name' => $data->bank_name,
                    'bank_account_number' => $data->bank_account_number,
                    'bank_account_name' => $data->bank_account_name,
                    'verification_status' => 'pending',
                ]
            );

            // Update user role to publisher if auto-approval or for testing
            $user->update(['role' => 'publisher']);
            $profile->update(['verification_status' => 'approved', 'approved_at' => now()]);

            return $profile;
        });
    }
}
