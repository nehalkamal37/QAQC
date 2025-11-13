@extends('layouts.app')

@section('content')
<div class="container-fluid px-4 mt-4">

    {{-- 🔹 Filter --}}
    <form method="GET" action="{{ route('qa_items.index', $sheet->id) }}" class="mb-3">
        <div class="row g-2">

            {{-- Status Filter --}}
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

            {{-- Assigned To Filter --}}
            <div class="col-md-3">
                <input type="text" name="assigned_to" value="{{ request('assigned_to') }}"
                    class="form-control" placeholder="Search by assignee…">
            </div>

            {{-- Severity Filter --}}
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

            {{-- Buttons --}}
            <div class="col-md-2">
                <button class="btn btn-secondary w-100">Filter</button>
            </div>

            @if(request()->hasAny(['status','assigned_to','severity']))
                <div class="col-md-2">
                    <a href="{{ route('qa_items.index', $sheet->id) }}" class="btn btn-outline-dark w-100">Reset</a>
                </div>
            @endif
        </div>
    </form>

    {{-- 🔹 Header --}}
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3 mb-0">QA Items for Sheet: {{ $sheet->title }}</h1>

        <div class="d-flex gap-2">
            @if(auth()->user()->hasRole(['Admin','PM','Reviewer']))
                <a href="{{ route('qa_items.create', $sheet->id) }}" class="btn btn-primary">+ Add QA Item</a>
                <a href="{{ route('qa_items.importForm', $sheet->id) }}" class="btn btn-success">📥 Import</a>
            @endif

            @if(auth()->user()->hasRole(['Reviewer','PM']))
                <form action="{{ route('qa_items.verifyAll', $sheet->id) }}" method="POST" class="d-inline">
                    @csrf
                    <button class="btn btn-outline-success">✔ Verify All</button>
                </form>
            @endif
        </div>
    </div>

    {{-- 🔹 Flash --}}
    @if(session('success')) <div class="alert alert-success">{{ session('success') }}</div> @endif
    @if(session('error')) <div class="alert alert-danger">{{ session('error') }}</div> @endif

    {{-- 🔹 Table --}}
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
                        <th style="width: 340px;">Actions</th>
                    </tr>
                </thead>

                <tbody>
                @forelse ($sheet->qaItems as $item)

                    {{-- BADGES --}}
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

                    {{-- QA ITEM ROW --}}
                    <tr>
                        <td>{{ $item->id }}</td>
                        <td>{{ $item->item_description }}</td>

                        <td><span class="badge bg-{{ $badge }}">{{ ucfirst(str_replace('_',' ', $item->status)) }}</span></td>

                        <td>{{ $item->due_date ?? '—' }}</td>
                        <td>{{ $item->assigned_to ?? '—' }}</td>

                        <td><span class="badge bg-{{ $sevBadge }}">{{ ucfirst($item->severity) }}</span></td>

                        <td>
                            @if(auth()->user()->hasRole(['Admin','Reviewer','PM']))
                                <a href="{{ route('qa_items.edit', [$sheet->id, $item->id]) }}" class="btn btn-sm btn-warning">Edit</a>

                                <form action="{{ route('qa_items.destroy', [$sheet->id, $item->id]) }}"
                                      method="POST" class="d-inline">
                                    @csrf @method('DELETE')
                                    <button onclick="return confirm('Are you sure?')" class="btn btn-sm btn-danger">Delete</button>
                                </form>
                            @endif

                            {{-- Status Buttons --}}
                            <form action="{{ route('qa_items.updateStatus', $item->id) }}" method="POST" class="d-inline">
                                @csrf
                                <input type="hidden" name="status" value="in_progress">
                                <button class="btn btn-sm btn-warning">Start</button>
                            </form>

                            <form action="{{ route('qa_items.updateStatus', $item->id) }}" method="POST" class="d-inline">
                                @csrf
                                <input type="hidden" name="status" value="resolved">
                                <button class="btn btn-sm btn-success">Resolve</button>
                            </form>

                            <form action="{{ route('qa_items.updateStatus', $item->id) }}" method="POST" class="d-inline">
                                @csrf
                                <input type="hidden" name="status" value="verified">
                                <button class="btn btn-sm btn-primary">Verify</button>
                            </form>

                        </td>
                    </tr>

                    {{-- ADD REVIEW ROW --}}
                    @if(auth()->user()->hasRole(['Reviewer','PM']))
                    <tr>
                        <td colspan="7">
                            <form action="{{ route('qa_items.addReview', [$sheet->id, $item->id]) }}"
                                  method="POST" class="p-3 bg-light border rounded">
                                @csrf
                                <div class="row g-2">
                                    <div class="col-md-6">
                                        <textarea name="comment" class="form-control" rows="2"
                                            placeholder="Add your review comment…" required></textarea>
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

                    {{-- REVIEW ROWS (each review = its own TR) --}}
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
                            <td colspan="7">
                                <div class="p-3 border rounded bg-white shadow-sm">

                                    <div class="d-flex justify-content-between">
                                        <div>
                                            <strong>{{ $review->user->name ?? 'User #'.$review->user_id }}</strong>
                                            <span class="text-muted">({{ $review->role }})</span>
                                            <span class="badge bg-{{ $reviewBadge }} ms-2">{{ ucfirst($review->status) }}</span>

                                            <div class="mt-1">{{ $review->comment }}</div>
                                            <div class="text-muted small mt-1">{{ $review->created_at->diffForHumans() }}</div>
                                        </div>
                                    </div>

                                    {{-- EDIT / DELETE --}}
                                    @if($review->user_id === auth()->id() && auth()->user()->hasRole(['Reviewer','PM']))
                                        <div class="mt-3">

                                            {{-- Edit --}}
                                            <form action="{{ route('qa_items.updateReview', $review->id) }}"
                                                  method="POST" class="d-inline">
                                                @csrf
                                                <input type="text" name="comment" value="{{ $review->comment }}"
                                                    class="form-control mb-1" required>

                                                <select name="status" class="form-select form-select-sm mb-1">
                                                    @foreach(['noted','open','resolved','verified','closed'] as $rs)
                                                        <option value="{{ $rs }}" {{ $review->status === $rs ? 'selected' : '' }}>
                                                            {{ ucfirst($rs) }}
                                                        </option>
                                                    @endforeach
                                                </select>

                                                <button class="btn btn-sm btn-outline-success">Save</button>
                                            </form>

                                            {{-- Delete --}}
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

                @empty
                    <tr>
                        <td colspan="7" class="text-center text-muted py-3">No QA items found.</td>
                    </tr>
                @endforelse
                </tbody>

            </table>
        </div>
    </div>

</div>
@endsection
