@extends('layouts.app')

@section('content')
<div class="container-fluid px-4 mt-4">
    <h1 class="h3 mb-4 text-gray-800">Edit Project</h1>

    <div class="card shadow-sm">
        <div class="card-body">
            <form action="{{ route('projects.update', $project->id) }}" method="POST">
                @csrf
                @method('PUT')

                <div class="mb-3">
                    <label class="form-label">Project Name *</label>
                    <input type="text" name="name" class="form-control" value="{{ $project->name }}" required>
                </div>

                <div class="mb-3">
                    <label class="form-label">Client</label>
                    <input type="text" name="client" class="form-control" value="{{ $project->client }}">
                </div>

                <div class="mb-3">
                    <label class="form-label">Start Date</label>
                    <input type="date" name="start_date" class="form-control" value="{{ $project->start_date }}">
                </div>

                <div class="mb-3">
                    <label class="form-label">Due Date</label>
                    <input type="date" name="due_date" class="form-control" value="{{ $project->due_date }}">
                </div>

                <div class="mb-3">
                    <label class="form-label">Status</label>
                    <select name="status" class="form-select">
                        <option value="planning" {{ $project->status == 'planning' ? 'selected' : '' }}>Planning</option>
                        <option value="in_review" {{ $project->status == 'in_review' ? 'selected' : '' }}>In Review</option>
                        <option value="closed" {{ $project->status == 'closed' ? 'selected' : '' }}>Closed</option>
                    </select>
                </div>

                <button type="submit" class="btn btn-primary">Update</button>
                <a href="{{ route('projects.index') }}" class="btn btn-secondary">Cancel</a>
            </form>
        </div>
    </div>
</div>
@endsection
