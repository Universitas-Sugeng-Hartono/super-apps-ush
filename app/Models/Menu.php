<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Menu extends Model
{
    protected $fillable = [
        'name',
        'icon',
        'url',
        'route_name',
        'order',
        'type',
        'description',
        'is_active',
        'badge_text',
        'badge_color',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'order' => 'integer',
    ];

    /**
     * Relasi ke permissions
     */
    public function permissions(): HasMany
    {
        return $this->hasMany(MenuPermission::class);
    }

    /**
     * Scope untuk menu aktif
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope untuk filter berdasarkan type
     */
    public function scopeOfType($query, $type)
    {
        return $query->where('type', $type);
    }

    /**
     * Scope untuk filter berdasarkan role
     */
    public function scopeForRole($query, $role)
    {
        return $query->whereHas('permissions', function($q) use ($role) {
            $q->where('role', $role);
        });
    }

    /**
     * Get menu URL (prioritaskan route_name jika ada)
     */
    public function getMenuUrlAttribute()
    {
        if ($this->route_name) {
            try {
                return route($this->route_name);
            } catch (\Exception $e) {
                return $this->url;
            }
        }
        return $this->url;
    }
}
