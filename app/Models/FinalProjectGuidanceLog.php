<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class FinalProjectGuidanceLog extends Model
{
    protected $fillable = [
        'final_project_id',
        'supervisor_id',
        'guidance_date',
        'materials_discussed',
        'student_notes',
        'supervisor_feedback',
        'file_path',
        'status',
        'approved_at',
    ];

    protected $casts = [
        'guidance_date' => 'date',
        'approved_at' => 'datetime',
    ];

    /*
    |--------------------------------------------------------------------------
    | Relationships
    |--------------------------------------------------------------------------
    */

    public function finalProject(): BelongsTo
    {
        return $this->belongsTo(FinalProject::class, 'final_project_id');
    }

    public function supervisor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'supervisor_id');
    }

    /*
    |--------------------------------------------------------------------------
    | Query Scopes
    |--------------------------------------------------------------------------
    */

    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    public function scopeApproved($query)
    {
        return $query->where('status', 'approved');
    }

    public function scopeBySupervisor($query, $supervisorId)
    {
        return $query->where('supervisor_id', $supervisorId);
    }
}
