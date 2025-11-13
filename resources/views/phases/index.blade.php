@extends('layouts.app')



@section('content')
<div class="container-fluid px-4 mt-4"> {{-- 👈 أضف mt-4 هنا --}}
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3">Phases for Project: {{ $project->name }}</h1>
        <a href="{{ route('phases.create', $project->id) }}" class="btn btn-primary">+ Add Phase</a>
    </div>

    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    <div class="card shadow-sm">
        <div class="card-body">
            <table class="table table-striped align-middle">
                <thead class="table-dark">
                    <tr>
                        <th>ID</th>
                        <th>Type</th>
                        <th>Due Date</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($project->phases as $phase)
                        <tr>
                            <td>{{ $phase->id }}</td>
                            <td>{{ $phase->type }}</td>
                            <td>{{ $phase->due_date ?? '-' }}</td>
                            <td>
                                <span class="badge bg-{{ $phase->status == 'closed' ? 'secondary' : 'success' }}">
                                    {{ ucfirst($phase->status) }}
                                </span>
                            </td>
                           <td>
    <a href="{{ route('phases.edit', $phase->id) }}" class="btn btn-sm btn-warning">Edit</a>

    <a href="{{ route('sheets.index', $phase->id) }}" class="btn btn-sm btn-info">
        Sheets
    </a>

    <form action="{{ route('phases.destroy', $phase->id) }}" method="POST" class="d-inline">
        @csrf
        @method('DELETE')
        <button onclick="return confirm('Are you sure?')" class="btn btn-sm btn-danger">Delete</button>
    </form>
</td>

                        </tr>
                    @empty
                        <tr><td colspan="5" class="text-center text-muted">No phases found.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
