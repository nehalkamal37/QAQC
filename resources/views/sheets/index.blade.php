@extends('layouts.app')

@section('content')
<div class="container-fluid px-4 mt-4">

    {{-- Header --}}
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3">Sheets for Phase: {{ $phase->type }}</h1>

        {{-- Add Sheet Allowed Roles: Admin, PM, Senior Reviewer, Reviewer --}}
        @if(auth()->user()->hasRole(['Admin', 'PM', 'Senior Reviewer', 'Reviewer']))
            <a href="{{ route('sheets.create', $phase->id) }}" class="btn btn-primary">+ Add Sheet</a>
        @endif
    </div>

    {{-- Flash --}}
    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    <div class="card shadow-sm">
        <div class="card-body">
            <table class="table table-striped align-middle">
                <thead class="table-dark">
                    <tr>
                        <th>ID</th>
                        <th>Discipline</th>
                        <th>Number</th>
                        <th>Title</th>
                        <th>Version</th>
                        <th>Status</th>
                        <th>Progress</th>
                        <th>Actions</th>
                    </tr>
                </thead>

                <tbody>
                    @forelse ($phase->sheets as $sheet)
                        <tr>
                            <td>{{ $sheet->id }}</td>
                            <td>{{ $sheet->discipline }}</td>
                            <td>{{ $sheet->number }}</td>
                            <td>{{ $sheet->title }}</td>
                            <td>{{ $sheet->version }}</td>

                            {{-- Status --}}
                            <td>
                                <span class="badge bg-{{ $sheet->status == 'approved' ? 'success' : 'warning' }}">
                                    {{ ucfirst($sheet->status) }}
                                </span>
                            </td>

                            {{-- Progress --}}
                            <td>
                                <div class="progress" style="height: 6px;">
                                    <div class="progress-bar bg-success" role="progressbar"
                                         style="width: {{ $sheet->completionPercentage() }}%"></div>
                                </div>
                                <small>{{ $sheet->completionPercentage() }}% completed</small>
                            </td>

                            {{-- Actions --}}
                            <td>

                                {{-- Everyone can open QA Items --}}
                                <a href="{{ route('qa_items.index', $sheet->id) }}" class="btn btn-sm btn-info">
                                    QA Items
                                </a>

                                {{-- Generate from Master when empty --}}
                                @if($sheet->qaItems->count() == 0)
                                    <form action="{{ route('sheets.generateFromMaster', $sheet->id) }}"
                                          method="POST" class="d-inline">
                                        @csrf
                                        <button class="btn btn-sm btn-primary">
                                            ⚡ Generate from Master CSV
                                        </button>
                                    </form>
                                @endif


                                {{-- Edit/Delete Allowed Roles --}}
                                @if(auth()->user()->hasRole(['Admin', 'PM', 'Senior Reviewer', 'Reviewer']))
                                    <a href="{{ route('sheets.edit', $sheet->id) }}" class="btn btn-sm btn-warning">
                                        Edit
                                    </a>

                                    <form action="{{ route('sheets.destroy', $sheet->id) }}"
                                          method="POST" class="d-inline">
                                        @csrf
                                        @method('DELETE')
                                        <button onclick="return confirm('Are you sure?')"
                                                class="btn btn-sm btn-danger">
                                            Delete
                                        </button>
                                    </form>
                                @endif

                            </td>

                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="text-center text-muted">No sheets found.</td>
                        </tr>
                    @endforelse
                </tbody>

            </table>
        </div>
    </div>

</div>
@endsection
