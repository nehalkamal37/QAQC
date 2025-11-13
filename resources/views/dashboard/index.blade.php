@extends('layouts.app')

@section('content')
<div class="container-fluid px-4 mt-4">

    <!-- Page Title -->
    <h1 class="h3 fw-bold mb-4 text-dark">📊 Dashboard Overview</h1>

    <!-- Summary Cards -->
    <div class="row g-3">

        {{-- Projects --}}
        <div class="col-md-3">
            <a href="{{ route('projects.index') }}" class="text-decoration-none">
                <div class="stat-card">
                    <div class="stat-title">Projects</div>
                    <div class="stat-value text-primary">{{ $data['projects_count'] }}</div>
                </div>
            </a>
        </div>

        {{-- Phases --}}
        <div class="col-md-3">
            <a href="{{ route('phases.indexAll') }}" class="text-decoration-none">
                <div class="stat-card">
                    <div class="stat-title">Phases</div>
                    <div class="stat-value text-success">{{ $data['phases_count'] }}</div>
                </div>
            </a>
        </div>

        {{-- Sheets --}}
        <div class="col-md-3">
            <a href="{{ route('sheets.indexAll') }}" class="text-decoration-none">
                <div class="stat-card">
                    <div class="stat-title">Sheets</div>
                    <div class="stat-value text-warning">{{ $data['sheets_count'] }}</div>
                </div>
            </a>
        </div>

        {{-- QA Items --}}
        <div class="col-md-3">
            <a href="{{ route('qa_items.indexAll') }}" class="text-decoration-none">
                <div class="stat-card">
                    <div class="stat-title">QA Items</div>
                    <div class="stat-value text-danger">{{ $data['qa_items_count'] }}</div>
                </div>
            </a>
        </div>

    </div>

    <!-- Status Cards -->
    <div class="row g-3 mt-3">

        {{-- Open --}}
        <div class="col-md-3">
            <div class="stat-card">
                <div class="stat-title">Open Items</div>
                <div class="stat-value text-danger">{{ $data['qa_open'] }}</div>
            </div>
        </div>

        {{-- Pending --}}
        <div class="col-md-3">
            <div class="stat-card">
                <div class="stat-title">Pending Items</div>
                <div class="stat-value text-warning">{{ $data['qa_pending'] }}</div>
            </div>
        </div>

        {{-- Resolved --}}
        <div class="col-md-3">
            <div class="stat-card">
                <div class="stat-title">Resolved Items</div>
                <div class="stat-value text-success">{{ $data['qa_resolved'] }}</div>
            </div>
        </div>

        {{-- Closed --}}
        <div class="col-md-3">
            <div class="stat-card">
                <div class="stat-title">Closed Items</div>
                <div class="stat-value text-secondary">{{ $data['qa_closed'] }}</div>
            </div>
        </div>

    </div>

    <!-- Reviewer Workload -->
    <div class="card mt-4 shadow-sm border-0">
        <div class="card-header bg-white border-0 py-3">
            <h5 class="fw-bold mb-0">👥 Reviewer Workload (Top 5)</h5>
        </div>
        <div class="card-body">

            @forelse($data['reviewer_load'] as $reviewer)
                <div class="mb-3">
                    <div class="d-flex justify-content-between">
                        <strong>{{ $reviewer->name }}</strong>
                        <span class="text-muted small">{{ $reviewer->qa_items_assigned_count }} items</span>
                    </div>

                    <div class="progress" style="height: 8px;">
                        <div class="progress-bar bg-primary"
                            role="progressbar"
                            style="width: {{ min($reviewer->qa_items_assigned_count * 10, 100) }}%;">
                        </div>
                    </div>
                </div>
            @empty
                <p class="text-muted">No reviewers with assigned items yet.</p>
            @endforelse

        </div>
    </div>

</div>

<!-- Custom CSS -->
<style>
    .stat-card {
        background: #ffffff;
        border-radius: 12px;
        padding: 22px;
        text-align: center;
        border: 1px solid #f1f1f1;
        box-shadow: 0 2px 8px rgba(0,0,0,0.05);
        transition: 0.2s ease;
    }

    .stat-card:hover {
        transform: translateY(-4px);
        box-shadow: 0 6px 18px rgba(0,0,0,0.12);
    }

    .stat-title {
        font-size: 0.85rem;
        font-weight: 600;
        color: #6c757d;
        text-transform: uppercase;
        letter-spacing: .5px;
    }

    .stat-value {
        font-size: 2.2rem;
        font-weight: 700;
        margin-top: 5px;
    }
</style>
@endsection
