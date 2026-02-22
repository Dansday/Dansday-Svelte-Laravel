@extends('layouts.admin.main')

@section('content')

<div class="container-fluid">

    @include('admin.modules.alert')

    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-gray-800">{{ __('content.profile_settings') }}</h1>
    </div>

    <div class="row">
        <div class="col-lg-12">
            <div class="card shadow mb-4">
                <div class="card-header py-3 d-flex justify-content-between align-items-center">
                    <h6 class="font-weight-bold text-primary m-0">{{ $user->name }}</h6>
                </div>
                <div class="card-body">
                    <form action="{{ url('/').'/admin/profile' }}" method="POST" enctype="multipart/form-data">
                        @csrf
                        @method('PUT')
                        <div class="modal-body">
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <div class="form-group">
                                        <label for="name" class="form-label">{{ __('content.name') }}</label>
                                        <input class="form-control @error('name') is-invalid @enderror" type="text" name="name" value="{{$user->name}}" />
                                        @error('name')
                                            <div class="invalid-feedback">
                                                {{ __('content.text_not_valid') }} {{ __('content.max_characters') }}: 55.
                                            </div>
                                        @enderror
                                    </div>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <div class="form-group">
                                        <label for="email" class="form-label">{{ __('content.email') }}</label>
                                        <input class="form-control @error('email') is-invalid @enderror" type="text" name="email" value="{{$user->email}}" />
                                        @error('email')
                                            <div class="invalid-feedback">
                                                {{ __('content.email_not_valid') }}
                                            </div>
                                        @enderror
                                    </div>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <div class="form-group">
                                        <label for="image_profile" class="form-label">{{ __('content.image') }}</label>
                                        @php
                                            $imgProfileUrl = ($user->image != '') ? $user->image : 'uploads/img/image_default.png';
                                        @endphp
                                        <div class="d-flex p-3 mb-3 bg-gray-200 justify-content-center">
                                            <img src="{{ asset($imgProfileUrl) }}" class="img-fluid img-maxsize-200 previewImage_image_profile" />
                                        </div>
                                        <input class="form-control previewImage @error('image_profile') is-invalid @enderror" type="file" name="image_profile" value=""/>
                                        <input type="hidden" name="image_profile_current" value="{{$user->image}}" />
                                        <div class="form-text d-flex justify-content-between">
                                            <span>{{ __('content.image_requirements') }}</span>
                                            <span>{{ __('content.image_size_recommended') }} 50x50px</span>
                                        </div>
                                        @error('image_profile')
                                            <div class="invalid-feedback">
                                                {{ __('content.error_validation_image') }}
                                            </div>
                                        @enderror
                                    </div>
                                </div>
                                <div class="col-12">
                                    <hr class="mt-4 mb-5 border-0">
                                    <h4 class="mt-3 mb-2 text-gray-800 fw-bold mb-3">{{ __('content.change_password') }}</h4>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <div class="form-group">
                                        <label for="pass_current" class="form-label">{{ __('content.pass_current') }}</label>
                                        <div class="form-password">
                                            <input class="form-control 
                                                @error('pass_current') is-invalid @enderror
                                                @if (Session::has('error-validation-pass-current')) is-invalid @endif" 
                                                type="password" name="pass_current" value="" />
                                            <i class="far fa-eye password-option password-visible css3animate"></i>
                                            <i class="far fa-eye-slash password-option password-hidden css3animate d-none"></i>
                                        </div>
                                        @error('pass_current')
                                            <div class="invalid-feedback">
                                                {{ __('content.password_required') }}
                                            </div>
                                        @enderror
                                        @if (Session::has('error-validation-pass-current'))
                                            <div class="invalid-feedback">
                                                {{ __('content.password_not_valid') }}
                                            </div>
                                        @endif
                                    </div>
                                </div>
                                <div class="col-md-6 mb-3"></div>
                                <div class="col-md-6 mb-3">
                                    <div class="form-group">
                                        <label for="pass_new_1" class="form-label">{{ __('content.pass_new_1') }}</label>
                                        <div class="form-password">
                                            <input class="form-control 
                                                @error('pass_new_1') is-invalid @enderror
                                                @if (Session::has('error-validation-new-pass')) is-invalid @endif" 
                                                type="password" name="pass_new_1" value="" />
                                            <i class="far fa-eye password-option password-visible css3animate"></i>
                                            <i class="far fa-eye-slash password-option password-hidden css3animate d-none"></i>
                                        </div>
                                        <div id="passwordHelpBlock" class="form-text">
                                            Password: must be at least 10 characters in length and contain at least one lowercase and uppercase letter, one digit and a special character: @$!%*#?&-_
                                        </div>
                                        @error('pass_new_1')
                                            <div class="invalid-feedback">
                                                {{ __('content.password_required') }}
                                            </div>
                                        @enderror
                                        @if (Session::has('error-validation-new-pass'))
                                            <div class="invalid-feedback">
                                                {{ __('content.password_not_same') }} 
                                            </div>
                                        @endif
                                    </div>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <div class="form-group">
                                        <label for="pass_new_2" class="form-label">{{ __('content.pass_new_2') }}</label>
                                        <div class="form-password">
                                            <input class="form-control 
                                                @error('pass_new_2') is-invalid @enderror
                                                @if (Session::has('error-validation-new-pass')) is-invalid @endif" 
                                                type="password" name="pass_new_2" value="" />
                                            <i class="far fa-eye password-option password-visible css3animate"></i>
                                            <i class="far fa-eye-slash password-option password-hidden css3animate d-none"></i>
                                        </div>
                                        <div id="passwordHelpBlock" class="form-text">
                                            Password: must be at least 10 characters in length and contain at least one lowercase and uppercase letter, one digit and a special character: @$!%*#?&-_
                                        </div>
                                        @error('pass_new_2')
                                            <div class="invalid-feedback">
                                                {{ __('content.password_required') }}
                                            </div>
                                        @enderror
                                        @if (Session::has('error-validation-new-pass'))
                                            <div class="invalid-feedback">
                                                {{ __('content.password_not_same') }} 
                                            </div>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="submit" class="btn btn-primary">
                                {{ __('content.update') }}
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

@endsection
