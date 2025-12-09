@extends('layouts.app')

@section('content')
<div class="container-fluid px-4 mt-4">
    <h2 class="mb-4">Preview Imported QA Items for Sheet: {{ $sheet->title }}</h2>

<form action="{{ route('qa_items.importConfirm', $sheet->id) }}" method="POST">
    @csrf
    <button class="btn btn-primary mt-3">Confirm Import</button>
</form>


 <table class="table table-bordered mt-4">
    <thead>
        <tr>
            <th>Topic</th>
            <th>Category</th>
            <th>Item</th>
            <th>Notes</th>
            <th>Status</th>
            <th>Due Date</th>
            <th>Assigned To</th>
        </tr>
    </thead>
    <tbody>
        @foreach ($previewData as $row)
        <tr>
            <td>{{ $row['topic'] ?? '' }}</td>
            <td>{{ $row['category'] ?? '' }}</td>
            <td>{{ $row['item'] ?? '' }}</td>
            <td>{{ $row['notes'] ?? '' }}</td>
            <td>{{ $row['status'] ?? 'open' }}</td>
            <td>{{ $row['due_date'] ?? '' }}</td>
            <td>{{ $row['assigned_to'] ?? '' }}</td>
        </tr>
        @endforeach
    </tbody>
</table>


    <div class="mt-4">
        <a href="{{ route('qa_items.index', $sheet->id) }}" class="btn btn-secondary">
            ← Back to QA Items
        </a>
    </div>
</div>
@endsection
