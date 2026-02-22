@extends('layouts.admin.main')

@section('content')

<div class="container-fluid">

    @include('admin.modules.alert')

    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-gray-800">{{ __('content.home') }}</h1>
    </div>

    <div class="row">
        <div class="col-lg-12">
            <div class="card shadow mb-4">
                <div class="card-header py-3 d-flex justify-content-between align-items-center">
                    <h6 class="font-weight-bold text-primary m-0">{{ __('content.home') }}</h6>
                </div>
                <div class="card-body">
                    <form action="{{ url('/').'/admin/home' }}" method="POST" enctype="multipart/form-data">
                        @csrf
                        @method('PUT')
                        <div class="modal-body">
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group mb-4">
                                        <label for="title" class="form-label">{{ __('content.title') }}</label>
                                        <input class="form-control @error('title') is-invalid @enderror" type="text" name="title" value="{{ $home->title }}" required />
                                        @error('title')<div class="invalid-feedback">{{ __('content.text_not_valid') }} {{ __('content.max_characters') }}: 75.</div>@enderror
                                        <div class="form-text"><span>{{ __('content.line_mark') }}</span></div>
                                    </div>
                                    <div class="form-group mb-4">
                                        <label for="description" class="form-label">{{ __('content.description') }}</label>
                                        <textarea class="form-control @error('description') is-invalid @enderror" name="description" cols="30" rows="10">{{ $home->description }}</textarea>
                                        @error('description')<div class="invalid-feedback">{{ __('content.text_not_valid') }}</div>@enderror
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="submit" class="btn btn-primary">{{ __('content.update') }}</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

@endsection
