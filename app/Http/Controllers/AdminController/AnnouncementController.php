<?php

namespace App\Http\Controllers\AdminController;

use App\Http\Controllers\Controller;
use App\Models\Announcement;
use Illuminate\Http\Request;

class AnnouncementController extends Controller
{
    public function index(Request $request)
    {
        $search = $request->input('search');
        $status = $request->input('status'); // published|draft|null

        $announcements = Announcement::query()
            ->when($search, function ($q, $search) {
                $q->where('title', 'like', "%{$search}%")
                    ->orWhere('content', 'like', "%{$search}%");
            })
            ->when($status, function ($q, $status) {
                if ($status === 'published') {
                    $q->where('is_published', true);
                }
                if ($status === 'draft') {
                    $q->where('is_published', false);
                }
            })
            ->orderByDesc('published_at')
            ->orderByDesc('id')
            ->paginate(15)
            ->appends(['search' => $search, 'status' => $status]);

        return view('admin.announcements.index', compact('announcements', 'search', 'status'));
    }

    public function create()
    {
        return view('admin.announcements.create');
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'title' => 'required|string|max:255',
            'content' => 'nullable|string',
            'is_published' => 'nullable|boolean',
            'published_at' => 'nullable|date',
        ]);

        $isPublished = (bool) ($request->input('is_published') ?? false);

        Announcement::create([
            'title' => $data['title'],
            'content' => $data['content'] ?? null,
            'is_published' => $isPublished,
            'published_at' => $isPublished ? ($data['published_at'] ?? now()) : null,
        ]);

        return redirect()->route('admin.announcements.index')
            ->with('success', 'Pengumuman berhasil dibuat.');
    }

    public function edit($id)
    {
        $announcement = Announcement::findOrFail($id);
        return view('admin.announcements.edit', compact('announcement'));
    }

    public function update(Request $request, $id)
    {
        $announcement = Announcement::findOrFail($id);

        $data = $request->validate([
            'title' => 'required|string|max:255',
            'content' => 'nullable|string',
            'is_published' => 'nullable|boolean',
            'published_at' => 'nullable|date',
        ]);

        $isPublished = (bool) ($request->input('is_published') ?? false);

        $announcement->update([
            'title' => $data['title'],
            'content' => $data['content'] ?? null,
            'is_published' => $isPublished,
            'published_at' => $isPublished ? ($data['published_at'] ?? ($announcement->published_at ?? now())) : null,
        ]);

        return redirect()->route('admin.announcements.index')
            ->with('success', 'Pengumuman berhasil diperbarui.');
    }

    public function togglePublish($id)
    {
        $announcement = Announcement::findOrFail($id);

        $nextPublished = !$announcement->is_published;
        $announcement->update([
            'is_published' => $nextPublished,
            'published_at' => $nextPublished ? ($announcement->published_at ?? now()) : null,
        ]);

        return back()->with('success', $nextPublished ? 'Pengumuman dipublikasikan.' : 'Pengumuman dijadikan draft.');
    }

    public function destroy($id)
    {
        $announcement = Announcement::findOrFail($id);
        $announcement->delete();

        return back()->with('success', 'Pengumuman berhasil dihapus.');
    }
}


