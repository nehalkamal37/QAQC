@extends('layouts.app')

@section('content')
<div class="container-fluid px-4 py-4 timeline-container">

    <!-- Page Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 fw-bold mb-1 text-dark">Activity Timeline</h1>
            <p class="text-muted mb-0">Track all project activities and changes</p>
        </div>
        <div class="timeline-stats">
            <span class="badge bg-light text-dark">
                <i class="fas fa-history me-1"></i>
                {{ $logs->total() }} total activities
            </span>
        </div>
    </div>

    <!-- Enhanced Filters Card -->
    <div class="card filter-card mb-4">
        <div class="card-body">
            <form method="GET" action="{{ route('timeline.index') }}">
                <div class="row g-3 align-items-end">
                    
                <!-- Item ID Filter -->
<div class="col-md-2">
    <label class="form-label fw-semibold small text-muted mb-1">Item ID</label>
    <div class="input-group input-group-sm">
        <span class="input-group-text bg-light border-end-0">
            <i class="fas fa-hashtag text-muted"></i>
        </span>
        <input type="number" name="item_id" 
               class="form-control border-start-0"
               value="{{ request('item_id') }}"
               placeholder="e.g. 125">
    </div>
</div>

                    <!-- User Filter -->
                    <div class="col-md-2">
                        <label class="form-label fw-semibold small text-muted mb-1">User</label>
                        <div class="input-group input-group-sm">
                            <span class="input-group-text bg-light border-end-0">
                                <i class="fas fa-user text-muted"></i>
                            </span>
                            <select name="user" class="form-select border-start-0">
                                <option value="">All Users</option>
                                @foreach(\App\Models\User::orderBy('name')->get() as $u)
                                    <option value="{{ $u->id }}" @selected(request('user') == $u->id)>
                                        {{ $u->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    <!-- Action Type Filter -->
                    <div class="col-md-2">
                        <label class="form-label fw-semibold small text-muted mb-1">Action Type</label>
                        <div class="input-group input-group-sm">
                            <span class="input-group-text bg-light border-end-0">
                                <i class="fas fa-play-circle text-muted"></i>
                            </span>
                            <select name="action" class="form-select border-start-0">
                                <option value="">All Actions</option>
                                @foreach(['status_change','review_added','attachment_uploaded','item_imported'] as $act)
                                    <option value="{{ $act }}" @selected(request('action') == $act)>
                                        {{ ucfirst(str_replace('_',' ',$act)) }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    <!-- Project Filter -->
                    <div class="col-md-2">
                        <label class="form-label fw-semibold small text-muted mb-1">Project</label>
                        <div class="input-group input-group-sm">
                            <span class="input-group-text bg-light border-end-0">
                                <i class="fas fa-folder text-muted"></i>
                            </span>
                            <select name="project" class="form-select border-start-0">
                                <option value="">All Projects</option>
                                @foreach(\App\Models\Project::orderBy('name')->get() as $p)
                                    <option value="{{ $p->id }}" @selected(request('project') == $p->id)>
                                        {{ $p->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    <!-- Severity Filter -->
                    <div class="col-md-2">
                        <label class="form-label fw-semibold small text-muted mb-1">Severity</label>
                        <div class="input-group input-group-sm">
                            <span class="input-group-text bg-light border-end-0">
                                <i class="fas fa-exclamation-triangle text-muted"></i>
                            </span>
                            <select name="severity" class="form-select border-start-0">
                                <option value="">All Severities</option>
                                @foreach(['critical','high','medium','low'] as $sev)
                                    <option value="{{ $sev }}" @selected(request('severity') == $sev)>
                                        {{ ucfirst($sev) }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    <!-- Search -->
                    <div class="col-md-3">
                        <label class="form-label fw-semibold small text-muted mb-1">Search</label>
                        <div class="input-group input-group-sm">
                            <span class="input-group-text bg-light border-end-0">
                                <i class="fas fa-search text-muted"></i>
                            </span>
                            <input type="text" name="search" class="form-control border-start-0"
                                   value="{{ request('search') }}" placeholder="Search notes or changes...">
                        </div>
                    </div>

                    <!-- Action Buttons -->
                    <div class="col-md-1">
                        <button type="submit" class="btn btn-primary btn-sm w-100">
                            <i class="fas fa-filter me-1"></i>Filter
                        </button>
                    </div>

                    <div class="col-md-2">
                        <a href="{{ route('timeline.index') }}" class="btn btn-outline-secondary btn-sm w-100">
                            <i class="fas fa-times me-1"></i>Clear
                        </a>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- Timeline Activity Card -->
    <div class="card timeline-card">
        <div class="card-header bg-white border-bottom-0 py-3">
            <div class="d-flex justify-content-between align-items-center">
                <h5 class="fw-bold mb-0">Recent Activities</h5>
                <div class="timeline-actions">
                    <button class="btn btn-sm btn-outline-secondary" id="expandAll">
                        <i class="fas fa-expand me-1"></i>Expand All
                    </button>
                    <button class="btn btn-sm btn-outline-secondary" id="collapseAll">
                        <i class="fas fa-compress me-1"></i>Collapse All
                    </button>
                </div>
            </div>
        </div>

        <div class="card-body p-0">
          @forelse ($logs as $log)
    @php
        $old = is_array($log->old_value) ? $log->old_value : ($log->old_value ?? []);
        $new = is_array($log->new_value) ? $log->new_value : ($log->new_value ?? []);

        $changes = [];
        if ($new) {
            foreach ($new as $field => $value) {
                if (in_array($field, ['path', 'mime', 'filename'])) continue;
                $oldVal = $old[$field] ?? null;

                if ($oldVal !== $value) {
                    $changes[] = [
                        'field' => $field,
                        'from'  => $oldVal,
                        'to'    => $value,
                    ];
                }
            }
        }

        // Icons + Colors for action types
        $actionConfig = [
            'status_change'       => ['icon' => 'fas fa-sync',            'color' => 'primary'],
            'review_added'        => ['icon' => 'fas fa-clipboard-check', 'color' => 'success'],
            'attachment_uploaded' => ['icon' => 'fas fa-paperclip',       'color' => 'warning'],
            'item_imported'       => ['icon' => 'fas fa-file-import',     'color' => 'info'],
        ];

        $actionIcon  = $actionConfig[$log->action_type]['icon']  ?? 'fas fa-circle';
        $actionColor = $actionConfig[$log->action_type]['color'] ?? 'secondary';
    @endphp

    <div class="timeline-item">
        <div class="timeline-marker">
            <div class="marker-icon bg-{{ $actionColor }}">
                <i class="{{ $actionIcon }}"></i>
            </div>
            <div class="timeline-line"></div>
        </div>

        <div class="timeline-content">

            <!-- HEADER -->
            <div class="timeline-header">
                <div class="d-flex justify-content-between align-items-start">

                    <div class="user-info">
                        <div class="user-avatar">
                            {{ substr($log->user->name ?? 'S', 0, 1) }}
                        </div>
                        <div class="user-details">
                            <strong class="user-name">{{ $log->user->name ?? 'System' }}</strong>
                            <span class="action-badge badge bg-{{ $actionColor }}">
                                {{ ucfirst(str_replace('_',' ', $log->action_type)) }}
                            </span>
                        </div>
                    </div>

                    <div class="timeline-time text-end">
                        <div class="time-date">{{ $log->created_at->format('M j, Y') }}</div>
                        <div class="time-hour text-muted">{{ $log->created_at->format('h:i A') }}</div>
                        <div class="time-ago text-muted small">{{ $log->created_at->diffForHumans() }}</div>

                        <!-- Delete Button -->
                        <button class="btn btn-sm btn-outline-danger mt-2"
                                data-bs-toggle="modal"
                                data-bs-target="#deleteLogModal{{ $log->id }}">
                            <i class="fas fa-trash"></i>
                        </button>
                    </div>
                </div>
            </div>

            <!-- DELETE MODAL -->
      <!--
            <div class="modal fade" id="deleteLogModal{{ $log->id }}" tabindex="-1">
                <div class="modal-dialog modal-dialog-centered">
                    <div class="modal-content">

                        <div class="modal-header">
                            <h5 class="modal-title text-danger">
                                <i class="fas fa-exclamation-triangle me-2"></i> Delete Activity
                            </h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                        </div>

                        <div class="modal-body">
                            Are you sure you want to delete this activity?
                            <br>
                            <small class="text-muted">This action cannot be undone.</small>
                        </div>

                        <div class="modal-footer">
                            <button class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                            <form action="{{ route('timeline.destroy', $log->id) }}" method="POST">
                                @csrf
                                @method('DELETE')
                                <button class="btn btn-danger">
                                    <i class="fas fa-trash"></i> Delete
                                </button>
                            </form>
                        </div>

                    </div>
                </div>
            </div>
    -->

            <!-- BODY -->
            <div class="timeline-body">

                <!-- Context Info -->
                <div class="context-section mb-3">
                    <div class="context-items">

                        @if($log->project)
                            <div class="context-item">
                                <i class="fas fa-folder text-primary me-2"></i>
                                <strong>Project:</strong> {{ $log->project->name }}
                            </div>
                        @endif

                        @if($log->phase)
                            <div class="context-item">
                                <i class="fas fa-layer-group text-success me-2"></i>
                                <strong>Phase:</strong> {{ $log->phase->type }}
                            </div>
                        @endif

                        @if($log->sheet)
                            <div class="context-item">
                                <i class="fas fa-file-alt text-warning me-2"></i>
                                <strong>Sheet:</strong>
                                {{ $log->sheet->number }} — {{ $log->sheet->title }}
                            </div>
                        @endif

                        @if($log->qa_item_id && $log->sheet_id)
                            <div class="context-item">
                                <i class="fas fa-clipboard-check text-danger me-2"></i>
                                <strong>QA Item:</strong>
                                <a href="{{ route('qa_items.index', ['sheetId' => $log->sheet_id]) }}#item-{{ $log->qa_item_id }}"
                                   class="qa-item-link">
                                    #{{ $log->qa_item_id }}
                                </a>
                            </div>
                        @endif

                    </div>
                </div>


                <!-- Note -->
                @if($log->note)
                    <div class="note-card">
                        <i class="fas fa-sticky-note text-muted me-2"></i>
                        <strong>Note:</strong> {{ $log->note }}
                    </div>
                @endif


                <!-- Attachments -->
                @if($log->action_type === 'attachment_uploaded' && isset($log->new_value['path']))
                    @php
                        $mime = $log->new_value['mime'] ?? '';
                        $path = asset('storage/'.$log->new_value['path']);
                    @endphp

                    <div class="attachment-preview mt-2">

                        @if(str_contains($mime, 'image'))
                            <img src="{{ $path }}" class="attachment-image"
                                 data-bs-toggle="modal"
                                 data-bs-target="#imgModal{{ $log->id }}">

                            <!-- Image Modal -->
                            <div class="modal fade" id="imgModal{{ $log->id }}" tabindex="-1">
                                <div class="modal-dialog modal-dialog-centered modal-lg">
                                    <div class="modal-content">
                                        <div class="modal-body p-0">
                                            <img src="{{ $path }}" class="w-100 rounded">
                                        </div>
                                    </div>
                                </div>
                            </div>

                        @elseif(str_contains($mime, 'pdf'))

                            <button class="btn btn-outline-primary btn-sm attachment-btn"
                                    data-bs-toggle="modal"
                                    data-bs-target="#pdfModal{{ $log->id }}">
                                <i class="fas fa-file-pdf me-1"></i>View PDF
                            </button>

                            <!-- PDF Modal -->
                            <div class="modal fade" id="pdfModal{{ $log->id }}" tabindex="-1">
                                <div class="modal-dialog modal-xl">
                                    <div class="modal-content">
                                        <div class="modal-header">
                                            <h5 class="modal-title">{{ $log->note }}</h5>
                                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                        </div>
                                        <div class="modal-body p-0">
                                            <iframe src="{{ $path }}" style="width:100%; height:85vh; border:0;"></iframe>
                                        </div>
                                    </div>
                                </div>
                            </div>

                        @else
                            <a href="{{ $path }}" target="_blank"
                               class="btn btn-sm btn-outline-secondary attachment-btn">
                                <i class="fas fa-download me-1"></i>Download File
                            </a>
                        @endif

                    </div>
                @endif


                <!-- Field Changes -->
                @if(count($changes))
                    <div class="changes-list mt-3">
                        <h6 class="changes-title">
                            <i class="fas fa-exchange-alt me-2"></i>Changes Made
                        </h6>

                        <div class="changes-grid">
                            @foreach($changes as $ch)
                                <div class="change-item">
                                    <span class="change-field">{{ ucfirst(str_replace('_', ' ', $ch['field'])) }}</span>

                                    <div class="change-arrow">
                                        <span class="from-value">{{ $ch['from'] ?? '—' }}</span>
                                        <i class="fas fa-arrow-right mx-2 text-muted"></i>
                                        <span class="to-value">{{ $ch['to'] ?? '—' }}</span>
                                    </div>
                                </div>
                            @endforeach
                        </div>

                    </div>
                @endif

            </div>

        </div>
    </div>

@empty
    <div class="empty-state text-center py-5">
        <div class="empty-icon mb-3">
            <i class="fas fa-history fa-3x text-muted"></i>
        </div>
        <h5 class="text-muted">No activity recorded yet</h5>
        <p class="text-muted">Activities will appear here as they happen</p>
    </div>
@endforelse
</div>

<!-- Pagination -->
@if($logs->hasPages())
    <div class="card-footer bg-white border-top-0 py-3">
        <div class="d-flex justify-content-between align-items-center">
            <div class="text-muted small">
                Showing {{ $logs->firstItem() }} to {{ $logs->lastItem() }} of {{ $logs->total() }} results
            </div>
            <div class="timeline-pagination">
                {{ $logs->links() }}
            </div>
        </div>
    </div>
@endif
</div>
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