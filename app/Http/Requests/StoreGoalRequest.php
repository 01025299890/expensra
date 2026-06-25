<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreGoalRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return auth()->check();
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $goalId = $this->route('goal');

        if ($this->isMethod('post')) {
            $goalNameRule = [
                'required',
                'string',
                'max:255',
                Rule::unique('goals')->where(fn($query) => $query->where('user_id', auth()->id()))
            ];
        } else {
            $goalNameRule = [
                'sometimes',
                'required',
                'string',
                'max:255',
                // تجاهل الهدف الحالي لكي لا يظهر خطأ "الاسم موجود مسبقاً" عند حفظ نفس الاسم
                Rule::unique('goals')->ignore($goalId)->where(fn($query) => $query->where('user_id', auth()->id()))
            ];
        }

        return [
            'goal_name' => $goalNameRule,
            'target_amount' => 'required|numeric|min:0',
            'saved_amount' => 'nullable|numeric|min:0',
            'deadline' => 'required|date|after:today',
        ];
    }

        public function messages(): array
        {
            return [
                'goal_name.required' => 'اسم الهدف مطلوب.',
                'goal_name.string' => 'اسم الهدف يجب أن يكون نصًا.',
                'goal_name.unique' => 'اسم الهدف موجود بالفعل.',
                'goal_name.max' => 'اسم الهدف لا يمكن أن يتجاوز 255 حرفًا.',
                'target_amount.required' => 'مبلغ الهدف مطلوب.',
                'target_amount.numeric' => 'مبلغ الهدف يجب أن يكون رقمًا.',
                'target_amount.min' => 'مبلغ الهدف يجب أن يكون 0 على الأقل.',
                'saved_amount.numeric' => 'مبلغ المُوفَّر يجب أن يكون رقمًا.',
                'saved_amount.min' => 'مبلغ المُوفَّر يجب أن يكون 0 على الأقل.',
                'deadline.required' => 'الموعد النهائي مطلوب.',
                'deadline.date' => 'الموعد النهائي يجب أن يكون تاريخًا صحيحًا.',
                'deadline.after' => 'الموعد النهائي يجب أن يكون تاريخًا بعد اليوم.',
            ];
        }
}
