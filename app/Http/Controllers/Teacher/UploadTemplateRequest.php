<?php

namespace App\Http\Requests\Bulletin;

use Illuminate\Foundation\Http\FormRequest;

/**
 * UploadTemplateRequest
 *
 * Valide l'upload du bulletin établissement :
 * - Formats acceptés : jpeg, png, pdf
 * - Taille max : 10 MB
 */
class UploadTemplateRequest extends FormRequest
{
    public function authorize(): bool
    {
        // La vérification du rôle prof principal est faite dans le contrôleur
        return auth()->check() && auth()->user()->role === 'teacher';
    }

    public function rules(): array
    {
        return [
            'bulletin_file' => [
                'required',
                'file',
                'mimetypes:image/jpeg,image/png,application/pdf',
                'max:10240', // 10 MB
            ],
            'term' => [
                'required',
                'integer',
                'between:1,3',
            ],
            'academic_year' => [
                'required',
                'string',
                'regex:/^\d{4}-\d{4}$/',
            ],
        ];
    }

    public function messages(): array
    {
        return [
            'bulletin_file.required'  => 'Veuillez sélectionner un fichier.',
            'bulletin_file.mimetypes' => 'Format non supporté. Utilisez JPEG, PNG ou PDF.',
            'bulletin_file.max'       => 'Le fichier ne doit pas dépasser 10 MB.',
            'term.between'            => 'Le trimestre doit être 1, 2 ou 3.',
            'academic_year.regex'     => 'Format d\'année requis : 2025-2026',
        ];
    }
}
