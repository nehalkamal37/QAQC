@extends('layouts.app')

@section('content')
<main class="content">
    <div class="container-fluid p-0">

        <div class="mb-3">
            <h1 class="h3 d-inline align-middle">My Profile</h1>
        </div>

              <div class="row justify-content-center ">

            {{-- LEFT PROFILE CARD --}}
    <div class="col-12 col-sm-10 col-md-8 col-lg-7 col-xl-6">
     
                <div class="card mb-3">
                    <div class="card-header">
                        <h5 class="card-title mb-0">Profile Details</h5>
                    </div>

                    <div class="card-body text-center">

                        {{-- Avatar --}}
                        <img src="https://ui-avatars.com/api/?name={{ urlencode($user->name) }}&background=0D6EFD&color=fff&size=128"
                             class="img-fluid rounded-circle mb-2" width="128" height="128"/>

                        {{-- Name --}}
                        <h5 class="card-title mb-0">{{ $user->name }}</h5>

                        {{-- Role --}}
                        <div class="text-muted mb-2">
                            {{ $user->role->name ?? 'No Role Assigned' }}
                        </div>

                        {{-- Email --}}
                        <div class="mb-2">
                            <span class="badge bg-light text-dark">{{ $user->email }}</span>
                        </div>
                    </div>

                    <hr class="my-0" />

                    <div class="card-body">
                        <h5 class="h6 card-title">Account Info</h5>
                        <ul class="list-unstyled mb-0">
                            <li class="mb-1">
                                <span data-feather="calendar" class="feather-sm me-1"></span>
                                Joined: <b>{{ $user->created_at->format('d M, Y') }}</b>
                            </li>

                            <li class="mb-1">
                                <span data-feather="lock" class="feather-sm me-1"></span>
                                Status:
                                <span class="badge bg-success">Active</span>
                            </li>
                        </ul>
                    </div>

                </div>
            </div>

            {{-- RIGHT SIDE -
            <div class="col-md-8 col-xl-9">
                <div class="card">
                    <div class="card-header">
                        <h5 class="card-title mb-0">Recent Activities</h5>
                    </div>

                    <div class="card-body">
                        <p class="text-muted">Activity tracking will be added later...</p>
                        <p class="text-muted">For now, enjoy your new clean profile page 😊</p>
                    </div>

                </div>
            </div>
--}}

        </div>

    </div>
</main>

<style>
    /* ============================= */
/* PROFILE HEADER – MOBILE */
/* ============================= */
@media (max-width: 768px) {

  .content h1.h3 {
    font-size: 1.4rem;
  }

  .content .mb-3 {
    margin-bottom: 1rem !important;
  }
}
/* ============================= */
/* PROFILE CARD – MOBILE */
/* ============================= */
@media (max-width: 768px) {

  .card-body.text-center img {
    width: 96px !important;
    height: 96px !important;
  }

  .card-body.text-center h5 {
    font-size: 1.1rem;
  }

  .card-body.text-center .badge {
    font-size: 12px;
    word-break: break-all;
  }
}
/* ============================= */
/* PROFILE LAYOUT – MOBILE */
/* ============================= */
@media (max-width: 768px) {

  .row > [class*="col-"] {
    margin-bottom: 1rem;
  }
}
/* ============================= */
/* ACCOUNT INFO – MOBILE */
/* ============================= */
@media (max-width: 576px) {

  .card-body ul li {
    font-size: 13px;
  }

  .card-body ul li span {
    margin-right: 6px;
  }
}
/* ============================= */
/* ACTIVITY CARD – MOBILE */
/* ============================= */
@media (max-width: 576px) {

  .card-header h5 {
    font-size: 1rem;
  }

  .card-body p {
    font-size: 13px;
  }
}

</style>
@endsection
