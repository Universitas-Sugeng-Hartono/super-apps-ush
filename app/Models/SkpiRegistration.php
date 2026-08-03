<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SkpiRegistration extends Model
{
    protected $fillable = [
        'student_id',
        'nomor_skpi',
        'nama_lengkap',
        'tempat_lahir',
        'tanggal_lahir',
        'nim',
        'angkatan',
        'gelar',
        'ipk',
        'sks',
        'judul_ta_indo',
        'judul_ta_inggris',
        'periode_lulus',
        'lama_studi',
        'nomor_ijazah',
        'doc_ijasah',
        'doc_ktp',
        'doc_pembayaran_wisuda',
        'doc_naskah_publikasi',
        'status',
        'approval_notes',
        'approved_by',
        'approved_at',
        'submitted_at',
        'skpi_document',
        'skpi_generated_at',
        'payment_status',
        'payment_approval_notes',
    ];

    protected $casts = [
        'tanggal_lahir'      => 'date',
        'approved_at'        => 'datetime',
        'submitted_at'       => 'datetime',
        'skpi_generated_at'  => 'datetime',
    ];

    /** Apakah file SKPI sudah pernah di-generate dan tersimpan. */
    public function hasGeneratedDocument(): bool
    {
        return !empty($this->skpi_document);
    }

    public function student(): BelongsTo
    {
        return $this->belongsTo(Student::class);
    }

    public function approver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    /**
     * Hitung lama studi secara otomatis dari tanggal masuk / angkatan hingga periode lulus.
     */
    public static function calculateLamaStudi(?string $tanggalMasuk, ?int $angkatan, ?string $periodeLulus): ?string
    {
        if (!$periodeLulus) {
            return null;
        }

        $lulusDate = \Carbon\Carbon::parse(strlen($periodeLulus) === 7 ? $periodeLulus . '-01' : $periodeLulus);

        if ($tanggalMasuk) {
            $masukDate = \Carbon\Carbon::parse($tanggalMasuk);
            if ($angkatan && $masukDate->year > $angkatan) {
                $masukDate = \Carbon\Carbon::createFromDate($angkatan, 9, 1);
            }
        } elseif ($angkatan) {
            $masukDate = \Carbon\Carbon::createFromDate($angkatan, 9, 1);
        } else {
            return null;
        }

        if ($lulusDate->lt($masukDate)) {
            return 'Data Tidak Valid';
        }

        $diffInMonths = ($lulusDate->year - $masukDate->year) * 12 - $masukDate->month + $lulusDate->month;

        if ($diffInMonths <= 0) {
            return '0 Bulan';
        }

        $years = intdiv($diffInMonths, 12);
        $months = $diffInMonths % 12;

        $parts = [];
        if ($years > 0) {
            $parts[] = "{$years} Tahun";
        }
        if ($months > 0) {
            $parts[] = "{$months} Bulan";
        }

        return implode(' ', $parts);
    }
}
