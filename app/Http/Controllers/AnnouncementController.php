<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Contracts\AnnouncementServiceInterface;

class AnnouncementController extends Controller
{
    public function __construct(private AnnouncementServiceInterface $service)
    {
    }

    public function index()
    {
        $announcements = $this->service->getPublished(12);
        return view('public.announcements.index', compact('announcements'));
    }

    public function show(string $slug)
    {
        $announcement = $this->service->getBySlug($slug);
        if (!$announcement) abort(404);
        return view('public.announcement', ['announcement' => $announcement]);
    }
}
