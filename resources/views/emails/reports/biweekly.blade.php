@component('mail::message')
# Bi-Weekly QA/QC Summary

@foreach($analytics as $phase)
### {{ $phase['project'] }} — {{ $phase['phase_type'] }}
* Status: **{{ $phase['phase_status'] }}**
* Progress: **{{ $phase['percent_complete'] }}%**
* Total: {{ $phase['total_items'] }}
* Completed: {{ $phase['completed'] }}
* Blocking: {{ $phase['blocking'] }}
* ETA: {{ $phase['eta_signoff'] ?? '—' }}

@endforeach

Thanks,  
**SSR QA/QC Tracker**
@endcomponent
