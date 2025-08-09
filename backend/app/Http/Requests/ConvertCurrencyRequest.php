<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ConvertCurrencyRequest extends FormRequest
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
            'from' => 'required|string|size:3',
            'to' => 'required|string|size:3',
            'amount' => 'required|numeric|min:0.01|max:1000000'
        ];
    }

        /**
     * Get custom error messages
     */
    public function messages()
    {
        return [
            'from.required' => 'Source currency is required',
            'from.size' => 'Currency code must be 3 characters',
            'to.required' => 'Target currency is required',
            'to.size' => 'Currency code must be 3 characters',
            'amount.required' => 'Amount is required',
            'amount.numeric' => 'Amount must be a number',
            'amount.min' => 'Amount must be at least 0.01',
            'amount.max' => 'Amount cannot exceed 1,000,000'
        ];
    }
}
