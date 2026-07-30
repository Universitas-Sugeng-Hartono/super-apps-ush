<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use App\Models\Course;

class CardCounseling extends Model
{
    use HasFactory;

    protected $fillable = [
        'id_student',
        'semester',
        'sks',
        'ip',
        'tanggal',
        'komentar',
        'failed_courses',
        'retaken_courses'
    ];

    protected $casts = [
        'failed_courses'  => 'array', // otomatis decode JSON ke array
        'retaken_courses' => 'array', // otomatis decode JSON ke array
    ];

    public function student()
    {
        return $this->belongsTo(Student::class, 'id_student');
    }

    /**
     * Accessor untuk daftar mata kuliah gagal
     */
    public function getFailedCoursesObjectsAttribute()
    {
        return Course::whereIn('id', $this->failed_courses ?? [])->get();
    }

    /**
     * Accessor untuk daftar mata kuliah yang diulang
     */
    public function getRetakenCoursesObjectsAttribute()
    {
        return Course::whereIn('id', $this->retaken_courses ?? [])->get();
    }
}