<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ExpensesDateRequest extends FormRequest
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
            'month' => 'nullable|integer|min:1|max:12',
            'year' => 'nullable|integer|min:1900|max:'.(date('Y') + 1),
        ];
    }

    public function messages(): array
    {
        return [
            'month.integer' => 'يجب أن يكون الشهر رقماً صحيحاً.',
            'month.min' => 'يجب أن يكون الشهر على الأقل 1.',
            'month.max' => 'يجب أن يكون الشهر على الأكثر 12.',
            'year.integer' => 'يجب أن يكون السنة رقماً صحيحاً.',
            'year.min' => 'يجب أن تكون السنة على الأقل 1900.',
            'year.max' => 'يجب أن تكون السنة على الأكثر '.(date('Y') + 1).'.',
        ];
    }
}

