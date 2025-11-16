@extends('layouts.app')

@section('content')
<div class="container-fluid px-4 mt-4">

    {{-- FILTER --}}
    <form method="GET" action="{{ route('qa_items.index', $sheet->id) }}" class="mb-3">
        <div class="row g-2">

            <div class="col-md-3">
                <select name="status" class="form-select">
                    <option value="">All Statuses</option>
                    @foreach (\App\Models\QAItem::STATUSES as $s)
                        <option value="{{ $s }}" {{ request('status') === $s ? 'selected' : '' }}>
                            {{ ucfirst(str_replace('_',' ', $s)) }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div class="col-md-3">
                <input type="text" name="assigned_to" value="{{ request('assigned_to') }}"
                       class="form-control" placeholder="Search by assignee…">
            </div>

            <div class="col-md-3">
                <select name="severity" class="form-select">
                    <option value="">All Severities</option>
                    @foreach (['critical','high','medium','low'] as $sev)
                        <option value="{{ $sev }}" {{ request('severity') === $sev ? 'selected' : '' }}>
                            {{ ucfirst($sev) }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div class="col-md-2">
                <button class="btn btn-secondary w-100">Filter</button>
            </div>

            @if(request()->hasAny(['status','assigned_to','severity']))
                <div class="col-md-2">
                    <a href="{{ route('qa_items.index', $sheet->id) }}"
                       class="btn btn-outline-dark w-100">Reset</a>
                </div>
            @endif

        </div>
    </form>


    {{-- HEADER --}}
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3 mb-0">QA Items for Sheet: {{ $sheet->title }}</h1>

        <div class="d-flex gap-2">

            @if(auth()->user()->hasRole(['Admin','PM','Senior Reviewer']))
                <a href="{{ route('qa_items.create', $sheet->id) }}" class="btn btn-primary">+ Add QA Item</a>
                <a href="{{ route('qa_items.importForm', $sheet->id) }}" class="btn btn-success">📥 Import</a>
            @endif

            @if(auth()->user()->hasRole(['PM','Senior Reviewer']))
                <form action="{{ route('qa_items.verifyAll', $sheet->id) }}" method="POST" class="d-inline">
                    @csrf
                    <button class="btn btn-outline-success">✔ Verify All</button>
                </form>
            @endif

        </div>
    </div>


    {{-- FLASH --}}
    @if(session('success')) <div class="alert alert-success">{{ session('success') }}</div> @endif
    @if(session('error')) <div class="alert alert-danger">{{ session('error') }}</div> @endif



    {{-- TABLE --}}
    <div class="card shadow-sm border-0">
        <div class="card-body">

            <table class="table table-striped align-middle">
                <thead class="table-dark">
                <tr>
                    <th>ID</th>
                    <th>Description</th>
                    <th>Status</th>
                    <th>Due Date</th>
                    <th>Assigned To</th>
                    <th>Severity</th>
                    <th>Progress</th>
                    <th style="width: 350px;">Actions</th>
                </tr>
                </thead>

                <tbody>

                @forelse ($sheet->qaItems as $item)

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

                    <tr>
                        <td>{{ $item->id }}</td>
                        <td>{{ $item->item_description }}</td>

                        <td><span class="badge bg-{{ $badge }}">{{ ucfirst(str_replace('_',' ', $item->status)) }}</span></td>

                        <td>{{ $item->due_date ?? '—' }}</td>
                        <td>{{ $item->assigned_to ?? '—' }}</td>

                        <td><span class="badge bg-{{ $sevBadge }}">{{ ucfirst($item->severity) }}</span></td>

                        <td>
                            <div class="progress" style="height: 6px;">
                                <div class="progress-bar bg-success"
                                     style="width: {{ $item->completionPercentage() }}%"></div>
                            </div>
                            <small>{{ $item->completionPercentage() }}% completed</small>
                        </td>


                        {{-- ACTIONS --}}
                        <td>

                            {{-- Edit --}}
                            @if(
                                auth()->user()->hasRole(['Admin','PM','Senior Reviewer']) ||
                                (auth()->user()->hasRole(['Engineer','Night Vision']) &&
                                  $item->assigned_to == auth()->user()->name)
                            )
                                <a href="{{ route('qa_items.edit', [$sheet->id, $item->id]) }}"
                                   class="btn btn-sm btn-warning">Edit</a>
                            @endif

                            {{-- Delete --}}
                            @if(auth()->user()->hasRole(['Admin','PM']))
                                <form action="{{ route('qa_items.destroy', [$sheet->id, $item->id]) }}"
                                      method="POST" class="d-inline">
                                    @csrf @method('DELETE')
                                    <button onclick="return confirm('Delete this item?')"
                                            class="btn btn-sm btn-danger">Delete</button>
                                </form>
                            @endif


                            {{-- Start --}}
                            @if(auth()->user()->hasRole(['Admin','PM','Senior Reviewer','Engineer','Night Vision']))
                                <form action="{{ route('qa_items.updateStatus', $item->id) }}"
                                      method="POST" class="d-inline">
                                    @csrf
                                    <input type="hidden" name="status" value="in_progress">
                                    <button class="btn btn-sm btn-warning">Start</button>
                                </form>
                            @endif

                            {{-- Resolve --}}
                            @if(auth()->user()->hasRole(['Admin','PM','Senior Reviewer','Engineer','Night Vision']))
                                <form action="{{ route('qa_items.updateStatus', $item->id) }}"
                                      method="POST" class="d-inline">
                                    @csrf
                                    <input type="hidden" name="status" value="resolved">
                                    <button class="btn btn-sm btn-success">Resolve</button>
                                </form>
                            @endif

                            {{-- Verify --}}
                            @if(auth()->user()->hasRole(['Admin','PM','Senior Reviewer']))
                                <form action="{{ route('qa_items.updateStatus', $item->id) }}"
                                      method="POST" class="d-inline">
                                    @csrf
                                    <input type="hidden" name="status" value="verified">
                                    <button class="btn btn-sm btn-primary">Verify</button>
                                </form>
                            @endif

                        </td>
                    </tr>



                    {{-- ADD REVIEW --}}
                    @if(auth()->user()->hasRole(['PM','Senior Reviewer']))
                        <tr>
                            <td colspan="8">
                                <form action="{{ route('qa_items.addReview', [$sheet->id, $item->id]) }}"
                                      method="POST" class="p-3 bg-light border rounded">
                                    @csrf

                                    <div class="row g-2">
                                        <div class="col-md-6">
                                            <textarea name="comment" class="form-control" rows="2"
                                                      placeholder="Add review…" required></textarea>
                                        </div>

                                        <div class="col-md-3">
                                            <select name="status" class="form-select" required>
                                                <option value="">-- Select Status --</option>
                                                @foreach(['noted','open','resolved','verified','closed'] as $rs)
                                                    <option value="{{ $rs }}">{{ ucfirst($rs) }}</option>
                                                @endforeach
                                            </select>
                                        </div>

                                        <div class="col-md-3">
                                            <button class="btn btn-outline-primary w-100">💬 Add Review</button>
                                        </div>
                                    </div>

                                </form>
                            </td>
                        </tr>
                    @endif



                    {{-- REVIEW ROWS --}}
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
                            <td colspan="8">
                                <div class="p-3 border rounded bg-white shadow-sm">

                                    <div>
                                        <strong>{{ $review->user->name }}</strong>
                                        <span class="text-muted">({{ $review->role }})</span>

                                        <span class="badge bg-{{ $reviewBadge }} ms-2">
                                            {{ ucfirst($review->status) }}
                                        </span>

                                        <div class="mt-1">{{ $review->comment }}</div>
                                        <div class="text-muted small mt-1">
                                            {{ $review->created_at->diffForHumans() }}
                                        </div>
                                    </div>


                                    {{-- PM CAN EDIT ANY REVIEW --}}
                                    @if(auth()->user()->hasRole('PM'))
                                        <div class="mt-3">

                                            <form action="{{ route('qa_items.updateReview', $review->id) }}"
                                                  method="POST" class="d-inline">
                                                @csrf
                                                <input type="text" name="comment"
                                                       value="{{ $review->comment }}"
                                                       class="form-control mb-1" required>

                                                <select name="status"
                                                        class="form-select form-select-sm mb-1">
                                                    @foreach(['noted','open','resolved','verified','closed'] as $rs)
                                                        <option value="{{ $rs }}"
                                                            {{ $review->status === $rs ? 'selected' : '' }}>
                                                            {{ ucfirst($rs) }}
                                                        </option>
                                                    @endforeach
                                                </select>

                                                <button class="btn btn-sm btn-outline-success">Save</button>
                                            </form>

                                            <form action="{{ route('qa_items.deleteReview', $review->id) }}"
                                                  method="POST" class="d-inline">
                                                @csrf @method('DELETE')
                                                <button onclick="return confirm('Delete this review?')"
                                                        class="btn btn-sm btn-outline-danger">Delete</button>
                                            </form>

                                        </div>
                                    @endif


                                    {{-- Senior Reviewer can edit ONLY own --}}
                                    @if(auth()->user()->hasRole('Senior Reviewer') &&
                                        $review->user_id == auth()->id())

                                        <div class="mt-3">

                                            <form action="{{ route('qa_items.updateReview', $review->id) }}"
                                                  method="POST" class="d-inline">
                                                @csrf
                                                <input type="text" name="comment"
                                                       value="{{ $review->comment }}"
                                                       class="form-control mb-1" required>

                                                <select name="status"
                                                        class="form-select form-select-sm mb-1">
                                                    @foreach(['noted','open','resolved','verified','closed'] as $rs)
                                                        <option value="{{ $rs }}"
                                                            {{ $review->status === $rs ? 'selected' : '' }}>
                                                            {{ ucfirst($rs) }}
                                                        </option>
                                                    @endforeach
                                                </select>

                                                <button class="btn btn-sm btn-outline-success">Save</button>
                                            </form>

                                            <form action="{{ route('qa_items.deleteReview', $review->id) }}"
                                                  method="POST" class="d-inline">
                                                @csrf @method('DELETE')
                                                <button onclick="return confirm('Delete this review?')"
                                                        class="btn btn-sm btn-outline-danger">Delete</button>
                                            </form>

                                        </div>

                                    @endif

                                </div>
                            </td>
                        </tr>

                    @endforeach



                    {{-- ATTACHMENTS --}}
                    <tr>
                        <td colspan="8">
                            <div class="p-3 bg-light border rounded">

                                <h6 class="fw-bold">Attachments</h6>

                                {{-- SHOW ATTACHMENTS --}}
                                <div class="mb-3">
                                    @foreach($item->attachments as $att)

                                        <div class="mb-3 p-2 border rounded bg-white">

                                            <div class="d-flex align-items-center">

                                                {{-- IMAGE PREVIEW --}}
                                                @if(str_contains($att->mime, 'image'))
                                                    <img src="{{ asset('storage/'.$att->path) }}"
                                                         class="me-3 rounded border"
                                                         style="width:80px; height:80px; object-fit:cover; cursor:pointer;"
                                                         data-bs-toggle="modal"
                                                         data-bs-target="#imgModal{{ $att->id }}">

                                                    {{-- IMAGE MODAL --}}
                                                    <div class="modal fade" id="imgModal{{ $att->id }}" tabindex="-1">
                                                      <div class="modal-dialog modal-dialog-centered modal-lg">
                                                        <div class="modal-content">
                                                          <div class="modal-body p-0">
                                                            <img src="{{ asset('storage/'.$att->path) }}" class="w-100 rounded">
                                                          </div>
                                                        </div>
                                                      </div>
                                                    </div>

                                                {{-- PDF PREVIEW --}}
                                                @elseif(str_contains($att->mime, 'pdf'))
                                                    <span class="badge bg-danger me-3">PDF</span>
                                                @endif

                                                {{-- FILE NAME --}}
                                                <a href="{{ asset('storage/'.$att->path) }}" target="_blank"
                                                   class="fw-bold">{{ $att->filename }}</a>

                                                {{-- DELETE BUTTON --}}
                                                @if(auth()->user()->hasRole(['Admin','PM','Senior Reviewer','Reviewer']) ||
                                                   $att->user_id == auth()->id())

                                                    <form action="{{ route('attachments.destroy', $att->id) }}"
                                                          method="POST" class="ms-3 d-inline">
                                                        @csrf @method('DELETE')
                                                        <button class="btn btn-sm btn-outline-danger">Delete</button>
                                                    </form>

                                                @endif

                                            </div>

                                            {{-- PDF INLINE VIEWER --}}
                                            @if(str_contains($att->mime, 'pdf'))
                                                <iframe src="{{ asset('storage/'.$att->path) }}"
                                                        class="mt-2 rounded border"
                                                        style="width:100%; height:260px;"></iframe>
                                            @endif

                                            {{-- UPLOADED INFO --}}
                                            <div class="text-muted small mt-2">
                                                Uploaded by <strong>{{ $att->user->name }}</strong> •
                                                {{ $att->created_at->diffForHumans() }}
                                            </div>

                                        </div>

                                    @endforeach
                                </div>



                                {{-- UPLOAD FORM --}}
        @if(auth()->user()->hasRole(['Admin','PM','Senior Reviewer','Engineer','Night Vision','Reviewer']) ||
                                                    $item->assigned_to == auth()->user()->name)
                              <form action="{{ route('attachments.store', $item->id) }}"
      method="POST" enctype="multipart/form-data" class="d-flex gap-2">
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
                        <td colspan="8" class="text-center text-muted py-3">No QA items found.</td>
                    </tr>
                @endforelse

                </tbody>
            </table>

        </div>
    </div>

</div>
@endsection
