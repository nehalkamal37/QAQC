@extends('layouts.app')

@section('content')
<div class="container-fluid px-4 mt-4">
    <h2 class="mb-4">Import QA Items for Sheet: {{ $sheet->title }}</h2>

<form action="{{ route('qa_items.importPreview', $sheet->id) }}" 
      method="POST" 
      enctype="multipart/form-data">
        @csrf
        <div class="mb-3">
            <label class="form-label">Upload Excel File</label>
            <input type="file" name="file" class="form-control" required>
            <small class="text-muted">Accepted formats: .xlsx, .xls (max 2MB)</small>
        </div>

        <button type="submit" class="btn btn-success">Preview</button>
        <a href="{{ route('qa_items.index', $sheet->id) }}" class="btn btn-secondary">Cancel</a>
    </form>
</div>
@endsection
