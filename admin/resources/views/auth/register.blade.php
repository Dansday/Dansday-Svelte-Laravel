@extends('layouts.admin.login')

@section('content')

<div class="container">
    <div class="row">
        <div class="col-lg-8 offset-lg-2 col-xl-7 offset-xl-5">
            <div class="main-content">
                <h1 class="h4 text-gray-900 mb-4">
                    <img src="{{ asset('assets/admin/img/dansday_logo.png') }}" class="img-fluid" />
                </h1>
                <h4 class="text-gray-900 mb-4 pb-3">Fill all the fields to register</h4>
                <form method="POST" action="{{ route('register') }}" class="user">
                    @csrf
                    <div class="form-group">
                        <input id="name" type="text" class="form-control form-control-user @error('name') is-invalid @enderror" name="name" value="{{ old('name') }}" required autocomplete="name" autofocus placeholder="Name" />
                        @error('name')
                            <span class="invalid-feedback" role="alert">
                                <strong>{{ $message }}</strong>
                            </span>
                        @enderror
                    </div>
                    <div class="form-group">
                        <input id="email" type="email" class="form-control form-control-user @error('email') is-invalid @enderror" name="email" value="{{ old('email') }}" required autocomplete="email" placeholder="Email" />
                        @error('email')
                            <span class="invalid-feedback" role="alert">
                                <strong>{{ $message }}</strong>
                            </span>
                        @enderror
                    </div>
                    <div class="form-group">
                        <input id="password" type="password" class="form-control form-control-user @error('password') is-invalid @enderror" name="password" required autocomplete="new-password" placeholder="Password" />
                        @error('password')
                            <span class="invalid-feedback" role="alert">
                                <strong>{{ $message }}</strong>
                            </span>
                        @enderror
                    </div>
                    <div class="form-group">
                        <input id="password-confirm" type="password" class="form-control form-control-user" name="password_confirmation" required autocomplete="new-password" placeholder="Confirm password" />
                    </div>
                    <button type="submit" class="btn btn-dark btn-user px-5 text-gray-100 css3animate">
                        {{ __('content.register') }}
                    </button>
                    <p class="mt-3 small text-gray-600">Already have an account? <a href="{{ route('login') }}">Login</a></p>
                </form>
            </div>
        </div>
    </div>
</div>

@endsection