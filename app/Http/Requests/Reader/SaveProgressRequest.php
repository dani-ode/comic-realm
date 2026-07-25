<?php

namespace App\Http\Requests\Reader;

use App\Domain\Reading\DTOs\ReadingProgressData;
use Illuminate\Foundation\Http\FormRequest;

class SaveProgressRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    public function rules(): array
    {
        return [
            'comic_id' => ['required', 'integer', 'exists:comics,id'],
            'chapter_id' => ['required', 'integer', 'exists:chapters,id'],
            'page_number' => ['required', 'integer', 'min:1'],
            'progress_percent' => ['required', 'numeric', 'min:0', 'max:100'],
        ];
    }

    public function toDTO(): ReadingProgressData
    {
        return ReadingProgressData::from($this->validated());
    }
}
