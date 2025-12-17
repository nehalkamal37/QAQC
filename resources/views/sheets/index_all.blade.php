@extends('layouts.app')

@section('content')
<div class="container-fluid px-4 mt-4">
    <h1 class="h3 mb-4">All Sheets</h1>

    <div class="card shadow-sm border-0">
        <div class="card-body">
<div class="table-responsive all-sheets-table-wrapper">
    <table class="table table-striped align-middle all-sheets-table">
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
            </div>
            {{ $sheets->links() }}
        </div>
    </div>
</div>
<style>
/* ================================= */
/* ALL SHEETS – RESPONSIVE TABLE */
/* ================================= */

@media (max-width: 768px) {

  /* Page title */
  .container-fluid h1 {
    font-size: 1.25rem;
  }

  /* Table behavior */
  .all-sheets-table {
    font-size: 13px;
    white-space: nowrap;
  }

  .all-sheets-table th,
  .all-sheets-table td {
    padding: 8px 10px;
    vertical-align: middle;
  }
}
/* ================================= */
/* PAGINATION – MOBILE FIX */
/* ================================= */

@media (max-width: 576px) {

  .pagination {
    flex-wrap: wrap;
    justify-content: center;
    gap: 4px;
  }

  .pagination .page-link {
    padding: 6px 10px;
    font-size: 12px;
  }
}

    </style>
@endsection
