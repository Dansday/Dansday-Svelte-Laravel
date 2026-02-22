@extends('layouts.admin.login')

@section('content')
<div class="auth-card">
    <div class="auth-card__header">
        <h1 class="auth-card__title">Admin</h1>
        <p class="auth-card__subtitle">Sign in to your account</p>
    </div>

    @if (session('message'))
        <div class="alert alert-info auth-card__alert" role="alert">{{ session('message') }}</div>
    @endif

    <form class="auth-card__form" method="POST" action="{{ route('login') }}">
        @csrf
        <div class="mb-3">
            <label for="email" class="form-label">Email</label>
            <input id="email" type="email"
                class="form-control form-control-user @error('email') is-invalid @enderror"
                name="email" value="{{ old('email') }}" required autocomplete="email" autofocus
                placeholder="you@example.com" />
            @error('email')
                <div class="invalid-feedback d-block">{{ $message }}</div>
            @enderror
        </div>
        <div class="mb-3">
            <label for="password" class="form-label">Password</label>
            <div class="form-password">
                <input id="password" type="password"
                    class="form-control form-control-user @error('password') is-invalid @enderror"
                    name="password" required autocomplete="current-password" placeholder="••••••••" />
                <i class="far fa-eye password-option password-visible css3animate"></i>
                <i class="far fa-eye-slash password-option password-hidden css3animate d-none"></i>
            </div>
            @error('password')
                <div class="invalid-feedback d-block">{{ $message }}</div>
            @enderror
        </div>
        <div class="mb-4">
            <div class="form-check">
                <input class="form-check-input" type="checkbox" name="remember" id="remember" {{ old('remember') ? 'checked' : '' }}>
                <label class="form-check-label" for="remember">{{ __('content.remember_me') }}</label>
            </div>
        </div>
        <button type="submit" class="btn btn-primary btn-user w-100 mb-3">
            {{ __('content.login') }}
        </button>
        @if (\App\Models\User::count() === 0)
            <p class="auth-card__footer text-center mb-0 small">
                No account yet? <a href="{{ route('register') }}">Register</a>
            </p>
        @endif
    </form>
</div>
@endsection
