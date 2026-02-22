@extends('layouts.admin.main')

@section('content')

<div class="container-fluid">

    @include('admin.modules.alert')

    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-gray-800">{{ $skill->title }}</h1>
    </div>

    <div class="row">
        <div class="col-lg-12">
            <div class="card shadow mb-4">
                <div class="card-header py-3 d-flex justify-content-between align-items-center">
                    <h6 class="font-weight-bold text-primary m-0">{{ __('content.edit_skill') }}</h6>
                </div>
                <div class="card-body">
                    <form action="{{ url('/').'/admin/skills' }}/{{ $skill->id }}" method="POST" class="user" enctype="multipart/form-data">
                        @csrf
                        @method('PUT')
                        <div class="modal-body">
                            <div class="row">
                                <input type="hidden" name="type" value="{{ $skill->type }}" />
                                <input type="hidden" name="order" value="{{ $skill->order }}" />
                                <div class="col-md-6 mb-3">
                                    <label for="title" class="form-label">{{ __('content.title') }}</label>
                                    <input class="form-control @error('title') is-invalid @enderror" type="text" name="title" value="{{ $skill->title }}" required />
                                    @error('title')
                                        <div class="invalid-feedback">
                                            {{ __('content.text_not_valid') }} {{ __('content.max_characters') }}: 55.
                                        </div>
                                    @enderror
                                </div>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="submit" class="btn btn-primary">
                                {{ __('content.update') }}
                            </button>
                            <a href="{{ url('/') }}/admin/skills">
                                <button type="button" class="btn btn-secondary">{{ __('content.cancel') }}</button>
                            </a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

@endsection
