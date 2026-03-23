<?php

/**
 * Admin\AnnouncementController — Gestion des Annonces
 * Phase 2 — Section 3 — Annonces publiques
 */

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Announcement;
use App\Events\AnnouncementPublished;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class AnnouncementController extends Controller
{
    public function index()
    {
        $announcements = Announcement::orderByDesc('created_at')->paginate(20);
        return view('admin.announcements.index', compact('announcements'));
    }

    public function create()
    {
        return view('admin.announcements.create');
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'title'           => 'required|string|max:255',
            'content'         => 'required|string',
            'category'        => 'nullable|string|max:60',
            'is_featured'     => 'boolean',
            'is_published'    => 'boolean',
            'cover_image'     => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:5120',
            'attached_file'   => 'nullable|file|mimes:pdf,doc,docx,xls,xlsx,ppt,pptx,zip|max:10240',
        ]);

        $coverImagePath = null;
        $attachedFilePath = null;
        $attachmentName = null;
        $attachmentType = null;
        $attachmentSize = null;

        // Upload photo de couverture
        if ($request->hasFile('cover_image')) {
            $coverImagePath = $request->file('cover_image')->store('announcements/covers', 'public');
        }

        // Upload fichier attaché
        if ($request->hasFile('attached_file')) {
            $file = $request->file('attached_file');
            $attachedFilePath = $file->store('announcements/attachments', 'public');
            $attachmentName = $file->getClientOriginalName();
            $attachmentType = $file->getMimeType();
            $attachmentSize = $file->getSize();
        }

        $announcement = Announcement::create([
            'title'             => $data['title'],
            'slug'              => Str::slug($data['title']) . '-' . Str::random(5),
            'content'           => $data['content'],
            'category'          => $data['category'] ?? null,
            'cover_image'       => $coverImagePath,
            'attached_file'     => $attachedFilePath,
            'attachment_name'   => $attachmentName,
            'attachment_type'   => $attachmentType,
            'attachment_size'   => $attachmentSize,
            'is_featured'       => $request->boolean('is_featured'),
            'is_published'      => $request->boolean('is_published'),
            'published_at'      => $request->boolean('is_published') ? now() : null,
            'author_id'         => auth()->id(),
        ]);

        if ($announcement->is_published) {
            broadcast(new AnnouncementPublished($announcement))->toOthers();
        }

        activity()->causedBy(auth()->user())->performedOn($announcement)->log('Annonce créée');

        return redirect()->route('admin.announcements.index')
            ->with('success', app()->getLocale() === 'fr' ? 'Annonce créée avec succès.' : 'Announcement created successfully.');
    }

    public function edit(int $id)
    {
        $announcement = Announcement::findOrFail($id);
        return view('admin.announcements.create', compact('announcement'));
    }

    public function update(Request $request, int $id)
    {
        $announcement = Announcement::findOrFail($id);
        
        $data = $request->validate([
            'title'           => 'required|string|max:255',
            'content'         => 'required|string',
            'category'        => 'nullable|string|max:60',
            'is_featured'     => 'boolean',
            'cover_image'     => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:5120',
            'attached_file'   => 'nullable|file|mimes:pdf,doc,docx,xls,xlsx,ppt,pptx,zip|max:10240',
            'remove_cover'    => 'boolean',
            'remove_file'     => 'boolean',
        ]);

        $updateData = [
            'title'       => $data['title'],
            'content'     => $data['content'],
            'category'    => $data['category'] ?? null,
            'is_featured' => $request->boolean('is_featured'),
        ];

        // Suppression de la couverture
        if ($request->boolean('remove_cover') && $announcement->cover_image) {
            \Illuminate\Support\Facades\Storage::disk('public')->delete($announcement->cover_image);
            $updateData['cover_image'] = null;
        }

        // Upload nouvelle couverture
        if ($request->hasFile('cover_image')) {
            if ($announcement->cover_image && \Illuminate\Support\Facades\Storage::disk('public')->exists($announcement->cover_image)) {
                \Illuminate\Support\Facades\Storage::disk('public')->delete($announcement->cover_image);
            }
            $updateData['cover_image'] = $request->file('cover_image')->store('announcements/covers', 'public');
        }

        // Suppression du fichier joint
        if ($request->boolean('remove_file') && $announcement->attached_file) {
            \Illuminate\Support\Facades\Storage::disk('public')->delete($announcement->attached_file);
            $updateData['attached_file'] = null;
            $updateData['attachment_name'] = null;
            $updateData['attachment_type'] = null;
            $updateData['attachment_size'] = null;
        }

        // Upload nouveau fichier joint
        if ($request->hasFile('attached_file')) {
            if ($announcement->attached_file && \Illuminate\Support\Facades\Storage::disk('public')->exists($announcement->attached_file)) {
                \Illuminate\Support\Facades\Storage::disk('public')->delete($announcement->attached_file);
            }
            $file = $request->file('attached_file');
            $updateData['attached_file'] = $file->store('announcements/attachments', 'public');
            $updateData['attachment_name'] = $file->getClientOriginalName();
            $updateData['attachment_type'] = $file->getMimeType();
            $updateData['attachment_size'] = $file->getSize();
        }

        $announcement->update($updateData);
        activity()->causedBy(auth()->user())->performedOn($announcement)->log('Annonce modifiée');

        return redirect()->route('admin.announcements.index')
            ->with('success', app()->getLocale() === 'fr' ? 'Annonce modifiée avec succès.' : 'Announcement updated successfully.');
    }

    public function publish(int $id)
    {
        $announcement = Announcement::findOrFail($id);
        $announcement->update(['is_published' => true, 'published_at' => now()]);
        broadcast(new AnnouncementPublished($announcement))->toOthers();

        \Illuminate\Support\Facades\Cache::forget('public.announcements');

        return back()->with('success', app()->getLocale() === 'fr' ? 'Annonce publiée.' : 'Announcement published.');
    }

    public function destroy(int $id)
    {
        Announcement::findOrFail($id)->delete();
        return back()->with('success', app()->getLocale() === 'fr' ? 'Annonce supprimée.' : 'Announcement deleted.');
    }
}
