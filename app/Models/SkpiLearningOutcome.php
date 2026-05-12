<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SkpiLearningOutcome extends Model
{
    protected $table = 'skpi_learning_outcomes';

    protected $fillable = [
        'study_program_id',
        'cp_sikap',
        'cp_sikap_en',
        'cp_pengetahuan',
        'cp_pengetahuan_en',
    ];

    protected $casts = [
        'cp_sikap'          => 'array',
        'cp_sikap_en'       => 'array',
        'cp_pengetahuan'    => 'array',
        'cp_pengetahuan_en' => 'array',
    ];

    /**
     * Relasi ke StudyProgram.
     */
    public function studyProgram(): BelongsTo
    {
        return $this->belongsTo(StudyProgram::class);
    }
}
