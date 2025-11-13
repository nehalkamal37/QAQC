@extends('layouts.app')

@section('content')
<div class="container-fluid px-4 mt-4">
    <h1 class="h3 mb-4">Add Sheet for Phase: {{ $phase->type }}</h1>

    <div class="card shadow-sm">
        <div class="card-body">
            <form action="{{ route('sheets.store', $phase->id) }}" method="POST">
                @csrf

                <div class="mb-3">
                    <label class="form-label">Discipline</label>
                    <input type="text" name="discipline" class="form-control" placeholder="Enter discipline">
                </div>

                <div class="mb-3">
                    <label class="form-label">Number</label>
                    <input type="text" name="number" class="form-control" placeholder="Enter sheet number">
                </div>

                <div class="mb-3">
                    <label class="form-label">Title</label>
                    <input type="text" name="title" class="form-control" placeholder="Enter title">
                </div>

                <div class="mb-3">
                    <label class="form-label">Version</label>
                    <input type="text" name="version" class="form-control" placeholder="Enter version">
                </div>

                <div class="mb-3">
                    <label class="form-label">Status</label>
                    <select name="status" class="form-select">
                        <option value="Pending">Pending</option>
                        <option value="Reviewed">Reviewed</option>
                        <option value="Approved">Approved</option>
                    </select>
                </div>

                <button type="submit" class="btn btn-primary">Save Sheet</button>
                <a href="{{ route('sheets.index', $phase->id) }}" class="btn btn-secondary">Cancel</a>
            </form>
        </div>
    </div>
</div>
@endsection
