<h2>Weekly QA/QC Report</h2>

<p>Generated at: {{ now() }}</p>

<table width="100%" border="1" cellspacing="0" cellpadding="5">
    <tr>
        <th>Status</th>
        <th>Count</th>
    </tr>

    @foreach ($report['by_status'] as $status => $count)
    <tr>
        <td>{{ ucfirst($status) }}</td>
        <td>{{ $count }}</td>
    </tr>
    @endforeach
</table>
