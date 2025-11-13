@extends('layouts.app')

@section('content')
<div class="container-fluid px-4 mt-4">
    <h1 class="h3 mb-4">Add Phase ({{ $project->name }})</h1>

    <div class="card shadow-sm">
        <div class="card-body">
            <form action="{{ route('phases.store', $project->id) }}" method="POST">
                @csrf
                <div class="mb-3">
                    <label class="form-label">Phase Type</label>
                    <select name="type" class="form-select" required>
                        <option value="SD">SD</option>
                        <option value="DD">DD</option>
                        <option value="CD">CD</option>
                        <option value="Closeout">Closeout</option>
                    </select>
                </div>

                <div class="mb-3">
                    <label class="form-label">Due Date</label>
                    <input type="date" name="due_date" class="form-control">
                </div>

                <div class="mb-3">
                    <label class="form-label">Status</label>
                    <select name="status" class="form-select">
                        <option value="in_review">In Review</option>
                        <option value="ready_for_signoff">Ready for Signoff</option>
                        <option value="closed">Closed</option>
                    </select>
                </div>

                <button type="submit" class="btn btn-success">Save</button>
                <a href="{{ route('phases.index', $project->id) }}" class="btn btn-secondary">Cancel</a>
            </form>
        </div>
    </div>
</div>
@endsection
