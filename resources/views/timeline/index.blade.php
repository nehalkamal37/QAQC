@extends('layouts.app')

@section('content')

<div class="container-fluid px-4 py-4 timeline-container">

    <!-- Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 fw-bold mb-1 text-dark">Activity Timeline</h1>
            <p class="text-muted mb-0">Track all projects updates, assignments and QA changes</p>
        </div>
        <span class="badge bg-light text-dark">
            <i class="fas fa-history me-1"></i> {{ $logs->total() }} activities
        </span>
    </div>

    <!-- FILTER CARD -->
    <div class="card filter-card mb-4">
        <div class="card-body">
            <form method="GET" action="{{ route('timeline.index') }}">

                <div class="row g-3 align-items-end">

                    <!-- Item ID -->
                    <div class="col-md-2">
                        <label class="form-label small text-muted">Item ID</label>
                        <input type="number" name="item_id"
                               class="form-control form-control-sm"
                               value="{{ request('item_id') }}"
                               placeholder="e.g. 521">
                    </div>

                    <!-- User -->
                    <div class="col-md-2">
                        <label class="form-label small text-muted">User</label>
                        <select name="user" class="form-select form-select-sm">
                            <option value="">All Users</option>
                            @foreach(\App\Models\User::orderBy('name')->get() as $u)
                                <option value="{{ $u->id }}" @selected(request('user') == $u->id)>
                                    {{ $u->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <!-- Action Type -->
                    <div class="col-md-2">
                        <label class="form-label small text-muted">Action Type</label>
                        <select name="action" class="form-select form-select-sm">
                            <option value="">All</option>
                            @foreach([
                                'status_change',
                                'review_added',
                                'attachment_uploaded',
                                'item_imported',
                                'qa_assignment',
                                'due_date_changed',
                                'applicable_changed',
                                'incorporated_changed',
                                'confirmed_changed'
                              ] as $act)
                                <option value="{{ $act }}" @selected(request('action') == $act)>
                                    {{ ucfirst(str_replace('_',' ', $act)) }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <!-- Assigned To -->
                    <div class="col-md-2">
                        <label class="form-label small text-muted">Assigned To</label>
                        <select name="assigned_to" class="form-select form-select-sm">
                            <option value="">All</option>
                            @foreach(\App\Models\User::orderBy('name')->get() as $u)
                                <option value="{{ $u->id }}" @selected(request('assigned_to') == $u->id)>
                                    {{ $u->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <!-- Applicable -->
                    <div class="col-md-1">
                        <label class="form-label small text-muted">Applicable</label>
                        <select name="applicable" class="form-select form-select-sm">
                            <option value="">All</option>
                            <option value="1" @selected(request('applicable')==='1')>Yes</option>
                            <option value="0" @selected(request('applicable')==='0')>No</option>
                        </select>
                    </div>

                    <!-- Incorporated -->
                    <div class="col-md-1">
                        <label class="form-label small text-muted">Inc.</label>
                        <select name="incorporated" class="form-select form-select-sm">
                            <option value="">All</option>
                            <option value="1" @selected(request('incorporated')==='1')>Yes</option>
                            <option value="0" @selected(request('incorporated')==='0')>No</option>
                        </select>
                    </div>

                    <!-- Confirmed -->
                    <div class="col-md-1">
                        <label class="form-label small text-muted">Conf.</label>
                        <select name="confirmed" class="form-select form-select-sm">
                            <option value="">All</option>
                            <option value="1" @selected(request('confirmed')==='1')>Yes</option>
                            <option value="0" @selected(request('confirmed')==='0')>No</option>
                        </select>
                    </div>

                    <!-- DUE DATE -->
                    <div class="col-md-2">
                        <label class="form-label small text-muted">Due From</label>
                        <input type="date" name="due_from"
                               class="form-control form-control-sm"
                               value="{{ request('due_from') }}">
                    </div>

                    <div class="col-md-2">
                        <label class="form-label small text-muted">Due To</label>
                        <input type="date" name="due_to"
                               class="form-control form-control-sm"
                               value="{{ request('due_to') }}">
                    </div>

                    <!-- Global Search -->
                    <div class="col-md-3">
                        <label class="form-label small text-muted">Search</label>
                        <input type="text" name="search"
                               class="form-control form-control-sm"
                               value="{{ request('search') }}"
                               placeholder="Search notes, changes, description...">
                    </div>

                    <!-- Apply / Reset -->
                    <div class="col-md-1">
                        <button class="btn btn-primary btn-sm w-100">Filter</button>
                    </div>

                    <div class="col-md-2">
                        <a href="{{ route('timeline.index') }}"
                           class="btn btn-outline-secondary btn-sm w-100">Clear</a>
                    </div>

                </div> <!-- row -->

            </form>
        </div>
    </div>

    <!-- TIMELINE ITEMS -->
    <div class="card timeline-card">
        <div class="card-body p-0">

            @foreach($logs as $log)
                @php
                    $changes = $log->extractChanges(); 
                    // new helper from logger
                @endphp

                <div class="timeline-item">

                    <!-- MARKER -->
                    <div class="timeline-marker">
                        <div class="marker-icon bg-{{ $log->color() }}">
                            <i class="{{ $log->icon() }}"></i>
                        </div>
                        <div class="timeline-line"></div>
                    </div>

                    <!-- CONTENT -->
                    <div class="timeline-content">

                        <!-- HEADER -->
                        <div class="timeline-header d-flex justify-content-between">
                            <div class="user-info">
                                <div class="user-avatar">{{ substr($log->user->name ?? 'S',0,1) }}</div>
                                <div>
                                    <strong>{{ $log->user->name ?? 'System' }}</strong>
                                    <span class="badge bg-{{ $log->color() }} ms-1">
                                        {{ ucfirst(str_replace('_',' ',$log->action_type)) }}
                                    </span>
                                </div>
                            </div>

                            <div class="timeline-time text-end">
                                <div>{{ $log->created_at->format('M j, Y') }}</div>
                                <small class="text-muted">{{ $log->created_at->diffForHumans() }}</small>
                            </div>
                        </div>

                        <!-- CONTEXT -->
                        <div class="context-items mt-2">

                            @if($log->project)
                                <div class="context-item">
                                    <i class="fas fa-folder text-primary me-1"></i>
                                    Project: {{ $log->project->name }}
                                </div>
                            @endif

                            @if($log->sheet)
                                <div class="context-item">
                                    <i class="fas fa-file-alt text-warning me-1"></i>
                                    Sheet: {{ $log->sheet->number }}
                                </div>
                            @endif

                                <div class="context-item">
                                    <i class="fas fa-clipboard-check text-danger me-1"></i>
                                 @if($log->qa_item_id && $log->sheet_id)
    <a href="{{ route('qa_items.index', ['sheet' => $log->sheet_id]) }}#item-{{ $log->qa_item_id }}">
        QA Item #{{ $log->qa_item_id }}
    </a>
@endif


                                </div>

                        </div>

                        <!-- NOTE -->
                        @if($log->note)
                            <div class="note-card mt-2">
                                <i class="fas fa-sticky-note me-1 text-muted"></i>
                                {{ $log->note }}
                            </div>
                        @endif

                        <!-- CHANGES -->
                        @if(count($changes))
                            <div class="changes-list mt-3">

                                <h6 class="changes-title">
                                    <i class="fas fa-exchange-alt me-1"></i>
                                    Changes Made
                                </h6>

                                <div class="changes-grid">
                                    @foreach($changes as $c)
                                        <div class="change-item">
                                            <span class="change-field">{{ $c['field'] }}</span>
                                            <span class="from-value">{{ $c['from'] }}</span>
                                            <i class="fas fa-arrow-right text-muted"></i>
                                            <span class="to-value text-success">{{ $c['to'] }}</span>
                                        </div>
                                    @endforeach
                                </div>

                            </div>
                        @endif

                    </div>

                </div>    

            @endforeach

        </div>

    </div>

<div class="timeline-pagination mt-4">
    {{ $logs->links('pagination::bootstrap-5') }}
</div>

</div>

<link rel="stylesheet" href="{{ asset('dash/css/timeline.css') }}">

<style>
.timeline-pagination {
    display: flex;
    justify-content: center;
}

.timeline-pagination ul.pagination {
    display: flex;
    flex-direction: row !important;
    align-items: center;
    gap: 6px;
}

.timeline-pagination .page-item {
    display: inline-block;
}

.timeline-pagination .page-link {
    padding: 4px 8px;
    font-size: 13px;
    line-height: 1.2;
}


    </style>


<!-- JavaScript for Expand/Collapse -->
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const expandAll = document.getElementById('expandAll');
        const collapseAll = document.getElementById('collapseAll');
        
        if (expandAll) {
            expandAll.addEventListener('click', function() {
                // Implementation for expand all functionality
                console.log('Expand all clicked');
            });
        }
        
        if (collapseAll) {
            collapseAll.addEventListener('click', function() {
                // Implementation for collapse all functionality
                console.log('Collapse all clicked');
            });
        }
    });
</script>

<!-- Font Awesome for Icons -->
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
@endsection