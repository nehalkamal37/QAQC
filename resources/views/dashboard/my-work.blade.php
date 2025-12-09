{{-- resources/views/dashboard/my-work.blade.php --}}
@extends('layouts.app')

@section('content')
<div class="container-fluid px-4">
    <!-- Header Section -->
    <div class="d-flex justify-content-between align-items-center py-4">
        <div>
            <h1 class="h3 mb-2 text-gray-800">My Project Assignments</h1>
            <p class="text-muted">Projects and roles currently assigned to you</p>
        </div>
        <div class="d-flex align-items-center gap-3">
            <div class="text-end">
                <div class="h4 mb-0 text-primary">{{ $stats['total'] ?? 0 }}</div>
                <small class="text-muted">Total Assignments</small>
            </div>
        </div>
    </div>

    <!-- Quick Stats Cards -->
    <div class="row mb-4">
        <div class="col-xl-2 col-md-4 col-6 mb-3">
            <div class="card border-left-primary shadow-sm h-100 py-2">
                <div class="card-body">
                    <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">
                        Project Manager
                    </div>
                    <div class="h5 mb-0 font-weight-bold text-gray-800">
                        {{ $stats['pm'] ?? 0 }}
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-2 col-md-4 col-6 mb-3">
            <div class="card border-left-success shadow-sm h-100 py-2">
                <div class="card-body">
                    <div class="text-xs font-weight-bold text-success text-uppercase mb-1">
                        Senior Reviewer
                    </div>
                    <div class="h5 mb-0 font-weight-bold text-gray-800">
                        {{ $stats['senior_reviewer'] ?? 0 }}
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-2 col-md-4 col-6 mb-3">
            <div class="card border-left-info shadow-sm h-100 py-2">
                <div class="card-body">
                    <div class="text-xs font-weight-bold text-info text-uppercase mb-1">
                        Engineer
                    </div>
                    <div class="h5 mb-0 font-weight-bold text-gray-800">
                        {{ $stats['engineer'] ?? 0 }}
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-2 col-md-4 col-6 mb-3">
            <div class="card border-left-warning shadow-sm h-100 py-2">
                <div class="card-body">
                    <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">
                        EIT
                    </div>
                    <div class="h5 mb-0 font-weight-bold text-gray-800">
                        {{ $stats['eit'] ?? 0 }}
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-2 col-md-4 col-6 mb-3">
            <div class="card border-left-secondary shadow-sm h-100 py-2">
                <div class="card-body">
                    <div class="text-xs font-weight-bold text-secondary text-uppercase mb-1">
                        Night Vision
                    </div>
                    <div class="h5 mb-0 font-weight-bold text-gray-800">
                        {{ $stats['night_vision'] ?? 0 }}
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-2 col-md-4 col-6 mb-3">
            <div class="card border-left-dark shadow-sm h-100 py-2">
                <div class="card-body">
                    <div class="text-xs font-weight-bold text-dark text-uppercase mb-1">
                        Active Projects
                    </div>
                    <div class="h5 mb-0 font-weight-bold text-gray-800">
                        {{ $projects->count() }}
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Main Content Card -->
    <div class="card shadow-sm border-0">
        <div class="card-header bg-white py-3 border-bottom">
            <div class="row align-items-center">
                <div class="col">
                    <h6 class="m-0 font-weight-bold text-primary">My Assignments</h6>
                </div>
                <div class="col-auto">
                    <form method="GET" action="{{ route('dashboard.my-work') }}" class="d-flex gap-2 align-items-center">
                        <small class="text-muted me-2">Filter:</small>
                        <select class="form-select form-select-sm w-auto" name="project_id" onchange="this.form.submit()">
                            <option value="">All Projects</option>
                            @foreach($projects as $project)
                                <option value="{{ $project->id }}" {{ request('project_id') == $project->id ? 'selected' : '' }}>
                                    {{ $project->name }}
                                </option>
                            @endforeach
                        </select>
                        <select class="form-select form-select-sm w-auto" name="role" onchange="this.form.submit()">
                            <option value="">All Roles</option>
                            <option value="pm" {{ request('role') == 'pm' ? 'selected' : '' }}>PM</option>
                            <option value="senior_reviewer" {{ request('role') == 'senior_reviewer' ? 'selected' : '' }}>Senior Reviewer</option>
                            <option value="engineer" {{ request('role') == 'engineer' ? 'selected' : '' }}>Engineer</option>
                            <option value="eit" {{ request('role') == 'eit' ? 'selected' : '' }}>EIT</option>
                            <option value="night_vision" {{ request('role') == 'night_vision' ? 'selected' : '' }}>Night Vision</option>
                        </select>
                        @if(request('project_id') || request('role'))
                            <a href="{{ route('dashboard.my-work') }}" class="btn btn-outline-secondary btn-sm">
                                <i class="fas fa-times"></i>
                            </a>
                        @endif
                    </form>
                </div>
            </div>
        </div>

        <div class="card-body p-0">
            @if($assignments->count() > 0)
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th class="border-0 ps-4">Project</th>
                                <th class="border-0">Phase</th>
                                <th class="border-0">Role</th>
                                <th class="border-0">Notes</th>
                                <th class="border-0 text-end pe-4">Assigned</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($assignments as $assignment)
                                <tr class="border-bottom">
                                    <td class="ps-4">
                                        <div class="fw-semibold text-primary">
                                            {{ $assignment->project->name ?? '—' }}
                                        </div>
                                        @if($assignment->project && $assignment->project->client)
                                            <div class="text-muted small">
                                                <i class="fas fa-building me-1"></i>
                                                {{ $assignment->project->client }}
                                            </div>
                                        @endif
                                    </td>
                                    <td>
                                        @if($assignment->phase)
                                            <span class="badge bg-light text-dark border">
                                                {{ $assignment->phase->type }}
                                            </span>
                                        @else
                                            <span class="text-muted fst-italic">All Phases</span>
                                        @endif
                                    </td>
                                    <td>
                                        <span class="badge bg-{{ $assignment->getRoleBadgeColor() }} py-2 px-3">
                                            <i class="{{ $assignment->getRoleIcon() }} me-1"></i>
                                            {{ $assignment->getRoleDisplayName() }}
                                        </span>
                                    </td>
                                    <td>
                                        @if($assignment->notes)
                                            <div class="text-muted small">
                                                {{ \Illuminate\Support\Str::limit($assignment->notes, 60) }}
                                            </div>
                                        @else
                                            <span class="text-muted small">—</span>
                                        @endif
                                    </td>
                                    <td class="text-end pe-4">
                                        <div class="text-muted small">
                                            {{ optional($assignment->assigned_at)->format('M d, Y') ?? '—' }}
                                        </div>
                                        @if($assignment->assigned_at)
                                            <div class="text-muted smaller">
                                                {{ $assignment->assigned_at->diffForHumans() }}
                                            </div>
                                        @endif
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @else
                <div class="text-center py-5">
                    <div class="empty-state">
                        <i class="fas fa-users fa-4x text-muted mb-3"></i>
                        <h5 class="text-muted">No Assignments Found</h5>
                        <p class="text-muted mb-4">
                            You currently have no project assignments.  
                            Contact your PM if you believe this is incorrect.
                        </p>
                        <a href="{{ route('assignments.index') }}" class="btn btn-primary">
                            <i class="fas fa-project-diagram me-2"></i>View All Assignments
                        </a>
                    </div>
                </div>
            @endif
        </div>
    </div>
</div>
@endsection

@push('styles')
<style>
.avatar-sm { width: 36px; height: 36px; }
.empty-state { max-width: 400px; margin: 0 auto; }
.card {
    border: none;
    box-shadow: 0 0.15rem 1.75rem 0 rgba(58, 59, 69, 0.15);
}
.table > :not(caption) > * > * {
    padding: 0.75rem 0.5rem;
}
.border-left-primary { border-left: 0.25rem solid #4e73df !important; }
.border-left-success { border-left: 0.25rem solid #1cc88a !important; }
.border-left-info    { border-left: 0.25rem solid #36b9cc !important; }
.border-left-warning { border-left: 0.25rem solid #f6c23e !important; }
.border-left-secondary { border-left: 0.25rem solid #858796 !important; }
.border-left-dark    { border-left: 0.25rem solid #343a40 !important; }
</style>
@endpush


@push('styles')
<style>
.avatar-sm {
    width: 40px;
    height: 40px;
}
.empty-state {
    max-width: 400px;
    margin: 0 auto;
}
.card {
    border: none;
    box-shadow: 0 0.15rem 1.75rem 0 rgba(58, 59, 69, 0.15);
}
.border-left-primary { border-left: 0.25rem solid #4e73df !important; }
.border-left-success { border-left: 0.25rem solid #1cc88a !important; }
.border-left-info { border-left: 0.25rem solid #36b9cc !important; }
.border-left-warning { border-left: 0.25rem solid #f6c23e !important; }
.border-left-danger { border-left: 0.25rem solid #e74a3b !important; }
.border-left-secondary { border-left: 0.25rem solid #858796 !important; }

.table > :not(caption) > * > * {
    padding: 1rem 0.5rem;
}
</style>
@endpush

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Initialize tooltips
    const tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
    const tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
        return new bootstrap.Tooltip(tooltipTriggerEl);
    });

    // Status filter
    const statusFilter = document.getElementById('statusFilter');
    if (statusFilter) {
        statusFilter.addEventListener('change', function() {
            const status = this.value;
            const rows = document.querySelectorAll('#assignmentsTable tbody tr');
            
            rows.forEach(row => {
                if (!status || row.querySelector('.badge').textContent.toLowerCase().includes(status)) {
                    row.style.display = '';
                } else {
                    row.style.display = 'none';
                }
            });
        });
    }

    // Quick action buttons
    const quickActionButtons = document.querySelectorAll('.quick-action');
    quickActionButtons.forEach(button => {
        button.addEventListener('click', function() {
            const itemId = this.dataset.id;
            const action = this.dataset.action;
            
            // You can implement AJAX calls here for quick actions
            console.log(`Quick action: ${action} for item ${itemId}`);
            
            // Example: Show a toast notification
            const toast = new bootstrap.Toast(document.getElementById('actionToast'));
            const toastMessage = document.getElementById('toastMessage');
            toastMessage.textContent = `Item ${action} action triggered!`;
            toast.show();
        });
    });

    // Hover effects
    const tableRows = document.querySelectorAll('#assignmentsTable tbody tr');
    tableRows.forEach(row => {
        row.addEventListener('mouseenter', function() {
            if (!this.classList.contains('table-warning')) {
                this.style.backgroundColor = '#f8f9fa';
            }
        });
        
        row.addEventListener('mouseleave', function() {
            if (!this.classList.contains('table-warning')) {
                this.style.backgroundColor = '';
            }
        });
    });
});
</script>
@endpush

<!-- Add this toast for actions -->
<div class="toast position-fixed top-0 end-0 m-3" id="actionToast" role="alert" aria-live="assertive" aria-atomic="true">
    <div class="toast-header">
        <i class="fas fa-info-circle text-primary me-2"></i>
        <strong class="me-auto">Action</strong>
        <button type="button" class="btn-close" data-bs-dismiss="toast" aria-label="Close"></button>
    </div>
    <div class="toast-body" id="toastMessage">
        Action completed successfully.
    </div>
</div>