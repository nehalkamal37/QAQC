@extends('layouts.app')

@section('content')
<div class="container-fluid px-4 py-3">
    
    {{-- PAGE HEADER --}}
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="{{ route('sheets.index',$sheet->id) }}">Sheets</a></li>
                    <li class="breadcrumb-item active" aria-current="page">QA Items</li>
                </ol>
            </nav>
            <h1 class="h2 fw-bold mb-1">{{ $sheet->title }}</h1>
            <p class="text-muted mb-0">Manage quality assurance items and track progress</p>
        </div>
        
        <div class="d-flex gap-2">
            @if(auth()->user()->hasRole(['Admin','PM','Senior Reviewer']))
            <div class="dropdown">
                <button class="btn btn-primary d-flex align-items-center gap-2" type="button" data-bs-toggle="dropdown">
                    <i class="bi bi-plus-circle"></i> Add Item
                </button>
                                <a href="{{ route('qa_items.create', $sheet->id) }}" class="btn btn-primary">+ Add QA Item</a>

                <ul class="dropdown-menu dropdown-menu-end">
                    <li><a class="dropdown-item" href="{{ route('qa_items.create', $sheet->id) }}">
                        <i class="bi bi-plus-lg me-2"></i>Create New QA Item
                    </a></li>
                    {{--
                    <li><a class="dropdown-item" href="{{ route('qa_items.importPdfForm', $sheet->id) }}">
                        <i class="bi bi-file-pdf me-2 text-danger"></i>Import from PDF
                    </a></li>
                    <li><a class="dropdown-item" href="{{ route('qa_items.importForm', $sheet->id) }}">
                        <i class="bi bi-upload me-2 text-success"></i>Import File
                    </a></li>
                    --}}
                </ul>
            </div>
            @endif
            
            @if(auth()->user()->hasRole(['PM','Senior Reviewer']))
            <form action="{{ route('qa_items.verifyAll', $sheet->id) }}" method="POST" class="d-inline">
                @csrf
                <button class="btn btn-success d-flex align-items-center gap-2">
                    <i class="bi bi-check-all"></i> Verify All
                </button>
            </form>
            @endif
        </div>
    </div>

    {{-- FILTER CARD --}}
    <div class="card shadow-sm mb-4 border-0">
        <div class="card-body p-3">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h6 class="card-title mb-0"><i class="bi bi-funnel me-2"></i>Filters</h6>
                @if(request()->hasAny(['status','assigned_to','severity']))
                <a href="{{ route('qa_items.index', $sheet->id) }}" class="btn btn-sm btn-outline-secondary">
                    <i class="bi bi-x-circle me-1"></i>Clear All
                </a>
                @endif
            </div>
            
            <form method="GET" action="{{ route('qa_items.index', $sheet->id) }}">
                <div class="row g-3">
                    <div class="col-md-3">
                        <label class="form-label small text-muted mb-1">Status</label>
                        <select name="status" class="form-select form-select-sm">
                            <option value="">All Statuses</option>
                            @foreach (\App\Models\QAItem::STATUSES as $s)
                            <option value="{{ $s }}" {{ request('status') === $s ? 'selected' : '' }}>
                                {{ ucfirst(str_replace('_',' ', $s)) }}
                            </option>
                            @endforeach
                        </select>
                    </div>
                    
                    <div class="col-md-3">
                        <label class="form-label small text-muted mb-1">Assignee</label>
                        <input type="text" name="assigned_to" value="{{ request('assigned_to') }}"
                               class="form-control form-control-sm" placeholder="Search assignee...">
                    </div>
                    
                    <div class="col-md-3">
                        <label class="form-label small text-muted mb-1">Severity</label>
                        <select name="severity" class="form-select form-select-sm">
                            <option value="">All Severities</option>
                            @foreach (['critical','high','medium','low'] as $sev)
                            <option value="{{ $sev }}" {{ request('severity') === $sev ? 'selected' : '' }}>
                                {{ ucfirst($sev) }}
                            </option>
                            @endforeach
                        </select>
                    </div>
                    
                    <div class="col-md-3 d-flex align-items-end">
                        <button type="submit" class="btn btn-primary btn-sm w-100 d-flex align-items-center justify-content-center gap-1">
                            <i class="bi bi-search"></i> Apply Filters
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    {{-- STATS CARDS --}}
    <div class="row mb-4">
        <div class="col-md-3 col-sm-6 mb-3">
            <div class="card border-start border-primary border-3 shadow-sm h-100">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="text-muted mb-1">Total Items</h6>
                            <h3 class="fw-bold mb-0">{{ $items->count() }}</h3>
                        </div>
                        <i class="bi bi-clipboard-data text-primary fs-4"></i>
                    </div>
                </div>
            </div>
        </div>
        
        @php
            $inProgressCount = $items->where('status', 'in_progress')->count();
            $openCount = $items->where('status', 'open')->count();
            $verifiedCount = $items->where('status', 'verified')->count();
            $resolvedCount = $items->where('status', 'resolved')->count();
        @endphp
        
        <div class="col-md-3 col-sm-6 mb-3">
            <div class="card border-start border-warning border-3 shadow-sm h-100">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="text-muted mb-1">In Progress</h6>
                            <h3 class="fw-bold mb-0">{{ $inProgressCount }}</h3>
                        </div>
                        <i class="bi bi-hourglass-split text-warning fs-4"></i>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-md-3 col-sm-6 mb-3">
            <div class="card border-start border-danger border-3 shadow-sm h-100">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="text-muted mb-1">Open Items</h6>
                            <h3 class="fw-bold mb-0">{{ $openCount }}</h3>
                        </div>
                        <i class="bi bi-exclamation-triangle text-danger fs-4"></i>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-md-3 col-sm-6 mb-3">
            <div class="card border-start border-success border-3 shadow-sm h-100">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="text-muted mb-1">Verified</h6>
                            <h3 class="fw-bold mb-0">{{ $verifiedCount }}</h3>
                        </div>
                        <i class="bi bi-check-circle text-success fs-4"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- FLASH MESSAGES --}}
    @if(session('success'))
    <div class="alert alert-success alert-dismissible fade show d-flex align-items-center" role="alert">
        <i class="bi bi-check-circle-fill me-2"></i>
        {{ session('success') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
    @endif
    
    @if(session('error'))
    <div class="alert alert-danger alert-dismissible fade show d-flex align-items-center" role="alert">
        <i class="bi bi-exclamation-triangle-fill me-2"></i>
        {{ session('error') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
    @endif

    {{-- TABLE --}}
    <div class="card shadow-sm border-0">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead class="table-light">
                        <tr>
                            <th class="ps-4">Title</th>
                            <th>Category</th>
                            <th>Status</th>
                            <th>Due Date</th>
                            <th>Assignee</th>
                            <th>Severity</th>
                            <th>Progress</th>
                            <th class="text-center">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($items as $item)
                        @php
                            $badge = match($item->status) {
                                'open'         => 'danger',
                                'in_progress'  => 'warning',
                                'needs_info'   => 'secondary',
                                'resolved'     => 'success',
                                'verified'     => 'primary',
                                'closed'       => 'dark',
                                default        => 'info'
                            };

                            $sevBadge = match($item->severity) {
                                'critical' => 'danger',
                                'high'     => 'warning',
                                'medium'   => 'info',
                                'low'      => 'secondary',
                                default    => 'dark'
                            };
                            
                            $statusIcons = [
                                'open' => 'bi-circle',
                                'in_progress' => 'bi-arrow-clockwise',
                                'needs_info' => 'bi-question-circle',
                                'resolved' => 'bi-check-circle',
                                'verified' => 'bi-shield-check',
                                'closed' => 'bi-lock'
                            ];
                            
                            $statusIcon = $statusIcons[$item->status] ?? 'bi-circle';
                        @endphp
                        
                        <tr id="item-{{ $item->id }}" class="align-middle">
                            <td class="ps-4">
                                <div class="d-flex align-items-center">
                                    <i class="bi bi-card-checklist text-muted me-2"></i>
                                    <div>
                                        <strong class="d-block">{{ $item->title }}</strong>
                                        <small class="text-muted">{{ Str::limit($item->item_description, 50) }}</small>
                                    </div>
                                </div>
                            </td>
                            
                            <td>
                                <span class="badge bg-light text-dark border">
                                    {{ $item->category }}
                                </span>
                            </td>
                            
                            <td>
                                <span class="badge bg-{{ $badge }} bg-opacity-10 text-{{ $badge }} border border-{{ $badge }} border-opacity-25 d-flex align-items-center gap-1">
                                    <i class="bi {{ $statusIcon }}"></i>
                                    {{ ucfirst(str_replace('_',' ', $item->status)) }}
                                </span>
                            </td>
                            
                            <td>
                                @if($item->project_due_date)
                                <div class="d-flex align-items-center gap-1">
                                    <i class="bi bi-calendar-event text-muted"></i>
                                    <span class="{{ \Carbon\Carbon::parse($item->project_due_date)->isPast() ? 'text-danger' : '' }}">
                                        {{ $item->project_due_date }}
                                    </span>
                                </div>
                                @else
                                <span class="text-muted">—</span>
                                @endif
                            </td>
                            
                            <td>
                                @if($item->project_assigned_to)
                                <div class="d-flex align-items-center gap-2">
                                    <div class="avatar-sm bg-primary bg-opacity-10 text-primary rounded-circle d-flex align-items-center justify-content-center">
                                        <i class="bi bi-person"></i>
                                    </div>
                                    {{ $item->project_assigned_to }}
                                </div>
                                @else
                                <span class="text-muted">—</span>
                                @endif
                            </td>
                            
                            <td>
                                <span class="badge bg-{{ $sevBadge }} d-flex align-items-center gap-1">
                                    <i class="bi bi-flag"></i>
                                    {{ ucfirst($item->severity) }}
                                </span>
                            </td>
                            
                            <td style="min-width: 150px;">
                                <div class="d-flex align-items-center gap-2">
                                    <div class="flex-grow-1">
                                        <div class="progress" style="height: 6px;">
                                            <div class="progress-bar bg-success"
                                                 style="width: {{ $item->completionPercentage() }}%"></div>
                                        </div>
                                    </div>
                                    <small class="text-muted">{{ $item->completionPercentage() }}%</small>
                                </div>
                                <div class="d-flex justify-content-between small mt-1">
                                    <span class="{{ $item->applicable ? 'text-success' : 'text-muted' }}" title="Applicable">
                                        <i class="bi bi-{{ $item->applicable ? 'check' : 'x' }}-circle"></i> App
                                    </span>
                                    <span class="{{ $item->incorporated ? 'text-success' : 'text-muted' }}" title="Incorporated">
                                        <i class="bi bi-{{ $item->incorporated ? 'check' : 'x' }}-circle"></i> Inc
                                    </span>
                                    <span class="{{ $item->confirmed ? 'text-success' : 'text-muted' }}" title="Confirmed">
                                        <i class="bi bi-{{ $item->confirmed ? 'check' : 'x' }}-circle"></i> Conf
                                    </span>
                                </div>
                            </td>
                            
                            <td class="text-center" style="min-width: 250px;">
                                <div class="d-flex flex-wrap gap-1 justify-content-center">
                                    {{-- EDIT --}}
                                    @if(auth()->user()->hasRole(['Admin','PM','Senior Reviewer']) ||
                                        (auth()->user()->hasRole(['Engineer','Night Vision']) && $item->project_assigned_to == auth()->user()->name))
                                    <a href="{{ route('qa_items.edit', [$sheet->id, $item->id]) }}"
                                       class="btn btn-sm btn-outline-primary d-flex align-items-center gap-1">
                                        <i class="bi bi-pencil"></i> Edit
                                    </a>
                                    @endif

                                    {{-- DELETE --}}
                                    @if(auth()->user()->hasRole(['Admin','PM']))
                                    <form action="{{ route('qa_items.destroy', [$sheet->id, $item->id]) }}"
                                          method="POST" class="d-inline">
                                        @csrf @method('DELETE')
                                        <button onclick="return confirm('Delete this item?')"
                                                class="btn btn-sm btn-outline-danger d-flex align-items-center gap-1">
                                            <i class="bi bi-trash"></i> Delete
                                        </button>
                                    </form>
                                    @endif

                                    {{-- Start --}}
                                    @if($item->status != 'in_progress' && $item->status != 'resolved' && $item->status != 'verified' && $item->status != 'closed')
                                    <form action="{{ route('qa_items.updateStatus', $item->id) }}"
                                          method="POST" class="d-inline">
                                        @csrf
                                        <input type="hidden" name="status" value="in_progress">
                                        <button class="btn btn-sm btn-outline-warning d-flex align-items-center gap-1">
                                            <i class="bi bi-play"></i> Start
                                        </button>
                                    </form>
                                    @endif

                                    {{-- Resolve --}}
                                    @if($item->status == 'in_progress' || $item->status == 'open' || $item->status == 'needs_info')
                                    <form action="{{ route('qa_items.updateStatus', $item->id) }}"
                                          method="POST" class="d-inline">
                                        @csrf
                                        <input type="hidden" name="status" value="resolved">
                                        <button class="btn btn-sm btn-outline-success d-flex align-items-center gap-1">
                                            <i class="bi bi-check"></i> Resolve
                                        </button>
                                    </form>
                                    @endif

                                    {{-- Verify (PM / SR only) --}}
                                    @if(auth()->user()->hasRole(['Admin','PM','Senior Reviewer']) && 
                                        ($item->status == 'resolved' || $item->status == 'in_progress'))
                                    <form action="{{ route('qa_items.updateStatus', $item->id) }}"
                                          method="POST" class="d-inline">
                                        @csrf
                                        <input type="hidden" name="status" value="verified">
                                        <button class="btn btn-sm btn-outline-info d-flex align-items-center gap-1">
                                            <i class="bi bi-shield-check"></i> Verify
                                        </button>
                                    </form>
                                    @endif
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="8" class="text-center py-5">
                                <div class="py-4">
                                    <i class="bi bi-clipboard-x display-5 text-muted"></i>
                                    <h5 class="mt-3 text-muted">No QA items found</h5>
                                    <p class="text-muted">Get started by creating your first QA item</p>
                                    @if(auth()->user()->hasRole(['Admin','PM','Senior Reviewer']))
                                    <a href="{{ route('qa_items.create', $sheet->id) }}" class="btn btn-primary mt-2">
                                        <i class="bi bi-plus-circle me-1"></i> Create First Item
                                    </a>
                                    @endif
                                </div>
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    
    {{-- ITEM COUNT (replacing pagination since we have a collection) --}}
    <div class="text-muted small mt-3">
        Showing {{ $items->count() }} item{{ $items->count() !== 1 ? 's' : '' }}
    </div>

</div>

<style>
.avatar-sm {
    width: 24px;
    height: 24px;
    font-size: 12px;
}
.progress {
    border-radius: 3px;
    background-color: #f0f0f0;
}
.table-hover tbody tr:hover {
    background-color: rgba(0, 123, 255, 0.04) !important;
}
.badge {
    padding: 0.35em 0.65em;
    font-weight: 500;
}
.card {
    border-radius: 10px;
}
.breadcrumb {
    background: transparent;
    padding: 0;
    margin-bottom: 0.5rem;
}
</style>

<script>
document.addEventListener("DOMContentLoaded", function () {
    // Highlight row from hash
    const hash = window.location.hash;
    if (hash && hash.startsWith("#item-")) {
        const row = document.querySelector(hash);
        if (row) {
            row.scrollIntoView({ behavior: "smooth", block: "center" });
            row.style.transition = "background-color 1s ease";
            row.style.backgroundColor = "#fff3cd";
            setTimeout(() => row.style.backgroundColor = "", 2000);
        }
    }
    
    // Add Bootstrap tooltips
    const tooltips = document.querySelectorAll('[title]');
    tooltips.forEach(el => {
        new bootstrap.Tooltip(el);
    });
});
</script>
@endsection