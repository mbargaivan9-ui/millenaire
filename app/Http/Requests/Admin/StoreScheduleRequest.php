<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class StoreScheduleRequest extends FormRequest
{
    public function authorize()
    {
        return $this->user()->hasPermission('create_schedule');
    }

    public function rules()
    {
        return [
            'classe_id' => 'required|exists:classes,id',
            'subject_id' => 'required|exists:subjects,id',
            'teacher_id' => 'required|exists:teachers,id',
            'day_of_week' => 'required|in:Monday,Tuesday,Wednesday,Thursday,Friday,Saturday',
            'start_time' => 'required|date_format:H:i',
            'end_time' => 'required|date_format:H:i|after:start_time',
            'room' => 'nullable|string|max:100'
        ];
    }

    public function messages()
    {
        return [
            'end_time.after' => 'L\'heure de fin doit être après l\'heure de début.',
            'day_of_week.required' => 'Le jour de la semaine est obligatoire.',
            'classe_id.required' => 'La classe est obligatoire.',
        ];
    }
}
