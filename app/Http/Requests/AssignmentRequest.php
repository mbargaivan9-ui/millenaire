<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;

class AssignmentRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        // Check if user has permission to manage assignments (admin or HR role)
        return Auth::check() && in_array(Auth::user()->role ?? null, ['admin', 'rh', 'directeur']);
    }

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        return [
            // New teacher that will be assigned (required)
            'new_teacher_id' => [
                'required',
                'integer',
                'exists:users,id',
                'different:old_teacher_id',
                // Must exist and not be deleted
                function ($attribute, $value, $fail) {
                    $teacher = \App\Models\User::find($value);
                    if (!$teacher || !in_array($teacher->role, ['professeur', 'prof_principal'])) {
                        $fail('The selected new teacher must have a teaching role.');
                    }
                    if (!$teacher->is_active) {
                        $fail('The selected teacher is not currently active.');
                    }
                },
            ],

            // Class to assign the teacher to (required)
            'class_id' => [
                'required',
                'integer',
                'exists:classes,id',
                function ($attribute, $value, $fail) {
                    $class = \App\Models\Classe::find($value);
                    if (!$class || !$class->is_active) {
                        $fail('The selected class does not exist or is not active.');
                    }
                },
            ],

            // Reason for the assignment/mutation (optional)
            'reason' => [
                'nullable',
                'string',
                'max:255',
                'in:promotion,demotion,transfer,medical_leave,sabbatical,resignation,retirement,temporary_relief,other',
            ],

            // Additional notes about the assignment (optional)
            'notes' => [
                'nullable',
                'string',
                'max:1000',
            ],

            // Whether to archive the old assignment (optional)
            'archive_previous' => [
                'nullable',
                'boolean',
            ],
        ];
    }

    /**
     * Get custom validation messages.
     */
    public function messages(): array
    {
        return [
            'new_teacher_id.required' => 'The new teacher is required.',
            'new_teacher_id.exists' => 'The selected teacher does not exist.',
            'new_teacher_id.different' => 'The new teacher must be different from the current teacher.',
            'class_id.required' => 'The class is required.',
            'class_id.exists' => 'The selected class does not exist.',
            'reason.in' => 'The selected reason is not valid.',
            'notes.max' => 'The notes field may not be greater than 1000 characters.',
        ];
    }

    /**
     * Get data to be validated from the request.
     */
    public function validationData(): array
    {
        return array_merge($this->all(), [
            'old_teacher_id' => $this->input('old_teacher_id') ?? null,
        ]);
    }

    /**
     * Prepare the data for validation.
     */
    protected function prepareForValidation(): void
    {
        // Clean up inputs
        if ($this->has('new_teacher_id')) {
            $this->merge([
                'new_teacher_id' => intval($this->input('new_teacher_id')),
            ]);
        }

        if ($this->has('class_id')) {
            $this->merge([
                'class_id' => intval($this->input('class_id')),
            ]);
        }

        if ($this->has('old_teacher_id') && !empty($this->input('old_teacher_id'))) {
            $this->merge([
                'old_teacher_id' => intval($this->input('old_teacher_id')),
            ]);
        }

        // Convert archive_previous to boolean
        if ($this->has('archive_previous')) {
            $this->merge([
                'archive_previous' => filter_var($this->input('archive_previous'), FILTER_VALIDATE_BOOLEAN),
            ]);
        }
    }
}
