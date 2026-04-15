<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class Student extends Authenticatable
{
    use Notifiable;

    /*
    |--------------------------------------------------------------------------
    | Constants
    |--------------------------------------------------------------------------
    */
    const STATUS_ACTIVE   = 'Aktif';
    const DEFAULT_PROGRAM = 'Bisnis Digital';

    /*
    |--------------------------------------------------------------------------
    | Mass Assignment
    |--------------------------------------------------------------------------
    */
    protected $fillable = [
        'id_lecturer',
        'nama_lengkap',
        'nim',
        'nik',
        'nisn',
        'password',
        'email',
        'angkatan',
        'program_studi',
        'fakultas',
        'jenis_kelamin',
        'tempat_lahir',
        'tanggal_lahir',
        'alamat',
        'alamat_lat',
        'alamat_lng',
        'no_telepon',
        'status_mahasiswa',
        'tanggal_masuk',
        'tanggal_lulus',
        'is_counseling',
        'tanggal_counseling',
        'notes',
        'foto',
        'ttd',
        'nama_orangtua',
        'nama_ibu_kandung',
        'is_edited',
    ];

    /*
    |--------------------------------------------------------------------------
    | Hidden & Casts
    |--------------------------------------------------------------------------
    */
    protected $hidden = [
        'password',
    ];

    protected $casts = [
        'is_edited' => 'boolean',
        'is_counseling' => 'boolean',
        'tanggal_lahir' => 'date',
        'tanggal_masuk' => 'datetime',
        'tanggal_lulus' => 'datetime',
        'tanggal_counseling' => 'datetime',
    ];

    /*
    |--------------------------------------------------------------------------
    | Relationships
    |--------------------------------------------------------------------------
    */
    public function dosenPA()
    {
        return $this->belongsTo(User::class, 'id_lecturer', 'id');
    }

    public function counselings()
    {
        return $this->hasMany(CardCounseling::class, 'id_student', 'id');
    }

    public function achievements()
    {
        return $this->hasMany(StudentAchievement::class, 'student_id', 'id');
    }

    public function finalProject()
    {
        return $this->hasOne(\App\Models\FinalProject::class, 'student_id', 'id');
    }

    public function skpiRegistration()
    {
        return $this->hasOne(\App\Models\SkpiRegistration::class, 'student_id', 'id');
    }

    /*
    |--------------------------------------------------------------------------
    | Query Scopes
    |--------------------------------------------------------------------------
    */
    public function scopeByLecturer($query, $lecturerId)
    {
        return $query->where('id_lecturer', $lecturerId);
    }

    public function scopeByBatch($query, $batch)
    {
        return $query->where('angkatan', $batch);
    }

    public function scopeSearch($query, $search)
    {
        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('nama_lengkap', 'like', "%{$search}%")
                    ->orWhere('nim', 'like', "%{$search}%")
                    ->orWhere('angkatan', 'like', "%{$search}%");
            });
        }
        return $query;
    }

    /**
     * Menghitung semester saat ini berdasarkan angkatan
     *
     * Logika perhitungan:
     * - Khusus angkatan 2021: masih semester 8 (karena mulai telat)
     * - Untuk angkatan lain: semester = (tahun sekarang - tahun angkatan) * 2 + semester saat ini
     *   dimana semester saat ini = 1 untuk ganjil (Agustus-Januari), 2 untuk genap (Februari-Juli)
     *
     * Contoh perhitungan (asumsi sekarang tahun 2025, bulan Januari = semester ganjil):
     * - Angkatan 2022: (2025 - 2022) * 2 + 1 = 3 * 2 + 1 = 7 ✓
     * - Angkatan 2023: (2025 - 2023) * 2 + 1 = 2 * 2 + 1 = 5 ✓
     * - Angkatan 2024: (2025 - 2024) * 2 + 1 = 1 * 2 + 1 = 3 ✓
     *
     * @return int
     */
    public function getCurrentSemester(): int
    {
        // Khusus angkatan 2021
        if ($this->angkatan == 2021) {
            return 8;
        }

        $now = now();
        $currentYear = $now->year;
        $currentMonth = $now->month;

        // Tentukan semester saat ini (1 = ganjil, 2 = genap)
        // Semester ganjil: Agustus - Januari (bulan 8-12, 1)
        // Semester genap: Februari - Juli (bulan 2-7)
        $currentSemesterInYear = ($currentMonth >= 8 || $currentMonth <= 1) ? 1 : 2;

        // Hitung selisih tahun
        $yearDiff = $currentYear - $this->angkatan;

        // Semester = (selisih tahun * 2) + semester saat ini
        // Contoh: angkatan 2022, sekarang 2025 semester ganjil
        // = (2025 - 2022) * 2 + 1 = 3 * 2 + 1 = 7
        $semester = ($yearDiff * 2) + $currentSemesterInYear;

        return max(1, $semester); // Minimal semester 1
    }
}
