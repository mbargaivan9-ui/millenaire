<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use App\Models\Announcement;

class CreateAnnouncementRequest extends FormRequest
{
    public function authorize(): bool
    {
        // Prefer policy if available, otherwise only allow admins
        return $this->user()?->can('create', Announcement::class) ?? ($this->user()?->isAdmin() ?? false);
    }

    public function rules(): array
    {
        return [
            'title' => 'required|string|max:255',
            'content' => 'required|string',
            'category' => 'nullable|string|max:100',
            'featured_image' => 'nullable|file|mimes:jpg,jpeg,png,webp,svg|max:5120',
            'attachment' => 'nullable|file|mimes:pdf,doc,docx,zip,txt|max:10240',
            'published_date' => 'nullable|date',
            'expires_at' => 'nullable|date|after_or_equal:published_date',
            'visibility' => 'nullable|string',
            'is_pinned' => 'nullable|boolean',
            'is_active' => 'nullable|boolean',
        ];
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'is_pinned' => $this->boolean('is_pinned'),
            'is_active' => $this->boolean('is_active', true),
        ]);
    }
}
