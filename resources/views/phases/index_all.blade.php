@extends('layouts.app')

@section('content')
<div class="container-fluid px-4 mt-4">
    <h1 class="h3 mb-4">All Phases</h1>
<div class="table-responsive all-phases-table-wrapper">
    <table class="table table-striped align-middle all-phases-table">
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
</div>
    {{ $phases->links() }}
</div>

<style>
    /* ================================= */
/* ALL PHASES – RESPONSIVE TABLE */
/* ================================= */

@media (max-width: 768px) {

  /* Page title */
  .container-fluid h1 {
    font-size: 1.25rem;
  }

  /* Table behavior */
  .all-phases-table {
    font-size: 13px;
    white-space: nowrap;
  }

  .all-phases-table th,
  .all-phases-table td {
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
