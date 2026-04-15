<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SkpiAcademicProfile extends Model
{
    use HasFactory;

    protected $fillable = [
        'study_program_id',
        'sk_pendirian_perguruan_tinggi',
        'nama_perguruan_tinggi',
        'akreditasi_perguruan_tinggi',
        'akreditasi_program_studi',
        'jenis_dan_jenjang_pendidikan',
        'jenjang_kualifikasi_kkni',
        'persyaratan_penerimaan',
        'bahasa_pengantar_kuliah',
        'nomor_akreditasi_perguruan_tinggi',
        'sistem_penilaian',
        'lama_studi',
        'nomor_akreditasi_program_studi',
        'status_profesi',
    ];

    public function studyProgram()
    {
        return $this->belongsTo(StudyProgram::class);
    }
}
