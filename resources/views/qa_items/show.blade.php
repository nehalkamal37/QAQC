{{-- resources/views/qa_items/show.blade.php --}}
@extends('layouts.app')

@section('content')
<div class="container-fluid px-4">
    <!-- Header -->
    <div class="d-flex justify-content-between align-items-center py-3">
        <div>
            <h4 class="mb-1">QA Item Details</h4>
            <p class="text-muted mb-0">Item #{{ $qa_item->id }}</p>
        </div>
        <a href="{{ route('dashboard.my-work') }}" class="btn btn-outline-secondary btn-sm">
            <i class="fas fa-arrow-left me-1"></i>Back
        </a>
    </div>

    <div class="row">
        <div class="col-lg-12">
            <!-- Main Card -->
            <div class="card">
                <div class="card-header bg-light">
                    <div class="d-flex justify-content-between align-items-center">
                        <h6 class="mb-0">Item Information</h6>
                        <span class="badge bg-{{ $qa_item->getStatusColor() }}">
                            {{ ucfirst($qa_item->status) }}
                        </span>
                    </div>
                </div>

                <div class="card-body">
                    <!-- Description -->
                    <div class="mb-4">
                        <h6>Description</h6>
                        <div class="p-3 bg-light rounded">
                            {{ $qa_item->item_description }}
                        </div>
                    </div>

                    <!-- Details Grid -->
                    <div class="row mb-4">
                        <div class="col-md-6">
                            <h6>Basic Info</h6>
                            <table class="table table-sm table-borderless">
                                <tr>
                                    <td class="text-muted" width="120">Severity:</td>
                                    <td>
                                        <span class="badge bg-{{ $qa_item->severity == 'high' ? 'danger' : ($qa_item->severity == 'medium' ? 'warning' : 'info') }}">
                                            {{ ucfirst($qa_item->severity) }}
                                        </span>
                                    </td>
                                </tr>
                                <tr>
                                    <td class="text-muted">Phase:</td>
                                    <td>{{ $qa_item->sheet->phase->type }}</td>
                                </tr>
                                <tr>
                                    <td class="text-muted">Assigned To:</td>
                                    <td>
                                        @if($qa_item->assignedUser)
                                            {{ $qa_item->assignedUser->name }}
                                        @else
                                            <span class="text-muted">Unassigned</span>
                                        @endif
                                    </td>
                                </tr>
                            </table>
                        </div>
                        <div class="col-md-6">
                            <h6>Dates</h6>
                            <table class="table table-sm table-borderless">
                                <tr>
                                    <td class="text-muted" width="120">Due Date:</td>
                                    <td>
                                        @if($qa_item->due_date)
                                            @php
                                                $dueDate = is_string($qa_item->due_date) 
                                                    ? \Carbon\Carbon::parse($qa_item->due_date) 
                                                    : $qa_item->due_date;
                                            @endphp
                                            <span class="{{ $dueDate->isPast() ? 'text-danger' : 'text-muted' }}">
                                                {{ $dueDate->format('M d, Y') }}
                                            </span>
                                        @else
                                            <span class="text-muted">Not set</span>
                                        @endif
                                    </td>
                                </tr>
                                <tr>
                                    <td class="text-muted">Created:</td>
                                    <td>{{ $qa_item->created_at->format('M d, Y') }}</td>
                                </tr>
                                <tr>
                                    <td class="text-muted">Updated:</td>
                                    <td>{{ $qa_item->updated_at->format('M d, Y') }}</td>
                                </tr>
                            </table>
                        </div>
                    </div>

                    <!-- Project Info -->
                    <div class="mb-4">
                        <h6>Project & Sheet</h6>
                        <div class="p-3 bg-light rounded">
                            <div class="row">
                                <div class="col-md-4">
                                    <strong>Project:</strong><br>
                                    {{ $qa_item->sheet->phase->project->name }}
                                </div>
                                <div class="col-md-4">
                                    <strong>Sheet:</strong><br>
                                    {{ $qa_item->sheet->number }} - {{ $qa_item->sheet->title }}
                                </div>
                                <div class="col-md-4">
                                    <strong>Discipline:</strong><br>
                                    {{ $qa_item->sheet->discipline }}
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Comments -->
                    @if($qa_item->comments)
                    <div class="mb-4">
                        <h6>Comments</h6>
                        <div class="p-3 bg-light rounded">
                            {{ $qa_item->comments }}
                        </div>
                    </div>
                    @endif

                    <!-- Actions -->
                    <div class="border-top pt-3">
                        <div class="d-flex gap-2">
                            @if(!$qa_item->assigned_to)
                                <form action="{{ route('qa_items.assign-to-me', $qa_item) }}" method="POST">
                                    @csrf
                                    <button type="submit" class="btn btn-success btn-sm">
                                        <i class="fas fa-user-plus me-1"></i>Assign to Me
                                    </button>
                                </form>
                            @endif
                            
                            <form action="{{ route('qa_items.update-status', $qa_item) }}" method="POST">
                                @csrf
                                @method('PATCH')
                                <input type="hidden" name="status" value="in_progress">
                                <button type="submit" class="btn btn-primary btn-sm">
                                    <i class="fas fa-play me-1"></i>Start Working
                                </button>
                            </form>
                            
                            <form action="{{ route('qa_items.update-status', $qa_item) }}" method="POST">
                                @csrf
                                @method('PATCH')
                                <input type="hidden" name="status" value="resolved">
                                <button type="submit" class="btn btn-info btn-sm">
                                    <i class="fas fa-check me-1"></i>Mark Resolved
                                </button>

                            </form>

                            <form action="{{ route('qa_items.update-status', $qa_item) }}" method="POST">
                                @csrf
                                @method('PATCH')
                                <input type="hidden" name="status" value="closed">
                                <button type="submit" class="btn btn-danger btn-sm">
                                    <i class="fas fa-times me-1"></i>Close Item
                                </button>
                            </form>

                     
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection