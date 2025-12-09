@extends('layouts.app')

@section('content')
<div class="container mt-5">

    <h3 class="mb-4 fw-bold">Import QA Items (Excel)</h3>

    <form action="{{ route('qa.import.preview') }}" method="POST" enctype="multipart/form-data">
        @csrf

        <div class="card p-4 shadow-sm">

            <div class="mb-3">
                <label class="form-label fw-semibold">Project</label>
                <select name="project_id" class="form-select" required>
                    <option value="">-- Select Project --</option>
                    @foreach($projects as $p)
                        <option value="{{ $p->id }}">{{ $p->name }}</option>
                    @endforeach
                </select>
            </div>

            <div class="mb-3">
                <label class="form-label fw-semibold">Phase</label>
                <select name="phase_id" class="form-select" required>
                    <option value="">-- Select Phase --</option>
                    @foreach($phases as $ph)
                        <option value="{{ $ph->id }}">{{ $ph->type }}</option>
                    @endforeach
                </select>
            </div>

            <div class="mb-3">
                <label class="form-label fw-semibold">Sheet</label>
                <select name="sheet_id" class="form-select" required>
                    <option value="">-- Select Sheet --</option>
                    @foreach($sheets as $s)
                        <option value="{{ $s->id }}">
                            Sheet {{ $s->number }} — {{ $s->title }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div class="mb-3">
                <label class="form-label fw-semibold">Excel File</label>
                <input type="file" name="file" class="form-control" accept=".xlsx,.xls" required>
            </div>

            <button class="btn btn-primary mt-3">
                <i class="fas fa-eye me-1"></i> Preview Data
            </button>

        </div>
    </form>
</div>
@endsection
