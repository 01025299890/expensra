<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateTransactionRequest extends FormRequest
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
            'category' => 'sometimes|required|string',
            'amount' => 'sometimes|required|numeric|min:0.01',
            'transaction_type' => 'sometimes|required|in:income,expense',
            'transaction_date' => 'sometimes|date|',
            'notes' => 'nullable|string'
        ];
    }


    public function messages(): array
    {
        return [
            // Category
            'category.string' => 'اسم القسم يجب أن يكون نصاً صحيحاً.',
            'category.required' => 'برجاء تحديد القسم الخاص بالمعاملة.',

            // Amount
            'amount.numeric' => 'يجب إدخال المبلغ كأرقام فقط.',
            'amount.min' => 'يجب أن يكون المبلغ أكبر من صفر.',
            'amount.required' => 'حقل المبلغ مطلوب ولا يمكن تركه فارغاً.',

            // Transaction Type
            'transaction_type.in' => 'نوع المعاملة يجب أن يكون إما (دخل) أو (مصروف).',
            'transaction_type.required' => 'يجب تحديد نوع المعاملة.',

            // Transaction Date
            'transaction_date.date' => 'تنسيق التاريخ غير صحيح.',

            // Notes
            'notes.string' => 'الملاحظات يجب أن تكون نصاً.',
        ];
    }
}
