<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SkpiRegistration extends Model
{
    protected $fillable = [
        'student_id',
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
}
