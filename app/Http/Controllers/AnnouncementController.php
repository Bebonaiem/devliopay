<?php

namespace App\Http\Controllers;

use App\Models\Announcement;

class AnnouncementController extends Controller
{
    public function index()
    {
        $announcements = Announcement::where('status', 'published')
            ->latest('published_at')
            ->paginate(10);

        return view('announcements.index', compact('announcements'));
    }

    public function show(string $slug)
    {
        $announcement = Announcement::where('slug', $slug)
            ->where('status', 'published')
            ->firstOrFail();

        return view('announcements.show', compact('announcement'));
    }
}
