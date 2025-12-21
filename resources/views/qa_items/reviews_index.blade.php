@extends('layouts.app')

@section('content')
<div class="container-fluid px-4 mt-4">

    <h1 class="h3 mb-4 fw-bold"> Reviews</h1>

    {{-- FILTERS --}}
    <form method="GET" action="{{ route('qa_reviews.index') }}" class="card p-3 mb-4 shadow-sm border-0">
        <div class="row g-3">

            <div class="col-md-3">
                <label class="form-label fw-bold">Severity</label>
                <select name="severity" class="form-select">
                    <option value="">All</option>
                    @foreach(['critical','high','medium','low'] as $sev)
                        <option value="{{ $sev }}" @selected(request('severity') === $sev)>
                            {{ ucfirst($sev) }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div class="col-md-3">
                <label class="form-label fw-bold">Reviewer</label>
                <select name="reviewer" class="form-select">
                    <option value="">All</option>
                    @foreach($reviewers as $u)
                        <option value="{{ $u->id }}" @selected(request('reviewer') == $u->id)>
                            {{ $u->name }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div class="col-md-3">
                <label class="form-label fw-bold">Search</label>
                <input type="text"
                       name="search"
                       value="{{ request('search') }}"
                       class="form-control"
                       placeholder="Search comments">
            </div>

            <div class="col-md-3 d-flex align-items-end">
                <button class="btn btn-primary w-100">Filter</button>
            </div>

        </div>
    </form>

    <div class="card shadow-sm border-0">
        <div class="card-body">

            <table class="table table-striped align-middle">
                <thead class="table-dark">
                    <tr>
                        <th>Item</th>
                        <th>Severity</th>
                        <th>Old → New</th>
                        <th>Comment</th>
                        <th>User</th>
                        <th>When</th>
                        <th>Open</th>
                    </tr>
                </thead>

                <tbody>

                @forelse($reviews as $rev)

                    @php
                        $item = $rev->item;
                        $user = $rev->user;

                        // Skip broken rows safely
                        if (!$item) {
                            continue;
                        }

                        switch ($item->severity) {
                            case 'critical': $sevBadge = 'danger'; break;
                            case 'high':     $sevBadge = 'warning'; break;
                            case 'medium':   $sevBadge = 'info'; break;
                            case 'low':      $sevBadge = 'secondary'; break;
                            default:         $sevBadge = 'dark';
                        }

                        $oldStatus = data_get($rev->old_value, 'status', '-');
                        $newStatus = data_get($rev->new_value, 'status', '-');
                    @endphp

                    <tr>
                        {{-- ITEM ID --}}
                        <td class="fw-bold">
                            <a href="{{ route('qa_items.index', $item->sheet_id) }}#item-{{ $item->id }}"
                               class="text-primary">
                                #{{ $item->id }}
                            </a>
                        </td>

                        {{-- SEVERITY --}}
                        <td>
                            <span class="badge bg-{{ $sevBadge }}">
                                {{ ucfirst($item->severity ?? 'unknown') }}
                            </span>
                        </td>

                        {{-- OLD → NEW --}}
                        <td>
                            <strong>{{ $oldStatus }}</strong>
                            →
                            <strong class="text-success">{{ $newStatus }}</strong>
                        </td>

                        {{-- COMMENT --}}
                        <td>{{ $rev->comment }}</td>

                        {{-- USER --}}
                        <td>{{ $user->name ?? '—' }}</td>

                        {{-- TIME --}}
                        <td>{{ optional($rev->created_at)->diffForHumans() }}</td>

                        {{-- OPEN ITEM --}}
                        <td>
                            <a href="{{ route('qa_items.index', $item->sheet_id) }}#item-{{ $item->id }}"
                               class="btn btn-sm btn-primary">
                                View
                            </a>
                        </td>
                    </tr>

                @empty
                    <tr>
                        <td colspan="7" class="text-center py-4 text-muted">
                            No reviews found.
                        </td>
                    </tr>
                @endforelse

                </tbody>
            </table>

            <div class="mt-3">
                {{ $reviews->withQueryString()->links() }}
            </div>

        </div>
    </div>

</div>
@endsection
