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
            'file' => 'file|max:2048',
            'text' => 'string|max:1000',
        ];
        
    }

    public function messages(): array
    {
        return [
            'file.file' => 'الملف المرفوع يجب أن يكون ملفًا صالحًا.',
            'file.max' => 'الملف المرفوع لا يمكن أن يتجاوز 2MB في الحجم.',
            'text.string' => 'الإدخال النصي يجب أن يكون نصًا.',
            'text.max' => 'الإدخال النصي لا يمكن أن يتجاوز 1000 حرفًا.',
        ];
    }
}
