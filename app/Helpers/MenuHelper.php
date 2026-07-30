<?php

namespace App\Helpers;

use App\Models\Menu;
use App\Models\User;

class MenuHelper
{
    /**
     * Get menus berdasarkan role dan type
     */
    public static function getMenus($role, $type = 'dashboard')
    {
        return Menu::active()
            ->ofType($type)
            ->forRole($role)
            ->orderBy('order', 'asc')
            ->orderBy('name', 'asc')
            ->get();
    }

    /**
     * Get role dari user yang sedang login
     */
    public static function getCurrentRole()
    {
        if (auth()->guard('student')->check()) {
            return 'student';
        }
        
        if (auth()->check()) {
            return User::normalizeRole(auth()->user()->role);
        }
        
        return null;
    }
}

