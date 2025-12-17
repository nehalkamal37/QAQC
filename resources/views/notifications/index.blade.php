{{-- resources/views/notifications/index.blade.php --}}
@extends('layouts.app')

@section('content')
<div class="container-fluid px-4">
    <div class="d-flex justify-content-between align-items-center py-3">
        <div>
            <h4 class="mb-1">Notifications</h4>
            <p class="text-muted mb-0">Your recent activities and alerts</p>
        </div>
        <div>
            @if(auth()->user()->unreadUserNotifications()->exists())
                <form action="{{ route('notifications.mark-all-read') }}" method="POST" class="d-inline">
                    @csrf
                    <button type="submit" class="btn btn-outline-primary btn-sm">
                        <i class="fas fa-check-double me-1"></i>Mark All as Read
                    </button>
                </form>
            @endif
        </div>
    </div>

    <div class="card">
        <div class="card-body">
            @forelse($notifications as $notification)
                <div class="border-bottom pb-3 mb-3 {{ $notification->is_read ? '' : 'bg-light rounded p-3' }}">
                    <div class="d-flex justify-content-between align-items-start">
                     

                        <div class="flex-grow-1">
    <h6 class="{{ $notification->is_read ? 'text-muted' : 'fw-bold' }}">
        {{ $notification->title }}
    </h6>


    <p class="mb-1">
    @if($notification->actor)
        <strong>{{ $notification->actor->name }}</strong>
    @else
        <strong>System</strong>
    @endif
    {{ 'has '.  $notification->message }}
</p>

    {{-- الرسالة الأساسية 
    <p class="mb-1">{{ $notification->message }}</p>
--}}
    {{-- لو النوتيفيكشن مرتبطة بـ QA Item --}}
    @if(isset($notification->data['qa_item_id'] )  && $notification->qa_item_link)
        <p class="mb-1">
            <a href="{{ $notification->qa_item_link }}" class="text-decoration-underline">
                View QA Item #{{ $notification->data['qa_item_id'] }}
            </a>
        </p>
    @endif

    <small class="text-muted">
        {{ $notification->created_at->format('M j, Y g:i A') }}
    </small>
</div>

                        <div class="d-flex gap-2">
                            @if(!$notification->is_read)
                                <form action="{{ route('notifications.mark-read', $notification) }}" method="POST">
                                    @csrf
                                    <button type="submit" class="btn btn-sm btn-outline-success" title="Mark as read">
                                        <i class="fas fa-check">mark as read</i>
                                    </button>
                                </form>
                                @else
                                
                                    <button type="" class="btn btn-sm btn-outline-secondary" title="Mark as read">
                                        <i class="fas fa-undo">readen</i>
                                    </button>
                            @endif
                            <form action="{{ route('notifications.destroy', $notification) }}" method="POST">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-sm btn-outline-danger" title="Delete">
                                    <i class="fas fa-trash">delete</i>
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            @empty
                <div class="text-center py-5">
                    <i class="fas fa-bell-slash fa-3x text-muted mb-3"></i>
                    <h5 class="text-muted">No notifications</h5>
                    <p class="text-muted">You're all caught up!</p>
                </div>
            @endforelse

            {{ $notifications->links() }}
        </div>
    </div>
</div>
@endsection