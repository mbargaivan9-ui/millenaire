<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class StoreAttendanceRequest extends FormRequest
{
    public function authorize()
    {
        return $this->user()->hasPermission('create_attendance');
    }

    public function rules()
    {
        return [
            'student_id' => 'required|exists:students,id',
            'date' => 'required|date|before_or_equal:today',
            'status' => 'required|in:present,absent,justified,ill',
            'notes' => 'nullable|string|max:500',
            'justified_by' => 'nullable|string|max:255'
        ];
    }

    public function messages()
    {
        return [
            'student_id.required' => 'L\'étudiant est obligatoire.',
            'date.before_or_equal' => 'La date ne peut pas être dans le futur.',
            'status.required' => 'Le statut est obligatoire.',
        ];
    }
}
