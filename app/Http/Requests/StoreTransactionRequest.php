<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreTransactionRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return True;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'category' => 'required|string',
            'amount' => 'required|numeric|min:0.01',
            'transaction_type' => 'required|in:income,expense',
            'transaction_date' => 'date|nullable',
            'notes' => 'nullable|string',
            'confidence' => 'nullable|numeric|min:0|max:1',
            'force_save' => 'nullable',
        ];
    }

        public function messages(): array
        {
            return [
                'user_id.required' => 'الرجاء تسجيل الدخول أولاً.',
                'user_id.exists' => 'المستخدم غير موجود.',
                'category_id.required' => 'معرف الفئة مطلوب.',
                'category_id.exists' => 'الفئة المحددة غير موجودة.',
                'amount.required' => 'المبلغ مطلوب.',
                'amount.numeric' => 'المبلغ يجب أن يكون رقمًا.',
                'transaction_type.required' => 'نوع المعاملة مطلوب.',
                'transaction_type.in' => 'نوع المعاملة يجب أن يكون إما دخل أو مصروف.',
                'transaction_date.date' => 'تاريخ المعاملة يجب أن يكون تاريخًا صحيحًا.',
                'notes.string' => 'الملاحظات يجب أن تكون نصًا.',
                'confidence.numeric' => 'خطأ في الخادم الداخلي.',
                'confidence.min' => 'خطأ في الخادم الداخلي.',
                'confidence.max' => 'خطأ في الخادم الداخلي.'
                
            ];
        }
}
