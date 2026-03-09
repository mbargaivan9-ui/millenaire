<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;

/**
 * InitiatePaymentRequest
 * 
 * Validates payment initiation data
 * Cameroon phone format validation
 */
class InitiatePaymentRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return Auth::check() && Auth::user()->role === 'parent';
    }

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        return [
            'student_id' => ['required', 'integer', 'exists:students,id'],
            'amount' => ['required', 'numeric', 'min:100', 'max:1000000'], // XAF currency
            'phone' => ['required', 'regex:/^(\+237|237)?6[0-9]{8}$/'], // Cameroon format
            'provider' => ['required', 'string', 'in:campay,orange,mtn'],
        ];
    }

    /**
     * Get custom error messages
     */
    public function messages(): array
    {
        return [
            'phone.regex' => 'Phone number must be in Cameroon format (6XX XX XX XX)',
            'amount.min' => 'Minimum payment amount is 100 XAF',
            'amount.max' => 'Maximum payment amount is 1,000,000 XAF',
            'provider.in' => 'Provider must be campay, orange, or mtn',
            'student_id.exists' => 'Selected student not found',
        ];
    }

    /**
     * Prepare the data for validation.
     */
    protected function prepareForValidation(): void
    {
        // Normalize phone number (remove spaces, +, hyphens)
        $this->merge([
            'phone' => preg_replace('/[^\d]/', '', $this->input('phone')),
        ]);
    }
}
