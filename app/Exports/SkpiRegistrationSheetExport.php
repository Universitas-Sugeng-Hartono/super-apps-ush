<?php

namespace App\Exports;

use App\Models\SkpiRegistration;
use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class SkpiRegistrationSheetExport implements FromArray, ShouldAutoSize, WithTitle, WithStyles
{
    private $registration;

    public function __construct(SkpiRegistration $registration)
    {
        $this->registration = $registration;
    }

    public function array(): array
    {
        $student = $this->registration->student;
        $dosenPA = $student ? $student->dosenPA : null;

        $statusLabel = 'Pending';
        if ($this->registration->status == 'approved') $statusLabel = 'Disetujui';
        if ($this->registration->status == 'rejected') $statusLabel = 'Ditolak';

        return [
            ['DATA PROFIL MAHASISWA & PENGAJUAN SKPI', ''],
            ['', ''],
            ['A. Data Pribadi', ''],
            ['Nama Lengkap', $student->nama_lengkap ?? '-'],
            ['NIM', $student->nim ?? '-'],
            ['NIK', $student->nik ?? '-'],
            ['NISN', $student->nisn ?? '-'],
            ['Email', $student->email ?? '-'],
            ['Jenis Kelamin', $student->jenis_kelamin ?? '-'],
            ['Tempat Lahir', $student->tempat_lahir ?? '-'],
            ['Tanggal Lahir', $student->tanggal_lahir ? \Carbon\Carbon::parse($student->tanggal_lahir)->format('d/m/Y') : '-'],
            ['Alamat', $student->alamat ?? '-'],
            ['No Telepon', $student->no_telepon ?? '-'],
            ['', ''],
            ['B. Data Orang Tua / Wali', ''],
            ['Nama Ayah/Wali', $student->nama_orangtua ?? '-'],
            ['Nama Ibu Kandung', $student->nama_ibu_kandung ?? '-'],
            ['', ''],
            ['C. Data Akademik', ''],
            ['Program Studi', $student->program_studi ?? '-'],
            ['Fakultas', $student->fakultas ?? '-'],
            ['Angkatan', $student->angkatan ?? '-'],
            ['Status Mahasiswa', $student->status_mahasiswa ?? '-'],
            ['Tanggal Masuk', $student->tanggal_masuk ? \Carbon\Carbon::parse($student->tanggal_masuk)->format('d/m/Y') : '-'],
            ['Tanggal Lulus', $student->tanggal_lulus ? \Carbon\Carbon::parse($student->tanggal_lulus)->format('d/m/Y') : '-'],
            ['IPK', $student->ipk ?? '-'],
            ['Total SKS', $student->sks ?? '-'],
            ['Dosen PA', $dosenPA->name ?? '-'],
            ['', ''],
            ['D. Data Pengajuan SKPI', ''],
            ['Status Pendaftaran', $statusLabel],
            ['Nomor Ijazah', $this->registration->nomor_ijazah ?? '-'],
            ['Tanggal Pengajuan', $this->registration->created_at ? $this->registration->created_at->format('d/m/Y H:i:s') : '-'],
            ['Tanggal Update Status', $this->registration->updated_at ? $this->registration->updated_at->format('d/m/Y H:i:s') : '-'],
            ['Catatan Admin', $this->registration->catatan ?? '-'],
        ];
    }

    public function title(): string
    {
        $title = $this->registration->student->nama_lengkap ?? $this->registration->student->nim ?? 'Student';
        $cleanTitle = substr(str_replace(['*', ':', '/', '\\', '?', '[', ']'], '', $title), 0, 31);
        
        if (empty(trim($cleanTitle))) {
            $cleanTitle = $this->registration->student->nim ?? 'Student';
        }
        
        return $cleanTitle;
    }

    public function styles(Worksheet $sheet)
    {
        // Merge cells for headings
        $sheet->mergeCells('A1:B1');
        $sheet->mergeCells('A3:B3');
        $sheet->mergeCells('A15:B15');
        $sheet->mergeCells('A19:B19');
        $sheet->mergeCells('A30:B30');

        return [
            
            1 => ['font' => ['bold' => true, 'size' => 14]],
            // Style section headers
            3 => ['font' => ['bold' => true], 'fill' => ['fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID, 'color' => ['argb' => 'FFE5E7EB']]],
            15 => ['font' => ['bold' => true], 'fill' => ['fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID, 'color' => ['argb' => 'FFE5E7EB']]],
            19 => ['font' => ['bold' => true], 'fill' => ['fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID, 'color' => ['argb' => 'FFE5E7EB']]],
            30 => ['font' => ['bold' => true], 'fill' => ['fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID, 'color' => ['argb' => 'FFE5E7EB']]],
            // Style the first column (labels)
            'A' => ['font' => ['bold' => true]],
        ];
    }
}
