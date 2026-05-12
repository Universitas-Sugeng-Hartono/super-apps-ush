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
        'nomor_ijazah',
        'status',
        'approval_notes',
        'approved_by',
        'approved_at',
        'submitted_at',
        'skpi_document',
        'skpi_generated_at',
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
