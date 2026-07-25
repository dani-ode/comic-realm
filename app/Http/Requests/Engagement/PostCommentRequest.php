<?php

namespace App\Http\Requests\Engagement;

use App\Domain\Engagement\DTOs\PostCommentData;
use Illuminate\Foundation\Http\FormRequest;

class PostCommentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    public function rules(): array
    {
        return [
            'comic_id' => ['required', 'integer', 'exists:comics,id'],
            'chapter_id' => ['nullable', 'integer', 'exists:chapters,id'],
            'parent_id' => ['nullable', 'integer', 'exists:comments,id'],
            'comment_text' => ['required', 'string', 'min:3', 'max:2000'],
            'is_spoiler' => ['nullable', 'boolean'],
        ];
    }

    public function toDTO(): PostCommentData
    {
        return PostCommentData::from($this->validated());
    }
}
