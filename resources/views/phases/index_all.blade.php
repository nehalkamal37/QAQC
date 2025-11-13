@extends('layouts.app')

@section('content')
<div class="container-fluid px-4 mt-4">
    <h1 class="h3 mb-4">All Phases</h1>
    <table class="table table-striped align-middle">
        <thead class="table-dark">
            <tr>
                <th>ID</th>
                <th>Type</th>
                <th>Project</th>
                <th>Due Date</th>
                <th>Status</th>
            </tr>
        </thead>
        <tbody>
            @foreach($phases as $phase)
                <tr>
                    <td>{{ $phase->id }}</td>
                    <td>{{ $phase->type }}</td>
                    <td>{{ $phase->project->name ?? '-' }}</td>
                    <td>{{ $phase->due_date ?? '-' }}</td>
                    <td>{{ ucfirst($phase->status) }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>

    {{ $phases->links() }}
</div>
@endsection
