<?php

namespace App\Http\Controllers\AdminController;

use App\Http\Controllers\Controller;
use App\Services\SiakadKipkService;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\StreamedResponse;

class MonitoringKipkController extends Controller
{
    protected SiakadKipkService $kipkService;

    public function __construct(SiakadKipkService $kipkService)
    {
        $this->kipkService = $kipkService;
    }

    /**
     * Tampilan utama halaman Monitoring KIP-K Kemahasiswaan
     */
    public function index(Request $request)
    {
        $yearsData = $this->kipkService->getAcademicYears();
        $academicYears = $yearsData['academic_years'] ?? [];
        $prodis = $yearsData['prodis'] ?? [];
        $statuses = $yearsData['statuses'] ?? ['AKTIF', 'CUTI', 'NON-AKTIF'];

        // Tentukan tahun akademik dan periode jika ada request
        $selectedBy = $request->input('id_by');
        $selectedSubBy = $request->input('id_sub_by');
        $subBatchYears = [];
        $hasSelectedPeriod = false;

        if (!empty($selectedBy)) {
            foreach ($academicYears as $year) {
                if ((string) $year['id'] === (string) $selectedBy) {
                    $subBatchYears = $year['sub_batch_years'] ?? [];
                    break;
                }
            }
        }

        if (!empty($selectedBy) && !empty($selectedSubBy)) {
            $hasSelectedPeriod = true;
            $filters = [
                'id_by' => $selectedBy,
                'id_sub_by' => $selectedSubBy,
                'prodi' => $request->input('prodi', ''),
                'status' => $request->input('status', ''),
                'search' => $request->input('search', ''),
            ];

            $kipkResult = $this->kipkService->getKipkData($filters);
            $statistics = $kipkResult['statistics'] ?? [
                'total_mahasiswa' => 0,
                'aktif_count' => 0,
                'cuti_count' => 0,
                'lainnya_count' => 0,
                'avg_ipk' => 0,
                'avg_ips' => 0,
                'active_batch_year' => '-',
                'active_sub_by' => '-',
            ];
            $students = $kipkResult['data'] ?? [];
            $isApiSuccess = $kipkResult['success'] ?? false;
            $apiErrorMessage = $kipkResult['message'] ?? null;
        } else {
            // Awal load dibikin kosong sebelum memilih tahun akademik & periode
            $filters = [
                'id_by' => $selectedBy ?? '',
                'id_sub_by' => $selectedSubBy ?? '',
                'prodi' => $request->input('prodi', ''),
                'status' => $request->input('status', ''),
                'search' => $request->input('search', ''),
            ];
            $statistics = [
                'total_mahasiswa' => 0,
                'aktif_count' => 0,
                'cuti_count' => 0,
                'lainnya_count' => 0,
                'avg_ipk' => 0,
                'avg_ips' => 0,
                'active_batch_year' => '-',
                'active_sub_by' => '-',
            ];
            $students = [];
            $isApiSuccess = $yearsData['success'] ?? true;
            $apiErrorMessage = $yearsData['message'] ?? null;
        }

        return view('kemahasiswaan.monitoring-kipk.index', compact(
            'academicYears',
            'prodis',
            'statuses',
            'selectedBy',
            'selectedSubBy',
            'subBatchYears',
            'hasSelectedPeriod',
            'filters',
            'statistics',
            'students',
            'isApiSuccess',
            'apiErrorMessage'
        ));
    }

    /**
     * AJAX endpoint data KIP-K untuk refresh / filter tabel
     */
    public function getData(Request $request)
    {
        if (empty($request->input('id_by')) || empty($request->input('id_sub_by'))) {
            return response()->json([
                'success' => true,
                'has_period' => false,
                'statistics' => [
                    'total_mahasiswa' => 0,
                    'aktif_count' => 0,
                    'cuti_count' => 0,
                    'lainnya_count' => 0,
                    'avg_ipk' => 0,
                    'avg_ips' => 0,
                    'active_batch_year' => '-',
                    'active_sub_by' => '-',
                ],
                'total' => 0,
                'data' => [],
            ]);
        }

        $filters = [
            'id_by' => $request->input('id_by'),
            'id_sub_by' => $request->input('id_sub_by'),
            'prodi' => $request->input('prodi'),
            'status' => $request->input('status'),
            'search' => $request->input('search'),
        ];

        $data = $this->kipkService->getKipkData($filters);
        return response()->json($data);
    }

    /**
     * AJAX endpoint untuk mendapatkan sub_by berdasarkan batch_year
     */
    public function getSubBy(Request $request, $id)
    {
        $subBys = $this->kipkService->getSubBy((int) $id);
        return response()->json([
            'success' => true,
            'sub_batch_years' => $subBys,
        ]);
    }

    /**
     * AJAX endpoint untuk mendapatkan riwayat semester mahasiswa
     */
    public function getStudentHistory(Request $request, $id)
    {
        $data = $this->kipkService->getStudentHistory((int) $id);
        return response()->json($data);
    }

    /**
     * Export data Monitoring KIP-K ke format CSV
     */
    public function exportCsv(Request $request)
    {
        $filters = [
            'id_by' => $request->input('id_by'),
            'id_sub_by' => $request->input('id_sub_by'),
            'prodi' => $request->input('prodi'),
            'status' => $request->input('status'),
            'search' => $request->input('search'),
        ];

        $result = $this->kipkService->getKipkData($filters);
        $students = $result['data'] ?? [];
        $stats = $result['statistics'] ?? [];
        $tahunName = str_replace('/', '-', $stats['active_batch_year'] ?? 'Tahun');
        $periodeName = $stats['active_sub_by'] ?? 'Periode';

        $fileName = 'monitoring_kipk_' . $tahunName . '_' . $periodeName . '_' . date('Ymd_His') . '.csv';

        return new StreamedResponse(function () use ($students, $stats) {
            $handle = fopen('php://output', 'w');

            // Header informasi dokumen
            fputcsv($handle, ['UNIVERSITAS SUGENG HARTONO - MONITORING BEASISWA KIP-K']);
            fputcsv($handle, ['Tahun Akademik: ' . ($stats['active_batch_year'] ?? '-') . ' | Periode: ' . ($stats['active_sub_by'] ?? '-')]);
            fputcsv($handle, ['Waktu Cetak: ' . date('d-m-Y H:i:s')]);
            fputcsv($handle, []);

            // Baris header kolom
            fputcsv($handle, [
                'No',
                'NIM',
                'Nama Mahasiswa',
                'Program Studi',
                'Semester',
                'IPS',
                'IPK',
                'Status Mahasiswa',
                'Tahun Masuk',
                'Jenis Kelamin',
                'No. Telepon',
                'Email',
            ]);

            $i = 1;
            foreach ($students as $s) {
                fputcsv($handle, [
                    $i++,
                    $s['nim'] ?? '-',
                    $s['name'] ?? '-',
                    $s['prodi'] ?? '-',
                    $s['semester'] ?? '-',
                    number_format((float) ($s['ips'] ?? 0), 2),
                    number_format((float) ($s['ipk'] ?? 0), 2),
                    $s['status'] ?? 'AKTIF',
                    $s['tahun_masuk'] ?? '-',
                    $s['gender'] ?? '-',
                    $s['phone'] ?? '-',
                    $s['email'] ?? '-',
                ]);
            }

            fclose($handle);
        }, 200, [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="' . $fileName . '"',
        ]);
    }
}
