<?php

namespace App\Http\Requests\Publisher;

use App\Domain\Publisher\DTOs\ApplyPublisherData;
use Illuminate\Foundation\Http\FormRequest;

class ApplyPublisherRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    public function rules(): array
    {
        return [
            'brand_name' => ['required', 'string', 'max:150'],
            'bio' => ['nullable', 'string', 'max:1000'],
            'bank_name' => ['nullable', 'string', 'max:100'],
            'bank_account_number' => ['nullable', 'string', 'max:50'],
            'bank_account_name' => ['nullable', 'string', 'max:150'],
        ];
    }

    public function toDTO(): ApplyPublisherData
    {
        return ApplyPublisherData::from($this->validated());
    }
}
