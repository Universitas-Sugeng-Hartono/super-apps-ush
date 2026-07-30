<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable;

    public const ROLE_LABELS = [
        'admin' => 'Dosen',
        'superadmin' => 'Kaprodi',
        'masteradmin' => 'Superuser',
        'student' => 'Mahasiswa',
    ];

    public const ROLE_ALIASES = [
        // legacy / UI labels (do not store these going forward, but support if they already exist)
        'dosen' => 'admin',
        'kaprodi' => 'superadmin',
        'superuser' => 'masteradmin',
        'mahasiswa' => 'student',
    ];

    public static function normalizeRole(?string $role): ?string
    {
        $role = trim((string) $role);
        if ($role === '') {
            return null;
        }

        return self::ROLE_ALIASES[$role] ?? $role;
    }

    public static function roleLabel(?string $role): string
    {
        $role = self::normalizeRole($role);
        if (!$role) {
            return '';
        }

        return self::ROLE_LABELS[$role] ?? ucfirst($role);
    }

    public function getRoleKeyAttribute(): ?string
    {
        return self::normalizeRole($this->role);
    }

    public function getRoleLabelAttribute(): string
    {
        return self::roleLabel($this->role);
    }

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'photo',
        'program_studi',
        'role',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    /**
     * Relasi: User (Dosen PA) memiliki banyak mahasiswa
     */
    public function students()
    {
        return $this->hasMany(Student::class, 'id_lecturer', 'id');
    }

    /**
     * Final projects where this user is supervisor 1
     */
    public function supervisedFinalProjects()
    {
        return $this->hasMany(\App\Models\FinalProject::class, 'supervisor_1_id', 'id');
    }

    /**
     * Final projects where this user is supervisor 2
     */
    public function supervisedFinalProjectsAsSecond()
    {
        return $this->hasMany(\App\Models\FinalProject::class, 'supervisor_2_id', 'id');
    }
}