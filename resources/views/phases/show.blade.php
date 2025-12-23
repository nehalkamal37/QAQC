@extends('layouts.app')

@section('content')@php
  $logs = \App\Models\ActivityLog::where('phase_id', $phase->id)
    ->where('action_type', 'phase_status_changed')
    ->latest()
    ->take(10)
    ->get();
@endphp

<div class="card mt-3">
  <div class="card-header fw-bold">Phase Status History</div>
  <div class="card-body p-0">
    <table class="table table-sm table-striped mb-0">
      <thead>
        <tr>
          <th>Date</th>
          <th>User</th>
          <th>From</th>
          <th>To</th>
          <th>Note</th>
        </tr>
      </thead>
      <tbody>
        @forelse($logs as $log)
          <tr>
            <td>{{ $log->created_at->format('M d, Y h:i A') }}</td>
            <td>{{ optional($log->user)->name ?? 'Unknown' }}</td>
            <td><span class="badge bg-secondary">{{ $log->old_value['status'] ?? '-' }}</span></td>
            <td><span class="badge bg-primary">{{ $log->new_value['status'] ?? '-' }}</span></td>
            <td>{{ $log->note ?? '-' }}</td>
          </tr>
        @empty
          <tr>
            <td colspan="5" class="text-muted text-center py-3">No status changes logged yet.</td>
          </tr>
        @endforelse
      </tbody>
    </table>
  </div>
</div>

@endsection