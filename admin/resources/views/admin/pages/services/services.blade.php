@extends('layouts.admin.main')

@section('content')

<div class="container-fluid">

    @include('admin.modules.alert')

    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-gray-800">{{ __('content.services') }}</h1>
    </div>

    <div class="row">
        
        <div class="col-lg-12">
            <div class="card shadow mb-4">
                <div class="card-header py-3 d-flex justify-content-between align-items-center">
                    <h6 class="font-weight-bold text-primary m-0">{{ __('content.services') }}</h6>
                    <button type="button" class="btn btn-primary btn-round d-inline" data-bs-toggle="modal" data-bs-target="#serviceNewModal">
                        <i class="fas fa-plus small"></i>
                        {{ __('content.add_service') }}
                    </button>
                </div>
                <div class="card-body">
                    @if (count($services) > 0)
                        <div class="table-responsive">
                            <table class="table table-bordered datatable" width="100%" cellspacing="0">
                                <thead>
                                    <tr>
                                        <th class="custom-width" scope="col">#</th>
                                        <th>{{ __('content.title') }}</th>
                                        <th>{{ __('content.description') }}</th>
                                        <th>{{ __('content.order') }}</th>
                                        <th class="custom-width-action">{{ __('content.action') }}</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @php $i = 1; @endphp
                                    @foreach ($services as $service)
                                        <tr>
                                            <td>{{ $i }}</td>
                                            <td>{{ $service->title }}</td>
                                            <td>{{ $service->description }}</td>
                                            <td>
                                                <div class="btn-group">
                                                    @if ($i < count($services))
                                                        <a href="{{ url('/') }}/admin/services/order-down/{{ $service->id }}" class="btn btn-warning btn-sm mr-1">
                                                            <i class="fas fa-arrow-down"></i>
                                                        </a>
                                                    @endif
                                                    @if ($i != 1)
                                                        <a href="{{ url('/') }}/admin/services/order-up/{{ $service->id }}" class="btn btn-warning btn-sm mr-1">
                                                            <i class="fas fa-arrow-up"></i>
                                                        </a>
                                                    @endif
                                                </div>
                                            </td>
                                            <td>
                                                <div class="btn-group">
                                                    <a href="{{ url('/') }}/admin/services/{{ $service->id }}" class="btn btn-primary btn-sm mr-1">
                                                        <i class="fas fa-pencil-alt"></i>
                                                    </a>
                                                    <form class="d-inline-block" action="{{url('/admin/services')}}/{{ $service->id }}" method="POST">
                                                        @csrf
                                                        @method('DELETE')
                                                        <button type="button" class="btn btn-danger btn-sm" data-bs-toggle="modal" data-bs-target="#deleteService{{ $service->id }}">
                                                            <i class="far fa-trash-alt"></i>
                                                        </button>
                                                        <div class="modal fade" id="deleteService{{ $service->id }}" tabindex="-1" role="dialog" aria-labelledby="exampleModalCenterTitle" aria-hidden="true">
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
                        {{ __('content.no_services_created_yet') }}
                    @endif
                </div>
            </div>
        </div>

    </div>
</div>

<div class="modal fade" id="serviceNewModal" tabindex="-1" role="dialog" aria-labelledby="serviceNewModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="exampleModalLabel">{{ __('content.new_service') }}</h5>
                <button type="button" class="close" data-bs-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form action="{{ url('/').'/admin/services' }}" method="POST" class="user">
                @csrf
                <div class="modal-body">
                    <div class="row">
                        <input type="hidden" name="order" value="@php echo (count($services) > 0) ? $i : 1; @endphp" />
                        <div class="col-md-12 mb-3">
                            <label for="title" class="form-label">{{ __('content.title') }}</label>
                            <input class="form-control @error('title') is-invalid @enderror" type="text" name="title" required value="{{old('title')}}"/>
                            @error('title')
                                <div class="invalid-feedback">
                                    {{ __('content.text_not_valid') }} {{ __('content.max_characters') }}: 55.
                                </div>
                            @enderror
                        </div>
                        <div class="col-md-12 mb-3">
                            <label for="description" class="form-label">{{ __('content.description') }}</label>
                            <textarea class="form-control @error('description') is-invalid @enderror" name="description" rows="4" required>{{old('description')}}</textarea>
                            @error('description')
                                <div class="invalid-feedback">
                                    {{ __('content.text_not_valid') }} {{ __('content.max_characters') }}: 255.
                                </div>
                            @enderror
                        </div>
                        <div class="col-md-12">
                            <div class="form-group info-content mb-4">
                                <label for="info" class="form-label">{{ __('content.info') }}</label>
                                <div class="row">
                                    <div class="col-10">
                                        <div class="input-group mb-3">
                                            <input type="text" name="info_label" id="info_label_info-list" class="form-control">
                                            <input type="hidden" name="kinfo_value" id="info_value_info-list" class="form-control">
                                        </div>
                                    </div>
                                    <div class="col-2">
                                        <button type="button" class="btn btn-success w-100 addInfo" data-target="info-list">{{ __('content.add') }}</button>
                                    </div>
                                    <div class="invalid-feedback d-none invalid-info-list">
                                        {{ __('content.characters_not_valid') }}
                                    </div>
                                </div>
                                <input type="hidden" name="info" value="" id="info-list" />
                                <div class="table-elements p-4">
                                    <table class="table table-info-list w-100 p-4" data-target="info-list">
                                        <tbody>
                                            
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="submit" class="btn btn-primary">
                        {{ __('content.add') }}
                    </button>
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">{{ __('content.close') }}</button>
                </div>
            </form>
        </div>
    </div>
</div>

@if (Session::has('error-modal'))
    <input class="openModal" data-id="serviceNewModal" type="hidden" val="1" />
@endif

@endsection
