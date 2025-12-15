<h2>Weekly QA/QC Report</h2>

<p>
    Period:
    {{ now()->subDays(7)->toDateString() }}
    →
    {{ now()->toDateString() }}
</p>

<hr>

<h3>Summary</h3>
<ul>
    <li>New QA Items: {{ $report['created_items'] }}</li>
    <li>Closed QA Items: {{ $report['closed_items'] }}</li>
</ul>

<h3>Items by Status</h3>
<ul>
@foreach ($report['by_status'] as $status => $count)
    <li>{{ ucfirst($status) }}: {{ $count }}</li>
@endforeach
</ul>

<h3>Recent Activities</h3>
<ul>
@foreach ($report['recent_activities'] as $activity)
    <li>
        {{ $activity->action_type }}
        – {{ $activity->created_at->format('Y-m-d H:i') }}
    </li>
@endforeach
</ul>

<hr>
<p>QA/QC Tracker System</p>
