<?php

namespace App\Livewire\Admin;

use Livewire\Component;
use Livewire\WithFileUploads;

class AnnouncementMediaUpload extends Component
{
    use WithFileUploads;

    public $coverImage;
    public $attachedFile;
    public $coverImagePreview;
    public $attachmentInfo;

    protected $rules = [
        'coverImage'   => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:5120',
        'attachedFile' => 'nullable|file|max:25600',
    ];

    protected $messages = [
        'coverImage.max'   => 'Photo de couverture: 5MB max',
        'attachedFile.max' => 'Fichier attaché: 25MB max',
    ];

    public function updatedCoverImage()
    {
        $this->validate(['coverImage' => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:5120']);
        if ($this->coverImage) {
            $this->coverImagePreview = $this->coverImage->temporaryUrl();
        }
    }

    public function updatedAttachedFile()
    {
        $this->validate(['attachedFile' => 'nullable|file|max:25600']);
        if ($this->attachedFile) {
            $this->attachmentInfo = [
                'name'  => $this->attachedFile->getClientOriginalName(),
                'size'  => round($this->attachedFile->getSize() / 1024, 2),
                'type'  => $this->attachedFile->getMimeType(),
            ];
        }
    }

    public function clearCoverImage()
    {
        $this->coverImage = null;
        $this->coverImagePreview = null;
    }

    public function clearAttachedFile()
    {
        $this->attachedFile = null;
        $this->attachmentInfo = null;
    }

    public function render()
    {
        return view('livewire.admin.announcement-media-upload');
    }
}
