<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class StoreFeeRequest extends FormRequest
{
    public function authorize()
    {
        return $this->user()->hasPermission('create_fees');
    }

    public function rules()
    {
        return [
            'name' => 'required|string|max:255|unique:fees,name',
            'amount' => 'required|numeric|min:0|max:9999999',
            'description' => 'nullable|string|max:500',
            'due_date' => 'nullable|date|after:today',
            'is_mandatory' => 'boolean',
            'status' => 'required|in:active,inactive'
        ];
    }

    public function messages()
    {
        return [
            'name.unique' => 'Ce type de frais existe déjà.',
            'amount.required' => 'Le montant est obligatoire.',
            'amount.numeric' => 'Le montant doit être un nombre valide.',
            'due_date.after' => 'La date d\'échéance must be in the future.',
        ];
    }
}
