<?php

namespace App\Http\Controllers;

use App\Models\MessageAttachment;
use App\Models\Message;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class MessageAttachmentController extends Controller
{
    /**
     * Display a listing of message attachments
     */
    public function index(Message $message)
    {
        $attachments = $message->attachments;
        
        return view('message-attachments.index', compact('message', 'attachments'));
    }

    /**
     * Store a newly created attachment
     */
    public function store(Request $request, Message $message)
    {
        $this->authorize('create', [MessageAttachment::class, $message]);

        $validated = $request->validate([
            'attachments' => 'required|array',
            'attachments.*' => 'file|max:10240',
        ]);

        foreach ($request->file('attachments') as $file) {
            $path = $file->store('message-attachments', 'public');
            
            MessageAttachment::create([
                'message_id' => $message->id,
                'file_path' => $path,
                'file_name' => $file->getClientOriginalName(),
                'file_size' => $file->getSize(),
                'mime_type' => $file->getMimeType(),
            ]);
        }

        return redirect()->back()
            ->with('success', 'Fichiers ajoutés avec succès');
    }

    /**
     * Download the specified attachment
     */
    public function download(MessageAttachment $attachment)
    {
        if (!Storage::disk('public')->exists($attachment->file_path)) {
            return redirect()->back()->with('error', 'Fichier non trouvé');
        }

        return Storage::disk('public')->download($attachment->file_path, $attachment->file_name);
    }

    /**
     * Remove the specified attachment
     */
    public function destroy(MessageAttachment $attachment)
    {
        $this->authorize('delete', $attachment);

        if (Storage::disk('public')->exists($attachment->file_path)) {
            Storage::disk('public')->delete($attachment->file_path);
        }

        $attachment->delete();

        return redirect()->back()
            ->with('success', 'Fichier supprimé avec succès');
    }
}
