<?php

namespace App\Services;

use App\Models\Student;
use App\Models\StudyProgram;
use App\Models\User;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class SiakadSyncService
{
    /**
     * Ambil data mahasiswa termin dari SIAKAD REST API (bisa semua atau filter per NIM)
     */
    public function fetchCandidates(?string $nim = null): Collection
    {
        $apiUrl = config('services.siakad.api_url', env('SIAKAD_API_URL', 'http://127.0.0.1:8090'));
        $apiKey = config('services.siakad.api_key', env('SIAKAD_API_KEY', 'ush-secret-key-2026'));
        $timeout = (int) config('services.siakad.timeout', 30);

        $endpoint = rtrim($apiUrl, '/') . '/api/external/rekomendasi-beasiswa-termin';
        $queryParams = [];
        if ($nim !== null && $nim !== '') {
            $queryParams['nim'] = $nim;
        }

        try {
            $response = Http::timeout($timeout)
                ->withHeaders([
                    'X-SIAKAD-API-KEY' => $apiKey,
                    'Accept' => 'application/json',
                ])
                ->get($endpoint, $queryParams);

            if ($response->successful()) {
                $body = $response->json();
                $rows = $body['data'] ?? [];
                return collect($rows)->map(fn ($item) => (object) $item);
            }

            $errMsg = "HTTP {$response->status()}: " . ($response->json('message') ?? $response->body());
            Log::warning("SIAKAD REST API request gagal [{$endpoint}]: {$errMsg}");
            throw new \RuntimeException($errMsg);
        } catch (\Throwable $apiEx) {
            Log::error("Koneksi HTTP SIAKAD REST API bermasalah ({$endpoint}): " . $apiEx->getMessage());
            throw new \RuntimeException("Gagal mengambil data dari SIAKAD REST API ({$endpoint}): " . $apiEx->getMessage(), 0, $apiEx);
        }
    }

    /**
     * Sinkronisasi data 1 mahasiswa spesifik berdasarkan NIM
     * Mengembalikan true jika data ditemukan dan diperbarui, false jika tidak ada di Siakad.
     */
    public function syncSingleStudent(Student $student): bool
    {
        if (empty($student->nim) || $student->nim === '-') {
            return false;
        }

        $candidates = $this->fetchCandidates($student->nim);
        if ($candidates->isEmpty()) {
            return false;
        }

        $row = $candidates->first();
        $updateData = $this->extractUpdateData($student, $row);

        $student->update($updateData);
        return true;
    }

    /**
     * Kirim rekomendasi beasiswa 1 mahasiswa ke SIAKAD REST API
     * Mengisi kolom "Rekomendasi" pada menu /rekomendasi-beasiswa-termin di Siakad
     */
    public function sendRecommendation(Student $student, string|float $persentase, ?string $catatan = null, string $status = 'draft'): array
    {
        if (empty($student->nim) || $student->nim === '-') {
            throw new \InvalidArgumentException("Mahasiswa {$student->nama_lengkap} belum memiliki NIM.");
        }

        if ($catatan === null || trim($catatan) === '') {
            $eval = PokemaEvaluatorService::evaluate($student);
            $catatan = "Rekomendasi POKEMA SuperApps: " . ($eval['reason'] ?? "Beasiswa {$persentase}");
        }

        return $this->sendBulkRecommendations([
            [
                'nim'        => $student->nim,
                'persentase' => $persentase,
                'catatan'    => $catatan,
                'status'     => $status,
            ]
        ]);
    }

    /**
     * Kirim rekomendasi beasiswa secara massal (bulk) ke SIAKAD REST API
     */
    public function sendBulkRecommendations(array $items): array
    {
        $apiUrl = config('services.siakad.api_url', env('SIAKAD_API_URL', 'http://127.0.0.1:8090'));
        $apiKey = config('services.siakad.api_key', env('SIAKAD_API_KEY', 'ush-secret-key-2026'));
        $timeout = (int) config('services.siakad.timeout', 30);

        $endpoint = rtrim($apiUrl, '/') . '/api/external/rekomendasi-beasiswa-termin';

        try {
            $response = Http::timeout($timeout)
                ->withHeaders([
                    'X-SIAKAD-API-KEY' => $apiKey,
                    'Accept'           => 'application/json',
                    'Content-Type'     => 'application/json',
                ])
                ->post($endpoint, [
                    'items' => $items,
                ]);

            if ($response->successful()) {
                return $response->json();
            }

            $errMsg = "HTTP {$response->status()}: " . ($response->json('message') ?? $response->body());
            Log::warning("Gagal mengirim rekomendasi ke SIAKAD API [{$endpoint}]: {$errMsg}");
            throw new \RuntimeException($errMsg);
        } catch (\Throwable $apiEx) {
            Log::error("Koneksi HTTP SIAKAD API bermasalah saat kirim rekomendasi ({$endpoint}): " . $apiEx->getMessage());
            throw new \RuntimeException("Gagal mengirim rekomendasi ke SIAKAD API ({$endpoint}): " . $apiEx->getMessage(), 0, $apiEx);
        }
    }

    /**
     * Eksekusi sinkronisasi massal seluruh data dari Siakad
     */
    /**
     * Sinkronisasi mahasiswa baru dari Siakad (Insert Only)
     * Hanya memasukkan mahasiswa baru yang belum terdaftar di SuperApps (Beasiswa & Reguler).
     * Tidak mengubah/menimpa data mahasiswa yang sudah ada di SuperApps.
     */
    public function syncNewStudents(): array
    {
        $candidates = $this->fetchCandidates();

        if ($candidates->isEmpty()) {
            return [
                'total'            => 0,
                'created'          => 0,
                'created_beasiswa' => 0,
                'created_reguler'  => 0,
                'skipped_existing' => 0,
            ];
        }

        $created = 0;
        $createdBeasiswa = 0;
        $createdReguler = 0;
        $skippedExisting = 0;

        DB::beginTransaction();
        try {
            $defaultLecturerId = User::whereIn('role', ['admin', 'superadmin', 'masteradmin'])->value('id') ?? 1;
            $defaultPasswordHash = Hash::make('12345678');

            foreach ($candidates as $row) {
                $nim = trim((string) $row->nim);
                if ($nim === '' || $nim === '-') {
                    continue;
                }

                $exists = Student::where('nim', $nim)->exists();
                if ($exists) {
                    $skippedExisting++;
                    continue; // PENTING: Tombol ini hanya menarik data baru, tidak menimpa yang sudah ada!
                }

                $createData = $this->extractCreateData($row, $defaultLecturerId, $defaultPasswordHash);
                Student::create($createData);
                $created++;

                if ($createData['status'] === 'studentambassador') {
                    $createdBeasiswa++;
                } else {
                    $createdReguler++;
                }
            }

            DB::commit();

            return [
                'total'            => $candidates->count(),
                'created'          => $created,
                'created_beasiswa' => $createdBeasiswa,
                'created_reguler'  => $createdReguler,
                'skipped_existing' => $skippedExisting,
            ];
        } catch (\Throwable $e) {
            DB::rollBack();
            throw $e;
        }
    }

    /**
     * Perbarui (Refresh) Semester & Nilai IPK dari Siakad (bisa seluruh mahasiswa atau khusus beasiswa)
     * Hanya memperbarui Semester, IPK, tanggal/periode masuk, dan riwayatnya tanpa menambah mahasiswa baru.
     */
    public function refreshScholarshipAcademicData(bool $onlyScholarship = true): array
    {
        $query = Student::whereNotNull('nim')
            ->where('nim', '!=', '')
            ->where('nim', '!=', '-');

        if ($onlyScholarship) {
            $query->where(function ($q) {
                $q->where('status', 'studentambassador')
                  ->orWhere(function ($sub) {
                      $sub->whereNotNull('beasiswa')
                          ->where('beasiswa', '!=', '0%')
                          ->where('beasiswa', '!=', '');
                  });
            });
        }

        $students = $query->get();

        if ($students->isEmpty()) {
            return [
                'total'   => 0,
                'updated' => 0,
                'skipped' => 0,
            ];
        }

        $candidates = $this->fetchCandidates();
        $candidateMap = $candidates->keyBy('nim');

        $updated = 0;
        $skipped = 0;

        DB::beginTransaction();
        try {
            foreach ($students as $student) {
                $row = $candidateMap->get($student->nim);

                if ($row) {
                    $updateData = $this->extractUpdateData($student, $row);
                    if (!empty($updateData)) {
                        $student->update($updateData);
                        $updated++;
                        continue;
                    }
                }
                $skipped++;
            }

            DB::commit();

            return [
                'total'   => $students->count(),
                'updated' => $updated,
                'skipped' => $skipped,
            ];
        } catch (\Throwable $e) {
            DB::rollBack();
            throw $e;
        }
    }

    /**
     * Eksekusi sinkronisasi massal seluruh data dari Siakad (Insert & Update)
     * Tetap dipertahankan untuk kompatibilitas fungsi sinkronisasi menyeluruh.
     */
    public function syncAll(): array
    {
        $candidates = $this->fetchCandidates();

        if ($candidates->isEmpty()) {
            return [
                'total'   => 0,
                'created' => 0,
                'updated' => 0,
                'skipped' => 0,
            ];
        }

        $created = 0;
        $updated = 0;
        $skipped = 0;

        DB::beginTransaction();
        try {
            $defaultLecturerId = User::whereIn('role', ['admin', 'superadmin', 'masteradmin'])->value('id') ?? 1;
            $defaultPasswordHash = Hash::make('12345678');

            foreach ($candidates as $row) {
                $nim = trim((string) $row->nim);
                if ($nim === '' || $nim === '-') {
                    $skipped++;
                    continue;
                }

                $student = Student::where('nim', $nim)->first();

                if ($student) {
                    $updateData = $this->extractUpdateData($student, $row);
                    $student->update($updateData);
                    $updated++;
                } else {
                    $createData = $this->extractCreateData($row, $defaultLecturerId, $defaultPasswordHash);
                    Student::create($createData);
                    $created++;
                }
            }

            DB::commit();

            return [
                'total'   => $candidates->count(),
                'created' => $created,
                'updated' => $updated,
                'skipped' => $skipped,
            ];
        } catch (\Throwable $e) {
            DB::rollBack();
            throw $e;
        }
    }

    /**
     * Preview ringkasan data sebelum sinkronisasi
     * Hanya menampilkan jumlah data beasiswa, reguler, dan calon mahasiswa baru yang siap ditarik.
     */
    public function preview(): array
    {
        $candidates = $this->fetchCandidates();
        $nims = $candidates->pluck('nim')->filter(fn ($n) => $n && $n !== '-')->values();
        $existingNims = Student::whereIn('nim', $nims)->pluck('nim')->toArray();

        $total = $candidates->count();
        $newCandidates = $candidates->filter(fn ($c) => !in_array($c->nim, $existingNims, true));
        $newCount = $newCandidates->count();

        // Hitung mahasiswa beasiswa dari rekomendasi beasiswa termin di Siakad (114 mahasiswa)
        $isBeasiswaFn = function ($c) {
            return !empty($c->fase_terakhir) && ((int) $c->fase_terakhir % 3 === 0) && ($c->beasiswa_sekarang > 0);
        };

        $beasiswaCandidates = $candidates->filter($isBeasiswaFn);
        $beasiswaCount = $beasiswaCandidates->count();
        $regulerCount = max(0, $total - $beasiswaCount);

        $newBeasiswaCandidates = $newCandidates->filter($isBeasiswaFn);
        $newBeasiswaCount = $newBeasiswaCandidates->count();
        $newRegulerCount = max(0, $newCount - $newBeasiswaCount);

        return [
            'total'              => $total,
            'beasiswa_count'     => $beasiswaCount,
            'reguler_count'      => $regulerCount,
            'new_count'          => $newCount,
            'new_beasiswa_count' => $newBeasiswaCount,
            'new_reguler_count'  => $newRegulerCount,
        ];
    }

    /**
     * Ekstraksi data update untuk record Student yang sudah ada
     * Hanya memperbarui Semester dan IPK resmi dari Siakad agar tidak menimpa
     * data kontak, status ambassador, maupun evaluasi beasiswa yang sudah ada di SuperApps.
     */
    private function extractUpdateData(Student $student, object $row): array
    {
        $semester = $this->resolveSemester($row);
        $ipk = $this->resolveIpk($row);

        $updateData = [];

        if ($semester !== null) {
            $updateData['semester'] = $semester;
        }

        if ($ipk !== null) {
            $updateData['ipk'] = $ipk;
        }

        // Catat dan akumulasikan riwayat IPK per semester agar semester lama tetap tersimpan
        $history = $student->semester_ipk_history;
        if (!is_array($history)) {
            $history = is_string($history) ? (json_decode($history, true) ?: []) : [];
        }

        // Amankan nilai IPK semester sebelumnya jika ada
        if (!empty($student->semester) && $student->ipk !== null) {
            $history[(string) $student->semester] = (float) $student->ipk;
        }

        // Catat semester & IPK terbaru hasil refresh SIAKAD
        $targetSem = $semester !== null ? $semester : $student->semester;
        $targetIpk = $ipk !== null ? $ipk : $student->ipk;
        if ($targetSem && $targetIpk !== null) {
            $history[(string) $targetSem] = (float) $targetIpk;
        }

        $updateData['semester_ipk_history'] = $history;

        // Catat status ACC & persentase beasiswa resmi dari Keuangan di SIAKAD
        // Di SIAKAD: ketika Keuangan menekan ACC dan generate termin, record rekomendasi termin ditutup
        // dan beasiswa aktif termin mahasiswa resmi terupdate di kolom `beasiswa_sekarang`.
        if ($row->persentase_final !== null && is_numeric($row->persentase_final)) {
            $finalPct = (float) $row->persentase_final;
            $updateData['beasiswa_acc_keuangan'] = rtrim(rtrim(number_format($finalPct, 2), '0'), '.') . '%';
            $updateData['status_rekomendasi_siakad'] = 'approved';
        } elseif (empty($row->id_rekomendasi) && !empty($row->beasiswa_sekarang) && is_numeric($row->beasiswa_sekarang)) {
            // Termin sudah resmi digenerate Keuangan di SIAKAD (rekomendasi termin sudah selesai/diproses)
            $bsNow = (float) $row->beasiswa_sekarang;
            $updateData['beasiswa_acc_keuangan'] = rtrim(rtrim(number_format($bsNow, 2), '0'), '.') . '%';
            $updateData['status_rekomendasi_siakad'] = 'approved';
        } elseif (!empty($row->status_rekomendasi)) {
            $updateData['status_rekomendasi_siakad'] = $row->status_rekomendasi;
        }

        // Lengkapi tanggal_masuk dan periode_masuk jika belum terisi pada data sebelumnya
        if (empty($student->tanggal_masuk) && !empty($row->tgl_masuk) && $row->tgl_masuk !== '0000-00-00') {
            $updateData['tanggal_masuk'] = $row->tgl_masuk;
        }

        if (empty($student->periode_masuk)) {
            $angkatan = (int) (!empty($row->tahun_masuk) ? $row->tahun_masuk : ($student->angkatan ?: 2026));
            $academicYear = $angkatan . '/' . ($angkatan + 1);
            $term = 'Ganjil';
            $tgl = !empty($row->tgl_masuk) ? $row->tgl_masuk : $student->tanggal_masuk;
            if (!empty($tgl) && $tgl !== '0000-00-00') {
                $month = (int) date('m', strtotime($tgl));
                $term = ($month >= 1 && $month <= 6) ? 'Genap' : 'Ganjil';
            }
            $updateData['periode_masuk'] = "{$academicYear} {$term}";
        }

        return $updateData;
    }

    /**
     * Ekstraksi data create untuk Student baru
     */
    private function extractCreateData(object $row, int $defaultLecturerId, string $defaultPasswordHash): array
    {
        $nim = trim((string) $row->nim);
        $nama = trim((string) $row->name);
        $angkatan = (int) ($row->tahun_masuk ?: 2026);
        $rawProdi = trim((string) $row->prodi);
        $prodiFormatted = $this->formatProdiName($rawProdi);

        if ($prodiFormatted !== '-') {
            StudyProgram::firstOrCreate(
                ['name' => $prodiFormatted],
                ['is_active' => true, 'order' => 99]
            );
        }

        $semester = $this->resolveSemester($row);
        $ipk = $this->resolveIpk($row);
        $isBeasiswa = !empty($row->fase_terakhir) && ((int) $row->fase_terakhir % 3 === 0) && ($this->resolveBeasiswa($row) !== null);
        $beasiswa = $isBeasiswa ? $this->resolveBeasiswa($row) : null;
        $status = $isBeasiswa ? 'studentambassador' : 'mahasiswa';
        $gender = in_array(strtoupper(trim((string) $row->gender)), ['P', 'PEREMPUAN', 'WANITA'], true) ? 'P' : 'L';
        $tanggalMasuk = $row->tgl_masuk ?: "{$angkatan}-09-01";

        $academicYear = $angkatan . '/' . ($angkatan + 1);
        $term = 'Ganjil';
        if (!empty($row->tgl_masuk) && $row->tgl_masuk !== '0000-00-00') {
            $month = (int) date('m', strtotime($row->tgl_masuk));
            $term = ($month >= 1 && $month <= 6) ? 'Genap' : 'Ganjil';
        }
        $periodeMasuk = "{$academicYear} {$term}";

        $nikClean = $row->nik ? substr(preg_replace('/\s+/', '', (string) $row->nik), 0, 16) : null;
        $nisnClean = $row->nisn ? substr(preg_replace('/\s+/', '', (string) $row->nisn), 0, 20) : null;
        $phoneClean = $row->phone ? substr(trim((string) $row->phone), 0, 15) : null;
        $placeBirthClean = $row->place_birth ? substr(trim((string) $row->place_birth), 0, 100) : null;
        $namaAyahClean = $row->nama_ayah ? substr(trim((string) $row->nama_ayah), 0, 250) : null;
        $namaIbuClean = $row->nama_ibu ? substr(trim((string) $row->nama_ibu), 0, 200) : null;

        $history = [];
        if ($semester !== null && $ipk !== null) {
            $history[(string) $semester] = (float) $ipk;
        }

        return [
            'id_lecturer'          => $defaultLecturerId,
            'nama_lengkap'         => $nama,
            'nim'                  => $nim,
            'password'             => $defaultPasswordHash,
            'angkatan'             => $angkatan,
            'periode_masuk'        => $periodeMasuk,
            'program_studi'        => $prodiFormatted !== '-' ? $prodiFormatted : 'Bisnis Digital',
            'status'               => $status,
            'semester'             => $semester,
            'ipk'                  => $ipk,
            'semester_ipk_history' => $history,
            'beasiswa'             => $beasiswa,
            'status_mahasiswa'     => 'Aktif',
            'email'                => $row->email ?: null,
            'no_telepon'           => $phoneClean ?: null,
            'alamat'               => $row->address ?: null,
            'nik'                  => $nikClean ?: null,
            'nisn'                 => $nisnClean ?: null,
            'tempat_lahir'         => $placeBirthClean ?: null,
            'tanggal_lahir'        => $row->date_birth ?: null,
            'nama_orangtua'        => $namaAyahClean ?: null,
            'nama_ibu_kandung'     => $namaIbuClean ?: null,
            'jenis_kelamin'        => $gender,
            'is_edited'            => 1,
            'tanggal_masuk'        => $tanggalMasuk,
        ];
    }

    private function resolveSemester(object $row): ?int
    {
        if (!empty($row->semester_mhs) && (int) $row->semester_mhs >= 1) {
            return (int) $row->semester_mhs;
        }
        if (!empty($row->fase_terakhir) && (int) $row->fase_terakhir >= 1) {
            return (int) $row->fase_terakhir;
        }
        if (!empty($row->semester_mulai) && (int) $row->semester_mulai >= 1) {
            return max(1, (int) $row->semester_mulai - 1);
        }
        return null;
    }

    private function resolveIpk(object $row): ?float
    {
        if ($row->ipk_acuan !== null && is_numeric($row->ipk_acuan)) {
            return (float) $row->ipk_acuan;
        }
        if ($row->ipk_terakhir !== null && is_numeric($row->ipk_terakhir)) {
            return (float) $row->ipk_terakhir;
        }
        return null;
    }

    private function resolveBeasiswa(object $row): ?string
    {
        $pct = null;
        if ($row->persentase_final !== null && is_numeric($row->persentase_final)) {
            $pct = (float) $row->persentase_final;
        } elseif ($row->persentase_rekomendasi !== null && is_numeric($row->persentase_rekomendasi)) {
            $pct = (float) $row->persentase_rekomendasi;
        } elseif ($row->beasiswa_sekarang !== null && is_numeric($row->beasiswa_sekarang)) {
            $pct = (float) $row->beasiswa_sekarang;
        }

        if ($pct === null || $pct <= 0) {
            return null;
        }

        return rtrim(rtrim(number_format($pct, 2), '0'), '.') . '%';
    }

    public function formatProdiName(string $rawProdi): string
    {
        $clean = trim($rawProdi);
        if ($clean === '' || $clean === '-') {
            return '-';
        }
        $withoutPrefix = preg_replace('/^PROGRAM\s+STUDI\s+/i', '', $clean);
        return ucwords(strtolower($withoutPrefix));
    }
}
