@extends('layouts.app')

@section('content')
<div class="container-fluid px-4 mt-4">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h1 class="h3 text-gray-800">Project Details</h1>
        <a href="{{ route('projects.index') }}" class="btn btn-secondary">← Back</a>
    </div>

    <div class="card shadow-sm">
        <div class="card-body">
            <h5 class="card-title mb-3">{{ $project->name }}</h5>

            <p><strong>Client:</strong> {{ $project->client ?? 'N/A' }}</p>
            <p><strong>Start Date:</strong> {{ $project->start_date ?? 'N/A' }}</p>
            <p><strong>Due Date:</strong> {{ $project->due_date ?? 'N/A' }}</p>
            <p><strong>Status:</strong> 
                <span class="badge bg-{{ $project->status === 'closed' ? 'secondary' : 'success' }}">
                    {{ ucfirst($project->status) }}
                </span>
            </p>

            <p class="text-muted">Created: {{ $project->created_at->format('Y-m-d H:i') }}</p>
            <p class="text-muted">Last Updated: {{ $project->updated_at->format('Y-m-d H:i') }}</p>

            <div class="mt-4">
                                                                   @if(auth()->user()->hasRole(['Admin', 'PM']))

                <a href="{{ route('projects.edit', $project->id) }}" class="btn btn-warning">Edit</a>
                <form action="{{ route('projects.destroy', $project->id) }}" method="POST" class="d-inline">
                    @csrf
                    @method('DELETE')
                    <button type="submit" onclick="return confirm('Are you sure?')" class="btn btn-danger">
                        Delete
                    </button>
                </form>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection
