@extends('layouts.app')

@section('content')
<div class="container py-4">
    <h1>Phase Details</h1>
    
    @if(isset($phase))
        <div class="card mt-3">
            <div class="card-body">
                <h5>Phase Information</h5>
                <p><strong>Type:</strong> {{ $phase->type ?? 'N/A' }}</p>
                <p><strong>Project:</strong> {{ $phase->project->name ?? 'N/A' }}</p>
                <p><strong>Status:</strong> {{ $phase->status ?? 'DRAFT' }}</p>
                <p><strong>Created:</strong> {{ $phase->created_at ? $phase->created_at->format('Y-m-d') : 'N/A' }}</p>
                
                @if($phase->due_date)
                    <p><strong>Due Date:</strong> {{ $phase->due_date->format('Y-m-d') }}</p>
                @else
                    <p><strong>Due Date:</strong> Not set</p>
                @endif
            </div>
        </div>
        
        <div class="mt-3">
            <a href="{{ url()->previous() }}" class="btn btn-secondary">Back</a>
            <a href="/sheets?phase_id={{ $phase->id }}" class="btn btn-primary">View Sheets</a>
        </div>
    @else
        <div class="alert alert-danger">Phase not found</div>
    @endif
</div>
@endsection