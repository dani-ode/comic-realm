<?php

namespace App\Http\Requests\Publisher;

use Illuminate\Foundation\Http\FormRequest;

class UploadChapterPagesRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    public function rules(): array
    {
        return [
            'chapter_id' => ['required', 'integer', 'exists:chapters,id'],
            'pages' => ['required', 'array', 'min:1'],
            'pages.*' => ['required', 'image', 'mimes:webp,jpeg,png,jpg', 'max:10240'], // 10MB per image
        ];
    }
}
