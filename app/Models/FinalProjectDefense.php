<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class FinalProjectDefense extends Model
{
    protected $fillable = [
        'final_project_id',
        'registered_at',
        'scheduled_at',
        'status',
        'approval_notes',
        'approved_by',
        'approved_at',
        'final_grade',
        'result_notes',
        'examiner_notes',
    ];

    protected $casts = [
        'registered_at' => 'datetime',
        'scheduled_at' => 'datetime',
        'approved_at' => 'datetime',
        'final_grade' => 'decimal:2',
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

    public function approver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
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
}
