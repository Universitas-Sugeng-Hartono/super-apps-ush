<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\SiakadSyncService;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;

class SyncSiakadAcademicData extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'siakad:sync-academic {--only-refresh : Hanya perbarui Semester & IPK mahasiswa yang sudah ada}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Sinkronisasi otomatis data akademik (Semester, Nilai IPK, Periode Masuk) dan mahasiswa baru dari SIAKAD';

    /**
     * Execute the console command.
     */
    public function handle(SiakadSyncService $syncService): int
    {
        $startTime = now();
        $this->info('[' . $startTime->toDateTimeString() . '] Memulai sinkronisasi otomatis SIAKAD...');
        Log::info('Background Sync SIAKAD dimulai.');

        try {
            // 1. Refresh Semester & IPK untuk seluruh mahasiswa yang sudah ada (Beasiswa & Reguler)
            $this->info('1. Memperbarui data Semester, IPK, dan Riwayat Akademik seluruh mahasiswa...');
            $refreshResult = $syncService->refreshScholarshipAcademicData(false);
            $this->line("   - Total diperiksa: {$refreshResult['total']} mahasiswa");
            $this->line("   - Berhasil diperbarui: {$refreshResult['updated']} mahasiswa");

            // 2. Tarik mahasiswa baru jika ada (kecuali di-skip dengan flag --only-refresh)
            $newResult = [
                'total'            => 0,
                'created'          => 0,
                'created_beasiswa' => 0,
                'created_reguler'  => 0,
                'skipped_existing' => 0,
            ];

            if (!$this->option('only-refresh')) {
                $this->info('2. Memeriksa pendaftaran mahasiswa baru di SIAKAD...');
                $newResult = $syncService->syncNewStudents();
                $this->line("   - Mahasiswa baru ditambahkan: {$newResult['created']} mahasiswa");
            }

            $endTime = now();
            $duration = $startTime->diffInSeconds($endTime);

            // Simpan ringkasan penarikan data ke Cache untuk ditampilkan sebagai pop-up di Manajemen Mahasiswa & Student Ambassador
            $syncSummary = [
                'sync_id'          => 'sync_' . $endTime->format('Ymd_His'),
                'synced_at'        => $endTime->translatedFormat('d F Y, H:i') . ' WIB',
                'date_formatted'   => $endTime->translatedFormat('l, d F Y'),
                'time_formatted'   => $endTime->format('H:i') . ' WIB',
                'status'           => 'success',
                'source'           => 'Penarikan Otomatis (01:00 WIB)',
                'duration_seconds' => $duration,
                'total_checked'    => $refreshResult['total'] ?? 0,
                'academic_updated' => $refreshResult['updated'] ?? 0,
                'academic_skipped' => $refreshResult['skipped'] ?? 0,
                'new_students'     => $newResult['created'] ?? 0,
                'new_beasiswa'     => $newResult['created_beasiswa'] ?? 0,
                'new_reguler'      => $newResult['created_reguler'] ?? 0,
            ];

            Cache::forever('siakad_last_sync_summary', $syncSummary);

            $this->info('[' . $endTime->toDateTimeString() . '] Sinkronisasi otomatis SIAKAD selesai dengan sukses.');
            Log::info('Background Sync SIAKAD selesai dengan sukses.', [
                'refreshed' => $refreshResult['updated'] ?? 0,
                'created'   => $newResult['created'] ?? 0,
                'duration'  => $duration,
            ]);

            return Command::SUCCESS;
        } catch (\Throwable $e) {
            $this->error('Gagal menjalankan sinkronisasi otomatis SIAKAD: ' . $e->getMessage());
            Log::error('Background Sync SIAKAD gagal: ' . $e->getMessage(), ['trace' => $e->getTraceAsString()]);

            // Simpan status kegagalan agar admin mengetahui bila sinkronisasi gagal
            Cache::forever('siakad_last_sync_summary', [
                'sync_id'          => 'sync_' . now()->format('Ymd_His'),
                'synced_at'        => now()->translatedFormat('d F Y, H:i') . ' WIB',
                'date_formatted'   => now()->translatedFormat('l, d F Y'),
                'time_formatted'   => now()->format('H:i') . ' WIB',
                'status'           => 'failed',
                'source'           => 'Penarikan Otomatis (01:00 WIB)',
                'error_message'    => $e->getMessage(),
            ]);

            return Command::FAILURE;
        }
    }
}
