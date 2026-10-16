<?php

namespace App\Services;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class SiakadAcademicPeriodService
{
    private const CACHE_TTL_SECONDS = 300; // 5 menit

    /**
     * Ambil data periode akademik aktif saat ini dari REST API SIAKAD
     * Format return:
     * [
     *   'id' => 20,
     *   'id_batch_year' => 13,
     *   'tahun_akademik' => '2026/2027',
     *   'semester' => 'Ganjil',
     *   'code' => '20261',
     *   'is_active' => true,
     *   'display_name' => 'T.A. 2026/2027 - Ganjil',
     *   'source' => 'api' | 'database' | 'default'
     * ]
     */
    public static function getActivePeriod(bool $forceRefresh = false): array
    {
        if ($forceRefresh) {
            Cache::forget('siakad_academic_period_data');
        }

        $data = Cache::remember('siakad_academic_period_data', self::CACHE_TTL_SECONDS, function () {
            return self::fetchFromApi();
        });

        return $data['active_period'] ?? self::defaultPeriod();
    }

    /**
     * Ambil seluruh daftar periode akademik untuk dropdown filter
     */
    public static function getAllPeriods(bool $forceRefresh = false): array
    {
        if ($forceRefresh) {
            Cache::forget('siakad_academic_period_data');
        }

        $data = Cache::remember('siakad_academic_period_data', self::CACHE_TTL_SECONDS, function () {
            return self::fetchFromApi();
        });

        return $data['periods'] ?? [self::defaultPeriod()];
    }

    /**
     * Request HTTP ke endpoint REST API SIAKAD: /api/external/tahun-akademik
     */
    private static function fetchFromApi(): array
    {
        $apiUrl = config('services.siakad.api_url', env('SIAKAD_API_URL', 'http://127.0.0.1:8090'));
        $apiKey = config('services.siakad.api_key', env('SIAKAD_API_KEY', 'ush-secret-key-2026'));
        $timeout = (int) config('services.siakad.timeout', 10);

        $endpoint = rtrim($apiUrl, '/') . '/api/external/tahun-akademik';

        try {
            $response = Http::timeout($timeout)
                ->withHeaders([
                    'X-SIAKAD-API-KEY' => $apiKey,
                    'Accept'           => 'application/json',
                ])
                ->get($endpoint);

            if ($response->successful()) {
                $json = $response->json();
                if (!empty($json['success']) && !empty($json['active_period'])) {
                    $active = $json['active_period'];
                    $active['source'] = 'api';

                    return [
                        'active_period' => $active,
                        'periods'       => $json['periods'] ?? [$active],
                    ];
                }
            }

            Log::warning("SIAKAD REST API response error [{$endpoint}]: HTTP {$response->status()} - {$response->body()}");
        } catch (\Throwable $e) {
            Log::warning("Gagal koneksi REST API SIAKAD ({$endpoint}): " . $e->getMessage());
        }

        // Fallback: Jika koneksi langsung DB 'siakad' masih aktif (opsi cadangan jika server API SIAKAD offline di dev)
        if (config('database.connections.siakad.database')) {
            try {
                $dbResult = self::fetchFromDatabase();
                if ($dbResult !== null) {
                    return $dbResult;
                }
            } catch (\Throwable $dbEx) {
                Log::warning("Fallback DB SIAKAD juga tidak berhasil: " . $dbEx->getMessage());
            }
        }

        $default = self::defaultPeriod();
        return [
            'active_period' => $default,
            'periods'       => [$default],
        ];
    }

    /**
     * Fallback query direct database jika REST API SIAKAD belum menyala saat development
     */
    private static function fetchFromDatabase(): ?array
    {
        $rows = DB::connection('siakad')->table('sub_by as s')
            ->join('batch_year as b', 'b.id', '=', 's.id_batch_year')
            ->where('b.is_deleted', 'N')
            ->where('b.name', 'not like', '%PMB%')
            ->select('s.id', 'b.id as id_batch_year', 'b.name as tahun_akademik', 's.code', 's.name as semester', 's.status as is_active_raw')
            ->orderByDesc('s.code')
            ->get();

        if ($rows->isEmpty()) {
            return null;
        }

        $activePeriod = null;
        $periods = [];

        foreach ($rows as $row) {
            $isActive = (strtoupper((string) $row->is_active_raw) === 'Y');
            $semesterName = trim((string) $row->semester);
            $tahunAkademik = trim((string) $row->tahun_akademik);

            $periodData = [
                'id'             => (int) $row->id,
                'id_batch_year'  => (int) $row->id_batch_year,
                'tahun_akademik' => $tahunAkademik,
                'semester'       => $semesterName,
                'code'           => (string) $row->code,
                'is_active'      => $isActive,
                'display_name'   => "T.A. {$tahunAkademik} - {$semesterName}",
                'source'         => 'database',
            ];

            if ($isActive && $activePeriod === null) {
                $activePeriod = $periodData;
            }

            $periods[] = $periodData;
        }

        if ($activePeriod === null && !empty($periods)) {
            $activePeriod = $periods[0];
            $activePeriod['is_active'] = true;
        }

        return [
            'active_period' => $activePeriod,
            'periods'       => $periods,
        ];
    }

    /**
     * Default periode jika terjadi gangguan total koneksi
     */
    private static function defaultPeriod(): array
    {
        return [
            'id'             => 20,
            'id_batch_year'  => 13,
            'tahun_akademik' => '2026/2027',
            'semester'       => 'Ganjil',
            'code'           => '20261',
            'is_active'      => true,
            'display_name'   => 'T.A. 2026/2027 - Ganjil',
            'source'         => 'default',
        ];
    }
}
