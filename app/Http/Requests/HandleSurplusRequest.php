<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class HandleSurplusRequest extends FormRequest
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
            'amount' => 'required_if:action,to_goal|numeric|min:0',
            'action' => 'required|in:to_income',
            'goal_id' => 'required_if:action,to_goal|exists:goals,id',
        ];
    }

    public function messages(): array
    {
        return [
            'amount.required_if' => 'يجب تحديد مبلغ الفائض عند اختيار تحويله إلى هدف.',
            'amount.numeric' => 'مبلغ الفائض يجب أن يكون أرقاماً.',
            'amount.min' => 'مبلغ الفائض لا يمكن أن يكون أقل من 0.',
            'action.required' => 'يجب تحديد الإجراء المراد اتخاذه للفائض.',
            'action.in' => 'الإجراء المحدد غير صالح. يجب أن يكون to_income',
            'goal_id.required_if' => 'يجب تحديد الهدف عند اختيار تحويل الفائض إلى هدف.',
            'goal_id.exists' => 'الهدف المحدد غير موجود.',
        ];
    }
}
