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
        ];
    }

        public function messages(): array
        {
            return [
                'user_id.required' => 'User ID is required.',
                'user_id.exists' => 'The specified user does not exist.',
                'category_id.required' => 'Category ID is required.',
                'category_id.exists' => 'The specified category does not exist.',
                'amount.required' => 'Amount is required.',
                'amount.numeric' => 'Amount must be a number.',
                'transaction_type.required' => 'Transaction type is required.',
                'transaction_type.in' => 'Transaction type must be either income or expense.',
                'transaction_date.date' => 'Transaction date must be a valid date.',
                'notes.string' => 'Notes must be a string.',
                'confidence.numeric' => 'internal server error.',
                'confidence.min' => 'internal server error.',
                'confidence.max' => 'internal server error.'
                
            ];
        }
}
