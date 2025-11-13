@extends('layouts.app')

@section('content')
<div class="container-fluid px-4 mt-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3">Add QA Item for Sheet: {{ $sheet->title ?? 'Untitled' }}</h1>
        <a href="{{ route('qa_items.index', $sheet->id) }}" class="btn btn-secondary">← Back</a>
    </div>

    <div class="card shadow-sm">
        <div class="card-body">
            <form action="{{ route('qa_items.store', $sheet->id) }}" method="POST">
                @csrf

                <div class="mb-3">
                    <label class="form-label">Item Description</label>
                    <textarea class="form-control" name="item_description" rows="3" required>{{ old('item_description') }}</textarea>
                </div>

                <div class="mb-3">
                    <label class="form-label">Status</label>
                    <select class="form-select" name="status" required>
                        <option value="open">Open</option>
                        <option value="in_progress">In Progress</option>
                        <option value="needs_info">Needs Info</option>
                        <option value="resolved">Resolved</option>
                        <option value="verified">Verified</option>
                        <option value="closed">Closed</option>
                    </select>
                </div>
<div class="mb-3">
    <label class="form-label">Severity</label>
    <select name="severity" class="form-select" required>
        <option value="low">Low</option>
        <option value="medium" selected>Medium</option>
        <option value="high">High</option>
        <option value="critical">Critical</option>
    </select>
</div>

                <div class="mb-3">
                    <label class="form-label">Comments</label>
                    <textarea class="form-control" name="comments" rows="2">{{ old('comments') }}</textarea>
                </div>

                <div class="mb-3">
                    <label class="form-label">Due Date</label>
                    <input type="date" name="due_date" class="form-control" value="{{ old('due_date') }}">
                </div>

                <div class="mb-3">
                    <label class="form-label">Assigned To</label>
                    <input type="text" name="assigned_to" class="form-control" placeholder="Enter assignee name" value="{{ old('assigned_to') }}">
                </div>

                <div class="text-end">
                    <button type="submit" class="btn btn-primary">Save QA Item</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
