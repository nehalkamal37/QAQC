@extends('layouts.app')

@section('content')
<div class="container-fluid px-4 mt-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3">Edit QA Item #{{ $qaItem->id }}</h1>
        <a href="{{ route('qa_items.index', $qaItem->sheet_id) }}" class="btn btn-secondary">← Back</a>
    </div>

    <div class="card shadow-sm">
        <div class="card-body">
            <form action="{{ route('qa_items.update', [$qaItem->sheet_id, $qaItem->id]) }}" method="POST">
                @csrf
                @method('PUT')

                {{-- Description --}}
                <div class="mb-3">
                    <label class="form-label">Item Description</label>
                    <textarea class="form-control" name="item_description" rows="3" required>{{ old('item_description', $qaItem->item_description) }}</textarea>
                </div>

                {{-- Status --}}
                <div class="mb-3">
                    <label class="form-label">Status</label>
                    <select class="form-select" name="status" required>
                        @foreach (['open', 'in_progress', 'needs_info', 'resolved', 'verified', 'closed'] as $status)
                            <option value="{{ $status }}" {{ $qaItem->status == $status ? 'selected' : '' }}>
                                {{ ucfirst(str_replace('_', ' ', $status)) }}
                            </option>
                        @endforeach
                    </select>
                </div>

                {{-- Severity --}}
                <div class="mb-3">
                    <label class="form-label">Severity</label>
                    <select class="form-select" name="severity" required>
                        @foreach (['critical', 'high', 'medium', 'low'] as $sev)
                            <option value="{{ $sev }}" {{ $qaItem->severity == $sev ? 'selected' : '' }}>
                                {{ ucfirst($sev) }}
                            </option>
                        @endforeach
                    </select>
                </div>

                {{-- Comments --}}
                <div class="mb-3">
                    <label class="form-label">Comments</label>
                    <textarea class="form-control" name="comments" rows="2">{{ old('comments', $qaItem->comments) }}</textarea>
                </div>

                {{-- Due date --}}
                <div class="mb-3">
                    <label class="form-label">Due Date</label>
                    <input type="date" name="due_date" class="form-control" value="{{ old('due_date', $qaItem->due_date) }}">
                </div>

                {{-- Assigned to --}}
                <div class="mb-3">
                    <label class="form-label">Assigned To</label>
                    <input type="text" name="assigned_to" class="form-control" value="{{ old('assigned_to', $qaItem->assigned_to) }}">
                </div>

                <div class="text-end">
                    <button type="submit" class="btn btn-success">Update QA Item</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
