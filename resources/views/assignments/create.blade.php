@extends('layouts.app')

@section('content')
<div class="container-fluid px-4">
    <div class="d-flex justify-content-between align-items-center py-4">
        <div>
            <h1 class="h3 mb-2 text-gray-800">Assign Team Member</h1>
            <p class="text-muted">Add users to projects with specific roles and permissions</p>
        </div>
        <a href="{{ route('assignments.index') }}" class="btn btn-outline-secondary">
            <i class="fas fa-arrow-left me-2"></i>Back to Assignments
        </a>
    </div>

    <div class="row justify-content-center">
        <div class="col-xl-8 col-lg-10">
            <div class="card shadow-sm border-0">
                <div class="card-header bg-white py-3 border-bottom">
                    <h6 class="m-0 font-weight-bold text-primary">
                        <i class="fas fa-user-plus me-2"></i>New Assignment
                    </h6>
                </div>
                <div class="card-body p-4">
                    <form action="{{ route('assignments.store') }}" method="POST" id="assignmentForm">
                        @csrf
                        
                        <!-- User & Project Selection -->
                        <div class="row mb-4">
                            <div class="col-md-6">
                                <label for="user_id" class="form-label fw-semibold text-dark mb-2">
                                    <i class="fas fa-user me-1 text-primary"></i>Team Member *
                                </label>
                                <select name="user_id" id="user_id" class="form-select form-select-lg shadow-sm" required>
                                    <option value="">Select Team Member</option>
                                    @foreach($users as $user)
                                        <option value="{{ $user->id }}" {{ old('user_id') == $user->id ? 'selected' : '' }}>
                                            {{ $user->name }} 
                                            <small class="text-muted">({{ $user->email }})</small>
                                        </option>
                                    @endforeach
                                </select>
                                @error('user_id')
                                    <div class="text-danger small mt-1">
                                        <i class="fas fa-exclamation-circle me-1"></i>{{ $message }}
                                    </div>
                                @enderror
                            </div>
                            
                            <div class="col-md-6">
                                <label for="project_id" class="form-label fw-semibold text-dark mb-2">
                                    <i class="fas fa-project-diagram me-1 text-primary"></i>Project *
                                </label>
                                <select name="project_id" id="project_id" class="form-select form-select-lg shadow-sm" required>
                                    <option value="">Select Project</option>
                                    @foreach($projects as $project)
                                        <option value="{{ $project->id }}" {{ old('project_id') == $project->id ? 'selected' : '' }}>
                                            {{ $project->name }}
                                            @if($project->client)
                                                <small class="text-muted">- {{ $project->client }}</small>
                                            @endif
                                        </option>
                                    @endforeach
                                </select>
                                @error('project_id')
                                    <div class="text-danger small mt-1">
                                        <i class="fas fa-exclamation-circle me-1"></i>{{ $message }}
                                    </div>
                                @enderror
                            </div>
                        </div>
                        
                        <!-- Role & Phase Selection -->
                        <div class="row mb-4">
                            <div class="col-md-6">
                                <label for="role" class="form-label fw-semibold text-dark mb-2">
                                    <i class="fas fa-user-tag me-1 text-primary"></i>Role *
                                </label>
                                <select name="role" id="role" class="form-select form-select-lg shadow-sm" required>
                                    <option value="">Select Role</option>
                                    @foreach($roles as $value => $label)
                                        <option value="{{ $value }}" {{ old('role') == $value ? 'selected' : '' }}>
                                            <i class="fas fa-{{ $roleIcons[$value] ?? 'user' }} me-2"></i>
                                            {{ $label }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('role')
                                    <div class="text-danger small mt-1">
                                        <i class="fas fa-exclamation-circle me-1"></i>{{ $message }}
                                    </div>
                                @enderror
                                
                                <!-- Role Descriptions -->
                                <div class="mt-2">
                                    <small class="text-muted" id="roleDescription">
                                        Select a role to see description
                                    </small>
                                </div>
                            </div>
                            
                          

                            <div class="col-md-6">
    <label for="phase_id" class="form-label">Phase (Optional)</label>
    <select name="phase_id" id="phase_id" class="form-select">
        <option value="">Select Phase</option>
        @foreach(\App\Models\Phase::all() as $phase)
            <option value="{{ $phase->id }}" {{ old('phase_id') == $phase->id ? 'selected' : '' }}>
                {{ $phase->type }} ({{ 'project :'.$phase->project->name }})
            </option>
        @endforeach
    </select>
    @error('phase_id')
        <div class="text-danger">{{ $message }}</div>
    @enderror
</div>
                               
                        </div>
                        
                        <!-- Notes Section -->
                        <div class="mb-4">
                            <label for="notes" class="form-label fw-semibold text-dark mb-2">
                                <i class="fas fa-sticky-note me-1 text-primary"></i>Assignment Notes
                            </label>
                            <textarea name="notes" id="notes" class="form-control shadow-sm" rows="3" 
                                      placeholder="Add any specific instructions or context for this assignment...">{{ old('notes') }}</textarea>
                            <div class="form-text">Optional notes about this assignment</div>
                        </div>

                        <!-- Role Descriptions Card -->
                        <div class="card border-info mb-4">
                            <div class="card-header bg-info text-white py-2">
                                <h6 class="mb-0">
                                    <i class="fas fa-info-circle me-2"></i>Role Descriptions
                                </h6>
                            </div>
                            <div class="card-body p-3">
                                <div class="row small text-muted">
                                    <div class="col-md-4 mb-2">
                                        <strong class="text-dark">Project Manager</strong><br>
                                        Approves phase gates, assigns reviewers
                                    </div>
                                    <div class="col-md-4 mb-2">
                                        <strong class="text-dark">Senior Reviewer</strong><br>
                                        Performs QA/QC reviews and verification
                                    </div>
                                    <div class="col-md-4 mb-2">
                                        <strong class="text-dark">Engineer/EIT</strong><br>
                                        Resolves comments and updates sheets
                                    </div>
                                    <div class="col-md-4 mb-2">
                                        <strong class="text-dark">Night Vision</strong><br>
                                        Executes overnight drafting and QA updates
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Action Buttons -->
                        <div class="d-flex justify-content-between align-items-center pt-3 border-top">
                            <a href="{{ route('assignments.index') }}" class="btn btn-outline-secondary">
                                <i class="fas fa-times me-2"></i>Cancel
                            </a>
                            <button type="submit" class="btn btn-primary btn-lg px-4">
                                <i class="fas fa-user-check me-2"></i>Assign Team Member
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('styles')
<style>
.form-select-lg {
    padding: 0.75rem 1rem;
    font-size: 0.9rem;
}
.card {
    border: none;
    box-shadow: 0 0.15rem 1.75rem 0 rgba(58, 59, 69, 0.15);
}
.form-label {
    font-size: 0.9rem;
}
</style>
@endpush

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const projectSelect = document.getElementById('project_id');
    const phaseSelect = document.getElementById('phase_id');
    const roleSelect = document.getElementById('role');
    const roleDescription = document.getElementById('roleDescription');
    
    // Role descriptions
    const roleDescriptions = {
        'pm': 'Project Manager: Approves phase gates, assigns reviewers, and manages project timeline.',
        'senior_reviewer': 'Senior Reviewer: Performs detailed QA/QC reviews and verification of work.',
        'engineer': 'Engineer: Resolves technical comments and updates project documentation.',
        'eit': 'EIT: Supports engineering tasks and resolves basic QA items.',
        'night_vision': 'Night Vision: Executes overnight drafting and QA updates for next-day delivery.'
    };
    
    // Role icons mapping (for visual consistency)
    const roleIcons = {
        'pm': 'user-tie',
        'senior_reviewer': 'user-check', 
        'engineer': 'cogs',
        'eit': 'user-graduate',
        'night_vision': 'moon'
    };
    
    // Update role description
    roleSelect.addEventListener('change', function() {
        const description = roleDescriptions[this.value] || 'Select a role to see description';
        roleDescription.textContent = description;
    });
    
    // Dynamic phase loading
    projectSelect.addEventListener('change', function() {
        const projectId = this.value;
        
        if (projectId) {
            phaseSelect.disabled = false;
            
            // Show loading state
            phaseSelect.innerHTML = '<option value="">Loading phases...</option>';
            
            fetch(`/api/projects/${projectId}/phases`)
                .then(response => {
                    if (!response.ok) throw new Error('Network error');
                    return response.json();
                })
                .then(phases => {
                    phaseSelect.innerHTML = '<option value="">Select Phase (Optional)</option>';
                    
                    phases.forEach(phase => {
                        const option = document.createElement('option');
                        option.value = phase.id;
                        option.textContent = `${phase.type} - ${phase.name || 'Phase'}`;
                        if (phase.due_date) {
                            option.textContent += ` (Due: ${new Date(phase.due_date).toLocaleDateString()})`;
                        }
                        phaseSelect.appendChild(option);
                    });
                    
                    // Set old value if exists
                    @if(old('phase_id'))
                        phaseSelect.value = '{{ old('phase_id') }}';
                    @endif
                })
                .catch(error => {
                    console.error('Error loading phases:', error);
                    phaseSelect.innerHTML = '<option value="">Error loading phases</option>';
                });
        } else {
            phaseSelect.disabled = true;
            phaseSelect.innerHTML = '<option value="">Select Phase</option>';
        }
    });
    
    // Initialize phase select based on current project
    @if(old('project_id'))
        projectSelect.dispatchEvent(new Event('change'));
    @endif
    
    // Form validation enhancement
    const form = document.getElementById('assignmentForm');
    form.addEventListener('submit', function(e) {
        const user = document.getElementById('user_id').value;
        const project = document.getElementById('project_id').value;
        const role = document.getElementById('role').value;
        
        if (!user || !project || !role) {
            e.preventDefault();
            alert('Please fill all required fields before submitting.');
        }
    });
});
</script>
@endpush