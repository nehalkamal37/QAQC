@extends('layouts.app')

@section('content')
<div class="container mt-4 px-4">
    <!-- Page Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 class="mb-1">Projects Dashboard</h2>
            <p class="text-muted mb-0">Showing {{ $projects->count() }} project(s)</p>
        </div>
        
        @if(auth()->user()->hasRole(['Admin', 'PM']))
        <a href="{{ route('projects.create') }}" class="btn btn-primary">
            <i class="fas fa-plus me-2"></i>Add New Project
        </a>
        @endif
    </div>

    <!-- Success Message -->
    @if(session('success'))
    <div class="alert alert-success alert-dismissible fade show mb-4" role="alert">
        <i class="fas fa-check-circle me-2"></i>
        {{ session('success') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
    @endif

    <!-- Projects Table Card -->
    <div class="card shadow-sm border-0">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead class="table-light">
                        <tr>
                            <th class="border-0 ps-4">Name</th>
                            <th class="border-0">Client</th>
                            <th class="border-0">PM</th>
                            <th class="border-0">Status</th>
                            <th class="border-0">Due Date</th>
                            <th class="border-0">Progress</th>
                            <th class="border-0 pe-4 text-nowrap">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($projects as $project)
                        <tr>
                            <!-- Project Name -->
                            <td class="ps-4 align-middle">
                                <strong>{{ $project->name }}</strong>
                            </td>
                            
                            <!-- Client -->
                            <td class="align-middle">{{ $project->client }}</td>
                            
                            <!-- Project Manager -->
                            <td class="align-middle">
                                @if($project->pm)
                                    {{ $project->pm->name }}
                                @else
                                    <span class="text-muted">N/A</span>
                                @endif
                            </td>
                            
                            <!-- Status -->
                            <td class="align-middle">
                                <span class="badge rounded-pill 
                                    @if($project->status == 'active') bg-success
                                    @elseif($project->status == 'in_progress') bg-warning
                                    @elseif($project->status == 'completed') bg-info
                                    @elseif($project->status == 'on_hold') bg-secondary
                                    @else bg-light text-dark @endif">
                                    {{ ucfirst($project->status) }}
                                </span>
                            </td>
                            
                            <!-- Due Date -->
                            <td class="align-middle">
                                <div class="d-flex flex-column">
                                    <span>{{ $project->due_date }}</span>
                                    @php
                                        $dueDate = \Carbon\Carbon::parse($project->due_date);
                                        $today = \Carbon\Carbon::now();
                                        $daysDiff = $today->diffInDays($dueDate, false);
                                    @endphp
                                    @if($daysDiff > 0)
                                        <small class="text-muted">{{ $daysDiff }} days left</small>
                                    @elseif($daysDiff == 0)
                                        <small class="text-warning">Due today</small>
                                    @else
                                        <small class="text-danger">{{ abs($daysDiff) }} days overdue</small>
                                    @endif
                                </div>
                            </td>
                            
                            <!-- Progress -->
                            <td class="align-middle">
                                <div class="d-flex align-items-center">
                                    <div class="flex-grow-1 me-3">
                                        <div class="progress mb-2" style="height: 10px;">
                                            <div class="progress-bar bg-success"
                                                style="width: {{ $project->completionPercentage() }}%"
                                                role="progressbar"
                                                aria-valuenow="{{ $project->completionPercentage() }}"
                                                aria-valuemin="0"
                                                aria-valuemax="100">
                                            </div>
                                        </div>
                                    </div>
                                    <div class="flex-shrink-0">
                                        <span class="fw-semibold">{{ $project->completionPercentage() }}%</span>
                                    </div>
                                </div>
                            </td>
                            
                            <!-- Actions -->
                            <td class="align-middle pe-4">
                                <div class="d-flex flex-wrap gap-1">
                                    <!-- View Button -->
                                    <a href="{{ route('projects.show', $project->id) }}" 
                                       class="btn btn-sm btn-info text-white d-flex align-items-center">
                                        <i class="fas fa-eye me-1"></i>
                                        View
                                    </a>
                                    
                                    <!-- Phases Button -->
                                    <a href="{{ route('phases.index', $project->id) }}" 
                                       class="btn btn-sm btn-success d-flex align-items-center">
                                        <i class="fas fa-tasks me-1"></i>
                                        Phases
                                    </a>
                                    
                                    <!-- Edit Button (Admin/PM only) -->
                                    @if(auth()->user()->hasRole(['Admin', 'PM']))
                                    <a href="{{ route('projects.edit', $project->id) }}" 
                                       class="btn btn-sm btn-warning d-flex align-items-center">
                                        <i class="fas fa-edit me-1"></i>
                                        Edit
                                    </a>
                                    
                                    <!-- Delete Button (Admin/PM only) -->
                                    <button type="button" 
                                            class="btn btn-sm btn-danger d-flex align-items-center"
                                            onclick="confirmDelete({{ $project->id }}, '{{ addslashes($project->name) }}')">
                                        <i class="fas fa-trash me-1"></i>
                                        Delete
                                    </button>
                                    @endif
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="7" class="text-center py-5">
                                <div class="py-5">
                                    <i class="fas fa-folder-open fa-3x text-muted mb-3"></i>
                                    <h5 class="text-muted">No projects found</h5>
                                    @if(auth()->user()->hasRole(['Admin', 'PM']))
                                    <p class="text-muted mb-4">Get started by creating your first project</p>
                                    <a href="{{ route('projects.create') }}" class="btn btn-primary">
                                        <i class="fas fa-plus me-2"></i>Create First Project
                                    </a>
                                    @endif
                                </div>
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- Delete Confirmation Modal -->
<div class="modal fade" id="deleteModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Confirm Delete</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <p>Are you sure you want to delete project "<span id="projectName"></span>"?</p>
                <p class="text-danger"><small>This action cannot be undone.</small></p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <form id="deleteForm" method="POST" style="display: inline;">
                    @csrf @method('DELETE')
                    <button type="submit" class="btn btn-danger">Delete Project</button>
                </form>
            </div>
        </div>
    </div>
</div>

@push('styles')
<style>
    .card {
        border-radius: 8px;
        overflow: hidden;
    }
    
    .table th {
        font-weight: 600;
        color: #495057;
        text-transform: uppercase;
        font-size: 0.85rem;
        letter-spacing: 0.5px;
        border-top: 0;
    }
    
    .table tbody tr {
        border-bottom: 1px solid #f0f0f0;
    }
    
    .table tbody tr:last-child {
        border-bottom: 0;
    }
    
    .table tbody tr:hover {
        background-color: rgba(0, 123, 255, 0.02);
    }
    
    .progress {
        border-radius: 4px;
        overflow: hidden;
    }
    
    .progress-bar {
        border-radius: 4px;
        transition: width 0.6s ease;
    }
    
    .badge {
        padding: 0.5em 0.9em;
        font-weight: 500;
    }
    
    .btn-sm {
        padding: 0.25rem 0.75rem;
        font-size: 0.875rem;
    }
</style>
@endpush

@push('scripts')
<script>
function confirmDelete(projectId, projectName) {
    // Set project name in modal
    document.getElementById('projectName').textContent = projectName;
    
    // Set form action
    const form = document.getElementById('deleteForm');
    form.action = `/projects/${projectId}`;
    
    // Show modal
    const deleteModal = new bootstrap.Modal(document.getElementById('deleteModal'));
    deleteModal.show();
}
</script>
@endpush
@endsection