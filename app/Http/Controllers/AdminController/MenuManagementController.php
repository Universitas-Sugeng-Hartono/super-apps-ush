<?php

namespace App\Http\Controllers\AdminController;

use App\Http\Controllers\Controller;
use App\Models\MenuItem;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class MenuManagementController extends Controller
{
    /**
     * Display a listing of menu items.
     */
    public function index(Request $request)
    {
        $search = $request->input('search');
        $roleFilter = $request->input('role');

        $menuItems = MenuItem::when($search, function ($query, $search) {
                $query->where('name', 'like', "%{$search}%")
                      ->orWhere('url', 'like', "%{$search}%")
                      ->orWhere('route_name', 'like', "%{$search}%");
            })
            ->when($roleFilter, function ($query, $roleFilter) {
                $query->whereRaw("FIND_IN_SET(?, roles)", [$roleFilter]);
            })
            ->ordered()
            ->paginate(20)
            ->appends(['search' => $search, 'role' => $roleFilter]);

        $roles = ['student', 'admin', 'superadmin', 'masteradmin'];

        return view('admin.management.menus.index', compact('menuItems', 'search', 'roleFilter', 'roles'));
    }

    /**
     * Show the form for creating a new menu item.
     */
    public function create()
    {
        $roles = ['student', 'admin', 'superadmin', 'masteradmin'];
        return view('admin.management.menus.create', compact('roles'));
    }

    /**
     * Store a newly created menu item.
     */
    public function store(Request $request)
    {
        $allowedRoles = ['student', 'admin', 'superadmin', 'masteradmin'];

        $request->validate([
            'name' => 'required|string|max:255',
            'icon' => 'nullable|string|max:100',
            'url' => 'nullable|string|max:500',
            'route_name' => 'nullable|string|max:255',
            'roles' => 'nullable|array',
            'roles.*' => ['string', Rule::in($allowedRoles)],
            'order' => 'nullable|integer|min:0',
            'is_active' => 'boolean',
            'target' => 'nullable|in:_self,_blank',
            'description' => 'nullable|string|max:500',
            'badge_text' => 'nullable|string|max:50',
            'badge_color' => 'nullable|string|max:50',
        ]);

        $roles = $request->input('roles');
        $rolesString = is_array($roles) && count($roles) > 0 ? implode(',', $roles) : null;

        MenuItem::create([
            'name' => $request->name,
            'icon' => $request->icon,
            'url' => $request->url,
            'route_name' => $request->route_name,
            'roles' => $rolesString,
            'order' => $request->order ?? 0,
            'is_active' => $request->has('is_active'),
            'target' => $request->target ?? '_self',
            'description' => $request->description,
            'badge_text' => $request->badge_text,
            'badge_color' => $request->badge_color,
        ]);

        return redirect()->route('admin.management.menus.index')
            ->with('success', 'Menu berhasil ditambahkan.');
    }

    /**
     * Show the form for editing the specified menu item.
     */
    public function edit($id)
    {
        $menuItem = MenuItem::findOrFail($id);
        $roles = ['student', 'admin', 'superadmin', 'masteradmin'];
        return view('admin.management.menus.edit', compact('menuItem', 'roles'));
    }

    /**
     * Update the specified menu item.
     */
    public function update(Request $request, $id)
    {
        $menuItem = MenuItem::findOrFail($id);
        $allowedRoles = ['student', 'admin', 'superadmin', 'masteradmin'];

        $request->validate([
            'name' => 'required|string|max:255',
            'icon' => 'nullable|string|max:100',
            'url' => 'nullable|string|max:500',
            'route_name' => 'nullable|string|max:255',
            'roles' => 'nullable|array',
            'roles.*' => ['string', Rule::in($allowedRoles)],
            'order' => 'nullable|integer|min:0',
            'is_active' => 'boolean',
            'target' => 'nullable|in:_self,_blank',
            'description' => 'nullable|string|max:500',
            'badge_text' => 'nullable|string|max:50',
            'badge_color' => 'nullable|string|max:50',
        ]);

        $roles = $request->input('roles');
        $rolesString = is_array($roles) && count($roles) > 0 ? implode(',', $roles) : null;

        $menuItem->update([
            'name' => $request->name,
            'icon' => $request->icon,
            'url' => $request->url,
            'route_name' => $request->route_name,
            'roles' => $rolesString,
            'order' => $request->order ?? 0,
            'is_active' => $request->has('is_active'),
            'target' => $request->target ?? '_self',
            'description' => $request->description,
            'badge_text' => $request->badge_text,
            'badge_color' => $request->badge_color,
        ]);

        return redirect()->route('admin.management.menus.index')
            ->with('success', 'Menu berhasil diperbarui.');
    }

    /**
     * Remove the specified menu item.
     */
    public function destroy($id)
    {
        $menuItem = MenuItem::findOrFail($id);
        $menuItem->delete();

        return redirect()->route('admin.management.menus.index')
            ->with('success', 'Menu berhasil dihapus.');
    }
}
