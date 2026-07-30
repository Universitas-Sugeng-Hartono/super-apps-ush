@extends('students.template.index')
@push('css')
    <style>
        .card-header {
            background-color: transparent !important;
            border-bottom: none !important;
        }
    </style>
@endpush
@section('content')
    <!-- Dashboard Cards -->
    <div class="dashboard-grid">
        <div class="card col-xl-3 col-md-6 mb-4">
            <div class="card-header">
                <div class="card-title">Total Counseling</div>
                <div class="card-icon">👥</div>
            </div>
            <div class="card-value">{{ $counseling }}</div>
            <div class="card-subtitle">Counseling di setiap semester</div>
        </div>
    </div>

    <!-- Chart Section -->
    {{-- <div class="chart-container">
        <h3 style="color: #3338A0; margin-bottom: 1rem; font-size: 1.3rem;">Monthly Analytics</h3>
        <div class="chart-placeholder">
            📊 Chart will be displayed here
        </div>
    </div> --}}

    <!-- Additional Content -->
    {{-- <div class="dashboard-grid">
        <div class="card">
            <div class="card-header">
                <div class="card-title">Recent Activity</div>
                <div class="card-icon">🔔</div>
            </div>
            <div style="color: #666; margin-top: 1rem;">
                <p>• New user registered</p>
                <p>• Order #1234 completed</p>
                <p>• Payment received</p>
            </div>
        </div>

        <div class="card">
            <div class="card-header">
                <div class="card-title">Quick Actions</div>
                <div class="card-icon">⚡</div>
            </div>
            <div style="margin-top: 1rem;">
                <button
                    style="background: #3338A0; color: white; border: none; padding: 0.5rem 1rem; border-radius: 8px; margin-right: 0.5rem; cursor: pointer;">Add
                    User</button>
                <button
                    style="background: #4a4fb8; color: white; border: none; padding: 0.5rem 1rem; border-radius: 8px; cursor: pointer;">Export
                    Data</button>
            </div>
        </div>
    </div> --}}
    <!-- Content Row -->
@endsection

@push('scripts')
@endpush
