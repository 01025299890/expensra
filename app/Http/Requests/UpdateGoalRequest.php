<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateGoalRequest extends FormRequest
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
            'goal_name' => 'sometimes|required|string|max:255',
            'target_amount' => 'sometimes|required|numeric|min:0.01',
            'deadline' => 'sometimes|required|date|after:today',
        ];
    }


    public function messages(): array
    {
        return [
            'goal_name.string' => 'اسم الهدف يجب أن يكون نصاً.',
            'goal_name.required' => 'حقل اسم الهدف مطلوب.',
            'goal_name.max' => 'اسم الهدف لا يجب أن يتجاوز 255 حرفاً.',

            'target_amount.numeric' => 'المبلغ المستهدف يجب أن يكون رقمًا.',
            'target_amount.min' => 'المبلغ المستهدف يجب أن يكون 0.01 على الأقل.',
            'target_amount.required' => 'حقل المبلغ المستهدف مطلوب.',

            'deadline.date' => 'تاريخ الانتهاء يجب أن يكون تاريخًا صحيحًا.',
            'deadline.after' => 'تاريخ الانتهاء يجب أن يكون بعد اليوم.',
            'deadline.required' => 'حقل تاريخ الانتهاء مطلوب.',
        ];
    }
}
