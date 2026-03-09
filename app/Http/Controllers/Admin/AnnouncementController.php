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
            'title'       => 'required|string|max:255',
            'content'     => 'required|string',
            'category'    => 'nullable|string|max:60',
            'is_featured' => 'boolean',
            'publish_now' => 'boolean',
        ]);

        $announcement = Announcement::create([
            'title'        => $data['title'],
            'slug'         => Str::slug($data['title']) . '-' . Str::random(5),
            'content'      => $data['content'],
            'category'     => $data['category'] ?? null,
            'is_featured'  => $request->boolean('is_featured'),
            'is_published' => $request->boolean('publish_now'),
            'published_at' => $request->boolean('publish_now') ? now() : null,
            'author_id'    => auth()->id(),
        ]);

        if ($announcement->is_published) {
            broadcast(new AnnouncementPublished($announcement))->toOthers();
        }

        activity()->causedBy(auth()->user())->performedOn($announcement)->log('Annonce créée');

        return redirect()->route('admin.announcements.index')
            ->with('success', app()->getLocale() === 'fr' ? 'Annonce créée.' : 'Announcement created.');
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
            'title'    => 'required|string|max:255',
            'content'  => 'required|string',
            'category' => 'nullable|string|max:60',
        ]);

        $announcement->update($data);
        activity()->causedBy(auth()->user())->performedOn($announcement)->log('Annonce modifiée');

        return redirect()->route('admin.announcements.index')
            ->with('success', app()->getLocale() === 'fr' ? 'Annonce modifiée.' : 'Announcement updated.');
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
