@extends('layouts.admin.main')

@section('content')

<div class="container-fluid">

    @include('admin.modules.alert')

    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-gray-800">{{ $post->title }}</h1>
        <a href="{{ url('/') }}/admin/articles/posts" class="css3animate btn btn-primary text-white">
            <i class="fas fa-angle-left mr-2"></i> {{ __('content.back') }}
        </a>
    </div>

    <div class="row">
        <div class="col-lg-12">
            <div class="card shadow mb-4">
                <div class="card-header py-3 d-flex justify-content-between align-items-center">
                    <h6 class="font-weight-bold text-primary m-0">{{ __('content.images') }}</h6>
                </div>
                <div class="card-body">
                    @if (count($gallery_images) > 0)
                        <div class="table-responsive">
                            <table class="table table-bordered datatable" width="100%" cellspacing="0">
                                <thead>
                                    <tr>
                                        <th class="custom-width-25" scope="row">#</th>
                                        <th>{{ __('content.image') }}</th>
                                        <th class="custom-width-action">{{ __('content.action') }}</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @php $i = 1; @endphp
                                    @foreach ($gallery_images as $gallery_image)
                                        <tr>
                                            <th scope="row">{{ $i++ }}</th>
                                            <td>
                                                <a href="{{ asset('/') }}/{{ $gallery_image->image }}" class="css3animate popup-content popup-image text-center text-gray-800 d-flex justify-content-between align-items-center popup-image-big">
                                                    <img src="{{ asset('/') }}/{{ $gallery_image->image }}" class="img-fluid img-size-200" />
                                                    <div class="popup-content-hover css3animate">
                                                        <i class="fas fa-eye text-gray-100 css3animate"></i>
                                                    </div>
                                                </a>
                                            <td>
                                                <div class="btn-group">
                                                    <form class="d-inline-block" action="{{url('/admin/articles/gallery')}}/{{ $gallery_image->id }}" method="POST">
                                                        @csrf
                                                        @method('DELETE')
                                                        <button type="button" class="btn btn-danger" data-bs-toggle="modal" data-bs-target="#deleteImage{{ $gallery_image->id }}">
                                                            <i class="far fa-trash-alt mr-2"></i> {{ __('content.yes_delete') }}
                                                        </button>
                                                        <div class="modal fade" id="deleteImage{{ $gallery_image->id }}" tabindex="-1" role="dialog" aria-labelledby="exampleModalCenterTitle" aria-hidden="true">
                                                            <div class="modal-dialog modal-dialog-centered modal-sm" role="document">
                                                                <div class="modal-content">
                                                                    <div class="modal-header">
                                                                        <h5 class="modal-title" id="exampleModalCenterTitle">{{ __('content.delete') }}</h5>
                                                                        <button type="button" class="close" data-bs-dismiss="modal" aria-label="{{ __('content.close') }}">
                                                                            <span aria-hidden="true">&times;</span>
                                                                        </button>
                                                                    </div>
                                                                    <div class="modal-body text-center">
                                                                        {{ __('content.sure_delete') }}
                                                                        <input type="hidden" name="post_id" value="{{ $gallery_image->article_id }}"/>
                                                                    </div>
                                                                    <div class="modal-footer">
                                                                        <button type="submit" class="btn btn-success">{{ __('content.yes_delete') }}</button>
                                                                        <button type="button" class="btn btn-danger" data-bs-dismiss="modal">{{ __('content.cancel') }}</button>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </form>
                                                </div>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @else
                        {{ __('content.no_images_created_yet') }}
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>

@endsection
