@extends('layouts.app')

@section('content')
<div class="container-fluid px-4">
    <!-- Header Section -->
    <div class="d-flex justify-content-between align-items-center py-4">
        <div>
            <h1 class="h3 mb-2 text-gray-800">Team Assignments</h1>
            <p class="text-muted">Manage project team members and their roles</p>
        </div>
        <a href="{{ route('assignments.create') }}" class="btn btn-primary btn-lg shadow-sm">
            <i class="fas fa-user-plus me-2"></i>New Assignment
        </a>
    </div>

    <!-- Stats Cards -->
    <div class="row mb-4">
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-primary shadow-sm h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">
                                Total Assignments
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $assignments->count() }}</div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-users fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-success shadow-sm h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-success text-uppercase mb-1">
                                Project Managers
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                {{ $assignments->where('role', 'pm')->count() }}
                            </div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-user-tie fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-info shadow-sm h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-info text-uppercase mb-1">
                                Engineers
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                {{ $assignments->whereIn('role', ['engineer', 'eit'])->count() }}
                            </div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-cogs fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-warning shadow-sm h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">
                                Active Projects
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                {{ $assignments->pluck('project_id')->unique()->count() }}
                            </div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-project-diagram fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Main Card -->
    <div class="card shadow-sm border-0">
        <div class="card-header bg-white py-3 border-bottom">
            <div class="row align-items-center">
                <div class="col">
                    <h6 class="m-0 font-weight-bold text-primary">Assignment Management</h6>
                </div>
                <div class="col-auto">
                    <div class="d-flex gap-2">
                        <!-- Export Button -->
                        <button class="btn btn-outline-secondary btn-sm">
                            <i class="fas fa-download me-1"></i>Export
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <div class="card-body">
            <!-- Enhanced Filters -->
            <!-- Form-Based Filters -->
<form method="GET" action="{{ route('assignments.index') }}" id="filterForm">
    <div class="row mb-4">
        <div class="col-md-4">
            <label class="form-label small text-uppercase text-muted fw-bold">Filter by Project</label>
            <select class="form-select form-select-sm shadow-sm" name="project_id" onchange="this.form.submit()">
                <option value="">All Projects</option>
                @foreach($projects as $project)
                    <option value="{{ $project->id }}" 
                        {{ request('project_id') == $project->id ? 'selected' : '' }}>
                        {{ $project->name }}
                    </option>
                @endforeach
            </select>
        </div>
        <div class="col-md-4">
            <label class="form-label small text-uppercase text-muted fw-bold">Filter by Role</label>
            <select class="form-select form-select-sm shadow-sm" name="role" onchange="this.form.submit()">
                <option value="">All Roles</option>
                <option value="pm" {{ request('role') == 'pm' ? 'selected' : '' }}>Project Manager</option>
                <option value="senior_reviewer" {{ request('role') == 'senior_reviewer' ? 'selected' : '' }}>Senior Reviewer</option>
                <option value="engineer" {{ request('role') == 'engineer' ? 'selected' : '' }}>Engineer</option>
                <option value="eit" {{ request('role') == 'eit' ? 'selected' : '' }}>EIT</option>
                <option value="night_vision" {{ request('role') == 'night_vision' ? 'selected' : '' }}>Night Vision</option>
            </select>
        </div>
        <div class="col-md-4">
            <label class="form-label small text-uppercase text-muted fw-bold">Actions</label>
            <div class="d-flex gap-2">
                <a href="{{ route('assignments.index') }}" class="btn btn-outline-secondary btn-sm flex-fill">
                    <i class="fas fa-times me-1"></i>Clear Filters
                </a>
            </div>
        </div>
    </div>
</form>

            <!-- Assignments Table -->
            @if($assignments->count() > 0)
                <div class="table-responsive">
                    <table class="table table-hover align-middle" id="assignmentsTable">
                        <thead class="table-light">
                            <tr>
                                <th class="border-0">
                                    <div class="d-flex align-items-center">
                                        <span>Team Member</span>
                                    </div>
                                </th>
                                <th class="border-0">Project</th>
                                <th class="border-0">Phase</th>
                                <th class="border-0">Role</th>
                                <th class="border-0">Assigned</th>
                                <th class="border-0 text-end">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($assignments as $assignment)
                            <tr class="border-bottom">
                                <td>
                                    <div class="d-flex align-items-center">
                                        <div class="avatar-sm me-3">
                                            <div class="bg-primary rounded-circle d-flex align-items-center justify-content-center" 
                                                 style="width: 36px; height: 36px;">
                                                <span class="text-white fw-bold">
                                                    {{ substr($assignment->user->name, 0, 1) }}
                                                </span>
                                            </div>
                                        </div>
                                        <div>
                                            <h6 class="mb-0 text-primary">{{ $assignment->user->name }}</h6>
                                            <small class="text-muted">{{ $assignment->user->email }}</small>
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    <span class="fw-semibold">{{ $assignment->project->name }}</span>
                                    <br>
                                    <small class="text-muted">{{ $assignment->project->client }}</small>
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
                                        <i class="fas fa-{{ $assignment->getRoleIcon() }} me-1"></i>
                                        {{ $assignment->getRoleDisplayName() }}
                                    </span>
                                </td>
                                <td>
                                    <div class="text-muted small">
                                        {{ $assignment->assigned_at->format('M d, Y') }}
                                    </div>
                                    <div class="text-muted smaller">
                                        {{ $assignment->assigned_at->diffForHumans() }}
                                    </div>
                                </td>
                                <td>
                                    <div class="d-flex justify-content-end gap-2">
                                      
                                        <form action="{{ route('assignments.destroy', $assignment) }}" 
                                              method="POST" class="d-inline">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" 
                                                    class="btn btn-outline-danger btn-sm"
                                                    data-bs-toggle="tooltip"
                                                    title="Remove Assignment"
                                                    onclick="return confirm('Are you sure you want to remove {{ $assignment->user->name }} from this assignment?')">
                                                <i class="fas fa-user-times">delete</i>
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <!-- Empty State -->
            @else
                <div class="text-center py-5">
                    <div class="empty-state">
                        <i class="fas fa-users fa-4x text-muted mb-3"></i>
                        <h5 class="text-muted">No Assignments Found</h5>
                        <p class="text-muted mb-4">Get started by creating your first team assignment</p>
                        <a href="{{ route('assignments.create') }}" class="btn btn-primary">
                            <i class="fas fa-user-plus me-2"></i>Create Assignment
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
.avatar-sm {
    width: 36px;
    height: 36px;
}
.empty-state {
    max-width: 400px;
    margin: 0 auto;
}
.card {
    border: none;
    box-shadow: 0 0.15rem 1.75rem 0 rgba(58, 59, 69, 0.15);
}
.table > :not(caption) > * > * {
    padding: 0.75rem 0.5rem;
}
.border-left-primary {
    border-left: 0.25rem solid #4e73df !important;
}
.border-left-success {
    border-left: 0.25rem solid #1cc88a !important;
}
.border-left-info {
    border-left: 0.25rem solid #36b9cc !important;
}
.border-left-warning {
    border-left: 0.25rem solid #f6c23e !important;
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

    // Filter functionality
    const projectFilter = document.getElementById('projectFilter');
    const roleFilter = document.getElementById('roleFilter');
    const clearFilters = document.getElementById('clearFilters');
    
    function applyFilters() {
        const projectValue = projectFilter.value;
        const roleValue = roleFilter.value;
        const url = new URL('{{ route('assignments.index') }}');
        
        if (projectValue) url.searchParams.set('project_id', projectValue);
        if (roleValue) url.searchParams.set('role', roleValue);
        
        window.location.href = url.toString();
    }
    
    function clearAllFilters() {
        window.location.href = '{{ route('assignments.index') }}';
    }
    
    projectFilter.addEventListener('change', applyFilters);
    roleFilter.addEventListener('change', applyFilters);
    clearFilters.addEventListener('click', clearAllFilters);

    // Add some interactive effects
    const tableRows = document.querySelectorAll('#assignmentsTable tbody tr');
    tableRows.forEach(row => {
        row.addEventListener('mouseenter', function() {
            this.style.backgroundColor = '#f8f9fa';
            this.style.transition = 'background-color 0.2s ease';
        });
        
        row.addEventListener('mouseleave', function() {
            this.style.backgroundColor = '';
        });
    });
});
</script>
@endpush