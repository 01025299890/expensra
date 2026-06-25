<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreMonthlyBudgetRequest extends FormRequest
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
            'amount' => 'required|numeric|min:0.01',
            'start_date' => 'nullable|date|after_or_equal:today',
            'end_date' => 'required|date|after_or_equal:start_date',
        ];
    }
    public function messages(): array
    {
        return [
            'amount.required' => 'حقل المبلغ مطلوب.',
            'amount.numeric' => 'حقل المبلغ يجب أن يكون رقمًا.',
            'amount.min' => 'حقل المبلغ يجب أن يكون أكبر من 0.',
            'start_date.required' => 'حقل تاريخ البداية مطلوب.',
            'start_date.date' => 'حقل تاريخ البداية يجب أن يكون تاريخًا صالحًا.',
            'start_date.after_or_equal' => 'تاريخ البداية يجب أن يكون بعد أو يساوي التاريخ الحالي.',
            'end_date.required' => 'حقل تاريخ النهاية مطلوب.',
            'end_date.date' => 'حقل تاريخ النهاية يجب أن يكون تاريخًا صالحًا.',
            'end_date.after_or_equal' => 'تاريخ النهاية يجب أن يكون بعد أو يساوي تاريخ البداية.',
        ];
    }
}
