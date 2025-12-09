@extends('layouts.app')

@section('content')
<div class="container-fluid px-4 py-4 timeline-container">

    <!-- Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 fw-bold mb-1 text-dark">Activity Timeline</h1>
            <p class="text-muted mb-0">Track all project updates, assignments and QA changes</p>
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

                            @if($log->qa_item_id)
                                <div class="context-item">
                                    <i class="fas fa-clipboard-check text-danger me-1"></i>
                                    <a href="{{ route('qa_items.index', $log->sheet_id) }}#item-{{ $log->qa_item_id }}"
                                       class="qa-item-link">
                                        QA Item #{{ $log->qa_item_id }}
                                    </a>
                                </div>
                            @endif

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

    {{ $logs->links() }}

</div>





<!-- Custom CSS -->
<style>
    .timeline-container {
        background-color: #f8fafc;
        min-height: 100vh;
    }

    /* Filter Card */
    .filter-card {
        border: none;
        box-shadow: 0 2px 8px rgba(0,0,0,0.08);
        border-radius: 12px;
    }

    .filter-card .form-select,
    .filter-card .form-control {
        border-radius: 8px;
        font-size: 0.875rem;
    }

    .filter-card .input-group-text {
        border-radius: 8px 0 0 8px;
    }

    .filter-card .form-select.border-start-0 {
        border-radius: 0 8px 8px 0;
    }

    /* Timeline Card */
    .timeline-card {
        border: none;
        box-shadow: 0 4px 12px rgba(0,0,0,0.1);
        border-radius: 12px;
        overflow: hidden;
    }

    /* Timeline Item */
    .timeline-item {
        display: flex;
        
    padding: 16px 20px;

        border-bottom: 1px solid #f1f1f1;
        transition: background-color 0.2s ease;
    }

    .timeline-item:hover {
        background-color: #fafbfc;
    }

    .timeline-item:last-child {
        border-bottom: none;
    }

    /* Timeline Marker */
    .timeline-marker {
        position: relative;
        margin-right: 20px;
        display: flex;
        flex-direction: column;
        align-items: center;
    }

    .marker-icon {
        width: 48px;
        height: 48px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        color: white;
        font-size: 1rem;
        z-index: 2;
        box-shadow: 0 2px 8px rgba(0,0,0,0.15);
    }
/*
    .timeline-line {
        width: 2px;
        flex-grow: 1;
        background: linear-gradient(to bottom, #e9ecef, transparent);
        margin-top: 8px;
    }*/
.timeline-line {
    width: 2px;
    height: 40px; /* fixed height instead of flex-grow */
    background: #e9ecef;
    margin-top: 8px;
}

    .timeline-item:last-child .timeline-line {
        background: #e9ecef;
    }

    /* Timeline Content */
    .timeline-content {
        flex: 1;
    }

    /* Timeline Header */
    .timeline-header {
        margin-bottom: 16px;
    }

    .user-info {
        display: flex;
        align-items: center;
    }

    .user-avatar {
        width: 40px;
        height: 40px;
        border-radius: 50%;
        background: linear-gradient(135deg, #4361ee, #3a56d4);
        color: white;
        display: flex;
        align-items: center;
        justify-content: center;
        font-weight: 600;
        margin-right: 12px;
    }

    .user-details {
        display: flex;
        flex-direction: column;
    }

    .user-name {
        font-size: 1rem;
        margin-bottom: 4px;
    }

    .action-badge {
        font-size: 0.75rem;
        padding: 4px 8px;
    }

    .timeline-time {
        text-align: right;
    }

    .time-date {
        font-weight: 600;
        color: #495057;
    }

    .time-hour {
        font-size: 0.875rem;
    }

    .time-ago {
        font-size: 0.75rem;
    }

    /* Timeline Body */
    .context-items {
        display: flex;
        flex-wrap: wrap;
        gap: 12px;
    }

    .context-item {
        background: #f8f9fa;
        padding: 8px 12px;
        border-radius: 8px;
        font-size: 0.875rem;
        border-left: 3px solid #4361ee;
    }

    .qa-item-link {
        color: #4361ee;
        text-decoration: none;
        font-weight: 600;
    }

    .qa-item-link:hover {
        text-decoration: underline;
    }

    /* Note Card */
    .note-card {
        background: #fff9e6;
        border-left: 4px solid #ffd166;
        padding: 12px 16px;
        border-radius: 8px;
        font-size: 0.9rem;
        margin-bottom: 12px;
    }

    /* Attachment Preview */
    .attachment-preview {
        display: flex;
        align-items: center;
    }

    .attachment-image {
        width: 80px;
        height: 80px;
        object-fit: cover;
        border-radius: 8px;
        border: 2px solid #e9ecef;
        cursor: pointer;
        transition: transform 0.2s ease;
    }

    .attachment-image:hover {
        transform: scale(1.05);
        border-color: #4361ee;
    }

    .attachment-btn {
        border-radius: 6px;
        font-size: 0.8rem;
    }

    /* Changes List */
    .changes-title {
        color: #495057;
        font-size: 0.9rem;
        margin-bottom: 12px;
        display: flex;
        align-items: center;
    }

    .changes-grid {
        display: grid;
        gap: 8px;
    }

    .change-item {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding: 8px 12px;
        background: #f8f9fa;
        border-radius: 6px;
        font-size: 0.85rem;
    }

    .change-field {
        font-weight: 600;
        color: #495057;
        min-width: 120px;
    }

    .change-arrow {
        display: flex;
        align-items: center;
        flex: 1;
        justify-content: space-between;
        max-width: 300px;
    }

    .from-value {
        color: #6c757d;
        text-decoration: line-through;
    }

    .to-value {
        color: #198754;
        font-weight: 600;
    }

    /* Empty State */
    .empty-state {
        padding: 60px 20px;
    }

    .empty-icon {
        opacity: 0.5;
    }

    /* Pagination */
    .timeline-pagination .pagination {
        margin-bottom: 0;
    }

    .timeline-pagination .page-link {
        border-radius: 6px;
        margin: 0 2px;
        border: none;
        color: #6c757d;
    }

    .timeline-pagination .page-item.active .page-link {
        background-color: #4361ee;
        border-color: #4361ee;
    }

    /* Timeline Actions */
    .timeline-actions .btn {
        border-radius: 6px;
        font-size: 0.8rem;
        margin-left: 8px;
    }

    /* Responsive Design */
    @media (max-width: 768px) {
        .timeline-item {
            flex-direction: column;
            padding: 16px;
        }

        .timeline-marker {
            flex-direction: row;
            margin-right: 0;
            margin-bottom: 16px;
            width: 100%;
        }

        .timeline-line {
            width: 100%;
            height: 2px;
            margin-top: 0;
            margin-left: 8px;
        }

        .timeline-header .d-flex {
            flex-direction: column;
        }

        .timeline-time {
            text-align: left;
            margin-top: 8px;
        }

        .context-items {
            flex-direction: column;
        }

        .change-item {
            flex-direction: column;
            align-items: flex-start;
        }

        .change-arrow {
            width: 100%;
            margin-top: 4px;
        }
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