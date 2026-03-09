<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;

/**
 * StoreGradeRequest
 * 
 * Validates grade entry data
 * SOLID - Separation of concerns
 */
class StoreGradeRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return Auth::check() && (Auth::user()->role === 'professeur' || Auth::user()->role === 'prof_principal');
    }

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        return [
            'student_id' => ['required', 'integer', 'exists:students,id'],
            'subject_id' => ['required', 'integer', 'exists:subjects,id'],
            'grade' => ['required', 'numeric', 'between:0,20'], // Grade between 0-20 (Cameroon system)
            'sequence' => ['required', 'integer', 'in:1,2,3'], // 3 sequences per year
            'academic_year' => ['required', 'integer', 'min:2020', 'max:2100'],
            'appreciation' => ['nullable', 'string', 'max:500'],
            'comments' => ['nullable', 'string', 'max:1000'],
        ];
    }

    /**
     * Get custom error messages
     */
    public function messages(): array
    {
        return [
            'grade.between' => 'Grade must be between 0 and 20',
            'sequence.in' => 'Sequence must be 1, 2, or 3',
            'student_id.exists' => 'Selected student not found',
            'subject_id.exists' => 'Selected subject not found',
        ];
    }
}
