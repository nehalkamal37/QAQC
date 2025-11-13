@extends('layouts.app')

@section('content')
<div class="container mt-4 px-4">
    <h2 class="mb-3">Projects Dashboard</h2>
                                                   @if(auth()->user()->hasRole(['Admin', 'PM']))

    <a href="{{ route('projects.create') }}" class="btn btn-primary mb-3">+ Add New Project</a>
@endif
    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    <table class="table table-bordered">
        <thead>
            <tr>
                <th>Name</th>
                <th>Client</th>
                <th>Status</th>
                <th>Due Date</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            @foreach($projects as $project)
            <tr>
                <td>{{ $project->name }}</td>
                <td>{{ $project->client }}</td>
                <td>{{ ucfirst($project->status) }}</td>
                <td>{{ $project->due_date }}</td>
                <td>

                    <a href="{{ route('projects.show', $project->id) }}" class="btn btn-sm btn-info">View</a>
                                                   @if(auth()->user()->hasRole(['Admin', 'PM']))
                    <a href="{{ route('phases.index', $project->id) }}" class="btn btn-sm btn-success text-white">Phases</a>

                    <a href="{{ route('projects.edit', $project->id) }}" class="btn btn-sm btn-warning">Edit</a>
                    <form action="{{ route('projects.destroy', $project->id) }}" method="POST" style="display:inline">
                        @csrf @method('DELETE')
                        <button type="submit" class="btn btn-sm btn-danger"
                            onclick="return confirm('Delete this project?')">Delete</button>
                    </form>
                    @endif
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>
</div>
@endsection
