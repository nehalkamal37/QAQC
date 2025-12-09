{{-- resources/views/phases/kanban.blade.php --}}
@extends('layouts.app')

@section('content')
<div class="container-fluid px-4 mt-4">

    {{-- Header --}}
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 mb-1">Kanban Board</h1>
            <p class="text-muted mb-0">
                {{ $phase->project->name }} - {{ $phase->type }} 
                <span class="badge bg-{{ $phase->status === 'in_review' ? 'warning' : 'success' }}">
                    {{ ucwords(str_replace('_', ' ', $phase->status)) }}
                </span>
            </p>
        </div>

        <div class="d-flex gap-2">
            <a href="{{ route('phases.index', $phase->project_id) }}" class="btn btn-outline-secondary">
                <i class="fas fa-arrow-left me-1"></i> Back to Phases
            </a>
            <a href="{{ route('sheets.index', $phase->id) }}" class="btn btn-info">
                <i class="fas fa-file-alt me-1"></i> View Sheets
            </a>
        </div>
    </div>

    {{-- View Controls --}}
    <div class="card shadow-sm mb-3">
        <div class="card-body py-2">
            <div class="row align-items-center">
                <div class="col-md-6">
                    <div class="d-flex align-items-center gap-3">
                        <span class="text-muted small">Items per column:</span>
                        <select id="itemsPerColumn" class="form-select form-select-sm" style="width: auto;" onchange="updateItemsPerColumn()">
                            <option value="5">5</option>
                            <option value="10" selected>10</option>
                            <option value="20">20</option>
                            <option value="50">50</option>
                            <option value="0">All</option>
                        </select>
                        <span class="text-muted small">({{ $debugInfo['total_qa_items'] }} total items)</span>
                    </div>
                </div>
                <div class="col-md-6 text-end">
                    <div class="form-check form-switch">
                        <input class="form-check-input" type="checkbox" id="compactView" onchange="toggleCompactView()">
                        <label class="form-check-label small" for="compactView">Compact View</label>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Bulk Actions Bar --}}
    <div id="bulkActionsBar" class="alert alert-primary d-flex align-items-center justify-content-between" style="display: none !important;">
        <div class="d-flex align-items-center gap-3">
            <span class="fw-bold" id="selectedItemsCount">0 items selected</span>
            
            <select id="bulkAction" class="form-select form-select-sm" style="width: auto;">
                <option value="">Choose action...</option>
                <option value="assign">Assign to...</option>
                <option value="update_status">Update status...</option>
            </select>

            <div id="assignSection" style="display: none;">
                <select id="bulkAssignTo" class="form-select form-select-sm" style="width: auto;">
                    <option value="">Select user...</option>
                    @foreach($assignees as $assignee)
                    <option value="{{ $assignee->id }}">{{ $assignee->name }}</option>
                    @endforeach
                </select>
            </div>

            <div id="statusSection" style="display: none;">
                <select id="bulkStatus" class="form-select form-select-sm" style="width: auto;">
                    <option value="">Select status...</option>
                    @foreach($statuses as $status)
                    <option value="{{ $status }}">{{ ucwords(str_replace('_', ' ', $status)) }}</option>
                    @endforeach
                </select>
            </div>
        </div>

        <div class="d-flex gap-2">
            <button onclick="executeBulkAction()" class="btn btn-sm btn-success">
                Apply
            </button>
            <button onclick="clearSelection()" class="btn btn-sm btn-outline-secondary">
                Cancel
            </button>
        </div>
    </div>

    {{-- Filters --}}
    <div class="card shadow-sm mb-4">
        <div class="card-body">
            <form method="GET" action="{{ route('phases.kanban', $phase) }}" id="filterForm">
                <div class="row g-3 align-items-center">
                    <div class="col-md-2">
                        <select name="discipline" class="form-select form-select-sm" onchange="this.form.submit()">
                            <option value="">All Disciplines</option>
                            @foreach($disciplineOptions as $discipline)
                            <option value="{{ $discipline }}" {{ request('discipline') == $discipline ? 'selected' : '' }}>
                                {{ ucfirst($discipline) }}
                            </option>
                            @endforeach
                        </select>
                    </div>
                    
                    <div class="col-md-2">
                        <select name="severity" class="form-select form-select-sm" onchange="this.form.submit()">
                            <option value="">All Severities</option>
                            <option value="critical" {{ request('severity') == 'critical' ? 'selected' : '' }}>Critical</option>
                            <option value="high" {{ request('severity') == 'high' ? 'selected' : '' }}>High</option>
                            <option value="medium" {{ request('severity') == 'medium' ? 'selected' : '' }}>Medium</option>
                            <option value="low" {{ request('severity') == 'low' ? 'selected' : '' }}>Low</option>
                        </select>
                    </div>
                    
                    <div class="col-md-2">
                        <select name="assigned_to" class="form-select form-select-sm" onchange="this.form.submit()">
                            <option value="">All Assignees</option>
                            @foreach($assignees as $assignee)
                            <option value="{{ $assignee->id }}" {{ request('assigned_to') == $assignee->id ? 'selected' : '' }}>
                                {{ $assignee->name }}
                            </option>
                            @endforeach
                        </select>
                    </div>
                    
                    <div class="col-md-2">
                        <a href="{{ route('phases.kanban', $phase) }}" class="btn btn-outline-secondary btn-sm w-100">
                            Clear Filters
                        </a>
                    </div>

                    <div class="col-md-2 text-end">
                        <span class="text-muted small">{{ $debugInfo['total_qa_items'] }} items</span>
                    </div>
                </div>
            </form>
        </div>
    </div>

    {{-- Kanban Board --}}
    <div class="row g-3">
        @php
            $groupedItems = $qaItems->groupBy('status');
        @endphp
        
        @foreach($phase->getKanbanColumns() as $status => $column)
        <div class="col-xl-2 col-lg-4 col-md-6">
            <div class="card border-0 shadow-sm h-100 {{ $column['color'] }}">
                <div class="card-header bg-transparent d-flex justify-content-between align-items-center py-3">
                    <h6 class="mb-0 fw-bold">{{ $column['title'] }}</h6>
                    <span class="badge bg-white text-dark column-counter" id="counter-{{ $status }}">
                        {{ $groupedItems->has($status) ? $groupedItems[$status]->count() : 0 }}
                    </span>
                </div>
                
                <div class="card-body p-3 kanban-column" 
                     data-status="{{ $status }}"
                     id="column-{{ $status }}"
                     style="min-height: 400px; max-height: 70vh; overflow-y: auto;">
                    
                    @if($groupedItems->has($status))
                        @php
                            $columnItems = $groupedItems[$status];
                            $totalItems = $columnItems->count();
                        @endphp

                        <div class="column-items" id="items-{{ $status }}">
                            @foreach($columnItems as $index => $item)
                            <div class="card mb-2 kanban-item cursor-grab item-{{ $status }}"
                                 draggable="true"
                                 data-item-id="{{ $item->id }}"
                                 data-item-index="{{ $index }}"
                                 ondragstart="handleDragStart(event)"
                                 ondblclick="editItem({{ $item->id }})"
                                 onclick="showItemDetails({{ $item->id }})">
                                
                                <div class="card-body p-2">
                                    <div class="d-flex justify-content-between align-items-start mb-1">
                                        <small class="text-muted">#{{ $item->id }}</small>
                                        <div class="d-flex gap-1">
                                            <span class="badge bg-{{ $item->getStatusColor() }} badge-sm">
                                                {{ substr($item->severity, 0, 1) }}
                                            </span>
                                            @if($item->isOverdue())
                                            <span class="badge bg-danger badge-sm">!</span>
                                            @endif
                                        </div>
                                    </div>
                                    
                                    <h6 class="card-title mb-1 item-description" style="font-size: 0.8rem; line-height: 1.2;">
                                        {{ $item->item_description }}
                                    </h6>

                                    <div class="item-details">
                                        <div class="d-flex justify-content-between align-items-center mb-1">
                                            <small class="text-muted">
                                                <i class="fas fa-file-alt me-1"></i>
                                                @if($item->sheet)
                                                    {{ $item->sheet->number }}
                                                @else
                                                    No Sheet
                                                @endif
                                            </small>
                                            <small class="{{ $item->isOverdue() ? 'text-danger fw-bold' : 'text-muted' }}">
                                                <i class="fas fa-calendar me-1"></i>
                                                @if($item->due_date)
                                                    {{ $item->due_date->format('M d') }}
                                                @else
                                                    No date
                                                @endif
                                            </small>
                                        </div>

                                        <div class="d-flex justify-content-between align-items-center">
                                            <div class="d-flex align-items-center">
                                                @if($item->assignedUser)
                                                <img src="https://ui-avatars.com/api/?name={{ urlencode($item->assignedUser->name) }}&background=random&size=20" 
                                                     class="rounded-circle me-1" 
                                                     alt="{{ $item->assignedUser->name }}"
                                                     title="{{ $item->assignedUser->name }}">
                                                @else
                                                <span class="text-muted small">Unassigned</span>
                                                @endif
                                                
                                                @if($item->attachments->count() > 0)
                                                <small class="text-muted ms-1">
                                                    <i class="fas fa-paperclip"></i> {{ $item->attachments->count() }}
                                                </small>
                                                @endif
                                            </div>
                                            
                                            <div class="d-flex gap-1">
                                                <button class="btn btn-sm btn-outline-primary p-0" style="width: 20px; height: 20px;" onclick="event.stopPropagation(); editItem({{ $item->id }})">
                                                    <i class="fas fa-edit fa-xs"></i>
                                                </button>
                                                <i class="fas {{ $item->getStatusIcon() }} text-{{ $item->getStatusColor() }} mt-1" style="font-size: 0.8rem;"></i>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="item-details-compact" style="display: none;">
                                        <div class="d-flex justify-content-between align-items-center">
                                            <small class="text-muted">
                                                @if($item->sheet)
                                                    {{ $item->sheet->number }}
                                                @endif
                                            </small>
                                            <small class="{{ $item->isOverdue() ? 'text-danger' : 'text-muted' }}">
                                                @if($item->due_date)
                                                    {{ $item->due_date->format('M d') }}
                                                @endif
                                            </small>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            @endforeach
                        </div>

                        {{-- Show More Button --}}
                        @if($totalItems > 10)
                        <div class="text-center mt-2" id="show-more-{{ $status }}">
                            <button class="btn btn-sm btn-outline-secondary show-more-btn" 
                                    onclick="loadMoreItems('{{ $status }}')">
                                <i class="fas fa-chevron-down me-1"></i>
                                Show More (<span id="remaining-{{ $status }}">{{ $totalItems - 10 }}</span>)
                            </button>
                        </div>
                        @endif
                    @else
                        <div class="text-center py-4 text-muted">
                            <i class="fas fa-inbox fa-lg mb-2"></i>
                            <p class="small mb-0">No items</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>
        @endforeach
    </div>

  {{-- Compact Item Details Modal --}}
<div id="itemDetailsModal" class="modal fade" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content border-0 shadow-lg" style="max-height: 85vh;">
            {{-- Modal Header with Gradient Background --}}
            <div class="modal-header bg-gradient-primary text-white border-0 py-3">
                <div class="d-flex align-items-center">
                    <div class="bg-white bg-opacity-20 rounded-circle p-2 me-3">
                        <i class="fas fa-clipboard-check fa-lg"></i>
                    </div>
                    <div>
                        <h5 class="modal-title mb-0 fw-bold">QA Item Details</h5>
                        <small class="text-white-50">Complete item information</small>
                    </div>
                </div>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>

            {{-- Scrollable Body --}}
            <div class="modal-body p-0" style="overflow-y: auto; max-height: calc(85vh - 130px);">
                <div class="container-fluid p-3">
                    <div class="row g-3">
                        {{-- Left Column - Main Content --}}
                        <div class="col-lg-7">
                            {{-- Description Card --}}
                            <div class="card border-0 shadow-sm">
                                <div class="card-header bg-transparent border-0 py-2">
                                    <div class="d-flex align-items-center">
                                        <i class="fas fa-align-left text-primary me-2 fs-6"></i>
                                        <h6 class="mb-0 fw-bold text-dark fs-6">Description</h6>
                                    </div>
                                </div>
                                <div class="card-body py-2">
                                    <div class="bg-light rounded p-2">
                                        <p class="mb-0 text-dark fs-7 lh-sm" id="detailDescription" style="min-height: 40px;">
                                            <div class="d-flex justify-content-center align-items-center py-1">
                                                <div class="spinner-border spinner-border-sm text-primary me-2" role="status"></div>
                                                <span class="small">Loading description...</span>
                                            </div>
                                        </p>
                                    </div>
                                </div>
                            </div>

                            {{-- Notes Card --}}
                            <div class="card border-0 shadow-sm">
                                <div class="card-header bg-transparent border-0 py-2">
                                    <div class="d-flex align-items-center">
                                        <i class="fas fa-sticky-note text-warning me-2 fs-6"></i>
                                        <h6 class="mb-0 fw-bold text-dark fs-6">Notes</h6>
                                    </div>
                                </div>
                                <div class="card-body py-2">
                                    <div class="bg-light rounded p-2 min-h-60">
                                        <p class="mb-0 text-muted fs-7 lh-sm" id="detailNotes">
                                            <div class="text-center text-muted py-1">
                                                <i class="fas fa-comment-dots fa-lg mb-1 opacity-50"></i>
                                                <p class="mb-0 small">No notes available</p>
                                            </div>
                                        </p>
                                    </div>
                                </div>
                            </div>
                        </div>

                        {{-- Right Column - Sidebar Information --}}
                        <div class="col-lg-5">
                            {{-- Quick Actions Card 
                            <div class="card border-0 shadow-sm mb-3">
                                <div class="card-header bg-transparent border-0 py-2">
                                    <h6 class="mb-0 fw-bold text-dark fs-6">Quick Actions</h6>
                                </div>
                                <div class="card-body py-2">
                                    <div class="d-grid gap-1">
                                        <button type="button" class="btn btn-primary btn-sm py-1" onclick="editCurrentItem()">
                                            <i class="fas fa-edit me-1"></i>Edit Item
                                        </button>
                                        <button type="button" class="btn btn-outline-secondary btn-sm py-1" onclick="changeItemStatus()">
                                            <i class="fas fa-sync-alt me-1"></i>Change Status
                                        </button>
                                    </div>
                                </div>
                            </div>
--}}
                            {{-- Item Information Card --}}
                            <div class="card border-0 shadow-sm">
                                <div class="card-header bg-transparent border-0 py-2">
                                    <div class="d-flex align-items-center">
                                        <i class="fas fa-info-circle text-info me-2 fs-6"></i>
                                        <h6 class="mb-0 fw-bold text-dark fs-6">Item Information</h6>
                                    </div>
                                </div>
                                <div class="card-body py-2">
                                    {{-- Status & Severity Row --}}
                                    <div class="row g-2 mb-2">
                                        <div class="col-6">
                                            <small class="text-muted fw-semibold d-block mb-1">STATUS</small>
                                            <span class="badge fs-8 w-100" id="detailStatus">
                                                <div class="spinner-border spinner-border-sm me-1" role="status"></div>
                                                Loading
                                            </span>
                                        </div>
                                        <div class="col-6">
                                            <small class="text-muted fw-semibold d-block mb-1">SEVERITY</small>
                                            <span class="badge fs-8 w-100" id="detailSeverity">
                                                <div class="spinner-border spinner-border-sm me-1" role="status"></div>
                                                Loading
                                            </span>
                                        </div>
                                    </div>

                                    {{-- Assigned To --}}
                                    <div class="mb-2">
                                        <small class="text-muted fw-semibold d-block mb-1">ASSIGNED TO</small>
                                        <div id="detailAssignedTo" class="d-flex align-items-center">
                                            <div class="spinner-border spinner-border-sm text-primary me-2" role="status"></div>
                                            <span class="text-muted small">Loading...</span>
                                        </div>
                                    </div>

                                    {{-- Due Date & Sheet --}}
                                    <div class="row g-2 mb-2">
                                        <div class="col-6">
                                            <small class="text-muted fw-semibold d-block mb-1">DUE DATE</small>
                                            <div class="d-flex align-items-center">
                                                <i class="fas fa-calendar-alt text-muted me-1 fs-7"></i>
                                                <span id="detailDueDate" class="text-dark small">Loading...</span>
                                            </div>
                                        </div>
                                        <div class="col-6">
                                            <small class="text-muted fw-semibold d-block mb-1">SHEET</small>
                                            <div class="d-flex align-items-center">
                                                <i class="fas fa-file-alt text-muted me-1 fs-7"></i>
                                                <span id="detailSheet" class="text-dark small">Loading...</span>
                                            </div>
                                        </div>
                                    </div>

                                    {{-- Timeline --}}
                                    <div class="mb-2">
                                        <small class="text-muted fw-semibold d-block mb-1">TIMELINE</small>
                                        <div class="timeline-compact">
                                            <div class="timeline-item-compact">
                                                <small class="text-muted">Created</small>
                                                <div class="fw-semibold text-dark small" id="detailCreatedAt">Loading...</div>
                                            </div>
                                            <div class="timeline-item-compact">
                                                <small class="text-muted">Updated</small>
                                                <div class="fw-semibold text-dark small" id="detailUpdatedAt">Loading...</div>
                                            </div>
                                        </div>
                                    </div>

                                    {{-- Progress --}}
                                    <div class="mt-2 pt-2 border-top">
                                        <small class="text-muted fw-semibold d-block mb-1">PROGRESS</small>
                                        <div class="progress mb-1" style="height: 4px;">
                                            <div id="progressBar" class="progress-bar bg-success" role="progressbar" style="width: 0%"></div>
                                        </div>
                                        <small class="text-muted" id="progressText">Calculating...</small>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- Attachments Section --}}
                    <div class="row mt-2">
                        <div class="col-12">
                            <div class="card border-0 shadow-sm">
                                <div class="card-header bg-transparent border-0 py-2">
                                    <div class="d-flex align-items-center justify-content-between">
                                        <div class="d-flex align-items-center">
                                            <i class="fas fa-paperclip text-success me-2 fs-6"></i>
                                            <h6 class="mb-0 fw-bold text-dark fs-6">Attachments</h6>
                                        </div>
                                        <span class="badge bg-success bg-opacity-10 text-success fs-8" id="attachmentCount">0 files</span>
                                    </div>
                                </div>
                                <div class="card-body py-2">
                                    <div id="detailAttachments">
                                        <div class="text-center py-2">
                                            <i class="fas fa-cloud-upload-alt fa-2x text-muted mb-1 opacity-50"></i>
                                            <p class="text-muted mb-0 small">No attachments</p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Compact Modal Footer --}}
            <div class="modal-footer bg-light border-0 py-2">
                <div class="d-flex justify-content-between w-100 align-items-center">
                    <small class="text-muted" id="lastUpdated">Loading last update...</small>
                    <div>
                        <button type="button" class="btn btn-outline-secondary btn-sm me-1" data-bs-dismiss="modal">
                            <i class="fas fa-times me-1"></i>Close
                        </button>
                        <button type="button" class="btn btn-primary btn-sm" onclick="editCurrentItem()">
                            <i class="fas fa-edit me-1"></i>Edit
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
/* Compact Modal Styles */
.modal-lg {
    max-width: 900px;
}

.bg-gradient-primary {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%) !important;
}

/* Compact timeline */
.timeline-compact {
    display: flex;
    gap: 15px;
}

.timeline-item-compact {
    flex: 1;
}

/* Font size adjustments */
.fs-7 {
    font-size: 0.8rem !important;
}

.fs-8 {
    font-size: 0.75rem !important;
}

/* Compact card bodies */
.min-h-60 {
    min-height: 60px;
}

/* Reduced padding and margins */
.card-body.py-2 {
    padding-top: 0.5rem !important;
    padding-bottom: 0.5rem !important;
}

.modal-body .container-fluid.p-3 {
    padding: 0.75rem !important;
}

/* Better scrollbar for compact modal */
.modal-body::-webkit-scrollbar {
    width: 4px;
}

.modal-body::-webkit-scrollbar-track {
    background: #f8f9fa;
}

.modal-body::-webkit-scrollbar-thumb {
    background: #dee2e6;
    border-radius: 2px;
}

.modal-body::-webkit-scrollbar-thumb:hover {
    background: #adb5bd;
}

/* Compact attachment grid */
.attachment-grid-compact {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(120px, 1fr));
    gap: 8px;
}

.attachment-card-compact {
    transition: all 0.2s ease;
    border: 1px solid #e9ecef;
    border-radius: 6px;
    padding: 6px;
}

.attachment-card-compact:hover {
    border-color: #007bff;
    transform: translateY(-1px);
}

/* Button adjustments for compact view */
.btn-sm.py-1 {
    padding-top: 0.25rem !important;
    padding-bottom: 0.25rem !important;
}

/* Progress bar compact */
.progress {
    height: 4px !important;
}

/* Badge adjustments */
.badge.fs-8 {
    padding: 0.25em 0.4em;
    font-size: 0.75rem;
}

/* Responsive adjustments */
@media (max-width: 768px) {
    .modal-lg {
        margin: 0.5rem;
    }
    
    .timeline-compact {
        flex-direction: column;
        gap: 8px;
    }
    
    .attachment-grid-compact {
        grid-template-columns: 1fr;
    }
}

/* Loading state improvements */
.spinner-border-sm {
    width: 0.8rem;
    height: 0.8rem;
}

/* Ensure text doesn't overflow */
.text-truncate-multiline {
    display: -webkit-box;
    -webkit-line-clamp: 3;
    -webkit-box-orient: vertical;
    overflow: hidden;
}

/* Compact file icons */
.file-icon-compact {
    font-size: 1.2rem !important;
}
</style>

<script>
// Compact attachment formatting
function formatAttachmentsCompact(attachments) {
    if (!attachments || attachments.length === 0) {
        return `
            <div class="text-center py-2">
                <i class="fas fa-cloud-upload-alt fa-2x text-muted mb-1 opacity-50"></i>
                <p class="text-muted mb-0 small">No attachments found</p>
            </div>
        `;
    }

    let html = `<div class="attachment-grid-compact">`;
    
    attachments.forEach(attachment => {
        const fileIcon = getFileIcon(attachment.filename);
        
        html += `
            <div class="attachment-card-compact text-center">
                <i class="${fileIcon} file-icon-compact text-primary mb-1 d-block"></i>
                <small class="d-block text-truncate" style="font-size: 0.7rem;" title="${attachment.filename}">
                    ${attachment.filename.split('.').shift().substring(0, 12)}${attachment.filename.split('.').shift().length > 12 ? '...' : ''}
                </small>
                <div class="mt-1">
                    <a href="${attachment.url}" target="_blank" class="btn btn-outline-primary btn-xs p-1 me-1" title="View">
                        <i class="fas fa-eye"></i>
                    </a>
                    <a href="${attachment.url}" download class="btn btn-outline-success btn-xs p-1" title="Download">
                        <i class="fas fa-download"></i>
                    </a>
                </div>
            </div>
        `;
    });
    
    html += `</div>`;
    return html;
}

// Update the showItemDetails function for compact view
async function showItemDetails(itemId) {
    currentItemId = itemId;
    
    try {
        const response = await fetch(`/qa-items/${itemId}/details`);
        const data = await response.json();
        
        if (data.success) {
            // Update main content
            document.getElementById('detailDescription').textContent = data.item.item_description || 'No description provided';
            document.getElementById('detailNotes').textContent = data.item.notes || 'No notes available';
            
            // Update status and severity
            updateStatusBadge(data.item.status);
            updateSeverityBadge(data.item.severity);
            
            // Update assigned user
            updateAssignedUser(data.item.assigned_user);
            
            // Update dates and sheet info
            document.getElementById('detailDueDate').textContent = data.item.due_date ? 
                new Date(data.item.due_date).toLocaleDateString() : 'Not set';
            
            document.getElementById('detailSheet').textContent = data.item.sheet ? 
                data.item.sheet.number : 'No sheet';
            
            document.getElementById('detailCreatedAt').textContent = new Date(data.item.created_at).toLocaleDateString();
            document.getElementById('detailUpdatedAt').textContent = new Date(data.item.updated_at).toLocaleDateString();
            
            // Update attachments with compact view
            document.getElementById('detailAttachments').innerHTML = formatAttachmentsCompact(data.item.attachments);
            document.getElementById('attachmentCount').textContent = `${data.item.attachments?.length || 0} files`;
            
            // Update progress bar
            updateProgressBar(data.item.status);
            
            // Update last updated text
            document.getElementById('lastUpdated').textContent = `Updated: ${new Date(data.item.updated_at).toLocaleTimeString()}`;
            
            // Show modal
            const modal = new bootstrap.Modal(document.getElementById('itemDetailsModal'));
            modal.show();
        }
    } catch (error) {
        console.error('Error loading item details:', error);
        showNotification('Error loading item details', 'error');
    }
}

// Update badge functions for compact view
function updateStatusBadge(status) {
    const badge = document.getElementById('detailStatus');
    const statusText = status.replace('_', ' ').toUpperCase();
    const colorClass = `bg-${getStatusColor(status)}`;
    badge.className = `badge ${colorClass} fs-8 w-100`;
    badge.innerHTML = `<i class="fas fa-circle me-1" style="font-size: 0.5em;"></i>${statusText}`;
}

function updateSeverityBadge(severity) {
    const badge = document.getElementById('detailSeverity');
    const severityText = severity.toUpperCase();
    const colorClass = `bg-${getSeverityColor(severity)}`;
    badge.className = `badge ${colorClass} fs-8 w-100`;
    badge.innerHTML = `<i class="fas fa-exclamation-triangle me-1" style="font-size: 0.5em;"></i>${severityText}`;
}

function updateAssignedUser(assignedUser) {
    const container = document.getElementById('detailAssignedTo');
    if (assignedUser) {
        container.innerHTML = `
            <div class="d-flex align-items-center">
                <img src="https://ui-avatars.com/api/?name=${encodeURIComponent(assignedUser.name)}&background=007bff&color=fff&size=24" 
                     class="rounded-circle me-2" 
                     alt="${assignedUser.name}"
                     style="width: 24px; height: 24px;">
                <div>
                    <div class="fw-semibold text-dark small">${assignedUser.name}</div>
                </div>
            </div>
        `;
    } else {
        container.innerHTML = `
            <div class="text-muted small">
                <i class="fas fa-user-slash me-1"></i>Unassigned
            </div>
        `;
    }
}

// Keep existing helper functions
function calculateProgress(status) {
    const progressMap = {
        'open': 10,
        'in_progress': 40,
        'needs_info': 25,
        'resolved': 80,
        'verified': 95,
        'closed': 100
    };
    return progressMap[status] || 0;
}

function updateProgressBar(status) {
    const progress = calculateProgress(status);
    const progressBar = document.getElementById('progressBar');
    const progressText = document.getElementById('progressText');
    
    progressBar.style.width = `${progress}%`;
    progressText.textContent = `${progress}%`;
    
    if (progress >= 80) {
        progressBar.className = 'progress-bar bg-success';
    } else if (progress >= 40) {
        progressBar.className = 'progress-bar bg-warning';
    } else {
        progressBar.className = 'progress-bar bg-danger';
    }
}

function getFileIcon(filename) {
    const ext = filename.split('.').pop().toLowerCase();
    const iconMap = {
        'pdf': 'fas fa-file-pdf',
        'doc': 'fas fa-file-word',
        'docx': 'fas fa-file-word',
        'xls': 'fas fa-file-excel',
        'xlsx': 'fas fa-file-excel',
        'jpg': 'fas fa-file-image',
        'jpeg': 'fas fa-file-image',
        'png': 'fas fa-file-image',
        'zip': 'fas fa-file-archive'
    };
    return iconMap[ext] || 'fas fa-file';
}
</script>
    {{-- Quick Edit Modal --}}
    <div id="editModal" class="modal fade" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Edit QA Item</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <form id="quickEditForm">
                        <input type="hidden" id="editItemId" name="qa_item_id">
                        
                        <div class="mb-3">
                            <label class="form-label">Description</label>
                            <textarea id="editDescription" name="item_description" class="form-control" rows="3"></textarea>
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label">Notes</label>
                            <textarea id="editNotes" name="notes" class="form-control" rows="2"></textarea>
                        </div>
                        
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label class="form-label">Severity</label>
                                <select id="editSeverity" name="severity" class="form-select">
                                    <option value="low">Low</option>
                                    <option value="medium">Medium</option>
                                    <option value="high">High</option>
                                    <option value="critical">Critical</option>
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Due Date</label>
                                <input type="date" id="editDueDate" name="due_date" class="form-control">
                            </div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Assigned To</label>
                            <select id="editAssignedTo" name="assigned_to" class="form-select">
                                <option value="">Unassigned</option>
                                @foreach($assignees as $assignee)
                                <option value="{{ $assignee->id }}">{{ $assignee->name }}</option>
                                @endforeach
                            </select>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="button" class="btn btn-primary" onclick="saveQuickEdit()">Save Changes</button>
                </div>
            </div>
        </div>
    </div>

</div>

<script>
// Global variables
let selectedItems = [];
let itemsPerColumn = 10;
let compactView = false;
let currentItemId = null;

// Initialize when page loads
document.addEventListener('DOMContentLoaded', function() {
    initializeKanban();
    setupBulkActionListeners();
    updateColumnDisplay(); // Show initial items
    restoreSettings();
});

function initializeKanban() {
    setupDragAndDrop();
}

function setupBulkActionListeners() {
    const bulkAction = document.getElementById('bulkAction');
    const assignSection = document.getElementById('assignSection');
    const statusSection = document.getElementById('statusSection');
    
    bulkAction.addEventListener('change', function() {
        assignSection.style.display = this.value === 'assign' ? 'block' : 'none';
        statusSection.style.display = this.value === 'update_status' ? 'block' : 'none';
    });
}

function setupDragAndDrop() {
    document.addEventListener('dragstart', function(e) {
        if (e.target.classList.contains('kanban-item')) {
            e.target.classList.add('opacity-50', 'rotate-1');
            e.dataTransfer.effectAllowed = 'move';
            e.dataTransfer.setData('text/plain', e.target.getAttribute('data-item-id'));
        }
    });
    
    document.addEventListener('dragend', function(e) {
        if (e.target.classList.contains('kanban-item')) {
            e.target.classList.remove('opacity-50', 'rotate-1');
        }
    });

    document.addEventListener('dragover', function(e) {
        e.preventDefault();
    });

    document.addEventListener('drop', async function(e) {
        e.preventDefault();
        const column = e.target.closest('.kanban-column');
        if (column) {
            const itemId = e.dataTransfer.getData('text/plain');
            const newStatus = column.getAttribute('data-status');
            
            try {
                const response = await fetch(`/qa-items/${itemId}/status`, {
                    method: 'PATCH',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    },
                    body: JSON.stringify({
                        status: newStatus,
                        _method: 'PATCH'
                    })
                });
                
                const data = await response.json();
                
                if (data.success) {
                    showNotification('Status updated successfully', 'success');
                    setTimeout(() => window.location.reload(), 500);
                } else {
                    throw new Error(data.message);
                }
            } catch (error) {
                console.error('Error updating QA item:', error);
                showNotification('Error updating status', 'error');
            }
        }
    });
}

// Show item details modal
async function showItemDetails(itemId) {
    currentItemId = itemId;
    
    try {
        const response = await fetch(`/qa-items/${itemId}/details`);
        const data = await response.json();
        
        if (data.success) {
            // Update modal content
            document.getElementById('detailDescription').textContent = data.item.item_description || 'No description';
            document.getElementById('detailNotes').textContent = data.item.notes || 'No notes available';
            
            // Status with color
            const statusBadge = document.getElementById('detailStatus');
            statusBadge.textContent = data.item.status.replace('_', ' ');
            statusBadge.className = `badge bg-${getStatusColor(data.item.status)}`;
            
            // Severity with color
            const severityBadge = document.getElementById('detailSeverity');
            severityBadge.textContent = data.item.severity;
            severityBadge.className = `badge bg-${getSeverityColor(data.item.severity)}`;
            
            // Assigned user
            const assignedTo = document.getElementById('detailAssignedTo');
            if (data.item.assigned_user) {
                assignedTo.innerHTML = `
                    <img src="https://ui-avatars.com/api/?name=${encodeURIComponent(data.item.assigned_user.name)}&background=random&size=32" 
                         class="rounded-circle me-2" 
                         alt="${data.item.assigned_user.name}"
                         style="width: 24px; height: 24px;">
                    ${data.item.assigned_user.name}
                `;
            } else {
                assignedTo.innerHTML = '<span class="text-muted">Unassigned</span>';
            }
            
            // Dates
            document.getElementById('detailDueDate').textContent = data.item.due_date ? new Date(data.item.due_date).toLocaleDateString() : 'No due date';
            document.getElementById('detailCreatedAt').textContent = new Date(data.item.created_at).toLocaleDateString();
            document.getElementById('detailUpdatedAt').textContent = new Date(data.item.updated_at).toLocaleDateString();
            
            // Sheet info
            document.getElementById('detailSheet').textContent = data.item.sheet ? data.item.sheet.number : 'No sheet';
            
            // Attachments
            const attachmentsContainer = document.getElementById('detailAttachments');
            if (data.item.attachments && data.item.attachments.length > 0) {
                let attachmentsHtml = '<div class="row g-2">';
                data.item.attachments.forEach(attachment => {
                    attachmentsHtml += `
                        <div class="col-md-6">
                            <div class="card border">
                                <div class="card-body p-2">
                                    <div class="d-flex align-items-center">
                                        <i class="fas fa-file me-2 text-muted"></i>
                                        <div class="flex-grow-1">
                                            <small class="d-block text-truncate" style="max-width: 200px;">${attachment.filename}</small>
                                            <small class="text-muted">${new Date(attachment.created_at).toLocaleDateString()}</small>
                                        </div>
                                        <a href="${attachment.url}" target="_blank" class="btn btn-sm btn-outline-primary ms-2">
                                            <i class="fas fa-download"></i>
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    `;
                });
                attachmentsHtml += '</div>';
                attachmentsContainer.innerHTML = attachmentsHtml;
            } else {
                attachmentsContainer.innerHTML = `
                    <div class="text-center py-3">
                        <i class="fas fa-paperclip fa-2x text-muted mb-2"></i>
                        <p class="text-muted mb-0">No attachments</p>
                    </div>
                `;
            }
            
            // Show modal
            const modal = new bootstrap.Modal(document.getElementById('itemDetailsModal'));
            modal.show();
        } else {
            throw new Error('Failed to load item details');
        }
    } catch (error) {
        console.error('Error loading item details:', error);
        showNotification('Error loading item details', 'error');
    }
}

function getStatusColor(status) {
    const colors = {
        'open': 'warning',
        'in_progress': 'info',
        'needs_info': 'orange',
        'resolved': 'success',
        'verified': 'primary',
        'closed': 'secondary'
    };
    return colors[status] || 'dark';
}

function getSeverityColor(severity) {
    const colors = {
        'critical': 'danger',
        'high': 'warning',
        'medium': 'info',
        'low': 'success'
    };
    return colors[severity] || 'secondary';
}

function editCurrentItem() {
    if (currentItemId) {
        const detailsModal = bootstrap.Modal.getInstance(document.getElementById('itemDetailsModal'));
        detailsModal.hide();
        setTimeout(() => editItem(currentItemId), 300);
    }
}

function updateItemsPerColumn() {
    const select = document.getElementById('itemsPerColumn');
    itemsPerColumn = parseInt(select.value);
    localStorage.setItem('kanbanItemsPerColumn', itemsPerColumn);
    updateColumnDisplay();
}

function toggleCompactView() {
    compactView = document.getElementById('compactView').checked;
    localStorage.setItem('kanbanCompactView', compactView);
    
    // Toggle between detailed and compact views
    const allItems = document.querySelectorAll('.kanban-item');
    allItems.forEach(item => {
        const details = item.querySelector('.item-details');
        const compactDetails = item.querySelector('.item-details-compact');
        const description = item.querySelector('.item-description');
        
        if (compactView) {
            details.style.display = 'none';
            compactDetails.style.display = 'block';
            description.classList.add('line-clamp-1');
            description.classList.remove('line-clamp-2');
            item.classList.add('compact-view');
        } else {
            details.style.display = 'block';
            compactDetails.style.display = 'none';
            description.classList.remove('line-clamp-1');
            description.classList.add('line-clamp-2');
            item.classList.remove('compact-view');
        }
    });
}

function updateColumnDisplay() {
    // Update display for all columns
    @foreach($phase->getKanbanColumns() as $status => $column)
        updateColumnItems('{{ $status }}');
    @endforeach
}

function updateColumnItems(status) {
    const items = document.querySelectorAll(`.item-${status}`);
    const showMoreBtn = document.getElementById(`show-more-${status}`);
    const remainingSpan = document.getElementById(`remaining-${status}`);
    
    let visibleCount = 0;
    const totalItems = items.length;
    
    items.forEach((item, index) => {
        if (itemsPerColumn === 0 || index < itemsPerColumn) {
            item.style.display = 'block';
            visibleCount++;
        } else {
            item.style.display = 'none';
        }
    });
    
    // Update show more button
    if (showMoreBtn && remainingSpan) {
        const remaining = totalItems - itemsPerColumn;
        if (remaining > 0 && itemsPerColumn > 0) {
            showMoreBtn.style.display = 'block';
            remainingSpan.textContent = remaining;
        } else {
            showMoreBtn.style.display = 'none';
        }
    }
}

function loadMoreItems(status) {
    // Increase the limit for this column
    itemsPerColumn += 10;
    document.getElementById('itemsPerColumn').value = itemsPerColumn;
    localStorage.setItem('kanbanItemsPerColumn', itemsPerColumn);
    updateColumnItems(status);
}

function toggleSelectItem(itemId, event) {
    event.stopPropagation();
    const index = selectedItems.indexOf(itemId);
    const itemElement = document.querySelector(`[data-item-id="${itemId}"]`);
    
    if (index > -1) {
        selectedItems.splice(index, 1);
        itemElement.classList.remove('border-primary');
    } else {
        selectedItems.push(itemId);
        itemElement.classList.add('border-primary');
    }
    
    updateBulkActionsBar();
}

function updateBulkActionsBar() {
    const bulkActionsBar = document.getElementById('bulkActionsBar');
    const selectedItemsCount = document.getElementById('selectedItemsCount');
    
    if (selectedItems.length > 0) {
        bulkActionsBar.style.display = 'flex';
        selectedItemsCount.textContent = selectedItems.length + ' items selected';
    } else {
        bulkActionsBar.style.display = 'none';
    }
}

function clearSelection() {
    selectedItems = [];
    document.querySelectorAll('.kanban-item').forEach(item => {
        item.classList.remove('border-primary');
    });
    updateBulkActionsBar();
}

async function executeBulkAction() {
    const action = document.getElementById('bulkAction').value;
    
    if (!action || selectedItems.length === 0) return;
    
    const formData = new FormData();
    formData.append('action', action);
    selectedItems.forEach(id => formData.append('qa_item_ids[]', id));
    
    if (action === 'assign') {
        const assignTo = document.getElementById('bulkAssignTo').value;
        if (!assignTo) {
            showNotification('Please select a user to assign', 'error');
            return;
        }
        formData.append('assigned_to', assignTo);
    } else if (action === 'update_status') {
        const status = document.getElementById('bulkStatus').value;
        if (!status) {
            showNotification('Please select a status', 'error');
            return;
        }
        formData.append('status', status);
    }
    
    try {
        const response = await fetch('{{ route("phases.kanban.bulk-update", $phase) }}', {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
            },
            body: formData
        });
        
        if (response.ok) {
            showNotification('Bulk action completed successfully', 'success');
            clearSelection();
            setTimeout(() => window.location.reload(), 1000);
        }
    } catch (error) {
        showNotification('Error executing bulk action', 'error');
    }
}

async function editItem(itemId) {
    try {
        const response = await fetch(`/qa-items/${itemId}/edit-data`);
        const data = await response.json();
        
        if (data.success) {
            document.getElementById('editItemId').value = data.item.id;
            document.getElementById('editDescription').value = data.item.item_description;
            document.getElementById('editNotes').value = data.item.notes || '';
            document.getElementById('editSeverity').value = data.item.severity;
            document.getElementById('editDueDate').value = data.item.due_date ? data.item.due_date.split('T')[0] : '';
            document.getElementById('editAssignedTo').value = data.item.assigned_to || '';
            
            const modal = new bootstrap.Modal(document.getElementById('editModal'));
            modal.show();
        }
    } catch (error) {
        console.error('Error fetching item data:', error);
        showNotification('Error loading item data', 'error');
    }
}

async function saveQuickEdit() {
    const formData = new FormData();
    formData.append('item_description', document.getElementById('editDescription').value);
    formData.append('notes', document.getElementById('editNotes').value);
    formData.append('severity', document.getElementById('editSeverity').value);
    formData.append('due_date', document.getElementById('editDueDate').value);
    formData.append('assigned_to', document.getElementById('editAssignedTo').value);
    formData.append('_method', 'PATCH');

    const itemId = document.getElementById('editItemId').value;

    try {
        const response = await fetch(`/qa-items/${itemId}`, {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
            },
            body: formData
        });

        if (response.ok) {
            const modal = bootstrap.Modal.getInstance(document.getElementById('editModal'));
            modal.hide();
            showNotification('QA item updated successfully', 'success');
            setTimeout(() => window.location.reload(), 1000);
        } else {
            throw new Error('Failed to update item');
        }
    } catch (error) {
        console.error('Error updating QA item:', error);
        showNotification('Error updating item', 'error');
    }
}

function showNotification(message, type = 'info') {
    const alertClass = type === 'success' ? 'alert-success' : 'alert-danger';
    const icon = type === 'success' ? 'fa-check-circle' : 'fa-exclamation-circle';
    
    const alert = document.createElement('div');
    alert.className = `alert ${alertClass} alert-dismissible fade show`;
    alert.innerHTML = `
        <i class="fas ${icon} me-2"></i>
        ${message}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    `;
    
    document.querySelector('.container-fluid').prepend(alert);
    
    setTimeout(() => {
        if (alert.parentNode) {
            alert.remove();
        }
    }, 5000);
}

// Restore settings from localStorage
function restoreSettings() {
    const savedItemsPerColumn = localStorage.getItem('kanbanItemsPerColumn');
    const savedCompactView = localStorage.getItem('kanbanCompactView');
    
    if (savedItemsPerColumn) {
        itemsPerColumn = parseInt(savedItemsPerColumn);
        document.getElementById('itemsPerColumn').value = itemsPerColumn;
    }
    
    if (savedCompactView === 'true') {
        compactView = true;
        document.getElementById('compactView').checked = true;
        toggleCompactView();
    }
}
</script>

<style>
.kanban-column {
    transition: all 0.2s ease;
    border: 2px dashed transparent;
}

.kanban-column.drag-over {
    background-color: rgba(59, 130, 246, 0.1) !important;
    border-color: #3b82f6;
}

.kanban-item {
    transition: all 0.2s ease;
    user-select: none;
    cursor: pointer;
}

.kanban-item:hover {
    transform: translateY(-1px);
    box-shadow: 0 2px 8px rgba(0,0,0,0.1);
}

.kanban-item.rotate-1 {
    transform: rotate(1deg);
}

.kanban-item.compact-view {
    margin-bottom: 0.25rem !important;
}

.kanban-item.compact-view .card-body {
    padding: 0.5rem !important;
}

.cursor-grab {
    cursor: grab;
}

.cursor-grab:active {
    cursor: grabbing;
}

.badge-sm {
    font-size: 0.6em;
    padding: 0.2em 0.4em;
}

.line-clamp-1 {
    display: -webkit-box;
    -webkit-line-clamp: 1;
    -webkit-box-orient: vertical;
    overflow: hidden;
}

.line-clamp-2 {
    display: -webkit-box;
    -webkit-line-clamp: 2;
    -webkit-box-orient: vertical;
    overflow: hidden;
}

.show-more-btn {
    font-size: 0.7rem;
    padding: 0.2rem 0.5rem;
}

.kanban-column::-webkit-scrollbar {
    width: 6px;
}

.kanban-column::-webkit-scrollbar-track {
    background: #f1f1f1;
    border-radius: 3px;
}

.kanban-column::-webkit-scrollbar-thumb {
    background: #c1c1c1;
    border-radius: 3px;
}

.kanban-column::-webkit-scrollbar-thumb:hover {
    background: #a8a8a8;
}

#bulkActionsBar {
    transition: all 0.3s ease;
}

/* Status badge colors */
.badge.bg-orange {
    background-color: #fd7e14 !important;
}
</style>
@endsection