<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateStockRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255', 'min:3', Rule::unique('stocks', 'name')->ignore($this->stock->id)],
            'symbol' => ['required', 'string', 'max:40', 'min:1', Rule::unique('stocks', 'symbol')->ignore($this->stock->id)],
            'description' => ['nullable', 'string', 'max:65535'],
        ];
    }
}
