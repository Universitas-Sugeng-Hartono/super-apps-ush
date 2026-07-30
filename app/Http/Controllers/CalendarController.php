<?php

namespace App\Http\Controllers;

use App\Models\FinalProjectDefense;
use App\Models\FinalProjectProposal;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;

class CalendarController extends Controller
{
    public function index(Request $request)
    {
        $month = $this->parseMonth($request->query('month')) ?? Carbon::now()->startOfMonth();

        $calendar = [
            'month' => $month->copy()->startOfMonth(),
            'start' => $month->copy()->startOfMonth()->startOfWeek(Carbon::MONDAY),
            'end' => $month->copy()->endOfMonth()->endOfWeek(Carbon::SUNDAY),
        ];

        $proposalEvents = FinalProjectProposal::query()
            ->whereNotNull('scheduled_at')
            ->where('status', '!=', 'rejected')
            ->whereBetween('scheduled_at', [$calendar['start'], $calendar['end']])
            ->with(['finalProject.student'])
            ->orderBy('scheduled_at')
            ->get();

        $defenseEvents = FinalProjectDefense::query()
            ->whereNotNull('scheduled_at')
            ->where('status', '!=', 'rejected')
            ->whereBetween('scheduled_at', [$calendar['start'], $calendar['end']])
            ->with(['finalProject.student'])
            ->orderBy('scheduled_at')
            ->get();

        $events = collect();

        foreach ($proposalEvents as $p) {
            $dt = Carbon::parse($p->scheduled_at);
            $events->push([
                'type' => 'Sempro',
                'datetime' => $dt,
                'status' => $p->status,
                'student_name' => data_get($p, 'finalProject.student.nama_lengkap') ?? 'Mahasiswa',
                'nim' => data_get($p, 'finalProject.student.nim') ?? '-',
                'prodi' => data_get($p, 'finalProject.student.program_studi') ?? '-',
                'project_title' => data_get($p, 'finalProject.title') ?? '',
                'approval_notes' => $p->approval_notes ?? '',
            ]);
        }

        foreach ($defenseEvents as $d) {
            $dt = Carbon::parse($d->scheduled_at);
            $events->push([
                'type' => 'Sidang',
                'datetime' => $dt,
                'status' => $d->status,
                'student_name' => data_get($d, 'finalProject.student.nama_lengkap') ?? 'Mahasiswa',
                'nim' => data_get($d, 'finalProject.student.nim') ?? '-',
                'prodi' => data_get($d, 'finalProject.student.program_studi') ?? '-',
                'project_title' => data_get($d, 'finalProject.title') ?? '',
                'approval_notes' => $d->approval_notes ?? '',
            ]);
        }

        $events = $events->sortBy(fn ($e) => $e['datetime']->timestamp)->values();

        $eventsByDate = [];
        foreach ($events as $e) {
            /** @var Carbon $dt */
            $dt = $e['datetime'];
            $key = $dt->copy()->startOfDay()->format('Y-m-d');
            $eventsByDate[$key][] = $e;
        }

        $eventsPage = $this->paginate($events, 10, $request);

        $upcomingEvents = $events
            ->filter(fn ($e) => $e['datetime'] instanceof Carbon && $e['datetime']->greaterThanOrEqualTo(Carbon::now()->startOfMinute()))
            ->values()
            ->take(10);

        $prevMonth = $calendar['month']->copy()->subMonth()->format('Y-m');
        $nextMonth = $calendar['month']->copy()->addMonth()->format('Y-m');

        return view('calendar.index', [
            'calendar' => $calendar,
            'eventsByDate' => $eventsByDate,
            'eventsPage' => $eventsPage,
            'upcomingEvents' => $upcomingEvents,
            'prevMonth' => $prevMonth,
            'nextMonth' => $nextMonth,
        ]);
    }

    private function parseMonth(?string $month): ?Carbon
    {
        $month = trim((string) $month);
        if ($month === '') {
            return null;
        }

        try {
            return Carbon::createFromFormat('Y-m', $month)->startOfMonth();
        } catch (\Throwable) {
            return null;
        }
    }

    private function paginate($items, int $perPage, Request $request): LengthAwarePaginator
    {
        $page = (int) ($request->query('page', 1));
        $page = max($page, 1);

        $total = $items->count();
        $results = $items->forPage($page, $perPage)->values();

        return new LengthAwarePaginator(
            $results,
            $total,
            $perPage,
            $page,
            [
                'path' => $request->url(),
                'query' => $request->query(),
            ]
        );
    }
}

