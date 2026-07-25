<?php

namespace App\Http\Requests\Cart;

use App\Domain\Cart\DTOs\AddToCartData;
use Illuminate\Foundation\Http\FormRequest;

class AddToCartRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    public function rules(): array
    {
        return [
            'chapter_id' => ['required', 'integer', 'exists:chapters,id'],
        ];
    }

    public function toDTO(): AddToCartData
    {
        return AddToCartData::from($this->validated());
    }
}
