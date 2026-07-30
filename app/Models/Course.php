<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Course extends Model
{
    protected $fillable = [
        'code_prefix',
        'code_number',
        'name',
        'type',
        'program_study',
        'lecturer',
        'room',
        'day',
        'description',
        'note',
        'semester',
        'sks',
        'start_time',
        'end_time',
    ];

    // Accessor → gabungkan kode prefix + number jadi full code
    public function getCodeAttribute(): string
    {
        return $this->code_prefix . $this->code_number;
    }


    public function failedInCounselings()
    {
        return $this->belongsToMany(CardCounseling::class, 'failed_courses');
    }

    public function retakenInCounselings()
    {
        return $this->belongsToMany(CardCounseling::class, 'retaken_courses');
    }
}