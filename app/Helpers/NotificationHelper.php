<?php

namespace App\Helpers;

use App\Models\Notification;
use App\Models\Student;
use App\Models\User;

class NotificationHelper
{
    public static function notifyStudent(int $studentId, string $type, string $title, ?string $body = null, ?string $url = null, array $data = []): void
    {
        Notification::create([
            'recipient_type' => 'student',
            'recipient_id' => $studentId,
            'type' => $type,
            'title' => $title,
            'body' => $body,
            'url' => $url,
            'data' => !empty($data) ? json_encode($data) : null,
        ]);
    }

    public static function notifyUsers(array $userIds, string $type, string $title, ?string $body = null, ?string $url = null, array $data = []): void
    {
        $userIds = array_values(array_unique(array_filter($userIds, fn ($v) => is_numeric($v))));
        if (count($userIds) === 0) {
            return;
        }

        $rows = [];
        $now = now();
        foreach ($userIds as $id) {
            $rows[] = [
                'recipient_type' => 'user',
                'recipient_id' => (int) $id,
                'type' => $type,
                'title' => $title,
                'body' => $body,
                'url' => $url,
                'data' => !empty($data) ? json_encode($data) : null,
                'created_at' => $now,
                'updated_at' => $now,
            ];
        }

        Notification::insert($rows);
    }

    public static function kaprodiAndSuperuserUserIdsForProdi(?string $programStudi): array
    {
        $kaprodiRoles = ['superadmin', 'kaprodi'];
        $superuserRoles = ['masteradmin', 'superuser'];

        $kaprodiIds = User::query()
            ->whereIn('role', $kaprodiRoles)
            ->when($programStudi, fn ($q) => $q->where('program_studi', $programStudi))
            ->pluck('id')
            ->all();

        $superuserIds = User::query()
            ->whereIn('role', $superuserRoles)
            ->pluck('id')
            ->all();

        return array_values(array_unique(array_merge($kaprodiIds, $superuserIds)));
    }

    public static function prodiFromStudent(?Student $student): ?string
    {
        return $student?->program_studi ?: null;
    }
}

