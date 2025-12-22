<h2>Weekly QA/QC Report</h2>

<p>
    <strong>Period:</strong>
    {{ $report['meta']['from'] }} → {{ $report['meta']['to'] }} <br>
    <strong>Generated at:</strong>
    {{ $report['meta']['generated_at'] }}
</p>

<hr>

{{-- ====================================================== --}}
{{-- THROUGHPUT --}}
{{-- ====================================================== --}}
<h3>Weekly Throughput</h3>
<table width="100%" border="1" cellpadding="6">
    <tr>
        <th>Metric</th>
        <th>Value</th>
    </tr>
    <tr>
        <td>Created This Week</td>
        <td>{{ $report['throughput']['created_this_week'] }}</td>
    </tr>
    <tr>
        <td>Created Last Week</td>
        <td>{{ $report['throughput']['created_last_week'] }}</td>
    </tr>
    <tr>
        <td>Closed This Week</td>
        <td>{{ $report['throughput']['closed_this_week'] }}</td>
    </tr>
    <tr>
        <td><strong>Net Change</strong></td>
        <td><strong>{{ $report['throughput']['net_change'] }}</strong></td>
    </tr>
</table>

<br>

{{-- ====================================================== --}}
{{-- STATUS DISTRIBUTION --}}
{{-- ====================================================== --}}
<h3>Status Distribution (Current)</h3>
<table width="100%" border="1" cellpadding="6">
    <tr>
        <th>Status</th>
        <th>Count</th>
        <th>Percentage</th>
    </tr>
    @foreach($report['status_distribution'] as $status => $row)
        <tr>
            <td>{{ ucfirst(str_replace('_',' ', $status)) }}</td>
            <td>{{ $row['count'] }}</td>
            <td>{{ $row['percentage'] }}%</td>
        </tr>
    @endforeach
</table>

<br>

{{-- ====================================================== --}}
{{-- A / I / C --}}
{{-- ====================================================== --}}
<h3>A / I / C Progress</h3>
<table width="100%" border="1" cellpadding="6">
    <tr>
        <th>Stage</th>
        <th>Total</th>
    </tr>
    <tr>
        <td>Applicable</td>
        <td>{{ $report['aic']['applicable_total'] }}</td>
    </tr>
    <tr>
        <td>Incorporated</td>
        <td>{{ $report['aic']['incorporated_total'] }}</td>
    </tr>
    <tr>
        <td>Confirmed</td>
        <td>{{ $report['aic']['confirmed_total'] }}</td>
    </tr>
</table>

<br>

<h4>A / I / C Changes This Week</h4>
<table width="100%" border="1" cellpadding="6">
    <tr>
        <th>Metric</th>
        <th>Count</th>
    </tr>
    <tr>
        <td>Became Applicable</td>
        <td>{{ $report['aic']['became_applicable_this_week'] }}</td>
    </tr>
    <tr>
        <td>Became Confirmed</td>
        <td>{{ $report['aic']['became_confirmed_this_week'] }}</td>
    </tr>
</table>

<br>

{{-- ====================================================== --}}
{{-- OVERDUE & RISK --}}
{{-- ====================================================== --}}
<h3>Overdue & Risk Indicators</h3>
<table width="100%" border="1" cellpadding="6">
    <tr>
        <th>Indicator</th>
        <th>Value</th>
    </tr>
    <tr>
        <td>Total Overdue Items</td>
        <td>{{ $report['overdue']['total_overdue'] }}</td>
    </tr>
    <tr>
        <td>Critical Overdue Items</td>
        <td>{{ $report['overdue']['critical_overdue'] }}</td>
    </tr>
    <tr>
        <td>Average Days Overdue</td>
        <td>{{ number_format($report['overdue']['avg_days_overdue'], 1) }}</td>
    </tr>
</table>

<br>

{{-- ====================================================== --}}
{{-- SEVERITY --}}
{{-- ====================================================== --}}
<h3>Severity Distribution</h3>
<table width="100%" border="1" cellpadding="6">
    <tr>
        <th>Severity</th>
        <th>Count</th>
    </tr>
    @foreach($report['severity']['distribution'] as $severity => $count)
        <tr>
            <td>{{ ucfirst($severity) }}</td>
            <td>{{ $count }}</td>
        </tr>
    @endforeach
</table>

<br>

{{-- ====================================================== --}}
{{-- ASSIGNEE PERFORMANCE --}}
{{-- ====================================================== --}}
<h3>Assignee Load & Completion</h3>
<table width="100%" border="1" cellpadding="6">
    <tr>
        <th>Assignee</th>
        <th>Assigned Items</th>
        <th>Closed Items</th>
    </tr>
    @foreach($report['assignees']['load'] as $name => $assigned)
        <tr>
            <td>{{ $name }}</td>
            <td>{{ $assigned }}</td>
            <td>{{ $report['assignees']['closed'][$name] ?? 0 }}</td>
        </tr>
    @endforeach
</table>

<br>

{{-- ====================================================== --}}
{{-- AGING --}}
{{-- ====================================================== --}}
<h3>Item Aging</h3>
<table width="100%" border="1" cellpadding="6">
    <tr>
        <th>Metric</th>
        <th>Value</th>
    </tr>
    <tr>
        <td>Average Open Days</td>
        <td>{{ number_format($report['aging']['avg_open_days'], 1) }}</td>
    </tr>
    <tr>
        <td>Open &gt; 14 Days</td>
        <td>{{ $report['aging']['older_than_14_days'] }}</td>
    </tr>
    <tr>
        <td>Open &gt; 30 Days</td>
        <td>{{ $report['aging']['older_than_30_days'] }}</td>
    </tr>
</table>

<br>

{{-- ====================================================== --}}
{{-- ACTIVITY --}}
{{-- ====================================================== --}}
<h3>Weekly Activity Summary</h3>
<table width="100%" border="1" cellpadding="6">
    <tr>
        <th>Activity Type</th>
        <th>Count</th>
    </tr>
    @foreach($report['activity']['summary'] as $action => $count)
        <tr>
            <td>{{ ucfirst(str_replace('_',' ', $action)) }}</td>
            <td>{{ $count }}</td>
        </tr>
    @endforeach
</table>

<br>

<h4>Status Transitions</h4>
<table width="100%" border="1" cellpadding="6">
    <tr>
        <th>New Status</th>
        <th>Transitions</th>
    </tr>
    @foreach($report['activity']['transitions'] as $status => $count)
        <tr>
            <td>{{ ucfirst($status) }}</td>
            <td>{{ $count }}</td>
        </tr>
    @endforeach
</table>
