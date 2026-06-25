<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateBudgetRequest extends FormRequest
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
            // 'category_id' => 'required|exists:categories,id',
            'limit_amount' => 'sometimes|numeric|min:.01',
            'start_date' => 'sometimes|date|after_or_equal:today',
            'end_date' => 'sometimes|date|after_or_equal:start_date',
        ];
    }

    public function messages(): array
    {
        return [
            // 'category_id.required' => 'معرف الفئة مطلوب.',
            // 'category_id.exists' => 'الفئة المحددة غير موجودة.',
            'limit_amount.numeric' => 'مبلغ الحد يجب أن يكون رقمًا.',
            'limit_amount.min' => 'مبلغ الحد يجب أن يكون 0.01 على الأقل.',
            'start_date.date' => 'تاريخ البدء يجب أن يكون تاريخًا صحيحًا.',
            'start_date.after_or_equal' => 'تاريخ البدء يجب أن يكون بعد أو يساوي التاريخ الحالي.',
            'end_date.date' => 'تاريخ الانتهاء يجب أن يكون تاريخًا صحيحًا.',
            'end_date.after_or_equal' => 'تاريخ الانتهاء يجب أن يكون بعد أو يساوي تاريخ البدء.',
        ];
    }
}
