<?php

namespace App\Http\Controllers;

use App\Models\Announcement;
use Illuminate\Http\Request;

class PublicAnnouncementController extends Controller
{
    public function index(Request $request)
    {
        $announcements = Announcement::query()
            ->published()
            ->orderByDesc('published_at')
            ->paginate(10);

        return view('announcements.index', compact('announcements'));
    }

    public function show($id)
    {
        $announcement = Announcement::query()
            ->published()
            ->findOrFail($id);

        return view('announcements.show', compact('announcement'));
    }
}

