@extends('layouts.app')

@section('content')
<div class="container-fluid px-4 mt-4">
    <h1 class="h3 mb-4">Edit Phase (Project: {{ $phase->project->name }})</h1>

    <div class="card shadow-sm">
        <div class="card-body">
            <form action="{{ route('phases.update', $phase->id) }}" method="POST">
                @csrf
                @method('PUT')

                <div class="mb-3">
                    <label class="form-label">Type</label>
                    <select name="type" class="form-select" required>
                        @foreach (['SD', 'DD', 'CD', 'Closeout'] as $type)
                            <option value="{{ $type }}" {{ $phase->type == $type ? 'selected' : '' }}>{{ $type }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="mb-3">
                    <label class="form-label">Due Date</label>
                    <input type="date" name="due_date" class="form-control" value="{{ $phase->due_date }}">
                </div>

                <div class="mb-3">
                    <label class="form-label">Status</label>
                    <select name="status" class="form-select">
                        @foreach (['in_review', 'ready_for_signoff', 'closed','changes required','planning'] as $status)
                            <option value="{{ $status }}" {{ $phase->status == $status ? 'selected' : '' }}>
                                {{ ucfirst(str_replace('_', ' ', $status)) }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <button type="submit" class="btn btn-primary">Update</button>
                <a href="{{ route('phases.index', $phase->project_id) }}" class="btn btn-secondary">Cancel</a>
            </form>
        </div>
    </div>
</div>
@endsection
