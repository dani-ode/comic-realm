<?php

namespace App\Http\Requests\Engagement;

use App\Domain\Engagement\DTOs\RateComicData;
use Illuminate\Foundation\Http\FormRequest;

class RateComicRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    public function rules(): array
    {
        return [
            'comic_id' => ['required', 'integer', 'exists:comics,id'],
            'rating' => ['required', 'integer', 'min:1', 'max:5'],
            'review_text' => ['nullable', 'string', 'max:1000'],
        ];
    }

    public function toDTO(): RateComicData
    {
        return RateComicData::from($this->validated());
    }
}
