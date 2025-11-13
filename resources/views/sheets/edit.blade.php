@extends('layouts.app')

@section('content')
<div class="container-fluid px-4 mt-4">
    <h1 class="h3 mb-4">Edit Sheet #{{ $sheet->id }}</h1>

    <div class="card shadow-sm">
        <div class="card-body">
            <form action="{{ route('sheets.update', $sheet->id) }}" method="POST">
                @csrf
                @method('PUT')

                <div class="mb-3">
                    <label class="form-label">Discipline</label>
                    <input type="text" name="discipline" class="form-control" value="{{ $sheet->discipline }}">
                </div>

                <div class="mb-3">
                    <label class="form-label">Number</label>
                    <input type="text" name="number" class="form-control" value="{{ $sheet->number }}">
                </div>

                <div class="mb-3">
                    <label class="form-label">Title</label>
                    <input type="text" name="title" class="form-control" value="{{ $sheet->title }}">
                </div>

                <div class="mb-3">
                    <label class="form-label">Version</label>
                    <input type="text" name="version" class="form-control" value="{{ $sheet->version }}">
                </div>

                <div class="mb-3">
                    <label class="form-label">Status</label>
                    <select name="status" class="form-select">
                        <option value="Pending" {{ $sheet->status == 'Pending' ? 'selected' : '' }}>Pending</option>
                        <option value="Reviewed" {{ $sheet->status == 'Reviewed' ? 'selected' : '' }}>Reviewed</option>
                        <option value="Approved" {{ $sheet->status == 'Approved' ? 'selected' : '' }}>Approved</option>
                    </select>
                </div>

                <button type="submit" class="btn btn-primary">Update</button>
                <a href="{{ route('sheets.index', $sheet->phase_id) }}" class="btn btn-secondary">Cancel</a>
            </form>
        </div>
    </div>
</div>
@endsection
