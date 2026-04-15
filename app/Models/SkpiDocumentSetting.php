<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SkpiDocumentSetting extends Model
{
    protected $fillable = [
        'nomor_skpi',
        'authorization_place_date',
        'vice_rector_name',
        'vice_rector_title',
        'signature_path',
        'updated_by',
    ];

    public function updater(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by');
    }
}
