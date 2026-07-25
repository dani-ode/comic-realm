<?php

namespace App\Domain\User\Actions;

use App\Domain\User\DTOs\RegisterUserData;
use App\Domain\User\Models\User;
use Illuminate\Support\Facades\DB;

class RegisterUser
{
    public function execute(RegisterUserData $data): User
    {
        return DB::transaction(function () use ($data) {
            $user = User::create([
                'name' => $data->name,
                'username' => strtolower($data->username),
                'email' => strtolower($data->email),
                'phone' => $data->phone,
                'password' => $data->password,
            ]);

            $user->profile()->create();

            return $user;
        });
    }
}
