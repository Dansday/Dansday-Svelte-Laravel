@extends('layouts.admin.main')

@section('content')

<div class="container-fluid">

    @include('admin.modules.alert')

    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-gray-800">{{ $project->title }}</h1>
    </div>

    <div class="row">
        <div class="col-lg-12">
            <div class="card shadow mb-4">
                <div class="card-header py-3 d-flex justify-content-between align-items-center">
                    <h6 class="font-weight-bold text-primary m-0">{{ __('content.edit_project') }}</h6>
                </div>
                <div class="card-body">
                    <form action="{{ url('/') }}/admin/projects/project/{{$project->id}}" method="POST" class="user" enctype="multipart/form-data">
                        @csrf
                        @method('PUT')
                        <div class="modal-body">
                            <div class="row">
                                <input type="hidden" name="id" value="{{$project->id}}" />
                                <div class="col-md-12 mb-4">
                                    <label for="enable_project" class="form-label">{{ __('content.enable') }}</label>
                                    <div class="form-switch mb-3">
                                        <input class="form-check-input" type="checkbox" id="enable_project" name="enable_project" @php echo ($project->enable == 1) ? 'checked' : ''; @endphp />
                                        <label class="form-check-label" for="enable_project">{{ __('content.enable') }}</label>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="mb-4">
                                        <label for="title" class="form-label">{{ __('content.title') }}</label>
                                        <input class="form-control @error('title') is-invalid @enderror" type="text" name="title" value="{{ $project->title }}" required />
                                        @error('title')
                                            <div class="invalid-feedback">
                                                {{ __('content.text_not_valid') }} {{ __('content.max_characters') }}: 55.
                                            </div>
                                        @enderror
                                    </div>
                                    <div class="mb-4">
                                        <label for="short_desc" class="form-label">{{ __('content.short_desc') }}</label>
                                        <input class="form-control @error('short_desc') is-invalid @enderror" type="text" name="short_desc" value="{{ $project->short_desc }}" required />
                                        @error('short_desc')
                                            <div class="invalid-feedback">
                                                {{ __('content.text_not_valid') }} {{ __('content.max_characters') }}: 55.
                                            </div>
                                        @enderror
                                    </div>
                                    <div class="mb-4">
                                        <div class="form-group">
                                            <label for="image" class="form-label">{{ __('content.image') }}</label>
                                            <div class="d-flex p-3 mb-2 bg-gray-200 justify-content-center">
                                                @php
                                                    if ($project->image != ''):
                                                        $image_link = $project->image;
                                                    else:
                                                        $image_link = 'uploads/img/image_default.png';
                                                    endif;
                                                @endphp
                                                <img src="{{asset('/')}}/@php echo $image_link @endphp" class="img-fluid img-maxsize-200 previewImage_image" />
                                            </div>
                                            <input class="form-control previewImage @error('image') is-invalid @enderror" type="file" name="image" value=""/>
                                            <input type="hidden" name="image_current" value="{{$project->image}}" />
                                            <div class="form-text d-flex justify-content-between">
                                                <span>{{ __('content.image_requirements') }}</span>
                                                <span>{{ __('content.image_size_recommended') }} 900x550px</span>
                                            </div>
                                            @error('image')
                                                <div class="invalid-feedback">
                                                    {{ __('content.error_validation_image') }}
                                                </div>
                                            @enderror
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="mb-4">
                                        <label for="category" class="form-label">{{ __('content.category') }}</label>
                                        <select class="form-select" name="category">
                                            @foreach ($categories as $category)
                                                <option value="{{$category->id}}" @php echo ($project->category_id == $category->id) ? 'selected' : ''; @endphp>{{ $category->name}}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>

                                <div class="col-12 mb-4">
                                    <label for="description" class="form-label">{{ __('content.description') }}</label>
                                    <textarea class="form-control summernote @error('description') is-invalid @enderror" name="description" data-folder="uploads/img/temp" data-route="{{url('/')}}" data-code="{{$project->images_code}}">{{ $project->description }}</textarea>
                                    @error('description')
                                        <div class="invalid-feedback d-none">
                                            {{ __('content.text_not_valid') }}
                                        </div>
                                    @enderror
                                </div>
                    
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="submit" class="btn btn-primary check-summernote">
                                {{ __('content.update') }}
                            </button>
                            <a href="{{ url('/') }}/admin/projects/projects">
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
