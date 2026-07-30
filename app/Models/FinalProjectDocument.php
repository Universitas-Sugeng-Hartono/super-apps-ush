<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class FinalProjectDocument extends Model
{
    protected $fillable = [
        'final_project_id',
        'document_type',
        'title',
        'file_path',
        'version',
        'uploaded_by',
        'uploaded_at',
        'review_status',
        'reviewer_id',
        'review_notes',
        'reviewed_at',
    ];

    protected $casts = [
        'uploaded_at' => 'datetime',
        'reviewed_at' => 'datetime',
        'version' => 'integer',
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

    public function uploader(): BelongsTo
    {
        return $this->belongsTo(Student::class, 'uploaded_by');
    }

    public function reviewer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reviewer_id');
    }

    /*
    |--------------------------------------------------------------------------
    | Query Scopes
    |--------------------------------------------------------------------------
    */

    public function scopePendingReview($query)
    {
        return $query->where('review_status', 'pending');
    }

    public function scopeApproved($query)
    {
        return $query->where('review_status', 'approved');
    }

    public function scopeByType($query, $type)
    {
        return $query->where('document_type', $type);
    }
}
