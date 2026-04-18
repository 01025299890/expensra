<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class SendDataToAiRequest extends FormRequest
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
            'file' => 'nullable|file|max:2048',
            'text' => 'nullable|string|max:1000',
        ];
        
    }

    public function messages(): array
    {
        return [
            'file.file' => 'The uploaded file must be a valid file.',
            'file.max' => 'The uploaded file must not exceed 2MB in size.',
            'text.string' => 'The text input must be a string.',
            'text.max' => 'The text input must not exceed 1000 characters.',
        ];
    }
}
