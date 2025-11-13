@extends('layouts.app')

@section('content')
<div class="container-fluid px-4 mt-4">
    <h1 class="h3 mb-4">All QA Items</h1>

    <div class="card shadow-sm border-0">
        <div class="card-body">
            <table class="table table-striped align-middle">
                <thead class="table-dark">
                    <tr>
                        <th>ID</th>
                        <th>Category</th>
                        <th>Severity</th>
                        <th>Title</th>
                        <th>Sheet</th>
                        <th>Phase</th>
                        <th>Project</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($qa_items as $item)
                        <tr>
                            <td>{{ $item->id }}</td>
                            <td>{{ $item->category ?? '-' }}</td>
                            <td>{{ $item->severity ?? '-' }}</td>
                            <td>{{ $item->title ?? '-' }}</td>
                            <td>{{ $item->sheet->number ?? '-' }}</td>
                            <td>{{ $item->sheet->phase->type ?? '-' }}</td>
                            <td>{{ $item->sheet->phase->project->name ?? '-' }}</td>
                            <td>
                                <span class="badge bg-{{ $item->status == 'closed' ? 'secondary' : 'warning' }}">
                                    {{ ucfirst($item->status) }}
                                </span>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="8" class="text-center text-muted">No QA Items found.</td></tr>
                    @endforelse
                </tbody>
            </table>
            {{ $qa_items->links() }}
        </div>
    </div>
</div>
@endsection
