@extends('layouts.app')

@section('content')
<div class="container mt-4">
    <h2 class="mb-3 text-center">Electrical QC Checklist (Demo)</h2>

    <div class="card shadow-sm">
        <div class="card-body p-0">
            <table class="table table-striped table-bordered mb-0">
                <thead class="table-dark">
                    <tr>
                        <th>#</th>
                        <th>Type</th>
                        <th>Category</th>
                        <th>Item</th>
                        <th>Notes</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($data as $index => $row)
                        <tr>
                            <td>{{ $index + 1 }}</td>
                            <td>{{ $row['type'] }}</td>
                            <td>{{ $row['category'] }}</td>
                            <td>{{ $row['item'] }}</td>
                            <td>{{ $row['notes'] }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
