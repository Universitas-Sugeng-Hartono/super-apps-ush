<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MenuItem extends Model
{
    protected $fillable = [
        'name',
        'icon',
        'url',
        'route_name',
        'roles',
        'order',
        'is_active',
        'target',
        'description',
        'badge_text',
        'badge_color',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'order' => 'integer',
    ];

    /**
     * Scope untuk filter berdasarkan role
     */
    public function scopeForRole($query, $role)
    {
        return $query->where(function($q) use ($role) {
            $q->whereNull('roles')
              ->orWhere('roles', '')
              ->orWhereRaw("FIND_IN_SET(?, roles)", [$role]);
        });
    }

    /**
     * Scope untuk menu aktif
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope untuk urut berdasarkan order
     */
    public function scopeOrdered($query)
    {
        return $query->orderBy('order', 'asc');
    }

    /**
     * Get roles as array
     */
    public function getRolesArrayAttribute()
    {
        if (empty($this->roles)) {
            return [];
        }
        return array_map('trim', explode(',', $this->roles));
    }

    /**
     * Get full URL
     */
    public function getFullUrlAttribute()
    {
        if ($this->route_name) {
            return route($this->route_name);
        }
        return $this->url ?? '#';
    }
}
