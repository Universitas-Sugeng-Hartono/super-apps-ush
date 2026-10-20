<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\SiakadSyncService;
use Illuminate\Support\Facades\Log;

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
        $this->info('[' . now()->toDateTimeString() . '] Memulai sinkronisasi otomatis SIAKAD...');
        Log::info('Background Sync SIAKAD dimulai.');

        try {
            // 1. Refresh Semester & IPK untuk seluruh mahasiswa yang sudah ada (Beasiswa & Reguler)
            $this->info('1. Memperbarui data Semester, IPK, dan Riwayat Akademik seluruh mahasiswa...');
            $refreshResult = $syncService->refreshScholarshipAcademicData(false);
            $this->line("   - Total diperiksa: {$refreshResult['total']} mahasiswa");
            $this->line("   - Berhasil diperbarui: {$refreshResult['updated']} mahasiswa");

            // 2. Tarik mahasiswa baru jika ada (kecuali di-skip dengan flag --only-refresh)
            if (!$this->option('only-refresh')) {
                $this->info('2. Memeriksa pendaftaran mahasiswa baru di SIAKAD...');
                $newResult = $syncService->syncNewStudents();
                $this->line("   - Mahasiswa baru ditambahkan: {$newResult['created']} mahasiswa");
            }

            $this->info('[' . now()->toDateTimeString() . '] Sinkronisasi otomatis SIAKAD selesai dengan sukses.');
            Log::info('Background Sync SIAKAD selesai dengan sukses.', [
                'refreshed' => $refreshResult['updated'] ?? 0,
                'created' => $newResult['created'] ?? 0,
            ]);

            return Command::SUCCESS;
        } catch (\Throwable $e) {
            $this->error('Gagal menjalankan sinkronisasi otomatis SIAKAD: ' . $e->getMessage());
            Log::error('Background Sync SIAKAD gagal: ' . $e->getMessage(), ['trace' => $e->getTraceAsString()]);

            return Command::FAILURE;
        }
    }
}
