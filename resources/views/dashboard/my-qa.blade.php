@extends('layouts.app')

@section('content')
<div class="container-fluid px-4 py-4">

    {{-- Header --}}
    <div class="d-flex justify-content-between align-items-center mb-3">
        <div>
            <h2 class="fw-bold mb-1"> My QA Work</h2>
            <p class="text-muted mb-0">Your assigned QA items & deadlines</p>
        </div>

        <a href="{{ route('dashboard') }}" class="btn btn-sm btn-outline-secondary">
            <i class="fas fa-arrow-left me-1"></i> Back
        </a>
    </div>

    {{-- Stats --}}
    <div class="row g-3 mb-4">
        <div class="col-md-3">
            <div class="card shadow-sm border-0 p-3">
                <div class="text-muted small">Total Assigned</div>
                <div class="h3 fw-bold mb-0">{{ $stats['total'] ?? 0 }}</div>
            </div>
        </div>

        <div class="col-md-3">
            <div class="card shadow-sm border-0 p-3">
                <div class="text-muted small">Overdue</div>
                <div class="h3 fw-bold mb-0 text-danger">{{ $stats['overdue'] ?? 0 }}</div>
            </div>
        </div>
    </div>

    {{-- Filters --}}
    <div class="card shadow-sm border-0 mb-4">
        <div class="card-body">
            <form method="GET" class="row g-2 align-items-end">

                <div class="col-md-4">
                    <label class="form-label small text-muted">Search</label>
                    <input name="search" value="{{ request('search') }}" class="form-control form-control-sm"
                           placeholder="ID or description...">
                </div>

                <div class="col-md-2">
                    <label class="form-label small text-muted">Status</label>
                    <select name="status" class="form-select form-select-sm">
                        <option value="">All</option>
                        @foreach(['open','in_progress','resolved','verified','closed'] as $st)
                            <option value="{{ $st }}" @selected(request('status')==$st)>
                                {{ strtoupper(str_replace('_',' ', $st)) }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="col-md-2">
                    <label class="form-label small text-muted">Due</label>
                    <select name="due" class="form-select form-select-sm">
                        <option value="">Any</option>
                        <option value="overdue" @selected(request('due')=='overdue')>Overdue</option>
                        <option value="today" @selected(request('due')=='today')>Due Today</option>
                        <option value="week" @selected(request('due')=='week')>Next 7 Days</option>
                    </select>
                </div>

                <div class="col-md-2">
                    <button class="btn btn-primary btn-sm w-100">
                        Filter
                    </button>
                </div>

                <div class="col-md-2">
                    <a href="{{ route('my.qa') }}" class="btn btn-outline-secondary btn-sm w-100">
                        Clear
                    </a>
                </div>

            </form>
        </div>
    </div>

    {{-- Items --}}
    <div class="card shadow-sm border-0">
        <div class="card-header bg-white fw-bold">
            <i class="fas fa-tasks me-1"></i> Assigned QA Items
        </div>

        <div class="card-body p-0">

            @if($items->isEmpty())
                <div class="text-muted text-center py-5">
                    No assigned QA items found.
                </div>
            @else
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th style="width:85px;">ID</th>
                                <th>Description</th>
                                <th style="width:180px;">Project</th>
                                <th style="width:120px;">Phase</th>
                                <th style="width:120px;">Status</th>
                                <th style="width:120px;">Due</th>
                                <th style="width:150px;" class="text-center">Action</th>
                            </tr>
                        </thead>

                        <tbody>
                        @foreach($items as $row)
                            @php
                                $item    = $row->qaItem;
                                $sheet   = optional($item)->sheet;
                                $phase   = optional($sheet)->phase;
                                $project = optional($phase)->project;
$statusText = $item->status ?? '-';

                                $isOverdue = $row->due_date && \Carbon\Carbon::parse($row->due_date)->isPast()
                                             && !in_array($row->status, ['closed','verified']);
                            @endphp

                            <tr class="{{ $isOverdue ? 'table-danger' : '' }}">
                                <td class="fw-bold">#{{ $item->id ?? '-' }}</td>

                                <td>
                                    {{ \Illuminate\Support\Str::limit($item->item_description ?? '-', 80) }}
                                    @if($isOverdue)
                                        <span class="badge bg-danger ms-2">Overdue</span>
                                    @endif
                                </td>

                                <td>{{ $project->name ?? '-' }}</td>
                                <td>{{ $phase->type ?? '-' }}</td>

                                <td>
    <span class="badge bg-light text-dark border">
        {{ strtoupper(str_replace('_',' ', $statusText)) }}
    </span>
</td>


                                <td>{{ $row->due_date ?? '-' }}</td>

                                <td class="text-center">
                                    @if($sheet && $item)
                               <a href="{{ route('qa_items.index', $item->sheet_id) }}#item-{{ $item->id }}"
                                           class="btn btn-sm btn-outline-primary">
                                            View in Sheet
                                        </a>
                                    @else
                                        <span class="text-muted small">No sheet</span>
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                        </tbody>

                    </table>
                </div>

                {{-- Pagination --}}
                <div class="p-3 d-flex justify-content-center">
                    {{ $items->links('pagination::bootstrap-5') }}
                </div>
            @endif

        </div>
    </div>

</div>
@endsection
