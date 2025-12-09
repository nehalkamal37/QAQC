@extends('layouts.app')

@section('content')


<div class="upload-container" style="max-width: 600px; margin: 50px auto;">

    <div class="card shadow-lg border-0">


        <div class="card-header bg-primary text-white py-3">
            <h4 class="mb-0">
                <i class="fas fa-file-pdf me-2"></i> Upload PDF Checklist
            </h4>
        </div>

        <div class="card-body p-4">

            {{-- SUCCESS MESSAGE --}}
            @if(session('success'))
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    <strong>Success!</strong> {{ session('success') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            @endif

            {{-- ERROR MESSAGE --}}
            @if(session('error'))
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <strong>Error!</strong> {{ session('error') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            @endif

            {{-- UPLOAD FORM --}}
            <form action="{{ route('checklist.upload.save') }}" method="POST" enctype="multipart/form-data">
                @csrf

                {{-- PROJECT --}}
                <div class="mb-4">
                    <label class="form-label fw-bold">Select Project:</label>
                    <select id="project_id" name="project_id" class="form-control form-control-lg" required>
                        <option value="">-- Select Project --</option>
                        @foreach($projects as $project)
                            <option value="{{ $project->id }}">{{ $project->name }}</option>
                        @endforeach
                    </select>
                </div>

                {{-- PHASE --}}
                <div class="mb-4">
                    <label class="form-label fw-bold">Select Phase:</label>
                    <select id="phase_id" name="phase_id" class="form-control form-control-lg" required disabled>
                        <option value="">-- Select Phase --</option>
                    </select>
                </div>

                {{-- SHEET --}}
                <div class="mb-4">
                    <label class="form-label fw-bold">Select Sheet:</label>
                    <select id="sheet_id" name="sheet_id" class="form-control form-control-lg" required disabled>
                        <option value="">-- Select Sheet --</option>
                    </select>
                </div>

                {{-- PDF --}}
                <div class="mb-4">
                    <label for="checklist_pdf" class="form-label fw-bold">Select Checklist PDF:</label>
                    <input 
                        type="file" 
                        class="form-control form-control-lg" 
                        id="checklist_pdf" 
                        name="checklist_pdf" 
                        required 
                        accept=".pdf">
                    <div class="form-text">Upload the checklist PDF with form checkboxes.</div>
                </div>

                <button type="submit" class="btn btn-primary btn-lg w-100">
                    <i class="fas fa-upload me-2"></i> Upload & Save to Database
                </button>
          

            </form>
<br>
<a href="{{ route('checklist.upload.csv') }}" 
   class="btn btn-info btn-lg w-100 mb-3 d-flex mt-11 align-items-center justify-content-center"
   style="font-weight: 600;">
    <i class="fas fa-file-csv me-2"></i> Upload CSV
</a>

        </div>
     
    </div>
</div>

{{-- نمرّر الداتا من PHP إلى JS --}}
<script>
document.addEventListener("DOMContentLoaded", function () {

    const projectSelect = document.getElementById("project_id");
    const phaseSelect   = document.getElementById("phase_id");
    const sheetSelect   = document.getElementById("sheet_id");

    // نبني داتا المشاريع + الفيز + الشيتس من السيرفر
 const projectsData = @json($projectsJson);

    function resetPhaseSelect() {
        phaseSelect.innerHTML = '<option value="">-- Select Phase --</option>';
        phaseSelect.disabled = true;
    }

    function resetSheetSelect() {
        sheetSelect.innerHTML = '<option value="">-- Select Sheet --</option>';
        sheetSelect.disabled = true;
    }

    // لما المشروع يتغيّر
    projectSelect.addEventListener("change", function () {
        const projectId = parseInt(this.value);
        resetPhaseSelect();
        resetSheetSelect();

        if (!projectId) return;

        const project = projectsData.find(p => p.id === projectId);
        if (!project || !project.phases || project.phases.length === 0) {
            phaseSelect.innerHTML = '<option value="">No phases found</option>';
            return;
        }

        phaseSelect.disabled = true;
        phaseSelect.innerHTML = '<option value="">-- Select Phase --</option>';

        project.phases.forEach(ph => {
            const opt = document.createElement('option');
            opt.value = ph.id;
            opt.textContent = ph.type;
            phaseSelect.appendChild(opt);
        });

        phaseSelect.disabled = false;
    });

    // لما الفيز تتغيّر
    phaseSelect.addEventListener("change", function () {
        const phaseId = parseInt(this.value);
        resetSheetSelect();

        if (!phaseId) return;

        const projectId = parseInt(projectSelect.value);
        const project   = projectsData.find(p => p.id === projectId);
        if (!project) return;

        const phase = project.phases.find(ph => ph.id === phaseId);
        if (!phase || !phase.sheets || phase.sheets.length === 0) {
            sheetSelect.innerHTML = '<option value="">No sheets found</option>';
            return;
        }

        sheetSelect.disabled = true;
        sheetSelect.innerHTML = '<option value="">-- Select Sheet --</option>';

        phase.sheets.forEach(s => {
            const opt = document.createElement('option');
            const label = `${s.number || ''} ${s.title ? '- ' + s.title : ''}`;
            opt.value = s.id;
            opt.textContent = label;
            sheetSelect.appendChild(opt);
        });

        sheetSelect.disabled = false;
    });

});
</script>

@endsection
