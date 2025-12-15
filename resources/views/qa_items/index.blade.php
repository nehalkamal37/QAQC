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

            {{-- ADD QA ITEM (Admin / PM / SR) --}}
            @if(auth()->user()->hasRole(['Admin','PM','Senior Reviewer']))
            <a href="{{ route('qa_items.create', $sheet->id) }}" class="btn btn-primary">
                <i class="bi bi-plus-circle me-1"></i> Add QA Item
            </a>
            @endif

            {{-- VERIFY ALL (Admin / PM / Senior Reviewer) --}}
            @if(auth()->user()->hasRole(['Admin','PM','Senior Reviewer']))
            <form action="{{ route('qa_items.verifyAll', $sheet->id) }}" method="POST">
                @csrf
                <button class="btn btn-success">
                    <i class="bi bi-check-all me-1"></i> Verify All
                </button>
            </form>
            @endif

        </div>
    </div>

    {{-- FILTER CARD --}}
    <div class="card shadow-sm mb-4 border-0">
        <div class="card-body p-3">
            <form method="GET" action="{{ route('qa_items.index', $sheet->id) }}">
                <div class="row g-3">

                    {{-- STATUS --}}
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

                    {{-- ASSIGNEE --}}
                    <div class="col-md-3">
                        <label class="form-label small text-muted mb-1">Assignee</label>
                        <input type="text" name="assigned_to" value="{{ request('assigned_to') }}"
                               class="form-control form-control-sm" placeholder="Search assignee...">
                    </div>

                    {{-- SEVERITY --}}
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

                    {{-- SUBMIT --}}
                    <div class="col-md-3 d-flex align-items-end">
                        <button type="submit" class="btn btn-primary btn-sm w-100">
                            <i class="bi bi-search me-1"></i> Apply Filters
                        </button>
                    </div>

                </div>
            </form>
        </div>
    </div>

    {{-- TABLE --}}
    <div class="card shadow-sm border-0">
        <div class="card-body p-0">
            <div class="table-responsive">

                <table class="table table-hover mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>ID</th>
                            <th class="ps-4">Title</th>
                            <th>Category</th>
                            <th>Status</th>
                            <th>Progress</th>
                            <th>Due Date</th>
                            <th>Assignee</th>
                            <th>Severity</th>
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
                    @endphp

                    <tr id="item-{{ $item->id }}" class="align-middle">

                        {{-- ID --}}
                        <td>{{ $item->id }}</td>

                        {{-- TITLE + DESCRIPTION --}}
                        <td class="ps-4">
                            <strong>{{ $item->title }}</strong>
                            <div class="text-muted small">{{ Str::limit($item->item_description, 60) }}</div>
                        </td>

                        {{-- CATEGORY --}}
                        <td>
                            <span class="badge bg-light text-dark border">{{ $item->category }}</span>
                        </td>

                        {{-- STATUS --}}
                        <td>
                            <span class="badge bg-{{ $badge }}">
                                {{ ucfirst(str_replace('_',' ', $item->status)) }}
                            </span>
                        </td>

                        {{-- PROGRESS --}}
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
                                <span class="{{ $item->applicable ? 'text-success' : 'text-muted' }}"><i class="bi bi-{{ $item->applicable ? 'check' : 'x' }}-circle"></i> App</span>
                                <span class="{{ $item->incorporated ? 'text-success' : 'text-muted' }}"><i class="bi bi-{{ $item->incorporated ? 'check' : 'x' }}-circle"></i> Inc</span>
                                <span class="{{ $item->confirmed ? 'text-success' : 'text-muted' }}"><i class="bi bi-{{ $item->confirmed ? 'check' : 'x' }}-circle"></i> Conf</span>
                            </div>
                        </td>

                        {{-- DUE DATE --}}
                        <td>{{ $item->project_due_date ?? '—' }}</td>

                        {{-- ASSIGNEE --}}
                        <td>{{ $item->project_assigned_to ?? '—' }}</td>

                        {{-- SEVERITY --}}
                        <td>
                            <span class="badge bg-{{ $sevBadge }}">{{ ucfirst($item->severity) }}</span>
                        </td>

                        {{-- ACTIONS --}}
                        <td class="text-center">
                            <div class="d-flex justify-content-center gap-1 flex-wrap">

                                {{-- EDIT --}}
                                @if(
                                    auth()->user()->hasRole(['Admin','PM','Senior Reviewer']) ||
                                    (auth()->user()->hasRole(['Engineer','Night Vision']) &&
                                     $item->assigned_to == auth()->user()->name)
                                )
                                <a href="{{ route('qa_items.edit', [$sheet->id, $item->id]) }}"
                                   class="btn btn-sm btn-outline-primary">Edit</a>
                                @endif

                                {{-- DELETE --}}
                                @if(auth()->user()->hasRole('Admin'))
                                <form action="{{ route('qa_items.destroy', [$sheet->id, $item->id]) }}"
                                      method="POST" onsubmit="return confirm('Delete item?')">
                                    @csrf @method('DELETE')
                                    <button class="btn btn-sm btn-outline-danger">Delete</button>
                                </form>
                                @endif

                                {{-- START (Engineer / NV only) --}}
                                @if(
                                    auth()->user()->hasRole(['Engineer','Night Vision']) &&
                                    (
                                        $item->status == 'open' ||
(string) $item->assigned_to === (string) auth()->user()->id
                                    )
                                )
                                @if($item->status == 'open')
                                <form action="{{ route('qa_items.updateStatus', $item->id) }}" method="POST">
                                    @csrf
                                    <input type="hidden" name="status" value="in_progress">
                                    <button class="btn btn-sm btn-outline-warning">Start</button>
                                </form>
                                @endif
                                @endif

                                {{-- RESOLVE (Engineer / NV on assigned item) --}}
                                @if(
                                    auth()->user()->hasRole(['Engineer','Night Vision']) &&
                                    in_array($item->status, ['in_progress','needs_info']) &&
(string) $item->assigned_to === (string) auth()->user()->id
                                )
                                <form action="{{ route('qa_items.updateStatus', $item->id) }}" method="POST">
                                    @csrf
                                    <input type="hidden" name="status" value="resolved">
                                    <button class="btn btn-sm btn-outline-success">Resolve</button>
                                </form>
                                @endif

                                {{-- VERIFY (Admin / PM / SR only when resolved) --}}
                                @if(
                                    auth()->user()->hasRole(['Admin','PM','Senior Reviewer']) &&
                                    $item->status == 'resolved'
                                )
                                <form action="{{ route('qa_items.updateStatus', $item->id) }}" method="POST">
                                    @csrf
                                    <input type="hidden" name="status" value="verified">
                                    <button class="btn btn-sm btn-outline-info">Verify</button>
                                </form>
                                @endif

                            </div>
                        </td>
                    </tr>

                    {{-- ADD REVIEW (PM & SR) --}}
                    @if(auth()->user()->hasRole(['PM','Senior Reviewer']))
                    <tr class="bg-light">
                        <td colspan="8" class="p-3">

                            @php
                                // Allowed review statuses by role
                                $allowedReviewStatuses = auth()->user()->hasRole('PM')
                                    ? ['noted','open','resolved','verified','closed']
                                    : ['noted','open','needs_info','verified'];
                            @endphp

                            <form action="{{ route('qa_items.addReview', [$sheet->id, $item->id]) }}" method="POST">
                                @csrf

                                <div class="card border-0 shadow-sm p-3">

                                    <h6 class="fw-bold mb-2">
                                        <i class="bi bi-chat-left-text text-primary me-1"></i> Add Review
                                    </h6>

                                    <div class="row g-2">
                                        <div class="col-md-7">
                                            <textarea name="comment" class="form-control" rows="2" required
                                                      placeholder="Write your comment…"></textarea>
                                        </div>

                                        <div class="col-md-3">
                                            <select name="status" class="form-select" required>
                                                <option value="">Select Status</option>
                                                @foreach($allowedReviewStatuses as $rs)
                                                <option value="{{ $rs }}">{{ ucfirst($rs) }}</option>
                                                @endforeach
                                            </select>
                                        </div>

                                        <div class="col-md-2">
                                            <button class="btn btn-primary w-100">
                                                <i class="bi bi-send"></i> Submit
                                            </button>
                                        </div>
                                    </div>

                                </div>
                            </form>
                        </td>
                    </tr>
                    @endif

                    {{-- REVIEWS --}}
                    @foreach($item->reviews as $review)

                    @php
                        $reviewBadge = match($review->status) {
                            'noted'     => 'secondary',
                            'open'      => 'danger',
                            'resolved'  => 'success',
                            'verified'  => 'primary',
                            'closed'    => 'dark',
                            default     => 'info'
                        };
                    @endphp

                    <tr>
                        <td colspan="8" class="p-3">

                            <div class="card border shadow-sm p-3">

                                {{-- HEADER --}}
                                <div class="d-flex justify-content-between">
                                    <div>
                                        <strong>{{ $review->user->name }}</strong>
                                        <small class="text-muted">({{ $review->role }})</small>
                                        <span class="badge bg-{{ $reviewBadge }} ms-2">{{ ucfirst($review->status) }}</span>
                                    </div>
                                    <span class="text-muted small">
                                        <i class="bi bi-clock me-1"></i>{{ $review->created_at->diffForHumans() }}
                                    </span>
                                </div>

                                {{-- COMMENT --}}
                                <div class="mt-2">{{ $review->comment }}</div>

                                {{-- CONTROLS --}}
                                <div class="mt-3 d-flex gap-2">

                                    {{-- PM: Edit any review --}}
                                    @if(auth()->user()->hasRole('PM'))
                                    <form action="{{ route('qa_items.updateReview', $review->id) }}"
                                          method="POST" class="d-flex gap-2">
                                        @csrf

                                        <input type="text" name="comment" class="form-control form-control-sm"
                                               value="{{ $review->comment }}">

                                        <select name="status" class="form-select form-select-sm">
                                            @foreach(['noted','open','resolved','verified','closed'] as $rs)
                                            <option value="{{ $rs }}" {{ $review->status === $rs ? 'selected' : '' }}>
                                                {{ ucfirst($rs) }}
                                            </option>
                                            @endforeach
                                        </select>

                                        <button class="btn btn-sm btn-success">Save</button>
                                    </form>

                                    <form action="{{ route('qa_items.deleteReview', $review->id) }}"
                                          method="POST" onsubmit="return confirm('Delete?')">
                                        @csrf @method('DELETE')
                                        <button class="btn btn-sm btn-outline-danger">Delete</button>
                                    </form>
                                    @endif

                                    {{-- Senior Reviewer: Edit ONLY own reviews --}}
                                    @if(
                                        auth()->user()->hasRole('Senior Reviewer') &&
                                        $review->user_id == auth()->id()
                                    )
                                    <form action="{{ route('qa_items.updateReview', $review->id) }}"
                                          method="POST" class="d-flex gap-2">
                                        @csrf

                                        <input type="text" name="comment" class="form-control form-control-sm"
                                               value="{{ $review->comment }}">

                                        <select name="status" class="form-select form-select-sm">
                                            @foreach(['noted','open','needs_info','verified'] as $rs)
                                            <option value="{{ $rs }}" {{ $review->status === $rs ? 'selected' : '' }}>
                                                {{ ucfirst($rs) }}
                                            </option>
                                            @endforeach
                                        </select>

                                        <button class="btn btn-sm btn-success">Save</button>
                                    </form>
                                    @endif

                                </div>

                            </div>

                        </td>
                    </tr>
                    @endforeach

                    {{-- ATTACHMENTS --}}
                    <tr>
                        <td colspan="8">
                            <div class="p-3 bg-light border rounded">

                                <h6 class="fw-bold">Attachments</h6>

                                {{-- EXISTING ATTACHMENTS --}}
                                <div class="mb-3">
                                    @foreach($item->attachments as $att)

                                    <div class="mb-3 p-2 border rounded bg-white">

                                        <div class="d-flex align-items-center">

                                            {{-- IMAGE --}}
                                            @if(str_contains($att->mime, 'image'))
                                                <img src="{{ asset('storage/'.$att->path) }}"
                                                     class="me-3 rounded border"
                                                     style="width:80px; height:80px; object-fit:cover; cursor:pointer;"
                                                     data-bs-toggle="modal"
                                                     data-bs-target="#imgModal{{ $att->id }}">

                                                {{-- MODAL --}}
                                                <div class="modal fade" id="imgModal{{ $att->id }}" tabindex="-1">
                                                  <div class="modal-dialog modal-dialog-centered modal-lg">
                                                    <div class="modal-content">
                                                      <div class="modal-body p-0">
                                                        <img src="{{ asset('storage/'.$att->path) }}" class="w-100 rounded">
                                                      </div>
                                                    </div>
                                                  </div>
                                                </div>

                                            {{-- PDF --}}
                                            @elseif(str_contains($att->mime, 'pdf'))
                                                <span class="badge bg-danger me-3">PDF</span>
                                            @endif

                                            {{-- FILENAME --}}
                                            <a href="{{ asset('storage/'.$att->path) }}" target="_blank"
                                               class="fw-bold">{{ $att->filename }}</a>

                                            {{-- DELETE ATTACHMENT --}}
                                            @if(
                                                auth()->user()->hasRole(['Admin','PM','Senior Reviewer']) ||
                                                $att->user_id == auth()->id()
                                            )
                                            <form action="{{ route('attachments.destroy', $att->id) }}"
                                                  method="POST" class="ms-3 d-inline">
                                                @csrf @method('DELETE')
                                                <button class="btn btn-sm btn-outline-danger">Delete</button>
                                            </form>
                                            @endif

                                        </div>

                                        {{-- INLINE PDF VIEW --}}
                                        @if(str_contains($att->mime, 'pdf'))
                                            <iframe src="{{ asset('storage/'.$att->path) }}"
                                                    class="mt-2 rounded border"
                                                    style="width:100%; height:260px;"></iframe>
                                        @endif

                                        <div class="text-muted small mt-2">
                                            Uploaded by <strong>{{ $att->user->name }}</strong> •
                                            {{ $att->created_at->diffForHumans() }}
                                        </div>

                                    </div>

                                    @endforeach
                                </div>

                                {{-- UPLOAD ATTACHMENTS --}}
                                @if(
                                    auth()->user()->hasRole(['Admin','PM','Senior Reviewer']) ||
                                    (
                                        auth()->user()->hasRole(['Engineer','Night Vision']) &&
(string) $item->assigned_to === (string) auth()->user()->id
                                    )
                                )
                                <form action="{{ route('attachments.store', $item->id) }}"
                                      method="POST" enctype="multipart/form-data"
                                      class="d-flex gap-2">
                                    @csrf
                                    <input type="file" name="files[]" class="form-control" multiple required>
                                    <button class="btn btn-primary">Upload</button>
                                </form>
                                @endif

                            </div>
                        </td>
                    </tr>

                    @empty
                    <tr>
                        <td colspan="8" class="text-center py-5 text-muted">
                            No QA items found.
                        </td>
                    </tr>
                    @endforelse

                    </tbody>
                </table>

            </div>
        </div>
    </div>
</div>

@endsection
