@extends('layouts.admin.login')

@section('content')
<div class="auth-card">
    <div class="auth-card__header">
        <h1 class="auth-card__title">Admin</h1>
        <p class="auth-card__subtitle">Create your account</p>
    </div>

    <form class="auth-card__form" method="POST" action="{{ route('register') }}">
        @csrf
        <div class="mb-3">
            <label for="name" class="form-label">Name</label>
            <input id="name" type="text"
                class="form-control form-control-user @error('name') is-invalid @enderror"
                name="name" value="{{ old('name') }}" required autocomplete="name" autofocus
                placeholder="Your name" />
            @error('name')
                <div class="invalid-feedback d-block">{{ $message }}</div>
            @enderror
        </div>
        <div class="mb-3">
            <label for="email" class="form-label">Email</label>
            <input id="email" type="email"
                class="form-control form-control-user @error('email') is-invalid @enderror"
                name="email" value="{{ old('email') }}" required autocomplete="email"
                placeholder="you@example.com" />
            @error('email')
                <div class="invalid-feedback d-block">{{ $message }}</div>
            @enderror
        </div>
        <div class="mb-3">
            <label for="password" class="form-label">Password</label>
            <input id="password" type="password"
                class="form-control form-control-user @error('password') is-invalid @enderror"
                name="password" required autocomplete="new-password" placeholder="••••••••" />
            @error('password')
                <div class="invalid-feedback d-block">{{ $message }}</div>
            @enderror
        </div>
        <div class="mb-4">
            <label for="password-confirm" class="form-label">Confirm password</label>
            <input id="password-confirm" type="password" class="form-control form-control-user"
                name="password_confirmation" required autocomplete="new-password" placeholder="••••••••" />
        </div>
        <button type="submit" class="btn btn-primary btn-user w-100 mb-3">
            {{ __('content.register') }}
        </button>
        <p class="auth-card__footer text-center mb-0 small">
            Already have an account? <a href="{{ route('login') }}">Login</a>
        </p>
    </form>
</div>
@endsection
