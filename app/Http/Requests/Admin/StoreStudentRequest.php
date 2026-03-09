<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class StoreStudentRequest extends FormRequest
{
    public function authorize()
    {
        return $this->user()->hasPermission('create_students');
    }

    public function rules()
    {
        return [
            'user_id' => 'nullable|exists:users,id',
            'classe_id' => 'required|exists:classes,id',
            'matricule' => 'required|unique:students,matricule|regex:/^[A-Z0-9]{6,15}$/',
            'date_of_birth' => 'nullable|date|before:today',
            'gender' => 'nullable|in:male,female',
            'location' => 'nullable|string|max:255',
            'phone' => 'nullable|regex:/^\+?237[6789]\d{7}$/',
            'financial_status' => 'nullable|in:paid,pending,overdue'
        ];
    }

    public function messages()
    {
        return [
            'matricule.unique' => 'Ce matricule existe déjà.',
            'matricule.regex' => 'Le matricule doit contenir 6-15 caractères alphanumériques.',
            'phone.regex' => 'Numéro de téléphone Cameroun invalide.',
            'classe_id.required' => 'La classe est obligatoire.',
        ];
    }
}
