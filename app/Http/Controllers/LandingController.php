<?php

namespace App\Http\Controllers;

use App\Models\Announcement;
use App\Models\FinalProjectDefense;
use App\Models\FinalProjectProposal;
use App\Models\Student;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

class LandingController extends Controller
{
    public function index()
    {
        $studentsCount = Schema::hasTable('students') ? Student::count() : 0;
        $lecturersCount = Schema::hasTable('users') ? User::count() : 0;
        $prodiCount = Schema::hasTable('students')
            ? Student::query()->whereNotNull('program_studi')->distinct()->count('program_studi')
            : 0;

        $announcements = collect();
        $announcementsCount = 0;
        if (Schema::hasTable('announcements')) {
            $announcementsCount = Announcement::query()->published()->count();
            $announcements = Announcement::query()
                ->published()
                ->orderByDesc('published_at')
                ->limit(10)
                ->get();
        }

        $eventsByDate = [];
        $calendar = [
            'month' => Carbon::now()->startOfMonth(),
            'start' => Carbon::now()->startOfMonth()->startOfWeek(Carbon::MONDAY),
            'end' => Carbon::now()->endOfMonth()->endOfWeek(Carbon::SUNDAY),
        ];

        $proposalEvents = collect();
        $defenseEvents = collect();

        if (Schema::hasTable('final_project_proposals')) {
            $proposalEvents = FinalProjectProposal::query()
                ->whereNotNull('scheduled_at')
                ->where('status', '!=', 'rejected')
                ->whereBetween('scheduled_at', [$calendar['start'], $calendar['end']])
                ->with(['finalProject.student'])
                ->orderBy('scheduled_at')
                ->get();
        }

        if (Schema::hasTable('final_project_defenses')) {
            $defenseEvents = FinalProjectDefense::query()
                ->whereNotNull('scheduled_at')
                ->where('status', '!=', 'rejected')
                ->whereBetween('scheduled_at', [$calendar['start'], $calendar['end']])
                ->with(['finalProject.student'])
                ->orderBy('scheduled_at')
                ->get();
        }

        $normalize = function (Carbon $dt): string {
            return $dt->copy()->startOfDay()->format('Y-m-d');
        };

        foreach ($proposalEvents as $proposal) {
            if (!$proposal->scheduled_at) {
                continue;
            }

            $dateKey = $normalize(Carbon::parse($proposal->scheduled_at));
            $studentName = data_get($proposal, 'finalProject.student.nama_lengkap') ?? 'Mahasiswa';
            $projectTitle = data_get($proposal, 'finalProject.title') ?? '';
            $approvalNotes = $proposal->approval_notes ?? '';

            $eventsByDate[$dateKey][] = [
                'type' => 'Sempro',
                'title' => 'Sempro - ' . $studentName,
                'project_title' => $projectTitle,
                'approval_notes' => $approvalNotes,
                'datetime' => Carbon::parse($proposal->scheduled_at),
                'status' => $proposal->status,
            ];
        }

        foreach ($defenseEvents as $defense) {
            if (!$defense->scheduled_at) {
                continue;
            }

            $dateKey = $normalize(Carbon::parse($defense->scheduled_at));
            $studentName = data_get($defense, 'finalProject.student.nama_lengkap') ?? 'Mahasiswa';
            $projectTitle = data_get($defense, 'finalProject.title') ?? '';
            $approvalNotes = $defense->approval_notes ?? '';

            $eventsByDate[$dateKey][] = [
                'type' => 'Sidang',
                'title' => 'Sidang Skripsi - ' . $studentName,
                'project_title' => $projectTitle,
                'approval_notes' => $approvalNotes,
                'datetime' => Carbon::parse($defense->scheduled_at),
                'status' => $defense->status,
            ];
        }

        // Query terpisah untuk Jadwal Terdekat (semua event yang akan datang, tidak terbatas bulan kalender)
        $upcomingProposals = collect();
        $upcomingDefenses = collect();
        
        if (Schema::hasTable('final_project_proposals')) {
            $upcomingProposals = FinalProjectProposal::query()
                ->whereNotNull('scheduled_at')
                ->where('status', '!=', 'rejected')
                ->where('scheduled_at', '>=', Carbon::now()->startOfMinute())
                ->with(['finalProject.student'])
                ->orderBy('scheduled_at')
                ->limit(10)
                ->get();
        }

        if (Schema::hasTable('final_project_defenses')) {
            $upcomingDefenses = FinalProjectDefense::query()
                ->whereNotNull('scheduled_at')
                ->where('status', '!=', 'rejected')
                ->where('scheduled_at', '>=', Carbon::now()->startOfMinute())
                ->with(['finalProject.student'])
                ->orderBy('scheduled_at')
                ->limit(10)
                ->get();
        }

        $upcomingEvents = collect();
        
        foreach ($upcomingProposals as $proposal) {
            $studentName = data_get($proposal, 'finalProject.student.nama_lengkap') ?? 'Mahasiswa';
            $projectTitle = data_get($proposal, 'finalProject.title') ?? '';
            $approvalNotes = $proposal->approval_notes ?? '';

            $upcomingEvents->push([
                'type' => 'Sempro',
                'title' => 'Sempro - ' . $studentName,
                'project_title' => $projectTitle,
                'approval_notes' => $approvalNotes,
                'datetime' => Carbon::parse($proposal->scheduled_at),
                'status' => $proposal->status,
            ]);
        }

        foreach ($upcomingDefenses as $defense) {
            $studentName = data_get($defense, 'finalProject.student.nama_lengkap') ?? 'Mahasiswa';
            $projectTitle = data_get($defense, 'finalProject.title') ?? '';
            $approvalNotes = $defense->approval_notes ?? '';

            $upcomingEvents->push([
                'type' => 'Sidang',
                'title' => 'Sidang Skripsi - ' . $studentName,
                'project_title' => $projectTitle,
                'approval_notes' => $approvalNotes,
                'datetime' => Carbon::parse($defense->scheduled_at),
                'status' => $defense->status,
            ]);
        }

        $upcomingEvents = $upcomingEvents
            ->sortBy(fn ($e) => $e['datetime']->timestamp)
            ->values()
            ->take(10);

        return view('landing.index', [
            'studentsCount' => $studentsCount,
            'lecturersCount' => $lecturersCount,
            'prodiCount' => $prodiCount,
            'announcements' => $announcements,
            'announcementsCount' => $announcementsCount,
            'calendar' => $calendar,
            'eventsByDate' => $eventsByDate,
            'upcomingEvents' => $upcomingEvents,
        ]);
    }
}


