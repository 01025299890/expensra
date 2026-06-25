<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class TransactionDateRequest extends FormRequest
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
            'month' => 'integer|min:1|max:12',
            'year' => 'integer|min:1900|max:' . (date('Y') + 1),
        ];
    }

    public function messages(): array
    {
        return [
            'month.integer' => 'الشهر يجب أن يكون رقماً صحيحاً.',
            'month.min' => 'الشهر يجب أن يكون بين 1 و 12.',
            'month.max' => 'الشهر يجب أن يكون بين 1 و 12.',
            'year.integer' => 'السنة يجب أن تكون رقماً صحيحاً.',
            'year.min' => 'السنة يجب أن تكون بعد 1900.',
            'year.max' => 'السنة لا يمكن أن تكون في المستقبل.',
        ];
    }
}
