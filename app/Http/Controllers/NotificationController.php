<?php

namespace App\Http\Controllers;

use App\Models\Notification;
use Illuminate\Http\Request;

class NotificationController extends Controller
{
    private function currentRecipient(): array
    {
        if (auth()->check()) {
            return ['user', (int) auth()->id()];
        }

        if (session()->has('student_id')) {
            try {
                return ['student', (int) decrypt(session('student_id'))];
            } catch (\Throwable) {
                // fallthrough
            }
        }

        return ['', 0];
    }

    public function index(Request $request)
    {
        [$type, $id] = $this->currentRecipient();
        if ($type === '' || $id === 0) {
            return redirect()->route('auth.login');
        }

        $notifications = Notification::query()
            ->forRecipient($type, $id)
            ->orderByDesc('created_at')
            ->paginate(12);

        $unreadCount = Notification::query()
            ->forRecipient($type, $id)
            ->unread()
            ->count();

        return view('notifications.index', [
            'notifications' => $notifications,
            'unreadCount' => $unreadCount,
        ]);
    }

    public function markRead(Request $request, $id)
    {
        [$type, $rid] = $this->currentRecipient();
        if ($type === '' || $rid === 0) {
            return redirect()->route('auth.login');
        }

        $notification = Notification::query()
            ->forRecipient($type, $rid)
            ->where('id', $id)
            ->firstOrFail();

        if (!$notification->read_at) {
            $notification->update(['read_at' => now()]);
        }

        if ($notification->url) {
            return redirect($notification->url);
        }

        return redirect()->back()->with('success', 'Notifikasi ditandai sudah dibaca.');
    }

    public function markAllRead()
    {
        [$type, $id] = $this->currentRecipient();
        if ($type === '' || $id === 0) {
            return redirect()->route('auth.login');
        }

        Notification::query()
            ->forRecipient($type, $id)
            ->unread()
            ->update(['read_at' => now()]);

        return redirect()->back()->with('success', 'Semua notifikasi ditandai sudah dibaca.');
    }
}

