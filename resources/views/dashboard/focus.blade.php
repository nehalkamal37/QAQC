@extends('layouts.app')

@section('content')
<div class="container-fluid px-4 py-4">

    {{-- HEADER --}}
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 class="fw-bold mb-1">⭐ My Focus</h2>
            <p class="text-muted mb-0">
                Quick useful items for your role:
                <strong class="text-primary">{{ strtoupper($role) }}</strong>
            </p>
        </div>

        <a href="{{ route('dashboard') }}" class="btn btn-sm btn-outline-secondary">
            <i class="fas fa-arrow-left me-1"></i> Back to Dashboard
        </a>
    </div>

    {{-- CARDS --}}
    <div class="row g-3 mb-4">
        <div class="col-md-4">
            <div class="card shadow-sm border-0 p-3">
                <div class="text-muted small">Assigned Projects</div>
                <div class="h3 fw-bold mb-0">{{ $stats['assigned_projects'] ?? 0 }}</div>
            </div>
        </div>

        <div class="col-md-4">
            <div class="card shadow-sm border-0 p-3">
                <div class="text-muted small">My Assigned QA Items</div>
                <div class="h3 fw-bold mb-0">{{ $stats['my_items'] ?? 0 }}</div>
            </div>
        </div>

        <div class="col-md-4">
            <div class="card shadow-sm border-0 p-3">
                <div class="text-muted small">Overdue Items</div>
                <div class="h3 fw-bold mb-0 text-danger">{{ $stats['my_overdue'] ?? 0 }}</div>
            </div>
        </div>
    </div>

    {{-- MY PROJECTS LIST --}}
    <div class="card shadow-sm border-0 mb-4">
        <div class="card-header bg-white fw-bold">
            <i class="fas fa-folder-open me-1"></i> My Projects
        </div>
        <div class="card-body">
            @if(($projects ?? collect())->isEmpty())
                <div class="text-muted">No assigned projects found.</div>
            @else
                <ul class="mb-0">
                    @foreach($projects as $p)
                        <li class="mb-1">
                            <strong>{{ $p->name }}</strong>
                            <a href="/projects/{{ $p->id }}" class="ms-2 small text-decoration-none">
                                View
                            </a>
                        </li>
                    @endforeach
                </ul>
            @endif
        </div>
    </div>


    {{-- ✅ MY ASSIGNED QA ITEMS FROM project_qa_item_status --}}
    <div class="card shadow-sm border-0 mb-4">
        <div class="card-header bg-white fw-bold d-flex justify-content-between align-items-center">
            <div>
                <i class="fas fa-tasks me-1"></i> My Assigned QA Items (Latest 20)
                <span class="text-muted small ms-2">(from project status assignments)</span>
            </div>

           
        </div>

        <div class="card-body table-responsive">
            @if(($myAssignedStatuses ?? collect())->isEmpty())
                <div class="text-muted">No assigned QA items yet.</div>
            @else
                <table class="table table-hover align-middle">
                    <thead class="table-light">
                        <tr>
                            <th style="width: 90px;">ID</th>
                            <th>Description</th>
                            <th>Project</th>
                            <th>Phase</th>
                            <th>Status</th>
                            <th style="width: 120px;">Due</th>
                            <th class="text-center" style="width: 140px;">Action</th>
                        </tr>
                    </thead>

                    <tbody>
                        @foreach($myAssignedStatuses as $row)

                            @php
                                $item    = $row->qaItem; // QaItem model
                                $sheet   = optional($item)->sheet;
                                $phase   = optional($sheet)->phase;
                                $project = optional($phase)->project;

                                // derived status fallback
                                $statusText = $row->derived_status ?? ($item->status ?? '-');
                            @endphp

                            <tr>
                              
     <td class="fw-bold">
                            <a href="{{ route('qa_items.index', $item->sheet_id) }}#item-{{ $item->id }}"
                               class="text-primary">
                                #{{ $item->id  ?? '-'}}
                            </a>
                        </td>
                                <td>{{ \Illuminate\Support\Str::limit($item->item_description ?? '-', 60) }}</td>

                                <td>{{ $project->name ?? '-' }}</td>
                                <td>{{ $phase->type ?? '-' }}</td>

                                <td>
                                    <span class="badge bg-light text-dark border">
                                        {{ $statusText }}
                                    </span>
                                </td>

                                <td>
                                    {{ $row->due_date ?? '-' }}
                                </td>

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

      
        @if($myAssignedStatuses->hasPages())
        <div class="timeline-pagination mt-3">
        {{ $myAssignedStatuses->links() }}
    </div>
@endif

            @endif
        </div>
    </div>


<style>
.timeline-pagination {
    display: flex;
    justify-content: center;
}

.timeline-pagination ul.pagination {
    display: flex;
    flex-direction: row !important;
    align-items: center;
    gap: 2px;
}

.timeline-pagination .page-item {
    display: inline-block;
}

.timeline-pagination .page-link {
    padding: 4px 8px;
    font-size: 3px;
    line-height: 1.2;
}


    </style>


    {{-- ✅ MY PROJECTS PROGRESS (SCOPED) --}}
    <div class="card shadow-sm border-0">
        <div class="card-header bg-white fw-bold">
            <i class="fas fa-chart-line me-1"></i> My Projects Progress
            <span class="text-muted small ms-2">(only projects assigned to you)</span>
        </div>

        <div class="card-body">
            @if(($myProjectsProgress ?? collect())->isEmpty())
                <div class="text-muted text-center py-4">
                    No assigned projects found.
                </div>
            @else
                @foreach($myProjectsProgress as $p)

                    <div class="border rounded p-3 mb-3 shadow-sm">

                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <strong class="text-primary">{{ $p->name }}</strong>
                            <span class="badge bg-primary px-3 py-2">{{ $p->progress }}%</span>
                        </div>

                        <div class="progress mb-3" style="height:10px;">
                            <div class="progress-bar" style="width: {{ $p->progress }}%"></div>
                        </div>

                        {{-- PHASES --}}
                        @foreach($p->phases as $phase)
                            <div class="mb-3">
                                <div class="d-flex justify-content-between align-items-center mb-1">
                                    <span class="fw-semibold">{{ $phase->type }}</span>
                                    <span class="text-muted small">{{ $phase->progress }}%</span>
                                </div>

                                <div class="progress" style="height:7px;">
                                    <div class="progress-bar bg-success" style="width: {{ $phase->progress }}%"></div>
                                </div>

                                {{-- SHEETS --}}
                                <div class="d-flex flex-wrap gap-2 mt-2">
                                    @foreach($phase->sheets as $sheet)
                                        <span class="badge bg-light text-dark border" title="{{ $sheet->title }}">
                                            {{ $sheet->number }} • {{ $sheet->progress }}%
                                        </span>
                                    @endforeach
                                </div>
                            </div>
                        @endforeach

                    </div>

                @endforeach
            @endif
        </div>
    </div>

</div>
@endsection
