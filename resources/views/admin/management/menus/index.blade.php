@extends('admin.layouts.super-app')

@section('content')
    <div class="content-card">
        <div class="card-header">
            <h3>Management Menu</h3>
            <a href="{{ route('admin.management.menus.create') }}" class="btn-primary">
                <i class="bi bi-plus-circle"></i> Tambah Menu
            </a>
        </div>

        @if(session('success'))
            <div class="alert-success">
                <i class="bi bi-check-circle"></i> {{ session('success') }}
            </div>
        @endif

        <!-- Search & Filter -->
        <div class="filter-box">
            <form method="GET" action="{{ route('admin.management.menus.index') }}" class="filter-form">
                <input type="text" name="search" class="search-input" 
                       placeholder="Cari nama, URL, atau route..." 
                       value="{{ $search }}">
                <select name="role" class="filter-select">
                    <option value="">Semua Role</option>
                    @foreach($roles as $role)
                        <option value="{{ $role }}" {{ $roleFilter == $role ? 'selected' : '' }}>
                            {{ \App\Models\User::roleLabel($role) }}
                        </option>
                    @endforeach
                </select>
                <button type="submit" class="search-btn">
                    <i class="bi bi-search"></i> Cari
                </button>
            </form>
        </div>

        @if($menuItems->count() > 0)
            <div class="table-responsive">
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>No</th>
                            <th>Nama Menu</th>
                            <th>Icon</th>
                            <th>URL/Route</th>
                            <th>Roles</th>
                            <th>Order</th>
                            <th>Status</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($menuItems as $menu)
                            <tr>
                                <td>{{ $loop->iteration + ($menuItems->currentPage() - 1) * $menuItems->perPage() }}</td>
                                <td>
                                    <strong>{{ $menu->name }}</strong>
                                    @if($menu->description)
                                        <br><small style="color: #999;">{{ $menu->description }}</small>
                                    @endif
                                </td>
                                <td>
                                    @if($menu->icon)
                                        <i class="{{ $menu->icon }}" style="font-size: 20px;"></i>
                                    @else
                                        <span style="color: #ccc;">-</span>
                                    @endif
                                </td>
                                <td>
                                    <code style="font-size: 11px;">
                                        {{ $menu->route_name ?: $menu->url ?: '-' }}
                                    </code>
                                </td>
                                <td>
                                    @if($menu->roles)
                                        @foreach(explode(',', $menu->roles) as $role)
                                            @php $r = trim($role); @endphp
                                            <span class="role-badge">{{ \App\Models\User::roleLabel($r) }}</span>
                                        @endforeach
                                    @else
                                        <span style="color: #999;">Semua</span>
                                    @endif
                                </td>
                                <td>{{ $menu->order }}</td>
                                <td>
                                    @if($menu->is_active)
                                        <span class="status-badge active">Aktif</span>
                                    @else
                                        <span class="status-badge inactive">Nonaktif</span>
                                    @endif
                                </td>
                                <td>
                                    <div class="action-buttons">
                                        <a href="{{ route('admin.management.menus.edit', $menu->id) }}" class="btn-edit">
                                            <i class="bi bi-pencil"></i> Edit
                                        </a>
                                        <form action="{{ route('admin.management.menus.destroy', $menu->id) }}" 
                                              method="POST" 
                                              onsubmit="return confirm('Yakin ingin menghapus menu ini?')"
                                              style="display: inline;">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn-delete">
                                                <i class="bi bi-trash"></i> Hapus
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <div class="pagination-wrapper">
                {{ $menuItems->appends(request()->query())->links('pagination::bootstrap-5') }}
            </div>
        @else
            <div class="empty-state">
                <i class="bi bi-inbox"></i>
                <p>Tidak ada data menu</p>
            </div>
        @endif
    </div>
@endsection

@push('css')
<style>
    .content-card {
        background: white;
        border-radius: 15px;
        padding: 25px;
        box-shadow: var(--shadow);
    }

    .card-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 25px;
        padding-bottom: 15px;
        border-bottom: 2px solid #F5F5F5;
    }

    .card-header h3 {
        font-size: 20px;
        font-weight: 600;
        margin: 0;
    }

    .btn-primary {
        background: linear-gradient(135deg, var(--primary-orange), #FFB347);
        color: white;
        padding: 10px 20px;
        border-radius: 10px;
        text-decoration: none;
        font-size: 14px;
        font-weight: 600;
        display: inline-flex;
        align-items: center;
        gap: 8px;
        transition: all 0.3s;
    }

    .btn-primary:hover {
        transform: translateY(-2px);
        box-shadow: 0 6px 20px rgba(255, 112, 67, 0.4);
    }

    .alert-success {
        background: #E8F5E9;
        color: #2E7D32;
        padding: 15px 20px;
        border-radius: 12px;
        margin-bottom: 20px;
        display: flex;
        align-items: center;
        gap: 10px;
    }

    .filter-box {
        margin-bottom: 20px;
    }

    .filter-form {
        display: flex;
        gap: 10px;
        flex-wrap: wrap;
    }

    .search-input {
        flex: 1;
        min-width: 200px;
        padding: 12px 15px;
        border: 2px solid #E0E0E0;
        border-radius: 10px;
        font-size: 14px;
    }

    .search-input:focus {
        outline: none;
        border-color: var(--primary-orange);
    }

    .filter-select {
        padding: 12px 15px;
        border: 2px solid #E0E0E0;
        border-radius: 10px;
        font-size: 14px;
        min-width: 150px;
    }

    .search-btn {
        padding: 12px 20px;
        background: var(--primary-orange);
        color: white;
        border: none;
        border-radius: 10px;
        cursor: pointer;
        font-size: 14px;
        font-weight: 600;
    }

    .data-table {
        width: 100%;
        border-collapse: collapse;
        margin-top: 20px;
    }

    .data-table th {
        padding: 12px;
        text-align: left;
        background: #F5F5F5;
        font-weight: 600;
        color: #333;
        font-size: 13px;
    }

    .data-table td {
        padding: 15px 12px;
        border-bottom: 1px solid #E0E0E0;
        vertical-align: top;
    }

    .data-table tr:hover {
        background: #F9F9F9;
    }

    .role-badge {
        display: inline-block;
        background: #E3F2FD;
        color: #1976D2;
        padding: 3px 8px;
        border-radius: 6px;
        font-size: 11px;
        margin: 2px;
    }

    .status-badge {
        padding: 5px 12px;
        border-radius: 12px;
        font-size: 12px;
        font-weight: 600;
    }

    .status-badge.active {
        background: #E8F5E9;
        color: #2E7D32;
    }

    .status-badge.inactive {
        background: #FFEBEE;
        color: #C62828;
    }

    .action-buttons {
        display: flex;
        gap: 8px;
    }

    .btn-edit {
        padding: 6px 12px;
        background: var(--primary-orange);
        color: white;
        border-radius: 6px;
        text-decoration: none;
        font-size: 12px;
        transition: all 0.3s;
        display: inline-flex;
        align-items: center;
        gap: 5px;
    }

    .btn-edit:hover {
        background: #FF7043;
        transform: translateY(-2px);
    }

    .btn-delete {
        padding: 6px 12px;
        background: #F44336;
        color: white;
        border: none;
        border-radius: 6px;
        font-size: 12px;
        cursor: pointer;
        transition: all 0.3s;
        display: inline-flex;
        align-items: center;
        gap: 5px;
    }

    .btn-delete:hover {
        background: #E53935;
        transform: translateY(-2px);
    }

    .pagination-wrapper {
        margin-top: 30px;
        display: flex;
        justify-content: center;
        align-items: center;
    }

    /* Custom Pagination Styling - Aggressive Override */
    .pagination-wrapper .pagination {
        display: flex !important;
        list-style: none !important;
        padding: 0 !important;
        margin: 0 !important;
        gap: 8px !important;
        flex-wrap: wrap !important;
        justify-content: center !important;
        align-items: center !important;
        border: none !important;
        background: transparent !important;
    }

    .pagination-wrapper .pagination .page-item {
        margin: 0 !important;
        list-style: none !important;
        display: inline-block !important;
    }

    .pagination-wrapper .pagination .page-link {
        padding: 10px 16px !important;
        border: 2px solid #E0E0E0 !important;
        border-radius: 10px !important;
        color: #666 !important;
        text-decoration: none !important;
        background: white !important;
        transition: all 0.3s !important;
        font-size: 14px !important;
        font-weight: 500 !important;
        min-width: 44px !important;
        text-align: center !important;
        display: inline-flex !important;
        align-items: center !important;
        justify-content: center !important;
        line-height: 1.5 !important;
        position: relative !important;
        margin: 0 !important;
        margin-left: 0 !important;
    }

    .pagination-wrapper .pagination .page-link:hover {
        background: var(--primary-orange) !important;
        color: white !important;
        border-color: var(--primary-orange) !important;
        transform: translateY(-2px) !important;
        box-shadow: 0 4px 12px rgba(255, 152, 0, 0.3) !important;
        z-index: 1 !important;
    }

    .pagination-wrapper .pagination .page-item.active .page-link {
        background: var(--primary-orange) !important;
        color: white !important;
        border-color: var(--primary-orange) !important;
        font-weight: 600 !important;
        z-index: 2 !important;
    }

    .pagination-wrapper .pagination .page-item.disabled .page-link {
        background: #F5F5F5 !important;
        color: #999 !important;
        border-color: #E0E0E0 !important;
        cursor: not-allowed !important;
        opacity: 0.6 !important;
        pointer-events: none !important;
    }

    .pagination-wrapper .pagination .page-item.disabled .page-link:hover {
        background: #F5F5F5 !important;
        color: #999 !important;
        border-color: #E0E0E0 !important;
        transform: none !important;
        box-shadow: none !important;
    }

    .pagination-wrapper .pagination .page-link::before,
    .pagination-wrapper .pagination .page-link::after {
        display: none !important;
        content: none !important;
    }

    .pagination-wrapper .pagination .page-link svg,
    .pagination-wrapper .pagination .page-link i {
        display: none !important;
    }

    .pagination-wrapper .pagination .page-item:first-child .page-link {
        margin-left: 0 !important;
        border-top-left-radius: 10px !important;
        border-bottom-left-radius: 10px !important;
        font-size: 14px !important;
    }

    .pagination-wrapper .pagination .page-item:last-child .page-link {
        margin-right: 0 !important;
        border-top-right-radius: 10px !important;
        border-bottom-right-radius: 10px !important;
        font-size: 14px !important;
    }

    .pagination-wrapper .pagination .page-link span {
        display: inline !important;
        font-size: 14px !important;
    }

    .pagination-wrapper .pagination .page-link[aria-label*="Previous"],
    .pagination-wrapper .pagination .page-link[aria-label*="Next"] {
        font-size: 14px !important;
        padding: 10px 16px !important;
    }

    .pagination-wrapper {
        overflow: visible !important;
        width: 100% !important;
        position: relative !important;
    }

    .pagination-wrapper .pagination {
        max-width: 100% !important;
        overflow: visible !important;
        position: relative !important;
    }

    .empty-state {
        text-align: center;
        padding: 80px 20px;
    }

    .empty-state i {
        font-size: 80px;
        color: #E0E0E0;
        margin-bottom: 20px;
    }

    .empty-state p {
        color: #999;
        font-size: 16px;
    }
</style>
@endpush
