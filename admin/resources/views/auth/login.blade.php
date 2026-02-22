@extends('layouts.admin.login')

@section('content')
    
<div class="container">
    <div class="row">
        <div class="col-12 col-lg-8 offset-lg-4 col-xl-7 offset-xl-5">
            <div class="main-content">
                <h1 class="h4 text-gray-900 mb-4">
                    <img src="{{ asset('assets/admin/img/dansday_logo.png') }}" class="img-fluid" />
                </h1>
                <h4 class="text-gray-900 mb-4 pb-3">Welcome back!<br/>Please login to your account</h4>
                @if (session('message'))
                    <div class="alert alert-info small mb-3">{{ session('message') }}</div>
                @endif
                <form class="user" method="POST" action="{{ route('login') }}">
                    @csrf
                    <div class="form-group">
                        <input id="email" type="email"
                            class="form-control form-control-user @error('email') is-invalid @enderror"
                            name="email" value="{{ old('email') }}" required autocomplete="email" autofocus
                            placeholder="Enter Email Address..." />
                        @error('email')
                            <span class="invalid-feedback" role="alert">
                                <strong>{{ $message }}</strong>
                            </span>
                        @enderror
                    </div>
                    <div class="form-group">
                        <div class="form-password">
                            <input id="password" type="password"
                                class="form-control form-control-user @error('password') is-invalid @enderror"
                                name="password" required autocomplete="current-password" placeholder="Password" />
                            <i class="far fa-eye password-option password-visible css3animate"></i>
                            <i class="far fa-eye-slash password-option password-hidden css3animate d-none"></i>
                        </div>
                        @error('password')
                            <span class="invalid-feedback" role="alert">
                                <strong>{{ $message }}</strong>
                            </span>
                        @enderror
                    </div>
                    <div class="form-group">
                        <div class="custom-control custom-checkbox small">
                            <div>
                                <input class="custom-control-input" type="checkbox" name="remember" id="remember"
                                    {{ old('remember') ? 'checked' : '' }}>
                                <label class="custom-control-label" for="remember">
                                    {{ __('content.remember_me') }}
                                </label>
                            </div>
                        </div>
                    </div>
                    <button type="submit" class="btn btn-dark btn-user px-5 text-gray-100 css3animate">
                        {{ __('content.login') }}
                    </button>
                    @guest
                        @if (\App\Models\User::count() === 0)
                            <p class="mt-3 small text-gray-600">Don't have an account? <a href="{{ route('register') }}">Register</a></p>
                        @endif
                    @endguest
                </form>                
            </div>   
        </div>         
    </div>
</div>

@endsection
