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

    <!-- Summary Cards -->
    <div class="row g-4 mb-4">
        <div class="col-md-3">
            <a href="{{ route('projects.index') }}" class="text-decoration-none card-link">
                <div class="stat-card stat-card-primary">
                    <div class="stat-icon">
                        <i class="fas fa-folder-open"></i>
                    </div>
                    <div class="stat-content">
                        <div class="stat-title">Projects</div>
                        <div class="stat-value">{{ $data['projects_count'] }}</div>
                    </div>
                </div>
            </a>
        </div>

        <div class="col-md-3">
            <a href="{{ route('phases.indexAll') }}" class="text-decoration-none card-link">
                <div class="stat-card stat-card-success">
                    <div class="stat-icon">
                        <i class="fas fa-layer-group"></i>
                    </div>
                    <div class="stat-content">
                        <div class="stat-title">Phases</div>
                        <div class="stat-value">{{ $data['phases_count'] }}</div>
                    </div>
                </div>
            </a>
        </div>

        <div class="col-md-3">
            <a href="{{ route('sheets.indexAll') }}" class="text-decoration-none card-link">
                <div class="stat-card stat-card-warning">
                    <div class="stat-icon">
                        <i class="fas fa-file-alt"></i>
                    </div>
                    <div class="stat-content">
                        <div class="stat-title">Sheets</div>
                        <div class="stat-value">{{ $data['sheets_count'] }}</div>
                    </div>
                </div>
            </a>
        </div>

        <div class="col-md-3">
            <a href="{{ route('qa_items.indexAll') }}" class="text-decoration-none card-link">
                <div class="stat-card stat-card-danger">
                    <div class="stat-icon">
                        <i class="fas fa-clipboard-check"></i>
                    </div>
                    <div class="stat-content">
                        <div class="stat-title">QA Items</div>
                        <div class="stat-value">{{ $data['qa_items_count'] }}</div>
                    </div>
                </div>
            </a>
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

<!-- Custom CSS -->
<style>
    :root {
        --primary: #4361ee;
        --success: #06d6a0;
        --warning: #ffd166;
        --danger: #ef476f;
        --info: #118ab2;
        --secondary: #8d99ae;
        --light: #f8f9fa;
        --dark: #212529;
        --open: #ef476f;
        --pending: #ffd166;
        --resolved: #06d6a0;
        --closed: #8d99ae;
        --critical: #d90429;
        --high: #f77f00;
        --medium: #4cc9f0;
        --low: #adb5bd;
    }

    .dashboard-container {
        background-color: #f8fafc;
        min-height: 100vh;
    }

    .date-display {
        background: white;
        padding: 8px 16px;
        border-radius: 8px;
        box-shadow: 0 2px 4px rgba(0,0,0,0.04);
    }

    /* Stat Cards */
    .stat-card {
        background: #ffffff;
        border-radius: 12px;
        padding: 24px;
        display: flex;
        align-items: center;
        border: 1px solid #f1f1f1;
        box-shadow: 0 2px 8px rgba(0,0,0,0.05);
        transition: all 0.3s ease;
        height: 100%;
    }

    .stat-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 8px 25px rgba(0,0,0,0.1);
    }

    .stat-card-primary {
        border-left: 4px solid var(--primary);
    }

    .stat-card-success {
        border-left: 4px solid var(--success);
    }

    .stat-card-warning {
        border-left: 4px solid var(--warning);
    }

    .stat-card-danger {
        border-left: 4px solid var(--danger);
    }

    .stat-icon {
        width: 60px;
        height: 60px;
        border-radius: 12px;
        display: flex;
        align-items: center;
        justify-content: center;
        margin-right: 16px;
        font-size: 24px;
    }

    .stat-card-primary .stat-icon {
        background-color: rgba(67, 97, 238, 0.1);
        color: var(--primary);
    }

    .stat-card-success .stat-icon {
        background-color: rgba(6, 214, 160, 0.1);
        color: var(--success);
    }

    .stat-card-warning .stat-icon {
        background-color: rgba(255, 209, 102, 0.1);
        color: var(--warning);
    }

    .stat-card-danger .stat-icon {
        background-color: rgba(239, 71, 111, 0.1);
        color: var(--danger);
    }

    .stat-title {
        font-size: 0.85rem;
        font-weight: 600;
        color: #6c757d;
        text-transform: uppercase;
        letter-spacing: .5px;
        margin-bottom: 4px;
    }

    .stat-value {
        font-size: 2.2rem;
        font-weight: 700;
        color: var(--dark);
    }

    /* Section Cards */
    .section-card {
        background: #ffffff;
        border-radius: 12px;
        padding: 24px;
        border: 1px solid #f1f1f1;
        box-shadow: 0 2px 8px rgba(0,0,0,0.05);
        height: 100%;
    }

    .section-header {
        margin-bottom: 16px;
    }

    /* Status Cards */
    .status-card {
        background: #ffffff;
        border-radius: 10px;
        padding: 16px;
        display: flex;
        align-items: center;
        border: 1px solid #f1f1f1;
        box-shadow: 0 2px 6px rgba(0,0,0,0.04);
        transition: all 0.2s ease;
        height: 100%;
    }

    .status-card:hover {
        transform: translateY(-2px);
        box-shadow: 0 4px 12px rgba(0,0,0,0.08);
    }

    .status-indicator {
        width: 12px;
        height: 12px;
        border-radius: 50%;
        margin-right: 12px;
    }

    .status-open .status-indicator {
        background-color: var(--open);
    }

    .status-pending .status-indicator {
        background-color: var(--pending);
    }

    .status-resolved .status-indicator {
        background-color: var(--resolved);
    }

    .status-closed .status-indicator {
        background-color: var(--closed);
    }

    .status-title {
        font-size: 0.85rem;
        font-weight: 600;
        color: #6c757d;
        margin-bottom: 4px;
    }

    .status-value {
        font-size: 1.8rem;
        font-weight: 700;
    }

    .status-open .status-value {
        color: var(--open);
    }

    .status-pending .status-value {
        color: var(--pending);
    }

    .status-resolved .status-value {
        color: var(--resolved);
    }

    .status-closed .status-value {
        color: var(--closed);
    }

    /* Severity Cards */
    .severity-card {
        background: #ffffff;
        border-radius: 10px;
        padding: 16px;
        display: flex;
        align-items: center;
        border: 1px solid #f1f1f1;
        box-shadow: 0 2px 6px rgba(0,0,0,0.04);
        transition: all 0.2s ease;
        height: 100%;
    }

    .severity-card:hover {
        transform: translateY(-2px);
        box-shadow: 0 4px 12px rgba(0,0,0,0.08);
    }

    .severity-icon {
        width: 48px;
        height: 48px;
        border-radius: 10px;
        display: flex;
        align-items: center;
        justify-content: center;
        margin-right: 16px;
        font-size: 20px;
    }

    .severity-critical .severity-icon {
        background-color: rgba(217, 4, 41, 0.1);
        color: var(--critical);
    }

    .severity-high .severity-icon {
        background-color: rgba(247, 127, 0, 0.1);
        color: var(--high);
    }

    .severity-medium .severity-icon {
        background-color: rgba(76, 201, 240, 0.1);
        color: var(--medium);
    }

    .severity-low .severity-icon {
        background-color: rgba(173, 181, 189, 0.1);
        color: var(--low);
    }

    .severity-title {
        font-size: 0.85rem;
        font-weight: 600;
        color: #6c757d;
        margin-bottom: 4px;
    }

    .severity-value {
        font-size: 1.8rem;
        font-weight: 700;
    }

    .severity-critical .severity-value {
        color: var(--critical);
    }

    .severity-high .severity-value {
        color: var(--high);
    }

    .severity-medium .severity-value {
        color: var(--medium);
    }

    .severity-low .severity-value {
        color: var(--low);
    }

    /* Timeline Card */
    .timeline-card {
        background: linear-gradient(135deg, #254bf7ff 0%, #455ecaff 100%);
        border-radius: 12px;
        padding: 24px;
        color: white;
        position: relative;
        overflow: hidden;
        height: 100%;
        display: flex;
        flex-direction: column;
        justify-content: space-between;
        transition: all 0.3s ease;
    }

    .timeline-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 10px 30px rgba(67, 97, 238, 0.3);
    }

    .timeline-icon {
        font-size: 32px;
        margin-bottom: 16px;
    }

    .timeline-content h5 {
        margin-bottom: 8px;
    }

    .timeline-action {
        margin-top: 16px;
    }

    .timeline-decoration {
        position: absolute;
        top: 0;
        right: 0;
        bottom: 0;
        left: 0;
        pointer-events: none;
    }

    .decoration-circle {
        position: absolute;
        border-radius: 50%;
        background: rgba(255, 255, 255, 0.1);
    }

    .circle-1 {
        width: 80px;
        height: 80px;
        top: -20px;
        right: -20px;
    }

    .circle-2 {
        width: 60px;
        height: 60px;
        bottom: -15px;
        left: -15px;
    }

    /* Reviewer Workload */
    .workload-legend {
        display: flex;
        align-items: center;
    }

    .legend-item {
        display: flex;
        align-items: center;
        margin-left: 16px;
    }

    .legend-color {
        width: 12px;
        height: 12px;
        border-radius: 2px;
        background-color: var(--primary);
        margin-right: 6px;
    }

    .legend-text {
        font-size: 0.8rem;
        color: #6c757d;
    }

    .reviewer-item {
        padding: 12px 0;
        border-bottom: 1px solid #f1f1f1;
    }

    .reviewer-item:last-child {
        border-bottom: none;
    }

    .reviewer-avatar {
        width: 36px;
        height: 36px;
        border-radius: 50%;
        background-color: var(--primary);
        color: white;
        display: flex;
        align-items: center;
        justify-content: center;
        font-weight: 600;
    }

    .reviewer-count {
        font-weight: 600;
        color: var(--dark);
    }

    .workload-progress {
        height: 8px;
        border-radius: 4px;
        background-color: #e9ecef;
    }

    .workload-progress .progress-bar {
        background-color: var(--primary);
        border-radius: 4px;
        transition: width 0.5s ease;
    }

    .empty-state {
        text-align: center;
        padding: 40px 20px;
    }

    .empty-icon {
        font-size: 48px;
        color: #dee2e6;
    }

    /* Card Links */
    .card-link {
        display: block;
        height: 100%;
    }

    /* Responsive Adjustments */
    @media (max-width: 768px) {
        .stat-card {
            flex-direction: column;
            text-align: center;
        }
        
        .stat-icon {
            margin-right: 0;
            margin-bottom: 12px;
        }
        
        .section-header {
            flex-direction: column;
            align-items: flex-start;
        }
        
        .workload-legend {
            margin-top: 8px;
        }
    }
</style>

<!-- Font Awesome for Icons -->
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
@endsection