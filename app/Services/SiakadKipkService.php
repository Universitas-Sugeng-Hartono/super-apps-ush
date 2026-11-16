<?php

namespace App\Services;

use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class SiakadKipkService
{
    private string $apiUrl;
    private string $apiKey;
    private int $timeout;

    public function __construct()
    {
        $this->apiUrl = rtrim((string) config('services.siakad.api_url', env('SIAKAD_API_URL', 'http://127.0.0.1:8090')), '/');
        $this->apiKey = (string) config('services.siakad.api_key', env('SIAKAD_API_KEY', 'ush-secret-key-2026'));
        $this->timeout = (int) config('services.siakad.timeout', env('SIAKAD_API_TIMEOUT', 30));
    }

    /**
     * Mengambil daftar Tahun Akademik & Periode dari SIAKAD
     */
    public function getAcademicYears(): array
    {
        $endpoint = "{$this->apiUrl}/api/external/beasiswa-kipk/academic-years";

        try {
            $response = Http::timeout($this->timeout)
                ->withHeaders([
                    'X-SIAKAD-API-KEY' => $this->apiKey,
                    'Accept' => 'application/json',
                ])
                ->get($endpoint);

            if ($response->successful()) {
                $body = $response->json();
                return [
                    'success' => true,
                    'academic_years' => $body['academic_years'] ?? [],
                    'prodis' => $body['prodis'] ?? [],
                    'statuses' => $body['statuses'] ?? ['AKTIF', 'CUTI', 'NON-AKTIF'],
                ];
            }

            Log::warning("SiakadKipkService@getAcademicYears failed: HTTP {$response->status()}");
            return [
                'success' => false,
                'message' => 'Gagal mengambil data dari SIAKAD (HTTP ' . $response->status() . ')',
                'academic_years' => [],
                'prodis' => [],
                'statuses' => ['AKTIF', 'CUTI', 'NON-AKTIF'],
            ];
        } catch (\Throwable $e) {
            Log::error("SiakadKipkService@getAcademicYears connection error: " . $e->getMessage());
            return [
                'success' => false,
                'message' => 'Koneksi ke SIAKAD bermasalah: ' . $e->getMessage(),
                'academic_years' => [],
                'prodis' => [],
                'statuses' => ['AKTIF', 'CUTI', 'NON-AKTIF'],
            ];
        }
    }

    /**
     * Mengambil sub_by berdasarkan ID batch year
     */
    public function getSubBy(int $idBy): array
    {
        $endpoint = "{$this->apiUrl}/api/external/beasiswa-kipk/sub-by/{$idBy}";

        try {
            $response = Http::timeout($this->timeout)
                ->withHeaders([
                    'X-SIAKAD-API-KEY' => $this->apiKey,
                    'Accept' => 'application/json',
                ])
                ->get($endpoint);

            if ($response->successful()) {
                $body = $response->json();
                return $body['sub_batch_years'] ?? [];
            }

            return [];
        } catch (\Throwable $e) {
            Log::error("SiakadKipkService@getSubBy connection error: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Mengambil data mahasiswa KIP-K dengan filtering
     */
    public function getKipkData(array $filters = []): array
    {
        $endpoint = "{$this->apiUrl}/api/external/beasiswa-kipk/data";

        try {
            $response = Http::timeout($this->timeout)
                ->withHeaders([
                    'X-SIAKAD-API-KEY' => $this->apiKey,
                    'Accept' => 'application/json',
                ])
                ->get($endpoint, $filters);

            if ($response->successful()) {
                $body = $response->json();
                return [
                    'success' => true,
                    'statistics' => $body['statistics'] ?? [],
                    'total' => $body['total'] ?? 0,
                    'data' => $body['data'] ?? [],
                ];
            }

            $errMsg = $response->json('message') ?? ('HTTP ' . $response->status());
            Log::warning("SiakadKipkService@getKipkData failed: {$errMsg}");
            return [
                'success' => false,
                'message' => $errMsg,
                'statistics' => [
                    'total_mahasiswa' => 0,
                    'aktif_count' => 0,
                    'cuti_count' => 0,
                    'lainnya_count' => 0,
                    'avg_ipk' => 0,
                    'avg_ips' => 0,
                ],
                'total' => 0,
                'data' => [],
            ];
        } catch (\Throwable $e) {
            Log::error("SiakadKipkService@getKipkData connection error: " . $e->getMessage());
            return [
                'success' => false,
                'message' => 'Gagal terhubung ke SIAKAD REST API: ' . $e->getMessage(),
                'statistics' => [
                    'total_mahasiswa' => 0,
                    'aktif_count' => 0,
                    'cuti_count' => 0,
                    'lainnya_count' => 0,
                    'avg_ipk' => 0,
                    'avg_ips' => 0,
                ],
                'total' => 0,
                'data' => [],
            ];
        }
    }

    /**
     * Mengambil riwayat semester mahasiswa (IPS, IPK, SKS) dari SIAKAD
     */
    public function getStudentHistory(int $id): array
    {
        $endpoint = "{$this->apiUrl}/api/external/beasiswa-kipk/student-history/{$id}";

        try {
            $response = Http::timeout($this->timeout)
                ->withHeaders([
                    'X-SIAKAD-API-KEY' => $this->apiKey,
                    'Accept' => 'application/json',
                ])
                ->get($endpoint);

            if ($response->successful()) {
                return $response->json();
            }

            return [
                'success' => false,
                'message' => 'Gagal memuat riwayat: ' . ($response->json('message') ?? 'HTTP ' . $response->status()),
            ];
        } catch (\Throwable $e) {
            Log::error("SiakadKipkService@getStudentHistory error: " . $e->getMessage());
            return [
                'success' => false,
                'message' => 'Koneksi ke SIAKAD bermasalah: ' . $e->getMessage(),
            ];
        }
    }
}
