@extends('layouts.app')

@section('content')
<div class="container-fluid px-4 mt-4">

    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3">Edit QA Item #{{ $qaItem->id }}</h1>
        <a href="{{ route('qa_items.index', $qaItem->sheet_id) }}" class="btn btn-secondary">← Back</a>
    </div>

    <div class="card shadow-sm">
        <div class="card-body">

            <form action="{{ route('qa_items.update', [$qaItem->sheet_id, $qaItem->id]) }}" method="POST">
                @csrf
                @method('PUT')

                {{-- ============================ --}}
                {{-- GENERAL QA ITEM FIELDS       --}}
                {{-- ============================ --}}
                <h5 class="fw-bold mb-3">General Item Info</h5>

                <div class="mb-3">
                    <label class="form-label fw-bold">Item Title</label>
                    <input type="text"
                        name="title"
                        class="form-control"
                        value="{{ old('title', $projectStatus->title)  }}"
                        required>   
                </div>

                <div class="mb-3">
                    <label class="form-label fw-bold">Category</label>
                    <input type="text"                          
                        name="category"
                        class="form-control"
                        value="{{ old('category', $projectStatus->category) }}"
                        required>
                </div>

                {{-- DESCRIPTION --}}
                <div class="mb-3">
                    <label class="form-label fw-bold">Item Description</label>
                    <textarea name="item_description" class="form-control" rows="3" required>{{ old('item_description', $qaItem->item_description) }}</textarea>
                </div>

                <div class="mb-3">
                    <label class="form-label fw-bold">Status</label>
                    <select name="status" class="form-select">      
                        @foreach (['open', 'in_progress', 'needs_info', 'resolved', 'verified', 'closed'] as $status)
                            <option value="{{ $status }}" {{ $qaItem->status === $status ? 'selected' : '' }}>
                                {{ ucfirst(str_replace('_', ' ', $status)) }}
                            </option>
                        @endforeach
                    </select>
                </div>

                {{-- SEVERITY --}}
                <div class="mb-3">
                    <label class="form-label fw-bold">Severity</label>
                    <select name="severity" class="form-select">
                        @foreach(['critical','high','medium','low'] as $s)
                            <option value="{{ $s }}" {{ $qaItem->severity === $s ? 'selected' : '' }}>
                                {{ ucfirst($s) }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <hr class="my-4">

                {{-- ============================ --}}
                {{-- PROJECT-SPECIFIC FIELDS      --}}
                {{-- ============================ --}}
                <h5 class="fw-bold">Project-Specific Status</h5>

                {{-- APPLICABLE --}}
                <div class="mb-3">
                    <label class="form-label">Applicable</label>
                    <select name="applicable" class="form-select">
                        <option value="0" {{ optional($projectStatus)->applicable ? '' : 'selected' }}>No</option>
                        <option value="1" {{ optional($projectStatus)->applicable ? 'selected' : '' }}>Yes</option>
                    </select>
                </div>

                {{-- INCORPORATED --}}
                <div class="mb-3">
                    <label class="form-label">Incorporated</label>
                    <select name="incorporated" class="form-select">
                        <option value="0" {{ optional($projectStatus)->incorporated ? '' : 'selected' }}>No</option>
                        <option value="1" {{ optional($projectStatus)->incorporated ? 'selected' : '' }}>Yes</option>
                    </select>
                </div>

                {{-- CONFIRMED --}}
                <div class="mb-3">
                    <label class="form-label">Confirmed</label>
                    <select name="confirmed" class="form-select">
                        <option value="0" {{ optional($projectStatus)->confirmed ? '' : 'selected' }}>No</option>
                        <option value="1" {{ optional($projectStatus)->confirmed ? 'selected' : '' }}>Yes</option>
                    </select>
                </div>

                {{-- PROJECT COMMENTS --}}
                <div class="mb-3">
                    <label class="form-label">Project Comments</label>
                    <textarea name="project_comments" class="form-control" rows="2">{{ optional($projectStatus)->comments }}</textarea>
                </div>

                {{-- PROJECT DUE DATE --}}
                <div class="mb-3">
                    <label class="form-label">Project Due Date</label>
                    <input type="date" 
                        name="project_due_date" 
                        class="form-control"
                        value="{{ optional($projectStatus)->due_date }}">
                </div>

                {{-- ASSIGNED TO (PROJECT) --}}
                <div class="mb-3">
                    <label class="form-label">Assigned To (User)</label>
                    <select name="project_assigned_to" class="form-select">
                        <option value="">-- Select User --</option>
                        @foreach($users as $user)
                            <option value="{{ $user->id }}"
                                {{ optional($projectStatus)->assigned_to == $user->id ? 'selected' : '' }}>
                                {{ $user->name }}
                            </option>
                        @endforeach
                    </select>
                </div>

                {{-- SUBMIT --}}
                <div class="text-end mt-4">
                    <button type="submit" class="btn btn-success px-4">Update Item</button>
                </div>

            </form>

        </div>
    </div>

</div>
@endsection
