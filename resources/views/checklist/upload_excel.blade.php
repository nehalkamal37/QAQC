{{-- resources/views/checklist/upload_excel.blade.php --}}
@extends('layouts.app')

@section('content')
<div class="upload-container" style="max-width: 600px; margin: 50px auto;">
    <div class="card shadow-lg border-0">
        
        <div class="card-header bg-primary text-white py-3">
            <h4 class="mb-0">
                <i class="fas fa-file-excel me-2"></i> Upload Excel Checklist
            </h4>
        </div>

        <div class="card-body p-4">

      {{-- SUCCESS MESSAGE --}}
@if(session('success'))
    <div class="alert border-0 rounded-3 mb-4"
         style="background:#e6fffa; color:#065f46;">
        <div class="d-flex align-items-center">
            <i class="fas fa-check-circle me-2"></i>
            <div>
                <strong>Success</strong><br>
                <span class="small">{{ session('success') }}</span>
            </div>
        </div>
    </div>
@endif

{{-- ERROR MESSAGE --}}
@if(session('error'))
    <div class="alert border-0 rounded-3 mb-4"
         style="background:#fee2e2; color:#7f1d1d;">
        <div class="d-flex align-items-center">
            <i class="fas fa-exclamation-triangle me-2"></i>
            <div>
                <strong>Error</strong><br>
                <span class="small">{{ session('error') }}</span>
            </div>
        </div>
    </div>
@endif


            {{-- UPLOAD FORM --}}
            <form action="{{ route('checklist.upload.excel.save') }}" method="POST" enctype="multipart/form-data">
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

                {{-- EXCEL FILE --}}
               {{-- EXCEL FILE --}}
<div class="mb-4">
    <label for="excel_file" class="form-label fw-bold">Select Excel File:</label>

    <div class="d-flex align-items-center gap-3">
        <input
            type="file"
            name="excel_file"
            id="excel_file"
            class="form-control form-control-lg"
            accept=".xlsx,.xls"
            required>

        <!-- File size progress -->
        <div class="flex-grow-1">
            <div class="progress" style="height: 8px;">
                <div
                    id="excelSizeBar"
                    class="progress-bar bg-success"
                    style="width: 0%;">
                </div>
            </div>
            <small class="text-muted" id="excelSizeText">
                No file selected
            </small>
        </div>
    </div>

    <div class="form-text mt-1">
        Upload Excel file with columns: Description, Applicable, Incorporated, Confirmed
    </div>
</div>


                <button type="submit" class="btn btn-success btn-lg w-100">
                    <i class="fas fa-upload me-2"></i> Upload & Process Excel
                </button>

     
                
            </form>
            <br>
<a href="{{ route('checklist.upload') }}" 
   class="btn btn-info btn-lg w-100 mb-3 d-flex mt-11 align-items-center justify-content-center"
   style="font-weight: 600;">
    <i class="fas fa-file-csv me-2"></i> Upload PDF
</a>
        </div>
    </div>
</div>












<script>
document.addEventListener("DOMContentLoaded", function () {

    const input = document.getElementById("excel_file");
    const bar   = document.getElementById("excelSizeBar");
    const text  = document.getElementById("excelSizeText");

    const MAX_MB = 10; // visual max size (adjust if you want)

    input.addEventListener("change", function () {
        if (!this.files || !this.files[0]) {
            bar.style.width = "0%";
            text.textContent = "No file selected";
            return;
        }

        const fileSizeMB = this.files[0].size / (1024 * 1024);
        const percent = Math.min((fileSizeMB / MAX_MB) * 100, 100);

        bar.style.width = percent + "%";
        text.textContent = fileSizeMB.toFixed(2) + " MB";

        // Optional color feedback
        bar.classList.remove("bg-success", "bg-warning", "bg-danger");

        if (fileSizeMB < 5) {
            bar.classList.add("bg-success");
        } else if (fileSizeMB < 10) {
            bar.classList.add("bg-warning");
        } else {
            bar.classList.add("bg-danger");
        }
    });

});
</script>


<script>
document.addEventListener("DOMContentLoaded", function () {
    const projectSelect = document.getElementById("project_id");
    const phaseSelect   = document.getElementById("phase_id");
    const sheetSelect   = document.getElementById("sheet_id");

    const projectsData = @json($projectsJson);

    function resetPhaseSelect() {
        phaseSelect.innerHTML = '<option value="">-- Select Phase --</option>';
        phaseSelect.disabled = true;
    }

    function resetSheetSelect() {
        sheetSelect.innerHTML = '<option value="">-- Select Sheet --</option>';
        sheetSelect.disabled = true;
    }

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