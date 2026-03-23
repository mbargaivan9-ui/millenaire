<?php

namespace App\Livewire;

use Livewire\Component;
use Livewire\WithFileUploads;

class AnnouncementMediaUpload extends Component
{
    use WithFileUploads;

    public $coverImage;
    public $attachedFile;
    public $existingCover;
    public $existingAttachment;
    public $attachmentName;
    public $removeCover = false;
    public $removeFile = false;

    protected $rules = [
        'coverImage'   => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:5120',
        'attachedFile' => 'nullable|file|mimes:pdf,doc,docx,xls,xlsx,ppt,pptx,zip|max:10240',
    ];

    public function mount($announcement = null)
    {
        if ($announcement) {
            $this->existingCover = $announcement->cover_image;
            $this->existingAttachment = $announcement->attached_file;
            $this->attachmentName = $announcement->attachment_name;
        }
    }

    public function updateCoverImage()
    {
        $this->validateOnly('coverImage');
        $this->removeCover = false;
    }

    public function updateAttachedFile()
    {
        $this->validateOnly('attachedFile');
        $this->removeFile = false;
    }

    public function removeCoverImage()
    {
        $this->coverImage = null;
        $this->existingCover = null;
        $this->removeCover = true;
    }

    public function removeAttachedFile()
    {
        $this->attachedFile = null;
        $this->existingAttachment = null;
        $this->attachmentName = null;
        $this->removeFile = true;
    }

    public function getFileIcon()
    {
        if (!$this->existingAttachment && !$this->attachedFile) {
            return null;
        }

        $fileName = $this->attachmentName ?? ($this->attachedFile ? $this->attachedFile->getClientOriginalName() : '');
        $ext = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));

        return match ($ext) {
            'pdf' => '📄',
            'doc', 'docx' => '📝',
            'xls', 'xlsx' => '📊',
            'ppt', 'pptx' => '🎬',
            'zip' => '📦',
            default => '📎',
        };
    }

    public function render()
    {
        return view('livewire.announcement-media-upload');
    }
}
