<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\HasMany;

class FinalProject extends Model
{
    protected $fillable = [
        'student_id',
        'title',
        'title_en',
        'title_approved_at',
        'supervisor_1_id',
        'supervisor_2_id',
        'status',
        'progress_percentage',
        'started_at',
        'completed_at',
    ];

    protected $casts = [
        'title_approved_at' => 'datetime',
        'started_at' => 'datetime',
        'completed_at' => 'datetime',
        'progress_percentage' => 'integer',
    ];

    /*
    |--------------------------------------------------------------------------
    | Relationships
    |--------------------------------------------------------------------------
    */

    public function student(): BelongsTo
    {
        return $this->belongsTo(Student::class, 'student_id');
    }

    public function supervisor1(): BelongsTo
    {
        return $this->belongsTo(User::class, 'supervisor_1_id');
    }

    public function supervisor2(): BelongsTo
    {
        return $this->belongsTo(User::class, 'supervisor_2_id');
    }

    public function proposal(): HasOne
    {
        return $this->hasOne(FinalProjectProposal::class, 'final_project_id');
    }

    public function defense(): HasOne
    {
        return $this->hasOne(FinalProjectDefense::class, 'final_project_id');
    }

    public function guidanceLogs(): HasMany
    {
        return $this->hasMany(FinalProjectGuidanceLog::class, 'final_project_id');
    }

    public function documents(): HasMany
    {
        return $this->hasMany(FinalProjectDocument::class, 'final_project_id');
    }

    /*
    |--------------------------------------------------------------------------
    | Query Scopes
    |--------------------------------------------------------------------------
    */

    public function scopeBySupervisor($query, $supervisorId)
    {
        return $query->where(function ($q) use ($supervisorId) {
            $q->where('supervisor_1_id', $supervisorId)
              ->orWhere('supervisor_2_id', $supervisorId);
        });
    }

    public function scopeByStatus($query, $status)
    {
        return $query->where('status', $status);
    }

    public function scopeActive($query)
    {
        return $query->whereIn('status', ['proposal', 'research', 'defense']);
    }
}
