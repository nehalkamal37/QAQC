@extends('layouts.app')

@section('content')
<div class="container-fluid px-4 py-4 dashboard-container">

    <!-- Page Header -->


   <div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h1 class="h3 fw-bold mb-1 text-dark">Dashboard Overview</h1>
        <p class="text-muted mb-0">Welcome back! Here's your project summary.</p>
    </div>

    <div class="d-flex align-items-center gap-3">
        <span class="text-muted">{{ now()->format('l, F j, Y') }}</span>

        <!-- ⭐ New: Focus Page -->
        <a href="{{ route('my.qa') }}" 
           class="btn btn-sm btn-warning shadow-sm d-flex align-items-center"
           style="border-radius:12px;">
            <i class="fas fa-star me-1"></i>
            Focus
        </a>
    </div>
</div>

<div class="section-card mb-4">

    <!-- HEADER + FILTER -->
    <div class="section-header d-flex justify-content-between align-items-center">
        <div>
            <h5 class="fw-bold mb-0"> Project QA Progress</h5>
            <span class="text-muted small">Visual progress across projects, phases & sheets</span>
        </div>

        <select id="projectProgressFilter" class="form-select form-select-sm" style="width:200px;">
            <option value="">Latest Project</option>
            @foreach($projects ?? [] as $proj)

                <option value="{{ $proj->id }}">{{ $proj->name }}</option>
            @endforeach
        </select>
    </div>

    <!-- PROJECT RESULTS CONTAINER -->
    <div id="projectProgressContainer" class="mt-3">

        <div class="text-center text-muted py-4">
            Select a project from the filter above to view progress.
        </div>

    </div>
</div>



{{-- Sheet Heatmap + SLA Analytics --}}

<!-- ===================== -->
<!-- 1) SHEET HEATMAP (FULL WIDTH) -->
<!-- ===================== -->
<div class="col-12">
    <div class="card shadow-sm h-100">
        <div class="card-header d-flex justify-content-between align-items-center">
            <div>
                <h5 class="fw-bold mb-0"> Sheet × Status Heatmap</h5>
                <small class="text-muted">Per-sheet QA distribution — based on A/I/C project status</small>
            </div>


            <div class="d-flex gap-2">

    <!-- PROJECT FILTER -->
    <select id="heatmapProjectFilter" class="form-select form-select-sm" style="width:200px;">
        <option value="">All Projects</option>
@foreach($projects ?? [] as $proj)
            <option value="{{ $proj->id }}">{{ $proj->name }}</option>
        @endforeach
    </select>

    <!-- NEW: PHASE FILTER -->
     
    <select id="heatmapPhaseFilter" class="form-select form-select-sm" style="width:200px;">
        <option value="">All Phases</option>
        {{-- سيتم ملؤها ديناميكياً من الجافاسكربت --}}

    </select>

</div>

        </div>

        <div class="card-body">
            <div class="table-responsive" style="max-height: 360px; overflow-y: auto;">
                <div id="heatmapHeaderInfo" class="fw-bold mb-2" style="font-size:16px;"></div>

                <table class="table table-sm table-hover heatmap-table align-middle">
                    <thead class="table-light sticky-top">
                        <tr>
                            <th>Sheet</th>
                          <th class="text-center">Total</th>

                            <th class="text-center">Open</th>
                            <th class="text-center">In Progress</th>
                            <th class="text-center">Needs Info</th>
                            <th class="text-center">Resolved</th>
                            <th class="text-center">Verified</th>
                            <th class="text-center">Closed</th>
                            <th class="text-center text-primary">A</th>
                            <th class="text-center text-info">I</th>
                            <th class="text-center text-success">C</th>
                        </tr>
                    </thead>
                    <tbody id="sheetHeatmapBody">
                        <tr>
                            <td colspan="12" class="text-center text-muted py-4">
                                Loading heatmap...
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>

             <div class="mt-3">
                <strong class="small">Declare:</strong>
                <div class="d-flex align-items-center gap-3 small mt-1">
                    <span>A --> Applicable</span> 
                    <span>I --> Incorporated</span>
                    <span>C --> Confirmed</span>
                </div>
            </div>
        </div>
    </div>


    
    <!-- ========================= -->
<!-- ========================================== -->
<!--   BEAUTIFUL MODERN QA ITEMS MODAL          -->
<!-- ========================================== -->
<div class="modal fade" id="qaItemsModal" tabindex="-1">
  <div class="modal-dialog modal-xl modal-dialog-scrollable">
      <div class="modal-content shadow-lg border-0" style="border-radius: 14px;">

          <!-- HEADER -->
          <div class="modal-header text-white" 
               style="background: linear-gradient(135deg, #1e3a8a, #3b82f6); border-radius: 14px 14px 0 0;">
              <h5 class="modal-title fw-bold d-flex align-items-center">
                  <i class="fas fa-tasks me-2"></i>
                  QA Items — <span id="modalStatus" class="ms-1"></span>
              </h5>
              <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
          </div>

          <!-- BODY -->
          <div class="modal-body" style="background:#f8fafc;">

              <!-- LOADING -->
              <div id="modalLoading" class="text-center py-5 d-none">
                  <div class="spinner-border text-primary" style="width:3rem; height:3rem;"></div>
                  <p class="mt-3 text-muted fw-semibold">Loading items…</p>
              </div>

              <!-- ITEMS TABLE -->
              <div id="modalItemsContainer" class="table-responsive d-none">
                  <table class="table table-hover align-middle shadow-sm bg-white" 
                         style="border-radius:10px; overflow:hidden;">
                      <thead class="table-light">
                          <tr>
                              <th style="width:70px;">ID</th>
                              <th style="width:35%;">Description</th>
                              <th>Sheet</th>
                              <th>Status</th>
                              <th>Assigned To</th>
                              <th>Due Date</th>
                              <th>Created</th>
                              <th class="text-center">Actions</th>
                          </tr>
                      </thead>
                      <tbody id="modalItemsBody"></tbody>
                  </table>
              </div>

          </div>
      </div>
  </div>
</div>

<style>
    /* Status badges */
    .status-badge {
        padding: 4px 10px;
        border-radius: 20px;
        font-size: 0.75rem;
        font-weight: 600;
        text-transform: capitalize;
    }
    .status-open       { background:#fee2e2; color:#b91c1c; }
    .status-in_progress{ background:#fef3c7; color:#b45309; }
    .status-needs_info { background:#e0f2fe; color:#0369a1; }
    .status-resolved   { background:#dcfce7; color:#166534; }
    .status-verified   { background:#cffafe; color:#0f766e; }
    .status-closed     { background:#e5e7eb; color:#374151; }

    /* A / I / C colors */
    .status-A { background:#dbeafe; color:#1d4ed8; }
    .status-I { background:#e0f2fe; color:#0369a1; }
    .status-C { background:#dcfce7; color:#15803d; }


    
</style>
    
</div>


    <!-- ===================== -->
    <!-- 2) SLA ANALYTICS (RIGHT) -->
    <!-- ===================== -->
     {{--
    <div class="col-lg-5">
        <div class="card shadow-sm h-100">
            <div class="card-header">
                <h5 class="fw-bold mb-0">⏱ SLA & Overdue Analytics</h5>
                <small class="text-muted">Age of active QA items vs SLA target</small>
            </div>

            <div class="card-body">

                <div class="row text-center mb-3">
                    <div class="col-4">
                        <div class="small text-muted">Active</div>
                        <div class="h4 mb-0" id="slaActiveCount">–</div>
                    </div>
                    <div class="col-4">
                        <div class="small text-muted">Overdue</div>
                        <div class="h4 mb-0 text-danger" id="slaOverdueCount">–</div>
                    </div>
                    <div class="col-4">
                        <div class="small text-muted">Avg Age (days)</div>
                        <div class="h4 mb-0" id="slaAvgAge">–</div>
                    </div>
                </div>

                <canvas id="slaAgeBucketsChart" height="110"></canvas>

                <hr>
                <small class="text-muted d-block mt-2">
    <strong> What this chart means:</strong><br>
    • <strong>X-Axis</strong> (horizontal): age groups = how many days each QA item has been open  
      (calculated as: <code>today − created_at</code>).<br>
    • <strong>Y-Axis</strong> (vertical): number of active QA items inside each age group.<br>
   <!-- • This chart shows backlog age only — not overdue status and not related to due dates. -->
</small>

<!--
                <h6 class="fw-bold mb-2">Overdue by Severity</h6>
                <canvas id="slaSeverityChart" height="110"></canvas>

                <small class="text-muted d-block mt-2">
                    SLA target: average age < 7 days. Overdue & age buckets help enforce discipline.
                </small>
-->
            </div>
        </div>
        
    </div>

</div>
--}}
{{-- <div class="card shadow-sm mb-4 mt-4">
    <div class="card-header d-flex justify-content-between align-items-center">
        <div>
            <h5 class="fw-bold mb-0"> Phase Gate Analytics</h5>
            <small class="text-muted">
                Progress, blocking items & signoff readiness for each phase
            </small>
        </div>
    </div>
    <div class="card-body">
        <div id="phaseGatesContainer">
            <div class="text-center text-muted py-3">
                Loading phase analytics...
            </div>
        </div>
    </div>
</div>
--}}


<div class="card shadow-sm mb-4 mt-4 border-0">
    <div class="card-header d-flex justify-content-between align-items-center bg-gradient-primary text-white" style="background: linear-gradient(135deg, #1e3a8a, #3b82f6);">
        <div>
            <h5 class="fw-bold mb-0"> Phase Gate Analytics</h5>
            <small class="text-white-75">
                Progress, blocking items & signoff readiness for each phase
            </small>
        </div>
        <div class="d-flex align-items-center gap-2">
            <button class="btn btn-sm btn-outline-light" id="expandAllPhases">
                <i class="fas fa-expand-alt me-1"></i> Expand All
            </button>
            <button class="btn btn-sm btn-outline-light" id="collapseAllPhases">
                <i class="fas fa-compress-alt me-1"></i> Collapse All
            </button>
        </div>
    </div>
    <div class="card-body p-0">
        <div class="phase-gates-container" id="phaseGatesContainer">
            <!-- Loading State -->
            <div class="text-center py-5">
                <div class="spinner-border text-primary mb-3" style="width: 3rem; height: 3rem;"></div>
                <p class="text-muted">Loading phase analytics...</p>
            </div>
        </div>
    </div>
</div>

<!-- Add this CSS -->
<style>
    .phase-gate-card {
        border: 1px solid #e2e8f0;
        border-radius: 10px;
        margin-bottom: 16px;
        background: white;
        transition: all 0.3s ease;
        overflow: hidden;
    }
    
    .phase-gate-card:hover {
        box-shadow: 0 4px 12px rgba(0,0,0,0.08);
        transform: translateY(-2px);
    }
    
    .phase-gate-header {
        padding: 16px 20px;
        background: #f8fafc;
        border-bottom: 1px solid #e2e8f0;
        cursor: pointer;
        display: flex;
        justify-content: space-between;
        align-items: center;
    }
    
    .phase-gate-title {
        display: flex;
        align-items: center;
        gap: 12px;
    }
    
    .phase-status-badge {
        padding: 4px 12px;
        border-radius: 20px;
        font-size: 0.75rem;
        font-weight: 600;
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }
    
    .phase-status-READY_FOR_SIGNOFF { background: #10b981; color: white; }
    .phase-status-CHANGES_REQUIRED { background: #f59e0b; color: white; }
    .phase-status-IN_REVIEW { background: #3b82f6; color: white; }
    .phase-status-CLOSED { background: #6b7280; color: white; }
    .phase-status-DRAFT { background: #9ca3af; color: white; }
    
    .phase-progress-container {
        flex: 1;
        max-width: 400px;
    }
    
    .phase-gate-content {
        padding: 20px;
        background: white;
    }
    
    .phase-metrics-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(150px, 1fr));
        gap: 16px;
        margin-bottom: 20px;
    }
    
    .phase-metric-card {
        background: #f8fafc;
        border-radius: 8px;
        padding: 16px;
        text-align: center;
        border: 1px solid #e2e8f0;
    }
    
    .phase-metric-value {
        font-size: 1.8rem;
        font-weight: 700;
        color: #1e40af;
        margin-bottom: 4px;
    }
    
    .phase-metric-label {
        font-size: 0.85rem;
        color: #64748b;
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }
    
    .phase-metric-critical {
        color: #dc2626;
    }
    
    .blocking-items-section {
        background: #fef2f2;
        border: 1px solid #fecaca;
        border-radius: 8px;
        padding: 16px;
        margin-top: 20px;
    }
    
    .blocking-item {
        display: flex;
        align-items: center;
        padding: 8px 12px;
        background: white;
        border-radius: 6px;
        margin-bottom: 8px;
        border-left: 4px solid #dc2626;
    }
    
    .blocking-item.severity-high {
        border-left-color: #dc2626;
    }
    
    .blocking-item.severity-medium {
        border-left-color: #f59e0b;
    }
    
    .blocking-item.severity-low {
        border-left-color: #10b981;
    }
    
    .signoff-readiness {
        background: linear-gradient(135deg, #dbeafe, #eff6ff);
        border: 1px solid #93c5fd;
        border-radius: 8px;
        padding: 20px;
        margin-top: 20px;
    }
    
    .readiness-meter {
        height: 8px;
        background: #e2e8f0;
        border-radius: 4px;
        overflow: hidden;
        margin: 12px 0;
    }
    
    .readiness-fill {
        height: 100%;
        background: linear-gradient(90deg, #10b981, #34d399);
        border-radius: 4px;
        transition: width 0.5s ease;
    }
    
    .phase-timeline {
        display: flex;
        align-items: center;
        justify-content: space-between;
        margin-top: 20px;
        padding: 16px;
        background: #f8fafc;
        border-radius: 8px;
    }
    
    .timeline-item {
        display: flex;
        flex-direction: column;
        align-items: center;
        position: relative;
        flex: 1;
    }
    
    .timeline-icon {
        width: 32px;
        height: 32px;
        border-radius: 50%;
        background: #e2e8f0;
        display: flex;
        align-items: center;
        justify-content: center;
        margin-bottom: 8px;
        color: #64748b;
        font-size: 14px;
    }
    
    .timeline-icon.completed {
        background: #10b981;
        color: white;
    }
    
    .timeline-icon.current {
        background: #3b82f6;
        color: white;
        animation: pulse 2s infinite;
    }
    
    @keyframes pulse {
        0% { box-shadow: 0 0 0 0 rgba(59, 130, 246, 0.4); }
        70% { box-shadow: 0 0 0 10px rgba(59, 130, 246, 0); }
        100% { box-shadow: 0 0 0 0 rgba(59, 130, 246, 0); }
    }
    
    .timeline-label {
        font-size: 0.8rem;
        color: #64748b;
        text-align: center;
        margin-top: 4px;
    }
    
    .phase-actions {
        display: flex;
        gap: 8px;
        margin-top: 20px;
        padding-top: 20px;
        border-top: 1px solid #e2e8f0;
    }
    
    .phase-toggle {
        background: none;
        border: none;
        color: #3b82f6;
        cursor: pointer;
        padding: 8px 16px;
        border-radius: 6px;
        transition: background 0.2s;
    }
    
    .phase-toggle:hover {
        background: #f1f5f9;
    }
    
    .no-phases {
        text-align: center;
        padding: 60px 20px;
        color: #64748b;
    }
    
    .no-phases i {
        font-size: 3rem;
        margin-bottom: 16px;
        color: #cbd5e1;
    }
</style>




    <!-- Status & Severity Section -->
    <div class="row g-4 mb-4">
        <!-- Status Cards -->
        <div class="col-lg-6">
            <div class="section-card">
            
                <div class="section-header">
    <h5 class="fw-bold mb-0">Status Overview</h5>
    <span class="text-muted small">
        Aggregated QA item status across all projects.
        <br>
     

</div>

                <div class="row g-3 mt-2">
                    <div class="col-md-6">
                        <div class="status-card status-open">
                            <div class="status-indicator"></div>
                            <div class="status-content">
                                <div class="status-title">Open Items</div>
<div class="status-value">{{ $data['qa_open'] ?? 0 }}</div>

                            </div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="status-card status-pending">
                            <div class="status-indicator"></div>
                            <div class="status-content">
                                <div class="status-title">Pending Items</div>
                                <div class="status-value">{{ $data['qa_pending'] ?? '' }}</div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="status-card status-resolved">
                            <div class="status-indicator"></div>
                            <div class="status-content">
                                <div class="status-title">Resolved Items</div>
                                <div class="status-value">{{ $data['qa_resolved'] ?? 0 }}</div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="status-card status-closed">
                            <div class="status-indicator"></div>
                            <div class="status-content">
                                <div class="status-title">Closed Items</div>
                                <div class="status-value">{{ $data['qa_closed'] ?? 0 }}</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Severity Cards -->
        <div class="col-lg-6">
            <div class="section-card">
                <div class="section-header">
                    <h5 class="fw-bold mb-0"> Severity Levels</h5>
                    <span class="text-muted small">Current QA items severity</span>
                </div>
                <div class="row g-3 mt-2">
                    <div class="col-md-6">
                   <!--     <a href="{{ route('qa_reviews.index', ['severity' => 'critical']) }}" class="text-decoration-none"> -->
                            <div class="severity-card severity-critical">
                               
                                <div class="severity-content">
                                    <div class="severity-title">Critical</div>
                                    <div class="severity-value">{{ $data['qa_critical'] ?? 0 }}</div>
                                </div>
                            </div>
                        
                    </div>
                    <div class="col-md-6">
                            <div class="severity-card severity-high">
                                
                                <div class="severity-content">
                                    <div class="severity-title">High</div>
                                    <div class="severity-value">{{ $data['qa_high'] ?? 0 }} </div>
                                </div>
                            </div>
                        
                    </div>
                    <div class="col-md-6">
                            <div class="severity-card severity-medium">
                        
                                <div class="severity-content">
                                    <div class="severity-title">Medium</div>
                                    <div class="severity-value">{{ $data['qa_medium'] ?? 0 }}</div>
                                </div>
                            </div>
                        
                    </div>
                    <div class="col-md-6">
                            <div class="severity-card severity-low">
                              
                                <div class="severity-content">
                                    <div class="severity-title">Low</div>
                                    <div class="severity-value">{{ $data['qa_low'] ?? 0 }}</div>
                                </div>
                            </div>
                        
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Timeline & Workload Section -->
    <div class="row g-4">
        <!-- Timeline Card -->
        <div class="col-lg-4">
            <a href="{{ route('timeline.index') }}" class="text-decoration-none card-link">
                <div class="timeline-card">
                    <div class="timeline-icon">
                        <i class="fas fa-stream"></i>
                    </div>
                    <div class="timeline-content">
                        <h5 class="fw-bold">Activity Timeline</h5>
                        <p class="text-muted mb-3">View recent project activities and updates</p>
                        <div class="timeline-action">
                            <span class="btn btn-primary btn-sm">View Timeline</span>
                        </div>
                    </div>
                    <div class="timeline-decoration">
                        <div class="decoration-circle circle-1"></div>
                        <div class="decoration-circle circle-2"></div>
                    </div>
                </div>
            </a>
        </div>

        <!-- Reviewer Workload 
      
        <div class="col-lg-8">
            <div class="section-card">
                <div class="section-header d-flex justify-content-between align-items-center">
                    <div>
                        <h5 class="fw-bold mb-0">👥 Reviewer Workload</h5>
                        <span class="text-muted small">Top 5 reviewers by assigned items</span>
                    </div>
                    <div class="workload-legend">
                        <span class="legend-item">
                            <span class="legend-color"></span>
                            <span class="legend-text">Items assigned</span>
                        </span>
                    </div>
                </div>
                <div class="section-body mt-3">
                    @forelse($data['reviewer_load'] as $reviewer)
                        <div class="reviewer-item mb-3">
                            <div class="d-flex justify-content-between align-items-center mb-2">
                                <div class="d-flex align-items-center">
                                    <div class="reviewer-avatar">
                                        {{ substr($reviewer->name, 0, 1) }}
                                    </div>
                                    <strong class="ms-2">{{ $reviewer->name }}</strong>
                                </div>
                                <span class="reviewer-count">{{ $reviewer->qa_items_assigned_count }} items</span>
                            </div>
                            <div class="progress workload-progress">
                                <div class="progress-bar"
                                    role="progressbar"
                                    style="width: {{ min($reviewer->qa_items_assigned_count * 10, 100) }}%;"
                                    aria-valuenow="{{ $reviewer->qa_items_assigned_count }}"
                                    aria-valuemin="0"
                                    aria-valuemax="10">
                                </div>
                            </div>
                        </div>
                    @empty
                        <div class="empty-state">
                            <i class="fas fa-users empty-icon"></i>
                            <p class="text-muted mt-2">No reviewers with assigned items yet.</p>
                        </div>
                    @endforelse
                </div>
            </div>
        </div>
-->
    </div>





</div>

<link rel="stylesheet" href="{{ asset('dash/css/styles.css') }}">

<!-- Font Awesome for Icons -->
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
<!-- Chart.js (v4) -->
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0"></script>

<!-- Chart.js (v4) -->
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0"></script>

<script>

document.addEventListener("DOMContentLoaded", function () {

    /*
    let select = document.getElementById("burndownPhaseFilter");
    let chart = null;

    if (select) {

        select.addEventListener("change", () => {
            const phaseId = select.value;
            if (!phaseId) return;

           fetch(`/analytics/phase-burndown?phase_id=${phaseId}`)      

                .then(r => r.json())
                .then(data => {

                    const ctx = document.getElementById("phaseBurndownChart").getContext("2d");

                    if (chart) chart.destroy();

                    chart = new Chart(ctx, {
                        type: "line",
                        data: {
                            labels: data.labels,
                            datasets: [{
                                label: "Remaining QA Items",
                                data: data.remaining,
                                borderColor: "#E11D48",
                                backgroundColor: "rgba(225,29,72,0.25)",
                                borderWidth: 2.5,
                                fill: true,
                                tension: 0.3
                            }]
                        },
                        options: {
                            responsive: true,
                            plugins: { legend: { position: "bottom" } },
                            scales: {
                                x: {
                                    title: {
                                        display: true,
                                        text: "Days (Last 30 Days)",
                                        font: { size: 13, weight: "bold" }
                                    }
                                },
                                y: {
                                    beginAtZero: true,
                                    title: {
                                        display: true,
                                        text: "Remaining QA Items",
                                        font: { size: 13, weight: "bold" }
                                    }
                                }
                            }
                        }
                    });

                });
        });
    }


*/

});


</script>


<script>
document.addEventListener("DOMContentLoaded", function () {

    const filter = document.getElementById("projectProgressFilter");
    const container = document.getElementById("projectProgressContainer");

    function loadProject(projectId) {
        
    if (!projectId || projectId === "") {
        container.innerHTML = `
            <div class="text-center text-muted py-4">
                Select a project to view progress.
            </div>`;
        return;
    }

        fetch(`/analytics/project-progress?project_id=${projectId}`)
            .then(res => res.json())
            .then(data => {

                if (!data.project) {
                    container.innerHTML = `
                        <div class="text-center text-muted py-4">
                            No project found.
                        </div>`;
                    return;
                }

                let p = data.project;

                container.innerHTML = `
<div class="project-card shadow-sm p-4 mb-4 rounded">

    <div class="d-flex justify-content-between align-items-center mb-2">
        <h5 class="fw-bold text-primary m-0">${p.name}</h5>
        <span class="badge bg-primary px-3 py-2">${p.progress}%</span>
    </div>

    <div class="progress stylish-progress mb-3">
        <div class="progress-bar dynamic-bar" style="width:${p.progress}%"></div>
    </div>

    ${p.phases.map(phase => `
        <div class="phase-block mt-3">

            <div class="d-flex justify-content-between align-items-center">
                <strong class="text-dark">${phase.type}</strong>
                <span class="text-muted small">${phase.progress}%</span>
            </div>

            <div class="progress stylish-progress-sm mb-1">
                <div class="progress-bar phase-bar" style="width:${phase.progress}%"></div>
            </div>

            <div class="sheet-bubbles mt-2">
                ${phase.sheets.map(sheet => `
                    <span class="sheet-pill" title="${sheet.title}">
                        ${sheet.number}
                        <span class="sheet-progress" style="width:${sheet.progress}%"></span>
                    </span>
                `).join("")}
            </div>

        </div>
    `).join("")}

</div>`;
            });
    }

    // ---- LOAD LATEST PROJECT ON PAGE LOAD ----
    let latestProjectId = "{{ $latest_project_id }}";

    if (latestProjectId) {
        filter.value = latestProjectId; // select it in the dropdown
        loadProject(latestProjectId);   // load automatically
    }

    // ---- LOAD NEW PROJECT WHEN FILTER CHANGES ----
    filter.addEventListener("change", function () {
        let projectId = this.value || latestProjectId;
        loadProject(projectId);
    });
});

</script>

<script>
  // Function to close the QA items popup modal
function closeQaPopup() {
    const modal = document.getElementById('qaPopupModal');
    const overlay = document.querySelector('.qa-popup-overlay');

    if (modal) modal.remove();
    if (overlay) overlay.remove();

    // Optional: إعادة السماح بالسكرول في الصفحة
    document.body.style.overflow = 'auto';
}
</script>

<script >

    // ==========================================
// GLOBAL VARIABLES & STATE MANAGEMENT
// ==========================================
let isModalOpen = false;
let isLoading = false;
let hasMore = true;

// ==========================================
// SINGLE EVENT LISTENER FOR HEATMAP CLICKS
// ==========================================
document.addEventListener("DOMContentLoaded", function () {
    console.log("Dashboard initialized");
    
    // Load initial heatmap
    loadHeatmap();
    
    // Filter event listeners
    document.getElementById("heatmapProjectFilter").addEventListener("change", loadHeatmap);
    
    // SINGLE event listener for heatmap clicks
    document.addEventListener("click", handleHeatmapClick);
});

// ==========================================
// HEATMAP CLICK HANDLER
// ==========================================
function handleHeatmapClick(e) {
    // Only handle heatmap-click elements
    if (!e.target.classList.contains("heatmap-click")) return;
    
    // Prevent multiple clicks
    e.preventDefault();
    e.stopPropagation();
    
    const sheetId = e.target.dataset.sheet;
    const status = e.target.dataset.status;
    
    if (!sheetId || !status) {
        console.error("Missing sheetId or status");
        return;
    }
    
    console.log(`Opening QA items: Sheet ${sheetId}, Status ${status}`);
    
    // Close existing modal if open
    if (isModalOpen) {
        closeQaPopup();
        setTimeout(() => {
            createQaModal(sheetId, status);
        }, 50);
    } else {
        createQaModal(sheetId, status);
    }
}

// ==========================================
// LOAD HEATMAP FUNCTION
// ==========================================
function loadHeatmap() {
    const projectId = document.getElementById("heatmapProjectFilter").value || "";
    const phaseId = document.getElementById("heatmapPhaseFilter").value || "";
    const tbody = document.getElementById('sheetHeatmapBody');
    
    // Show loading
    tbody.innerHTML = `
        <tr>
            <td colspan="12" class="text-center py-4">
                <div class="spinner-border spinner-border-sm text-primary me-2"></div>
                Loading heatmap...
            </td>
        </tr>`;
    
    fetch(`/analytics/sheet-heatmap?project_id=${projectId}&phase_id=${phaseId}`)
        .then(res => res.json())
        .then(data => {
            if (!data.rows || data.rows.length === 0) {
                tbody.innerHTML = `
                    <tr>
                        <td colspan="11" class="text-center text-muted py-4">
                            No data available for selected filters.
                        </td>
                    </tr>`;
                return;
            }
            
            // Update header
            const headerPhase = data.rows[0]?.phase || "-";
            const headerProject = data.rows[0]?.project || "-";
            
            document.getElementById("heatmapHeaderInfo").innerHTML = 
                `Phase: <span class="text-primary">${headerPhase}</span> → 
                 Project: <span class="text-primary">${headerProject}</span>`;
            
            // Clear and rebuild table
            tbody.innerHTML = "";
            
            data.rows.forEach(row => {
                let statusCells = "";
                
                if (data.statuses && Array.isArray(data.statuses)) {
                    data.statuses.forEach(st => {
                        const val = row[st] ?? 0;
                        const bg = val > 0 ? "rgba(37,99,235,0.9)" : "transparent";
                        const color = val > 0 ? "white" : "#333";
                        
                        statusCells += `
                            <td class="text-center">
                                <span class="badge heatmap-click"
                                    data-sheet="${row.sheet_id}"
                                    data-status="${st}"
                                    style="cursor:pointer; background:${bg}; color:${color}; min-width:32px;">
                                    ${val}
                                </span>
                            </td>`;
                    });
                }
                
                tbody.innerHTML += `
                    <tr>
                        <td>${row.sheet_label || "-"}</td>
                        <td class="text-center fw-bold">${row.total || 0}</td>
                        ${statusCells}
                        <td class="text-center text-primary fw-bold">${row.a_count || 0}</td>
                        <td class="text-center text-info fw-bold">${row.i_count || 0}</td>
                        <td class="text-center text-success fw-bold">${row.c_count || 0}</td>
                    </tr>`;
            });
            
            console.log(`Heatmap loaded: ${data.rows.length} rows`);
        })
        .catch(err => {
            console.error("Heatmap error:", err);
            tbody.innerHTML = `
                <tr>
                    <td colspan="11" class="text-center text-danger py-3">
                        <i class="fas fa-exclamation-triangle me-2"></i>
                        Error loading heatmap data
                    </td>
                </tr>`;
        });
}

// ==========================================
// MODAL FUNCTIONS
// ==========================================
function createQaModal(sheetId, status) {
    isModalOpen = true;
    
    // Create modal elements
    const modalContainer = document.createElement('div');
    modalContainer.id = 'qaPopupModal';
    modalContainer.className = 'qa-popup-modal';
    
    const overlay = document.createElement('div');
    overlay.className = 'qa-popup-overlay';
    
    // Modal HTML
    modalContainer.innerHTML = `
        <div class="qa-popup-content">
            <div class="qa-popup-header">
                <div class="qa-popup-title">
                    <i class="fas fa-tasks me-2"></i>
                    QA Items — <span id="popupStatus">${status.toUpperCase()}</span>
                    <span id="popupCount" class="ms-2 badge bg-light text-dark" style="font-size: 0.8rem;"></span>
                </div>
                <button type="button" class="qa-popup-close">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            
            <div class="qa-popup-body" id="popupBody">
                <div id="popupLoading" class="qa-popup-loading">
                    <div class="spinner-border text-primary" style="width:3rem; height:3rem;"></div>
                    <p class="mt-3 text-muted fw-semibold">Loading items…</p>
                </div>
                
                <div id="popupItemsContainer" class="qa-popup-items d-none">
                    <div class="table-responsive" style="max-height: 400px;">
                        <table class="table table-hover align-middle mb-0">
                            <thead>
                                <tr>
                                    <th style="width:70px;">ID</th>
                                    <th>Description</th>
                                    <th>Sheet</th>
                                    <th>Status</th>
                                    <th>Assigned To</th>
                                    <th>Due Date</th>
                                    <th>Created</th>
                                    <th class="text-center">Actions</th>
                                </tr>
                            </thead>
                            <tbody id="popupItemsBody"></tbody>
                        </table>
                    </div>
                    
                    <div id="popupLoadMore" class="qa-popup-load-more d-none">
                        <button id="loadMoreBtn">
                            <i class="fas fa-chevron-down me-1"></i> Load More Items
                        </button>
                    </div>
                    
                    <div id="infiniteLoading" class="qa-popup-infinite-loading d-none">
                        <div class="spinner-border spinner-border-sm text-secondary me-2"></div>
                        Loading more items...
                    </div>
                </div>
            </div>
            
            <div class="qa-popup-footer d-none" id="popupFooter">
                <div class="d-flex justify-content-between align-items-center px-3 py-2">
                    <small class="text-muted">
                        Showing <span id="showingCount">0</span> of <span id="totalCount">0</span> items
                    </small>
                    <div>
                        <button class="btn btn-sm btn-outline-secondary me-2" id="scrollTopBtn">
                            <i class="fas fa-arrow-up"></i> Top
                        </button>
                        <button class="btn btn-sm btn-outline-secondary" id="scrollBottomBtn">
                            <i class="fas fa-arrow-down"></i> Bottom
                        </button>
                    </div>
                </div>
            </div>
        </div>
    `;
    
    // Add to document
    document.body.appendChild(overlay);
    document.body.appendChild(modalContainer);
    
    // Show with animation
    setTimeout(() => {
        overlay.classList.add('show');
        modalContainer.classList.add('show');
    }, 10);
    
    // Store data
    modalContainer.dataset.sheetId = sheetId;
    modalContainer.dataset.status = status;
    modalContainer.dataset.currentPage = 1;
    modalContainer.dataset.totalItems = 0;
    
    // Setup event listeners for modal
    setupModalEvents(modalContainer, overlay);
    
    // Load data
    loadPopupData(sheetId, status, 1);
}

function setupModalEvents(modal, overlay) {
    // Close button
    const closeBtn = modal.querySelector('.qa-popup-close');
    if (closeBtn) {
        closeBtn.addEventListener('click', () => {
            isModalOpen = false;
            closeQaPopup();
        });
    }
    
    // Overlay click to close
    overlay.addEventListener('click', () => {
        isModalOpen = false;
        closeQaPopup();
    });
    
    // Scroll buttons
    const scrollTopBtn = modal.querySelector('#scrollTopBtn');
    const scrollBottomBtn = modal.querySelector('#scrollBottomBtn');
    
    if (scrollTopBtn) {
        scrollTopBtn.addEventListener('click', () => {
            const popupBody = document.getElementById('popupBody');
            if (popupBody) popupBody.scrollTo({ top: 0, behavior: 'smooth' });
        });
    }
    
    if (scrollBottomBtn) {
        scrollBottomBtn.addEventListener('click', () => {
            const popupBody = document.getElementById('popupBody');
            if (popupBody) popupBody.scrollTo({ top: popupBody.scrollHeight, behavior: 'smooth' });
        });
    }
    
    // Load more button
    const loadMoreBtn = modal.querySelector('#loadMoreBtn');
    if (loadMoreBtn) {
        loadMoreBtn.addEventListener('click', loadMoreItems);
    }
    
    // Infinite scroll
    const popupBody = document.getElementById('popupBody');
    if (popupBody) {
        popupBody.addEventListener('scroll', handlePopupScroll);
    }
}

function closeQaPopup() {
    isModalOpen = false;
    isLoading = false;
    hasMore = true;
    
    const modal = document.getElementById('qaPopupModal');
    const overlay = document.querySelector('.qa-popup-overlay');
    
    if (modal) {
        // Remove scroll event listener
        const popupBody = modal.querySelector('#popupBody');
        if (popupBody) {
            popupBody.removeEventListener('scroll', handlePopupScroll);
        }
        modal.remove();
    }
    
    if (overlay) overlay.remove();
    
    document.body.style.overflow = 'auto';
}

// ==========================================
// DATA LOADING FUNCTIONS
// ==========================================
function loadPopupData(sheetId, status, page = 1) {
    const tbody = document.getElementById('popupItemsBody');
    const loading = document.getElementById('popupLoading');
    const container = document.getElementById('popupItemsContainer');
    const loadMoreDiv = document.getElementById('popupLoadMore');
    
    if (!tbody || !loading || !container) return;
    
    // Reset
    tbody.innerHTML = '';
    isLoading = false;
    
    if (loadMoreDiv) {
        loadMoreDiv.classList.add('d-none');
    }
    
    loading.classList.remove('d-none');
    container.classList.add('d-none');
    
    fetch(`/analytics/qa-items?sheet_id=${sheetId}&status=${status}&page=${page}`)
        .then(res => res.json())
        .then(data => {
            const modal = document.getElementById('qaPopupModal');
            if (modal) {
                modal.dataset.totalItems = data.total || 0;
            }
            
            if (!data.items || !Array.isArray(data.items) || data.items.length === 0) {
                tbody.innerHTML = `
                    <tr>
                        <td colspan="8" class="text-center text-muted py-5">
                            <i class="fas fa-inbox fa-3x mb-3 opacity-50"></i><br>
                            <h5 class="mt-2">No QA items found</h5>
                            <p class="small">No items match the selected criteria</p>
                        </td>
                    </tr>`;
                hasMore = false;
            } else {
                appendItemsToTable(data.items);
                
                // Check if we should show load more button
                const itemsPerPage = 20;
                const totalLoaded = page * itemsPerPage;
                hasMore = totalLoaded < (data.total || 0);
                
                if (hasMore && loadMoreDiv) {
                    loadMoreDiv.classList.remove('d-none');
                } else if (loadMoreDiv) {
                    loadMoreDiv.classList.add('d-none');
                }
            }
            
            loading.classList.add('d-none');
            container.classList.remove('d-none');
            updateCounters();
            
            setTimeout(adjustModalHeight, 100);
        })
        .catch(err => {
            console.error("Error loading items:", err);
            tbody.innerHTML = `
                <tr>
                    <td colspan="8" class="text-center text-danger py-5">
                        <i class="fas fa-exclamation-triangle fa-3x mb-3"></i><br>
                        <h5 class="mt-2">Error loading QA items</h5>
                        <p class="small">${err.message || 'Please try again later'}</p>
                        <button onclick="loadPopupData('${sheetId}', '${status}', ${page})" 
                                class="btn btn-sm btn-outline-primary mt-2">
                            <i class="fas fa-redo me-1"></i> Retry
                        </button>
                    </td>
                </tr>`;
            
            loading.classList.add('d-none');
            container.classList.remove('d-none');
        });
}

function handlePopupScroll(e) {
    const container = e.target;
    const modal = document.getElementById('qaPopupModal');
    
    if (!container || !modal || isLoading || !hasMore) return;
    
    const currentPage = parseInt(modal.dataset.currentPage);
    if (currentPage === 1) return;
    
    const scrollBottom = container.scrollHeight - container.scrollTop - container.clientHeight;
    
    if (scrollBottom < 100) {
        loadMoreItems();
    }
}

function loadMoreItems() {
    const modal = document.getElementById('qaPopupModal');
    if (!modal || isLoading) return;
    
    const sheetId = modal.dataset.sheetId;
    const status = modal.dataset.status;
    const currentPage = parseInt(modal.dataset.currentPage) + 1;
    
    const infiniteLoading = document.getElementById('infiniteLoading');
    if (infiniteLoading) {
        infiniteLoading.classList.remove('d-none');
    }
    
    const loadMoreBtn = document.getElementById('loadMoreBtn');
    if (loadMoreBtn) {
        loadMoreBtn.disabled = true;
    }
    
    isLoading = true;
    
    fetch(`/analytics/qa-items?sheet_id=${sheetId}&status=${status}&page=${currentPage}`)
        .then(res => res.json())
        .then(data => {
            if (data.items && data.items.length > 0) {
                modal.dataset.currentPage = currentPage;
                modal.dataset.totalItems = data.total || 0;
                
                appendItemsToTable(data.items);
                updateCounters();
                
                const itemsPerPage = 20;
                const totalLoaded = currentPage * itemsPerPage;
                hasMore = totalLoaded < (data.total || 0);
                
                if (!hasMore) {
                    const loadMoreDiv = document.getElementById('popupLoadMore');
                    if (loadMoreDiv) {
                        loadMoreDiv.classList.add('d-none');
                    }
                }
            } else {
                hasMore = false;
                const loadMoreDiv = document.getElementById('popupLoadMore');
                if (loadMoreDiv) {
                    loadMoreDiv.classList.add('d-none');
                }
            }
        })
        .catch(err => {
            console.error("Error loading more items:", err);
        })
        .finally(() => {
            isLoading = false;
            if (infiniteLoading) {
                infiniteLoading.classList.add('d-none');
            }
            if (loadMoreBtn) {
                loadMoreBtn.disabled = false;
            }
        });
}

function appendItemsToTable(items) {
    const tbody = document.getElementById('popupItemsBody');
    if (!tbody) return;
    
    items.forEach(item => {
        const statusClass = getStatusClass(item.status);
        
        const row = document.createElement('tr');
        row.innerHTML = `
            <td>${item.id || "-"}</td>
            <td>${item.description || "No description"}</td>
            <td>${item.sheet_label || "-"}</td>
            <td>
                <span class="status-badge ${statusClass}">
                    ${item.status || "-"}
                </span>
            </td>
            <td>${item.assignee || "-"}</td>
            <td>${item.due_date || "-"}</td>
            <td>${item.created_at || "-"}</td>
            <td class="text-center">
                <a href="/qa_reviews/${item.id}" class="btn btn-sm btn-primary">
                    <i class="fas fa-eye"></i> View
                </a>
            </td>
        `;
        
        tbody.appendChild(row);
    });
}

function getStatusClass(status) {
    const statusParam = document.getElementById('popupStatus')?.textContent?.trim() || '';
    if (statusParam === 'A') return "status-A";
    if (statusParam === 'I') return "status-I";
    if (statusParam === 'C') return "status-C";
    
    const st = (status || "").toLowerCase();
    
    const statusMap = {
        "open": "status-open",
        "in_progress": "status-in_progress",
        "in-progress": "status-in_progress",
        "needs_info": "status-needs_info",
        "needs-info": "status-needs_info",
        "resolved": "status-resolved",
        "verified": "status-verified",
        "closed": "status-closed"
    };
    
    return statusMap[st] || "status-open";
}

function updateCounters() {
    const modal = document.getElementById('qaPopupModal');
    if (!modal) return;
    
    const tbody = document.getElementById('popupItemsBody');
    const currentCount = tbody ? tbody.children.length : 0;
    const totalCount = modal.dataset.totalItems || currentCount;
    
    document.getElementById('showingCount').textContent = currentCount;
    document.getElementById('totalCount').textContent = totalCount;
    document.getElementById('popupCount').textContent = `${currentCount} items`;
    
    const footer = document.getElementById('popupFooter');
    if (footer) {
        footer.classList[currentCount > 0 ? 'remove' : 'add']('d-none');
    }
}

function adjustModalHeight() {
    const modal = document.getElementById('qaPopupModal');
    const body = document.getElementById('popupBody');
    const itemsContainer = document.getElementById('popupItemsContainer');
    
    if (!modal || !body || !itemsContainer) return;
    
    const viewportHeight = window.innerHeight;
    const modalHeaderHeight = modal.querySelector('.qa-popup-header')?.offsetHeight || 70;
    const modalFooterHeight = modal.querySelector('.qa-popup-footer')?.offsetHeight || 0;
    const contentHeight = itemsContainer.scrollHeight;
    
    const maxBodyHeight = Math.min(
        contentHeight,
        viewportHeight * 0.8 - modalHeaderHeight - modalFooterHeight - 48
    );
    
    body.style.maxHeight = `${maxBodyHeight}px`;
    
    const tableResponsive = itemsContainer.querySelector('.table-responsive');
    if (tableResponsive) {
        tableResponsive.style.maxHeight = `${maxBodyHeight - 100}px`;
    }
}



// Handle window resize
window.addEventListener('resize', adjustModalHeight);

</script>

<script>
document.addEventListener("DOMContentLoaded", function () {

    const projectSelect = document.getElementById("heatmapProjectFilter");
    const phaseSelect   = document.getElementById("heatmapPhaseFilter");

    // =====================
    // LOAD PHASES
    // =====================
    function loadPhases(projectId) {
        phaseSelect.innerHTML = `<option value="">All Phases</option>`;

        if (!projectId) return;

        fetch(`/phases/by-project/${projectId}`)
            .then(res => res.json())
            .then(phases => {
                phases.forEach(phase => {
                    const opt = document.createElement("option");
                    opt.value = phase.id;
                    opt.textContent = phase.type;
                    phaseSelect.appendChild(opt);
                });
            });
    }

    // =====================
    // EVENTS
    // =====================
    projectSelect.addEventListener("change", function () {
        loadPhases(this.value);
        loadHeatmap();
    });

    phaseSelect.addEventListener("change", function () {
        loadHeatmap();
    });

    // =====================
    // INITIAL LOAD
    // =====================
    loadHeatmap();

});
</script>







<!-- Add this JavaScript -->
<script>
document.addEventListener("DOMContentLoaded", function () {
    // =========================
    // PHASE GATE ANALYTICS
    // =========================
    fetch("{{ route('analytics.phase-gates') }}")
        .then(res => res.json())
        .then(phases => {
            const container = document.getElementById('phaseGatesContainer');

            if (!phases || phases.length === 0) {
                container.innerHTML = `
                    <div class="no-phases">
                        <i class="fas fa-chart-line"></i>
                        <h5 class="mt-3 mb-2">No Phase Data Available</h5>
                        <p class="text-muted">Start creating phases and QA items to see analytics here.</p>
                    </div>
                `;
                return;
            }

            container.innerHTML = '';

            phases.forEach((p, index) => {
                const isExpanded = index === 0; // First phase expanded by default
                
                const progressPercentage = p.percent_complete ?? 0;
                const blockingPercentage = p.total_items > 0 ? ((p.blocking / p.total_items) * 100) : 0;
                const readinessPercentage = p.ready_for_signoff ? 100 : Math.min(progressPercentage, 100);
                
                // Generate timeline based on phase status
                const timelineStages = generateTimeline(p.phase_status);
                
                const card = document.createElement('div');
                card.className = 'phase-gate-card';
                card.innerHTML = `
                    <div class="phase-gate-header" data-phase-id="${p.phase_id}">
                        <div class="phase-gate-title">
                            <div class="phase-status-badge phase-status-${p.phase_status || 'DRAFT'}">
                                ${p.phase_status || 'DRAFT'}
                            </div>
                            <div>
                                <strong class="text-dark">${p.project || 'Unnamed Project'}</strong>
                                <div class="text-muted small">${p.phase_type || 'Phase'} • ID: ${p.phase_id}</div>
                            </div>
                        </div>
                        
                        <div class="d-flex align-items-center gap-4">
                            <div class="phase-progress-container">
                                <div class="d-flex justify-content-between mb-1">
                                    <small class="text-muted">Overall Progress</small>
                                    <small class="fw-bold">${progressPercentage.toFixed(1)}%</small>
                                </div>
                                <div class="progress" style="height: 6px;">
                                    <div class="progress-bar bg-success" 
                                         role="progressbar" 
                                         style="width: ${progressPercentage}%">
                                    </div>
                                </div>
                            </div>
                            
                            <div class="phase-progress-container">
                                <div class="d-flex justify-content-between mb-1">
                                    <small class="text-muted">Blocking Items</small>
                                    <small class="fw-bold text-danger">${p.blocking || 0}</small>
                                </div>
                                <div class="progress" style="height: 6px;">
                                    <div class="progress-bar bg-danger" 
                                         role="progressbar" 
                                         style="width: ${blockingPercentage}%">
                                    </div>
                                </div>
                            </div>
                            
                            <button class="phase-toggle" data-toggle="collapse">
                                <i class="fas fa-chevron-${isExpanded ? 'up' : 'down'}"></i>
                            </button>
                        </div>
                    </div>
                    
                    <div class="phase-gate-content collapse ${isExpanded ? 'show' : ''}" 
                         data-phase-content="${p.phase_id}">
                        
                        <div class="phase-metrics-grid">
                            <div class="phase-metric-card">
                                <div class="phase-metric-value">${p.total_items || 0}</div>
                                <div class="phase-metric-label">Total Items(Applicable only)</div>
                            </div>
                            
                            <div class="phase-metric-card">
                                <div class="phase-metric-value">${p.completed || 0}</div>
                                <div class="phase-metric-label">Completed[Applicable + Confirmed]</div>
                            </div>
                            
                            <div class="phase-metric-card">
                                <div class="phase-metric-value">${p.blocking || 0}</div>
                                <div class="phase-metric-label">Blocking(non-applicable)</div>
                            </div>
                            
                            <div class="phase-metric-card">
                                <div class="phase-metric-value phase-metric-critical">${p.critical_blocking || 0}</div>
                                <div class="phase-metric-label">Critical Blocking</div>
                            </div>
                        </div>
                        
                        <!-- Sign-off Readiness -->
                        <div class="signoff-readiness">
                            <div class="d-flex justify-content-between align-items-center mb-2">
                                <div>
                                    <strong class="text-primary">Sign-off Readiness</strong>
                                    <div class="text-muted small">
                                        ${p.ready_for_signoff ? '✅ Ready for Sign-off' : ' Not ready for Sign-off'}
                                    </div>
                                </div>
                                <div class="fw-bold">${readinessPercentage.toFixed(1)}%</div>
                            </div>
                            <div class="readiness-meter">
                                <div class="readiness-fill" style="width: ${readinessPercentage}%"></div>
                            </div>
                            <div class="d-flex justify-content-between">
                                <small class="text-muted">ETA: ${p.eta_signoff || 'Not available'}</small>
                                <small class="text-muted">
                                    ${p.ready_for_signoff ? 'All checks passed' : 'Review blocking items below'}
                                </small>
                            </div>
                        </div>
                        
                        <!-- Blocking Items Section -->
                        ${p.blocking > 0 ? `
                        <div class="blocking-items-section">
                            <h6 class="fw-bold text-danger mb-3">
                                Blocking Items (${p.blocking})
                            </h6>
                            ${generateBlockingItems(p.blocking_items || [])}
                        </div>
                        ` : `
                        <div class="alert alert-success mt-3">
                            <i class="fas fa-check-circle me-2"></i>
                            No blocking items! Phase is progressing smoothly.
                        </div>
                        `}
                        
                        <!-- Timeline -->
                        <div class="phase-timeline">
                            ${timelineStages.map(stage => `
                                <div class="timeline-item">
                                    <div class="timeline-icon ${stage.status}">
                                        <i class="fas fa-${stage.icon}"></i>
                                    </div>
                                    <div class="timeline-label">${stage.label}</div>
                                </div>
                            `).join('')}
                        </div>
                        
                        <!-- Actions 
                        <div class="phase-actions">
                            <button class="btn btn-sm btn-primary" onclick="viewPhaseDetails(${p.phase_id})">
                                <i class="fas fa-eye me-1"></i> View Phase Details
                            </button>
                            <button class="btn btn-sm btn-outline-primary" onclick="viewQaItems(${p.phase_id})">
                                <i class="fas fa-list-check me-1"></i> View QA Items
                            </button>
                            ${p.ready_for_signoff ? `
                            <button class="btn btn-sm btn-success ms-auto" onclick="initiateSignoff(${p.phase_id})">
                                <i class="fas fa-file-signature me-1"></i> Initiate Sign-off
                            </button>
                            ` : ''}
                        </
                        div>
-->
                        
                    </div>
                `;

                container.appendChild(card);
            });

            // Add click handlers for expand/collapse
            document.querySelectorAll('.phase-gate-header').forEach(header => {
                header.addEventListener('click', function() {
                    const phaseId = this.dataset.phaseId;
                    const content = document.querySelector(`[data-phase-content="${phaseId}"]`);
                    const toggleBtn = this.querySelector('.phase-toggle i');
                    
                    content.classList.toggle('show');
                    toggleBtn.className = content.classList.contains('show') 
                        ? 'fas fa-chevron-up' 
                        : 'fas fa-chevron-down';
                });
            });

            // Expand/Collapse All buttons
            document.getElementById('expandAllPhases').addEventListener('click', function() {
                document.querySelectorAll('.phase-gate-content').forEach(content => {
                    content.classList.add('show');
                });
                document.querySelectorAll('.phase-toggle i').forEach(icon => {
                    icon.className = 'fas fa-chevron-up';
                });
            });

            document.getElementById('collapseAllPhases').addEventListener('click', function() {
                document.querySelectorAll('.phase-gate-content').forEach(content => {
                    content.classList.remove('show');
                });
                document.querySelectorAll('.phase-toggle i').forEach(icon => {
                    icon.className = 'fas fa-chevron-down';
                });
            });
        })
        .catch(error => {
            console.error('Error loading phase gates:', error);
            document.getElementById('phaseGatesContainer').innerHTML = `
                <div class="alert alert-danger m-3">
                    <i class="fas fa-exclamation-triangle me-2"></i>
                    Failed to load phase analytics. Please try again.
                </div>
            `;
        });
});

function generateBlockingItems(items) {
    if (!items || items.length === 0) {
        return '<div class="text-muted small">No specific blocking items available.</div>';
    }
    
    return items.slice(0, 5).map(item => `
        <div class="blocking-item severity-${item.severity || 'medium'}">
            <i class="fas fa-circle text-danger me-2" style="font-size: 8px;"></i>
            <div class="flex-grow-1">
                <div class="fw-medium">${item.title || 'Untitled Item'}</div>
                <small class="text-muted">${item.sheet_label || 'Unknown Sheet'} • ID: ${item.id}</small>
            </div>
            <span class="badge bg-${item.severity === 'critical' ? 'danger' : 'warning'}">
                ${item.severity || 'medium'}
            </span>
        </div>
    `).join('');
}

function generateTimeline(phaseStatus) {
    const stages = [
        { icon: 'file-alt', label: 'Draft', status: 'completed' },
        { icon: 'play-circle', label: 'In Review', status: phaseStatus === 'IN_REVIEW' || 
                                                         phaseStatus === 'CHANGES_REQUIRED' || 
                                                         phaseStatus === 'READY_FOR_SIGNOFF' || 
                                                         phaseStatus === 'CLOSED' ? 'completed' : '' },
        { icon: 'check-circle', label: 'Ready', status: phaseStatus === 'READY_FOR_SIGNOFF' || 
                                                      phaseStatus === 'CLOSED' ? 'completed' : 
                                                      phaseStatus === 'CHANGES_REQUIRED' ? 'current' : '' },
        { icon: 'file-signature', label: 'Signed', status: phaseStatus === 'CLOSED' ? 'completed' : '' }
    ];
    
    // Mark current stage
    const statusMap = {
        'DRAFT': 0,
        'IN_REVIEW': 1,
        'CHANGES_REQUIRED': 1,
        'READY_FOR_SIGNOFF': 2,
        'CLOSED': 3
    };
    
    const currentIndex = statusMap[phaseStatus] || 0;
    if (stages[currentIndex]) {
        stages[currentIndex].status = 'current';
    }
    
    return stages;
}

// Example action functions
function viewPhaseDetails(phaseId) {
    window.location.href = `/phases/${phaseId}`;
}

function viewQaItems(phaseId) {
    window.location.href = `/qa_items?phase_id=${phaseId}`;
}

function initiateSignoff(phaseId) {
    if (confirm('Are you sure you want to initiate sign-off for this phase?')) {
        // Implement sign-off initiation logic
        alert(`Sign-off initiated for phase ${phaseId}. This would typically open a sign-off workflow.`);
    }
}



</script>



@endsection

