<!DOCTYPE html>
<html lang="en">

<head>
	<meta charset="utf-8">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<title>Sign Up | QAQC Tool</title>

	<link rel="shortcut icon" href="{{ asset('dash/img/icons/icon-48x48.png') }}" />
	<link href="{{ asset('dash/css/app.css') }}" rel="stylesheet">
	<link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;600&display=swap" rel="stylesheet">
</head>

<body>
	<main class="d-flex w-100">
		<div class="container d-flex flex-column">
			<div class="row vh-100">
				<div class="col-sm-10 col-md-8 col-lg-6 col-xl-5 mx-auto d-table h-100">
					<div class="d-table-cell align-middle">

						<div class="text-center mt-4">
							<h1 class="h2">Create your account</h1>
							<p class="lead">
								Join QAQC Tool and start tracking your projects.
							</p>
						</div>

						<div class="card">
							<div class="card-body">
								<div class="m-sm-3">
									<form method="POST" action="{{ route('register') }}">
										@csrf

										<div class="mb-3">
											<label class="form-label">Full name</label>
											<input class="form-control form-control-lg" type="text" name="name" placeholder="Enter your name" value="{{ old('name') }}" required>
										
                                            @php
    if (!isset($errors)) {
        $errors = session('errors') ?: new \Illuminate\Support\ViewErrorBag;
    }
@endphp

										</div>

										<div class="mb-3">
											<label class="form-label">Email</label>
											<input class="form-control form-control-lg" type="email" name="email" placeholder="Enter your email" value="{{ old('email') }}" required>
											@error('email')
												<small class="text-danger">{{ $message }}</small>
											@enderror
										</div>

										<div class="mb-3">
											<label class="form-label">Password</label>
											<input class="form-control form-control-lg" type="password" name="password" placeholder="Enter password" required>
											@error('password')
												<small class="text-danger">{{ $message }}</small>
											@enderror
										</div>

										<div class="mb-3">
											<label class="form-label">Confirm Password</label>
											<input class="form-control form-control-lg" type="password" name="password_confirmation" placeholder="Confirm password" required>
										</div>
<div class="mb-3">
    <label class="form-label">Role</label>
    <select class="form-control form-control-lg" name="role_id" required>
        <option value="">Select Role</option>
        @foreach ($roles as $role)
            <option value="{{ $role->id }}" {{ old('role_id') == $role->id ? 'selected' : '' }}>
                {{ ucfirst($role->name) }}
            </option>
        @endforeach
    </select>
    @error('role_id')
        <small class="text-danger">{{ $message }}</small>
    @enderror
</div>

										<div class="d-grid gap-2 mt-3">
											<button type="submit" class="btn btn-lg btn-primary">Sign up</button>
										</div>
									</form>
								</div>
							</div>
						</div>

						<div class="text-center mb-3">
							Already have an account?
							<a href="{{ route('login') }}">Log in</a>
						</div>
					</div>
				</div>
			</div>
		</div>
	</main>

	<script src="{{ asset('dash/js/app.js') }}"></script>
</body>

</html>
