@extends('layouts.app')

@section('content')
<div class="container-fluid px-4 py-4 dashboard-container">

    <!-- Page Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 fw-bold mb-1 text-dark">📊 Dashboard Overview</h1>
            <p class="text-muted mb-0">Welcome back! Here's your project summary.</p>
        </div>
        <div class="date-display">
            <span class="text-muted">{{ now()->format('l, F j, Y') }}</span>
        </div>
    </div>

   
<div class="section-card mb-4">

    <!-- HEADER + FILTER -->
    <div class="section-header d-flex justify-content-between align-items-center">
        <div>
            <h5 class="fw-bold mb-0">📌 Project QA Progress</h5>
            <span class="text-muted small">Visual progress across projects, phases & sheets</span>
        </div>

        <select id="projectProgressFilter" class="form-select form-select-sm" style="width:200px;">
            <option value="">All Projects</option>
            @foreach($projects as $proj)
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


{{--
<div class="card shadow-sm mb-4">
    <div class="card-header d-flex justify-content-between align-items-center">
        <div>
            <h5 class="fw-bold mb-0">📉 Phase Burn-Down Chart</h5>
            <small class="text-muted">Remaining QA items per day — toward Signoff</small>
        </div>

        <div class="d-flex">
            <select id="burndownPhaseFilter" class="form-select form-select-sm" style="width: 200px;">
                <option value="">Select Phase</option>
                @foreach($projects as $proj)
                    @foreach($proj->phases as $ph)
                        <option value="{{ $ph->id }}">
                            {{ $proj->name }} — {{ $ph->type }}
                        </option>
                    @endforeach
                @endforeach
            </select>
        </div>
    </div>

    <div class="card-body">
        <canvas id="phaseBurndownChart" height="120"></canvas>
    </div>
</div>

--}}


{{-- Sheet Heatmap + SLA Analytics --}}

<!-- ===================== -->
<!-- 1) SHEET HEATMAP (FULL WIDTH) -->
<!-- ===================== -->
<div class="col-12">
    <div class="card shadow-sm h-100">
        <div class="card-header d-flex justify-content-between align-items-center">
            <div>
                <h5 class="fw-bold mb-0">🗺️ Sheet × Status Heatmap</h5>
                <small class="text-muted">Per-sheet QA distribution — based on A/I/C project status</small>
            </div>
{{--
            <select id="heatmapProjectFilter" class="form-select form-select-sm" style="width:200px;">
                <option value="">All Projects</option>
                @foreach($projects as $proj)
                    <option value="{{ $proj->id }}">{{ $proj->name }}</option>
                @endforeach
            </select>  --}}

            <div class="d-flex gap-2">

    <!-- PROJECT FILTER -->
    <select id="heatmapProjectFilter" class="form-select form-select-sm" style="width:200px;">
        <option value="">All Projects</option>
        @foreach($projects as $proj)
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
<!--
            <div class="mt-3">
                <strong class="small">Legend:</strong>
                <div class="d-flex align-items-center gap-3 small mt-1">
                    <span><span class="legend-box" style="background:rgba(220,53,69,0.2)"></span> Low</span>
                    <span><span class="legend-box" style="background:rgba(220,53,69,0.5)"></span> Medium</span>
                    <span><span class="legend-box" style="background:rgba(220,53,69,0.8)"></span> High</span>
                </div>
            </div>
-->
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
                <h5 class="fw-bold mb-0">⏱️ SLA & Overdue Analytics</h5>
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
    <strong>📌 What this chart means:</strong><br>
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
<div class="card shadow-sm mb-4 mt-4">
    <div class="card-header d-flex justify-content-between align-items-center">
        <div>
            <h5 class="fw-bold mb-0">🚦 Phase Gate Analytics</h5>
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





    <!-- Status & Severity Section -->
    <div class="row g-4 mb-4">
        <!-- Status Cards -->
        <div class="col-lg-6">
            <div class="section-card">
                <div class="section-header">
                    <h5 class="fw-bold mb-0">📈 Status Overview</h5>
                    <span class="text-muted small">Current QA item status</span>
                </div>
                <div class="row g-3 mt-2">
                    <div class="col-md-6">
                        <div class="status-card status-open">
                            <div class="status-indicator"></div>
                            <div class="status-content">
                                <div class="status-title">Open Items</div>
                                <div class="status-value">{{ $data['qa_open'] }}</div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="status-card status-pending">
                            <div class="status-indicator"></div>
                            <div class="status-content">
                                <div class="status-title">Pending Items</div>
                                <div class="status-value">{{ $data['qa_pending'] }}</div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="status-card status-resolved">
                            <div class="status-indicator"></div>
                            <div class="status-content">
                                <div class="status-title">Resolved Items</div>
                                <div class="status-value">{{ $data['qa_resolved'] }}</div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="status-card status-closed">
                            <div class="status-indicator"></div>
                            <div class="status-content">
                                <div class="status-title">Closed Items</div>
                                <div class="status-value">{{ $data['qa_closed'] }}</div>
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
                    <h5 class="fw-bold mb-0">⚠️ Severity Levels</h5>
                    <span class="text-muted small">Show QA items reviews by severity</span>
                </div>
                <div class="row g-3 mt-2">
                    <div class="col-md-6">
                        <a href="{{ route('qa_reviews.index', ['severity' => 'critical']) }}" class="text-decoration-none">
                            <div class="severity-card severity-critical">
                                <div class="severity-icon">
                                    <i class="fas fa-exclamation-circle"></i>
                                </div>
                                <div class="severity-content">
                                    <div class="severity-title">Critical</div>
                                    <div class="severity-value">{{ $data['qa_critical'] }}</div>
                                </div>
                            </div>
                        </a>
                    </div>
                    <div class="col-md-6">
                        <a href="{{ route('qa_reviews.index', ['severity' => 'high']) }}" class="text-decoration-none">
                            <div class="severity-card severity-high">
                                <div class="severity-icon">
                                    <i class="fas fa-exclamation-triangle"></i>
                                </div>
                                <div class="severity-content">
                                    <div class="severity-title">High</div>
                                    <div class="severity-value">{{ $data['qa_high'] }}</div>
                                </div>
                            </div>
                        </a>
                    </div>
                    <div class="col-md-6">
                        <a href="{{ route('qa_reviews.index', ['severity' => 'medium']) }}" class="text-decoration-none">
                            <div class="severity-card severity-medium">
                                <div class="severity-icon">
                                    <i class="fas fa-info-circle"></i>
                                </div>
                                <div class="severity-content">
                                    <div class="severity-title">Medium</div>
                                    <div class="severity-value">{{ $data['qa_medium'] }}</div>
                                </div>
                            </div>
                        </a>
                    </div>
                    <div class="col-md-6">
                        <a href="{{ route('qa_reviews.index', ['severity' => 'low']) }}" class="text-decoration-none">
                            <div class="severity-card severity-low">
                                <div class="severity-icon">
                                    <i class="fas fa-info"></i>
                                </div>
                                <div class="severity-content">
                                    <div class="severity-title">Low</div>
                                    <div class="severity-value">{{ $data['qa_low'] }}</div>
                                </div>
                            </div>
                        </a>
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

        <!-- Reviewer Workload -->
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

    // =========================
    // 1) SHEET × STATUS HEATMAP
    // =========================
 


    // =========================
    // 2) SLA / OVERDUE ANALYTICS
    // =========================
    fetch("{{ route('analytics.sla') }}")
        .then(res => res.json())
        .then(data => {

            document.getElementById('slaActiveCount').textContent  = data.total_active ?? 0;
            document.getElementById('slaOverdueCount').textContent = data.overdue_count ?? 0;
            document.getElementById('slaAvgAge').textContent       = data.avg_age_days ?? 0;

            const bucketsCtx = document.getElementById('slaAgeBucketsChart').getContext('2d');
            new Chart(bucketsCtx, {
                type: 'bar',
                data: {
                    labels: ['0–3 days', '4–7', '8–14', '15+'],
                    datasets: [{
                        label: 'Active QA Items',
                        data: [
                            data.age_buckets?.['0_3'] ?? 0,
                            data.age_buckets?.['4_7'] ?? 0,
                            data.age_buckets?.['8_14'] ?? 0,
                            data.age_buckets?.['15_plus'] ?? 0,
                        ],
                        backgroundColor: 'rgba(37, 99, 235, 0.4)',
                        borderColor: 'rgba(37, 99, 235, 1)',
                        borderWidth: 1
                    }]
                },
                options: {
                    responsive: true,
                    plugins: { legend: { display: false } },
                    scales: {
                        y: { beginAtZero: true }
                    }
                }
            });

            const severityCtx = document.getElementById('slaSeverityChart').getContext('2d');
            new Chart(severityCtx, {
                type: 'doughnut',
                data: {
                    labels: ['Critical', 'High', 'Medium', 'Low'],
                    datasets: [{
                        data: [
                            data.severity_overdue?.critical ?? 0,
                            data.severity_overdue?.high ?? 0,
                            data.severity_overdue?.medium ?? 0,
                            data.severity_overdue?.low ?? 0,
                        ],
                        backgroundColor: [
                            '#dc2626',
                            '#ea580c',
                            '#f59e0b',
                            '#16a34a'
                        ]
                    }]
                },
                options: {
                    responsive: true,
                    plugins: {
                        legend: { position: 'bottom' }
                    },
                    cutout: '60%'
                }
            });
        });

    // =========================
    // 3) PHASE GATE ANALYTICS
    // =========================
    fetch("{{ route('analytics.phase-gates') }}")
        .then(res => res.json())
        .then(phases => {
            const container = document.getElementById('phaseGatesContainer');

            if (!phases || phases.length === 0) {
                container.innerHTML = `
                    <div class="text-center text-muted py-3">
                        No phases found yet.
                    </div>
                `;
                return;
            }

            container.innerHTML = '';

            phases.forEach(p => {
                const statusBadgeClass = (function () {
                    switch (p.phase_status) {
                        case 'CLOSED': return 'bg-success';
                        case 'READY_FOR_SIGNOFF': return 'bg-primary';
                        case 'CHANGES_REQUIRED': return 'bg-warning text-dark';
                        case 'IN_REVIEW': return 'bg-info text-dark';
                        default: return 'bg-secondary';
                    }
                })();

                const card = document.createElement('div');
                card.className = 'mb-3';

                card.innerHTML =
                    '<div class="border rounded p-3 bg-white">' +
                        '<div class="d-flex justify-content-between align-items-center mb-2">' +
                            '<div>' +
                                '<strong>' + (p.project || '-') + '</strong>' +
                                '<span class="text-muted"> · ' + p.phase_type + '</span>' +
                            '</div>' +
                            '<span class="badge ' + statusBadgeClass + '">' +
                                (p.phase_status || 'N/A') +
                            '</span>' +
                        '</div>' +

                        '<div class="progress mb-2" style="height: 10px;">' +
                            '<div class="progress-bar" role="progressbar"' +
                                ' style="width: ' + (p.percent_complete ?? 0) + '%;"' +
                                ' aria-valuenow="' + (p.percent_complete ?? 0) + '"' +
                                ' aria-valuemin="0" aria-valuemax="100">' +
                            '</div>' +
                        '</div>' +
                        '<div class="d-flex justify-content-between small text-muted mb-2">' +
                            '<span>Completed: ' + p.completed + '/' + p.total_items + '</span>' +
                            '<span>Blocking: ' + p.blocking + ' (' + p.critical_blocking + ' critical)</span>' +
                        '</div>' +

                        '<div class="d-flex justify-content-between small">' +
                            '<span>' +
                                (p.eta_signoff
                                    ? 'Estimated Ready For Signoff: <strong>' + p.eta_signoff + '</strong>'
                                    : 'No ETA — all blocking items cleared or not enough data') +
                            '</span>' +
                        '</div>' +
                    '</div>';

                container.appendChild(card);
            });
        });

    // =========================
    // 4) PHASE BURN-DOWN CHART
    // =========================

    // -------------------------------
    // PHASE BURNDOWN CHART (FIXED)
    // -------------------------------
    
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




});


</script>

<script>
document.addEventListener("DOMContentLoaded", function () {


    loadHeatmap();

    document.getElementById("heatmapProjectFilter").addEventListener("change", loadHeatmap);
function loadHeatmap() {

    let projectId = document.getElementById("heatmapProjectFilter").value || "";
    let phaseId   = document.getElementById("heatmapPhaseFilter").value || "";

    fetch(`/analytics/sheet-heatmap?project_id=${projectId}&phase_id=${phaseId}`)
        .then(res => res.json())
        .then(data => {
            const tbody = document.getElementById('sheetHeatmapBody');
            tbody.innerHTML = "";


                if (!data.rows || data.rows.length === 0) {
                    tbody.innerHTML = `
                        <tr>
                            <td colspan="11" class="text-center text-muted py-4">No data available.</td>
                        </tr>`;
                    return;
                }

             //   let lastGroup = "";

                let headerPhase = data.rows[0]?.phase || "-";
              let headerProject = data.rows[0]?.project || "-";

      document.getElementById("heatmapHeaderInfo").innerHTML =
      `Phase: <span class="text-primary">${headerPhase}</span> → 
       Project: <span class="text-primary">${headerProject}</span>`;

                data.rows.forEach(row => {

                    let groupTitle = `Phase: ${row.phase || '-'} → Project: ${row.project || '-'}`;

                    // Group Header
              /*      if (groupTitle !== lastGroup) {
                        lastGroup = groupTitle;
                        tbody.innerHTML += `
                            <tr class="table-group-row">
                                <td colspan="11" class="fw-bold bg-light text-dark py-2">${groupTitle}</td>
                            </tr>`;
                    }*/

                    const total = row.total;

                    let statusCells = "";
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

            
                        tbody.innerHTML += `
    <tr>
        <td>${row.sheet_label}</td>
        <td class="text-center fw-bold">${row.total}</td>
        ${statusCells}
        <td class="text-center text-primary fw-bold">${row.a_count}</td>
        <td class="text-center text-info fw-bold">${row.i_count}</td>
        <td class="text-center text-success fw-bold">${row.c_count}</td>
    </tr>`;

                });

            })
            .catch(err => {
                console.error("Heatmap error:", err);
                tbody.innerHTML = `
                    <tr><td colspan="11" class="text-danger text-center py-3">
                        Error loading heatmap
                    </td></tr>`;
            });
    }

});
</script>

  
<!-- أزل هذا المودال بالكامل -->
<!--
<div class="modal fade" id="qaItemsModal" tabindex="-1">
    ...
</div>
-->
<script>
document.addEventListener("DOMContentLoaded", function () {

    const filter = document.getElementById("projectProgressFilter");
    const container = document.getElementById("projectProgressContainer");

    function loadProject(projectId) {

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


@endsection

