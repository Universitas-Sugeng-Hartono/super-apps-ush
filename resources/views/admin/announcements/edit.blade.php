@extends('admin.layouts.super-app')

@section('content')
    <div class="content-card">
        <div class="card-header">
            <h3>Edit Pengumuman</h3>
            <a href="{{ route('admin.announcements.index') }}" class="btn-back">
                <i class="bi bi-arrow-left"></i> Kembali
            </a>
        </div>

        <form method="POST" action="{{ route('admin.announcements.update', $announcement->id) }}">
            @csrf
            @method('PUT')
            @include('admin.announcements.form', ['announcement' => $announcement])

            <div class="mt-4 d-flex gap-2">
                <button type="submit" class="btn-primary">
                    <i class="bi bi-check2-circle"></i> Simpan Perubahan
                </button>
            </div>
        </form>
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

    .btn-back {
        color: var(--text-dark);
        text-decoration: none;
        font-weight: 600;
        display: inline-flex;
        align-items: center;
        gap: 8px;
        padding: 10px 14px;
        border-radius: 10px;
        background: #F5F5F5;
    }

    .btn-primary {
        background: linear-gradient(135deg, var(--primary-orange), #FFB347);
        color: white;
        padding: 10px 20px;
        border-radius: 10px;
        border: none;
        font-size: 14px;
        font-weight: 600;
        display: inline-flex;
        align-items: center;
        gap: 8px;
        transition: all 0.3s;
    }

    .form-group {
        margin-bottom: 16px;
    }

    .form-label {
        font-weight: 600;
        margin-bottom: 8px;
        display: block;
    }

    .form-control {
        border-radius: 12px;
        padding: 12px 14px;
        border: 2px solid #E0E0E0;
    }
</style>
@endpush


