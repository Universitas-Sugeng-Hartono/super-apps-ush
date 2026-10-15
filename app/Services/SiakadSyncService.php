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
            Log::warning("Koneksi HTTP SIAKAD API bermasalah ({$endpoint}): " . $apiEx->getMessage());

            // Fallback: Jika koneksi langsung DB 'siakad' masih dikonfigurasi di database.php
            if (config('database.connections.siakad.database')) {
                try {
                    Log::info("Mencoba fallback ke direct database connection 'siakad'...");
                    return $this->fetchCandidatesFromDatabase($nim);
                } catch (\Throwable $dbEx) {
                    Log::error("Fallback direct database Siakad juga gagal: " . $dbEx->getMessage());
                }
            }

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
     */
    public function preview(): array
    {
        $candidates = $this->fetchCandidates();
        $nims = $candidates->pluck('nim')->filter(fn ($n) => $n && $n !== '-')->values();
        $existingNims = Student::whereIn('nim', $nims)->pluck('nim')->toArray();

        $total = $candidates->count();
        $updateCount = count($existingNims);
        $newCount = $total - $updateCount;

        $sample = $candidates->take(5)->map(function ($c) use ($existingNims) {
            $beasiswaVal = $c->persentase_final ?: ($c->persentase_rekomendasi ?: $c->beasiswa_sekarang);
            return [
                'nim' => $c->nim,
                'name' => $c->name,
                'prodi' => $this->formatProdiName((string) $c->prodi),
                'semester' => $c->semester_mhs ?: $c->fase_terakhir,
                'ipk' => $c->ipk_acuan ?: $c->ipk_terakhir,
                'beasiswa' => $beasiswaVal !== null ? (rtrim(rtrim(number_format((float) $beasiswaVal, 2), '0'), '.') . '%') : '-',
                'is_existing' => in_array($c->nim, $existingNims, true),
            ];
        });

        return [
            'total'        => $total,
            'new_count'    => $newCount,
            'update_count' => $updateCount,
            'sample'       => $sample,
        ];
    }

    /**
     * Ekstraksi data update untuk record Student yang sudah ada
     */
    private function extractUpdateData(Student $student, object $row): array
    {
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
        $beasiswa = $this->resolveBeasiswa($row);
        $gender = in_array(strtoupper(trim((string) $row->gender)), ['P', 'PEREMPUAN', 'WANITA'], true) ? 'P' : 'L';

        $nikClean = $row->nik ? substr(preg_replace('/\s+/', '', (string) $row->nik), 0, 16) : null;
        $nisnClean = $row->nisn ? substr(preg_replace('/\s+/', '', (string) $row->nisn), 0, 20) : null;
        $phoneClean = $row->phone ? substr(trim((string) $row->phone), 0, 15) : null;
        $addressClean = $row->address ? trim((string) $row->address) : null;
        $placeBirthClean = $row->place_birth ? substr(trim((string) $row->place_birth), 0, 100) : null;
        $namaAyahClean = $row->nama_ayah ? substr(trim((string) $row->nama_ayah), 0, 250) : null;
        $namaIbuClean = $row->nama_ibu ? substr(trim((string) $row->nama_ibu), 0, 200) : null;

        return [
            'nama_lengkap'     => $nama ?: $student->nama_lengkap,
            'angkatan'         => $angkatan ?: $student->angkatan,
            'program_studi'    => $prodiFormatted !== '-' ? $prodiFormatted : $student->program_studi,
            'status'           => $student->status ?: 'studentambassador',
            'semester'         => $semester !== null ? $semester : $student->semester,
            'ipk'              => $ipk !== null ? $ipk : $student->ipk,
            'beasiswa'         => $beasiswa ?: $student->beasiswa,
            'email'            => $row->email ?: $student->email,
            'no_telepon'       => $phoneClean ?: $student->no_telepon,
            'alamat'           => $addressClean ?: $student->alamat,
            'nik'              => $nikClean ?: $student->nik,
            'nisn'             => $nisnClean ?: $student->nisn,
            'tempat_lahir'     => $placeBirthClean ?: $student->tempat_lahir,
            'tanggal_lahir'    => $row->date_birth ?: $student->tanggal_lahir,
            'nama_orangtua'    => $namaAyahClean ?: $student->nama_orangtua,
            'nama_ibu_kandung' => $namaIbuClean ?: $student->nama_ibu_kandung,
            'jenis_kelamin'    => $gender,
            'is_edited'        => 1,
        ];
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
        $beasiswa = $this->resolveBeasiswa($row);
        $gender = in_array(strtoupper(trim((string) $row->gender)), ['P', 'PEREMPUAN', 'WANITA'], true) ? 'P' : 'L';
        $tanggalMasuk = $row->tgl_masuk ?: "{$angkatan}-09-01";

        $nikClean = $row->nik ? substr(preg_replace('/\s+/', '', (string) $row->nik), 0, 16) : null;
        $nisnClean = $row->nisn ? substr(preg_replace('/\s+/', '', (string) $row->nisn), 0, 20) : null;
        $phoneClean = $row->phone ? substr(trim((string) $row->phone), 0, 15) : null;
        $placeBirthClean = $row->place_birth ? substr(trim((string) $row->place_birth), 0, 100) : null;
        $namaAyahClean = $row->nama_ayah ? substr(trim((string) $row->nama_ayah), 0, 250) : null;
        $namaIbuClean = $row->nama_ibu ? substr(trim((string) $row->nama_ibu), 0, 200) : null;

        return [
            'id_lecturer'      => $defaultLecturerId,
            'nama_lengkap'     => $nama,
            'nim'              => $nim,
            'password'         => $defaultPasswordHash,
            'angkatan'         => $angkatan,
            'program_studi'    => $prodiFormatted !== '-' ? $prodiFormatted : 'Bisnis Digital',
            'status'           => 'studentambassador',
            'semester'         => $semester,
            'ipk'              => $ipk,
            'beasiswa'         => $beasiswa,
            'status_mahasiswa' => 'Aktif',
            'email'            => $row->email ?: null,
            'no_telepon'       => $phoneClean ?: null,
            'alamat'           => $row->address ?: null,
            'nik'              => $nikClean ?: null,
            'nisn'             => $nisnClean ?: null,
            'tempat_lahir'     => $placeBirthClean ?: null,
            'tanggal_lahir'    => $row->date_birth ?: null,
            'nama_orangtua'    => $namaAyahClean ?: null,
            'nama_ibu_kandung' => $namaIbuClean ?: null,
            'jenis_kelamin'    => $gender,
            'is_edited'        => 1,
            'tanggal_masuk'    => $tanggalMasuk,
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

        return $pct !== null ? (rtrim(rtrim(number_format($pct, 2), '0'), '.') . '%') : null;
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

    private function fetchCandidatesFromDatabase(?string $nim = null): Collection
    {
        $db = DB::connection('siakad');

        $semesterTertinggi = $db->table('ips_ipk')
            ->selectRaw('id_mahasiswa, MAX(CAST(semester AS UNSIGNED)) as smt')
            ->groupBy('id_mahasiswa');

        $barisTerbaru = $db->table('ips_ipk as i')
            ->joinSub($semesterTertinggi, 's', function ($join) {
                $join->on('s.id_mahasiswa', '=', 'i.id_mahasiswa')
                    ->whereRaw('CAST(i.semester AS UNSIGNED) = s.smt');
            })
            ->selectRaw('i.id_mahasiswa, MAX(i.id) as id')
            ->groupBy('i.id_mahasiswa');

        $ipsSub = $db->table('ips_ipk as ii')
            ->joinSub($barisTerbaru, 'b', 'b.id', '=', 'ii.id')
            ->selectRaw('ii.id_mahasiswa, ii.ips, ii.ipk, ii.semester');

        $terminTerakhir = $db->table('tagihan_termin_mahasiswa')
            ->selectRaw('id_registration, MAX(fase) as fase_terakhir, MAX(termin) as termin_terakhir')
            ->groupBy('id_registration');

        $query = $db->table('mahasiswa as m')
            ->join('registration as r', 'r.id_user', '=', 'm.id_user')
            ->joinSub($terminTerakhir, 't', 't.id_registration', '=', 'r.id')
            ->leftJoin('prodi as p', 'p.id', '=', 'm.id_prodi')
            ->leftJoinSub($ipsSub, 'n', 'n.id_mahasiswa', '=', 'm.id')
            ->leftJoin('rekomendasi_beasiswa_termin as k', function ($join) {
                $join->on('k.id_registration', '=', 'r.id')
                    ->on('k.tahun_ke', '=', DB::raw('FLOOR(t.fase_terakhir / 3) + 1'));
            })
            ->where('m.tahun_masuk', '>=', 2026)
            ->whereRaw('t.fase_terakhir % 3 = 0')
            ->selectRaw('m.id as id_mahasiswa, r.id as id_registration,
                COALESCE(m.nim, "-") as nim,
                COALESCE(m.name, r.name, "-") as name,
                COALESCE(p.name, "-") as prodi,
                m.tahun_masuk,
                m.gender,
                m.email,
                COALESCE(m.phone, r.phone) as phone,
                COALESCE(m.address, r.address, m.alamat_mahasiswa) as address,
                m.nik,
                m.nisn,
                m.place_birth,
                m.date_birth,
                m.tgl_masuk,
                m.nama_ayah,
                m.nama_ibu,
                COALESCE(m.semester, 0) as semester_mhs,
                t.fase_terakhir,
                (t.fase_terakhir + 1) as semester_mulai,
                (FLOOR(t.fase_terakhir / 3) + 1) as tahun_ke,
                COALESCE(r.beasiswa, 0) as beasiswa_sekarang,
                n.ips as ips_terakhir,
                n.ipk as ipk_terakhir,
                k.id as id_rekomendasi,
                k.status as status_rekomendasi,
                k.persentase_rekomendasi,
                k.persentase_final,
                k.ipk_acuan,
                k.ips_acuan')
            ->orderBy('m.nim');

        if ($nim !== null && $nim !== '') {
            $query->where('m.nim', $nim);
        }

        return $query->get();
    }
}
