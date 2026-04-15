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
        'nomor_ijazah',
        'gelar',
        'status',
        'approval_notes',
        'approved_by',
        'approved_at',
        'submitted_at',
    ];

    protected $casts = [
        'tanggal_lahir' => 'date',
        'approved_at' => 'datetime',
        'submitted_at' => 'datetime',
    ];

    public function student(): BelongsTo
    {
        return $this->belongsTo(Student::class);
    }

    public function approver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }
}
