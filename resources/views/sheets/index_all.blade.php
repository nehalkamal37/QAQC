@extends('layouts.app')

@section('content')
<div class="container-fluid px-4 mt-4">
    <h1 class="h3 mb-4">All Sheets</h1>

    <div class="card shadow-sm border-0">
        <div class="card-body">
            <table class="table table-striped align-middle">
                <thead class="table-dark">
                    <tr>
                        <th>ID</th>
                        <th>Discipline</th>
                        <th>Number</th>
                        <th>Title</th>
                        <th>Phase</th>
                        <th>Project</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($sheets as $sheet)
                        <tr>
                            <td>{{ $sheet->id }}</td>
                            <td>{{ $sheet->discipline }}</td>
                            <td>{{ $sheet->number }}</td>
                            <td>{{ $sheet->title }}</td>
                            <td>{{ $sheet->phase->type ?? '-' }}</td>
                            <td>{{ $sheet->phase->project->name ?? '-' }}</td>
                            <td>
                                <span class="badge bg-{{ $sheet->status == 'closed' ? 'secondary' : 'info' }}">
                                    {{ ucfirst($sheet->status) }}
                                </span>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="7" class="text-center text-muted">No sheets found.</td></tr>
                    @endforelse
                </tbody>
            </table>
            {{ $sheets->links() }}
        </div>
    </div>
</div>
@endsection
