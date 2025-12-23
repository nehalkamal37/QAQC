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
 

   {{-- Flash Messages --}}
@if(session('success'))
    <div class="alert alert-success alert-modern alert-dismissible fade show" role="alert">
        <div class="d-flex align-items-start gap-2">
            <div class="alert-icon">
                <i class="fas fa-check-circle"></i>
            </div>
            <div class="flex-grow-1">
                <div class="alert-title">Success</div>
                <div class="alert-message">{{ session('success') }}</div>
            </div>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    </div>
@endif

@if(session('error'))
    <div class="alert alert-danger alert-modern alert-dismissible fade show" role="alert">
        <div class="d-flex align-items-start gap-2">
            <div class="alert-icon">
                <i class="fas fa-exclamation-triangle"></i>
            </div>
            <div class="flex-grow-1">
                <div class="alert-title">Action blocked</div>
                <div class="alert-message">{{ session('error') }}</div>
            </div>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    </div>
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


/* =============================== */
/* Modern Alerts */
/* =============================== */
.alert-modern {
    border: 0;
    border-radius: 12px;
    padding: 14px 16px;
    box-shadow: 0 10px 25px rgba(0,0,0,0.06);
    position: relative;
}

.alert-modern .alert-icon {
    width: 34px;
    height: 34px;
    border-radius: 10px;
    display: flex;
    align-items: center;
    justify-content: center;
    flex-shrink: 0;
    font-size: 16px;
}

.alert-modern .alert-title {
    font-weight: 700;
    font-size: 0.95rem;
    margin-bottom: 2px;
}

.alert-modern .alert-message {
    font-size: 0.88rem;
    opacity: 0.95;
    line-height: 1.4;
}

/* ✅ Success styling */
.alert-success.alert-modern {
    background: #ecfdf5;
    color: #065f46;
}

.alert-success.alert-modern .alert-icon {
    background: #d1fae5;
    color: #059669;
}

/* ✅ Error styling */
.alert-danger.alert-modern {
    background: #fef2f2;
    color: #7f1d1d;
}

.alert-danger.alert-modern .alert-icon {
    background: #fee2e2;
    color: #dc2626;
}

/* Improve close button */
.alert-modern .btn-close {
    opacity: 0.7;
    transform: scale(0.9);
}

.alert-modern .btn-close:hover {
    opacity: 1;
}

    </style>

    <script>
document.addEventListener('DOMContentLoaded', () => {
    const alerts = document.querySelectorAll('.alert-modern');
    alerts.forEach(alert => {
        setTimeout(() => {
            try {
                const bsAlert = bootstrap.Alert.getOrCreateInstance(alert);
                bsAlert.close();
            } catch (e) {}
        }, 5000);
    });
});
</script>

@endsection
