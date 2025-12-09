@extends('layouts.app')

@section('content')
<div class="container-fluid px-4 mt-4">

    {{-- Header --}}
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3">Sheets for Phase: {{ $phase->type }}</h1>

        @if(auth()->user()->hasRole(['Admin','PM','Senior Reviewer','Reviewer']))
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
                        <th>Attachments</th>
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
                                         style="width: {{ $sheet->completionPercentage() }}%">
                                    </div>
                                </div>
                                <small>{{ $sheet->completionPercentage() }}% completed</small>
                            </td>

                            {{-- Attachments Column --}}
                           {{-- Attachments Column --}}
<td style="min-width: 250px;">

    <h6 class="fw-bold mb-2">Attachments</h6>

    {{-- Show attachments --}}
    <div class="mb-2">

        @forelse($sheet->attachments as $att)
            <div class="d-flex align-items-center justify-content-between mb-2 p-2 border rounded bg-light">

                <div class="d-flex align-items-center">
                    {{-- Preview image or PDF badge --}}
                    @if(str_contains($att->mime, 'image'))
                        <img src="{{ asset('storage/'.$att->path) }}"
                             class="rounded me-2 border"
                             style="width:55px; height:55px; object-fit:cover;">
                    @else
                        <span class="badge bg-danger me-2">PDF</span>
                    @endif

                    {{-- File Name Link --}}
                    <a href="{{ asset('storage/'.$att->path) }}" target="_blank">
                        {{ $att->filename }}
                    </a>
                </div>

                {{-- Delete button (only for allowed roles OR file owner) --}}
                @if(auth()->user()->hasRole(['Admin','PM','Senior Reviewer','Reviewer']) 
                    || auth()->id() == $att->user_id)

                    <form action="{{ route('attachments.destroy', $att->id) }}"
                          method="POST"
                          onsubmit="return confirm('Delete this attachment?');">
                        @csrf
                        @method('DELETE')

                        <button class="btn btn-sm btn-outline-danger">
                            ✕
                        </button>
                    </form>

                @endif

            </div>
        @empty
            <small class="text-muted">No attachments</small>
        @endforelse

    </div>

    {{-- Upload form --}}
    <form action="{{ route('attachments.store.sheet', $sheet->id) }}"
          method="POST" enctype="multipart/form-data">
        @csrf
        <input type="file" name="files[]" class="form-control form-control-sm mb-2" multiple required>
        <button class="btn btn-sm btn-primary w-100">Upload</button>
    </form>

</td>


                            {{-- Actions --}}
                            <td>
                                <a href="{{ route('qa_items.index', $sheet->id) }}" class="btn btn-sm btn-info">
                                    QA Items
                                </a>

                                @if($sheet->qaItems->count() == 0)
                                
         <a href="{{ route('checklist.upload') }}" class="btn btn-sm btn-primary">

                                            ⚡ Upload CSV
                                        </a>
                                        
                                
                                {{--<form action="{{ route('sheets.generateFromMaster', $sheet->id) }}"
                                          method="POST" class="d-inline">
                                        @csrf
                                        <button class="btn btn-sm btn-primary">
                                            ⚡ Generate CSV
                                        </button>
                                    </form>
                                    --}}
                                @endif

                                @if(auth()->user()->hasRole(['Admin','PM','Senior Reviewer','Reviewer']))
                                    <a href="{{ route('sheets.edit', $sheet->id) }}"
                                       class="btn btn-sm btn-warning">Edit</a>

                                    <form action="{{ route('sheets.destroy', $sheet->id) }}"
                                          method="POST" class="d-inline">
                                        @csrf
                                        @method('DELETE')
                                        <button onclick="return confirm('Are you sure?')"
                                                class="btn btn-sm btn-danger">Delete</button>
                                    </form>
                                @endif
                            </td>

                        </tr>
                    @empty
                        <tr>
                            <td colspan="9" class="text-center text-muted">No sheets found.</td>
                        </tr>
                    @endforelse
                </tbody>

            </table>
        </div>
    </div>
</div>
@endsection
