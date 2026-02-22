@extends('layouts.admin.main')

@section('content')

<div class="container-fluid">

    @include('admin.modules.alert')

    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-gray-800">{{ __('content.projects') }}</h1>
    </div>

    <div class="row">
        
        {{-- CATEGORIES TABLE --}}
        <div class="col-lg-12 mb-4">
            <div class="card shadow mb-4">
                <div class="card-header py-3 d-flex justify-content-between align-items-center">
                    <h6 class="font-weight-bold text-primary m-0">{{ __('content.projects') }}</h6>
                    <a href="{{ url('/') }}/admin/projects/project" class="btn btn-primary btn-round d-inline">
                        <i class="fas fa-plus small"></i>
                        {{ __('content.add_project') }}
                    </a>
                </div>
                <div class="card-body">
                    @if (count($projects) > 0)
                        <div class="table-responsive">
                            <table class="table table-bordered datatable" width="100%" cellspacing="0">
                                <thead>
                                    <tr>
                                        <th class="custom-width" scope="col">#</th>
                                        <th>{{ __('content.status') }}</th>
                                        <th>{{ __('content.title') }}</th>
                                        <th>{{ __('content.category') }}</th>
                                        <th class="max-w-150">{{ __('content.image') }}</th>
                                        <th>{{ __('content.short_desc') }}</th>
                                        <th>{{ __('content.order') }}</th>
                                        <th class="custom-width-action">{{ __('content.action') }}</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @php $i = 1; @endphp
                                    @foreach ($projects as $project)
                                        <tr>
                                            <td>{{ $i }}</td>
                                            <td>
                                                @if ($project->enable == 0)
                                                    <i class="fas fa-circle h4 text-danger" data-bs-toggle="tooltip" data-bs-placement="bottom" title="{{ __('content.disabled') }}"></i>
                                                @else
                                                    <i class="fas fa-circle h4 text-success" data-bs-toggle="tooltip" data-bs-placement="bottom" title="{{ __('content.enabled') }}"></i>
                                                @endif
                                            </td>
                                            <td>{{ $project->title }}</td>
                                            <td>
                                            @foreach ($categories as $category )
                                                @if( $project->category_id == $category->id)
                                                    {{$category->name}}
                                                @endif
                                            @endforeach
                                            </td>
                                            <td>
                                                <a href="{{url('/')}}/{{ $project->image }}" class="css3animate popup-content popup-image text-center text-gray-800 d-flex justify-content-between align-items-center w-100">
                                                    <img class="img-fluid" src="{{url('/')}}/{{ $project->image }}" />
                                                    <div class="popup-content-hover css3animate">
                                                        <i class="fas fa-search-plus text-gray-100 css3animate"></i>
                                                    </div>
                                                </a>
                                            </td>
                                            <td>{{ $project->short_desc }}</td>
                                            <td>
                                                <div class="btn-group">
                                                    @if ($i < count($projects))
                                                        <a href="{{ url('/') }}/admin/projects/projects/order-down/{{ $project->id }}" class="btn btn-warning btn-sm mr-1">
                                                            <i class="fas fa-arrow-down"></i>
                                                        </a>
                                                    @endif
                                                    @if ($i != 1)
                                                        <a href="{{ url('/') }}/admin/projects/projects/order-up/{{ $project->id }}" class="btn btn-warning btn-sm mr-1">
                                                            <i class="fas fa-arrow-up"></i>
                                                        </a>
                                                    @endif
                                                </div>
                                            </td>
                                            <td>
                                                <div class="btn-group">
                                                    <a href="{{ url('/') }}/admin/projects/project/{{ $project->id }}" class="btn btn-primary btn-sm mr-1">
                                                        <i class="fas fa-pencil-alt"></i>
                                                    </a>
                                                    <form class="d-inline-block" action="{{url('/admin/projects/project')}}/{{ $project->id }}" method="POST">
                                                        @csrf
                                                        @method('DELETE')
                                                        <button type="button" class="btn btn-danger btn-sm" data-bs-toggle="modal" data-bs-target="#deleteProject{{ $project->id }}">
                                                            <i class="far fa-trash-alt"></i>
                                                        </button>
                                                        <div class="modal fade" id="deleteProject{{ $project->id }}" tabindex="-1" role="dialog" aria-labelledby="exampleModalCenterTitle" aria-hidden="true">
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
                                        @php $i++; @endphp
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @else
                        {{ __('content.no_projects_created_yet') }}
                    @endif
                </div>
            </div>
        </div>

    </div>
</div>

@endsection
