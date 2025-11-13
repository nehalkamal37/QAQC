@extends('layouts.app')

@section('content')
<div class="container-fluid px-4 mt-4">
    <h2 class="mb-4">Preview Imported QA Items for: {{ $sheet->title }}</h2>

    @if(count($previewData))
    <div class="card shadow-sm">
        <div class="card-body">
            <table class="table table-striped align-middle">
                <thead class="table-dark">
                    <tr>
                        <th>Description</th>
                        <th>Status</th>
                        <th>Due Date</th>
                        <th>Assigned To</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($previewData as $item)
                    <tr>
                        <td>{{ $item['description'] }}</td>
                        <td>{{ $item['status'] }}</td>
                        <td>{{ $item['due_date'] }}</td>
                        <td>{{ $item['assigned_to'] }}</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
    @else
        <div class="alert alert-warning">No data found in the uploaded Excel file.</div>
    @endif

    <div class="mt-3">
        <a href="{{ route('qa_items.index', $sheet->id) }}" class="btn btn-secondary">← Back to QA Items</a>
    </div>
</div>
@endsection
