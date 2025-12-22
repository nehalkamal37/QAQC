@extends('layouts.app')

@section('content')
<div class="container-fluid px-4 mt-4">

    {{-- Header --}}
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3">Phases for Project: {{ $project->name }}</h1>

        {{-- Only Admin & PM can add phases --}}
        @if(auth()->user()->hasRole(['Admin', 'PM']))
            <a href="{{ route('phases.create', $project->id) }}" class="btn btn-primary">+ Add Phase</a>
        @endif
    </div>

    {{-- Flash Message --}}
    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    <div class="card shadow-sm">
        <div class="card-body">
<div class="table-responsive">
    <table class="table table-striped align-middle">

                <thead class="table-dark">
                    <tr>
                        <th>ID</th>
                        <th>Type</th>
                        <th>Due Date</th>
                        <th>Status</th>
                        <th>Progress</th>
                        <th>Actions</th>
                    </tr>
                </thead>

                <tbody>
                @forelse ($project->phases as $phase)
                    <tr>
                        <td>{{ $phase->id }}</td>
                        <td>{{ $phase->type }}</td>
                        <td>{{ $phase->due_date ?? '-' }}</td>

                        {{-- STATUS COLUMN --}}
                        <td>
                            {{-- Engineer + Night Vision -> Read Only --}}
                            
                            @if(auth()->user()->hasRole(['Engineer', 'Night Vision']))
                                <span class="badge bg-secondary">
                                    {{ ucwords(str_replace('_',' ', $phase->status)) }}
                                </span>

                            {{-- Admin, PM, Senior Reviewer, Reviewer -> Editable --}}
                            @else
                                <form action="{{ route('phases.status.update', $phase->id) }}" method="POST">
                                    @csrf
                                    @method('PATCH')

                                    <select name="status" class="form-select form-select-sm" onchange="this.form.submit()">
                                        @foreach(\App\Models\Phase::statuses() as $status)
                                            <option value="{{ $status }}" 
                                                {{ $phase->status === $status ? 'selected' : '' }}>
                                                {{ ucwords(str_replace('_', ' ', $status)) }}
                                            </option>
                                        @endforeach
                                    </select>
                                </form>
                            @endif
                        </td>

                        {{-- PROGRESS --}}
                        <td>
                            <span class="badge bg-primary">{{ $phase->completionPercentage() }}%</span>
                        </td>

                        {{-- ACTIONS COLUMN --}}
                        <td>
{{-- In your existing phases/index.blade.php --}}
{{-- Add this in the actions column --}}

{{-- Kanban Board Button --}}
<a href="{{ route('phases.kanban', $phase->id) }}" class="btn btn-sm btn-success">
    <i class="fas fa-columns me-1"></i> Kanban
</a>


                            {{-- Everyone can access Sheets --}}
                            <a href="{{ route('sheets.index', $phase->id) }}" class="btn btn-sm btn-info">
                                Sheets
                            </a>

                            {{-- Only Admin + PM can Edit/Delete --}}
                            @if(auth()->user()->hasRole(['Admin', 'PM']))
                                <a href="{{ route('phases.edit', $phase->id) }}" class="btn btn-sm btn-warning">
                                    Edit
                                </a>

                                <form action="{{ route('phases.destroy', $phase->id) }}" 
                                      method="POST" class="d-inline">
                                    @csrf
                                    @method('DELETE')
                                    <button onclick="return confirm('Are you sure?')" 
                                            class="btn btn-sm btn-danger">
                                        Delete
                                    </button>
                                </form>
                            @endif

                        </td>

                    </tr>

                @empty
                    <tr>
                        <td colspan="6" class="text-center text-muted py-3">No phases found.</td>
                    </tr>
                @endforelse
                </tbody>

            </table>
            </div>

        </div>
    </div>

</div>

<style>
    /* ================================= */
/* PHASES TABLE – MOBILE RESPONSIVE */
/* ================================= */

@media (max-width: 768px) {

  /* Header stack */
  .container-fluid > .d-flex:first-child {
    flex-direction: column;
    align-items: flex-start !important;
    gap: 10px;
  }

  .container-fluid h1 {
    font-size: 1.25rem;
  }

  /* Table tweaks */
  table {
    font-size: 13px;
    white-space: nowrap;
  }

  th, td {
    padding: 8px 10px;
    vertical-align: middle;
  }

  /* Make actions buttons wrap nicely */
  td:last-child {
    min-width: 180px;
  }

  td:last-child .btn {
    margin-bottom: 4px;
  }
}
/* ================================= */
/* PHASES TABLE – EXTRA SMALL PHONES */
/* ================================= */

@media (max-width: 576px) {


  /* Status select full width */
  select.form-select-sm {
    width: 100%;
    font-size: 12px;
  }

  /* Actions stack vertically */
  td:last-child {
    white-space: normal;
  }

  td:last-child .btn {
    display: block;
    width: 100%;
  }
}

    </style>
@endsection
