<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class StudentAchievement extends Model
{
    public const CATEGORY_WAJIB = 'wajib';
    public const CATEGORY_ORGANISASI = 'organisasi';
    public const CATEGORY_PENALARAN = 'penalaran';
    public const CATEGORY_MINAT_BAKAT = 'minat_bakat';
    public const CATEGORY_KEPEDULIAN_SOSIAL = 'kepedulian_sosial';
    public const CATEGORY_LAINNYA = 'lainnya';
    public const CATEGORY_VOLUNTEER = 'volunteer';

    // Backward-compatible aliases (used in generate-skpi views)
    public const CATEGORY_PRESTASI = 'penalaran';
    public const CATEGORY_MAGANG = 'lainnya';
    public const CATEGORY_SKILL_CERTIFICATE = 'wajib';
    
    // Automatic Category
    public const CATEGORY_SKRIPSI = 'skripsi';

    protected $fillable = [
        'student_id',
        'category',
        'activity_type',
        'event',
        'achievement',
        'level',
        'participation_role',
        'skp_points',
        'certificate',
        'status',
        'approval_notes',
        'approved_by',
        'approved_at',
    ];

    protected $casts = [
        'approved_at' => 'datetime',
    ];

    /**
     * Relationship to Student
     */
    public function student()
    {
        return $this->belongsTo(Student::class);
    }

    public function approver()
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    public function scopeApproved($query)
    {
        return $query->where('status', 'approved');
    }

    public static function categoryOptions(): array
    {
        $opts = \App\Services\SkpPointCalculator::getCategoryOptions();
        $opts[self::CATEGORY_SKRIPSI] = 'Skripsi / Tugas Akhir (Otomatis)';
        return $opts;
    }

    public static function manualCategoryOptions(): array
    {
        return collect(self::categoryOptions())
            ->except([self::CATEGORY_SKRIPSI])
            ->all();
    }

    public static function automaticCategoryOptions(): array
    {
        return [
            self::CATEGORY_SKRIPSI => self::categoryOptions()[self::CATEGORY_SKRIPSI],
        ];
    }

    public function getCategoryLabelAttribute(): string
    {
        return self::categoryOptions()[$this->category] ?? 'Aktivitas SKPI';
    }

    public function getActivityTypeLabelAttribute(): ?string
    {
        $dict = \App\Services\SkpPointCalculator::getDictionary();
        foreach ($dict as $catData) {
            if (isset($catData['types'][$this->activity_type])) {
                return $catData['types'][$this->activity_type]['label'];
            }
        }
        return $this->activity_type;
    }
}
