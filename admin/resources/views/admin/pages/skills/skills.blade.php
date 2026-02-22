@extends('layouts.admin.main')

@section('content')

<div class="container-fluid">

    @include('admin.modules.alert')

    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-gray-800">{{ __('content.skills') }}</h1>
        <button type="button" class="btn btn-primary btn-round d-inline" data-bs-toggle="modal" data-bs-target="#skillNewModal">
            <i class="fas fa-plus small"></i>
            {{ __('content.add_skill') }}
        </button>
    </div>

    <div class="row">
        
        <div class="col-lg-6">
            <div class="card shadow mb-4">
                <div class="card-header py-3 d-flex justify-content-between align-items-center">
                    <h6 class="font-weight-bold text-primary m-0">{{ __('content.skills_design') }}</h6>
                </div>
                <div class="card-body">
                    @if (count($design_skills) > 0)
                        <div class="table-responsive">
                            <table class="table table-bordered datatable" width="100%" cellspacing="0">
                                <thead>
                                    <tr>
                                        <th class="custom-width" scope="col">#</th>
                                        <th>{{ __('content.title') }}</th>
                                        <th>{{ __('content.order') }}</th>
                                        <th class="custom-width-action">{{ __('content.action') }}</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @php $i = 1; @endphp
                                    @foreach ($design_skills as $design_skill)
                                        <tr>
                                            <td>{{ $i }}</td>
                                            <td class="text-capitalize">{{ $design_skill->title }}</td>
                                            <td>
                                                <div class="btn-group">
                                                    @if ($i < count($design_skills))
                                                        <a href="{{ url('/') }}/admin/skills/order-down/{{ $design_skill->id }}" class="btn btn-warning btn-sm mr-1">
                                                            <i class="fas fa-arrow-down"></i>
                                                        </a>
                                                    @endif
                                                    @if ($i != 1)
                                                        <a href="{{ url('/') }}/admin/skills/order-up/{{ $design_skill->id }}" class="btn btn-warning btn-sm mr-1">
                                                            <i class="fas fa-arrow-up"></i>
                                                        </a>
                                                    @endif
                                                </div>
                                            </td>
                                            <td>
                                                <div class="btn-group">
                                                    <a href="{{ url('/') }}/admin/skills/{{ $design_skill->id }}" class="btn btn-primary btn-sm mr-1">
                                                        <i class="fas fa-pencil-alt"></i>
                                                    </a>
                                                    <form class="d-inline-block" action="{{url('/admin/skills')}}/{{ $design_skill->id }}" method="POST">
                                                        @csrf
                                                        @method('DELETE')
                                                        <button type="button" class="btn btn-danger btn-sm" data-bs-toggle="modal" data-bs-target="#deleteSkill{{ $design_skill->id }}">
                                                            <i class="far fa-trash-alt"></i>
                                                        </button>
                                                        <div class="modal fade" id="deleteSkill{{ $design_skill->id }}" tabindex="-1" role="dialog" aria-labelledby="exampleModalCenterTitle" aria-hidden="true">
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
                        {{ __('content.no_skills_created_yet') }}
                    @endif
                </div>
            </div>
        </div>

        <div class="col-lg-6">
            <div class="card shadow mb-4">
                <div class="card-header py-3 d-flex justify-content-between align-items-center">
                    <h6 class="font-weight-bold text-primary m-0">{{ __('content.skills_development') }}</h6>
                </div>
                <div class="card-body">
                    @if (count($dev_skills) > 0)
                        <div class="table-responsive">
                            <table class="table table-bordered datatable" width="100%" cellspacing="0">
                                <thead>
                                    <tr>
                                        <th class="custom-width" scope="col">#</th>
                                        <th>{{ __('content.title') }}</th>
                                        <th>{{ __('content.order') }}</th>
                                        <th class="custom-width-action">{{ __('content.action') }}</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @php $j = 1; @endphp
                                    @foreach ($dev_skills as $dev_skill)
                                        <tr>
                                            <td>{{ $j }}</td>
                                            <td>{{ $dev_skill->title }}</td>
                                            <td>
                                                <div class="btn-group">
                                                    @if ($j < count($dev_skills))
                                                        <a href="{{ url('/') }}/admin/skills/order-down/{{ $dev_skill->id }}" class="btn btn-warning btn-sm mr-1">
                                                            <i class="fas fa-arrow-down"></i>
                                                        </a>
                                                    @endif
                                                    @if ($j != 1)
                                                        <a href="{{ url('/') }}/admin/skills/order-up/{{ $dev_skill->id }}" class="btn btn-warning btn-sm mr-1">
                                                            <i class="fas fa-arrow-up"></i>
                                                        </a>
                                                    @endif
                                                </div>
                                            </td>
                                            <td>
                                                <div class="btn-group">
                                                    <a href="{{ url('/') }}/admin/skills/{{ $dev_skill->id }}" class="btn btn-primary btn-sm mr-1">
                                                        <i class="fas fa-pencil-alt"></i>
                                                    </a>
                                                    <form class="d-inline-block" action="{{url('/admin/skills')}}/{{ $dev_skill->id }}" method="POST">
                                                        @csrf
                                                        @method('DELETE')
                                                        <button type="button" class="btn btn-danger btn-sm" data-bs-toggle="modal" data-bs-target="#deleteSkill{{ $dev_skill->id }}">
                                                            <i class="far fa-trash-alt"></i>
                                                        </button>
                                                        <div class="modal fade" id="deleteSkill{{ $dev_skill->id }}" tabindex="-1" role="dialog" aria-labelledby="exampleModalCenterTitle" aria-hidden="true">
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
                                        @php $j++; @endphp
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @else
                        {{ __('content.no_skills_created_yet') }}
                    @endif
                </div>
            </div>
        </div>

    </div>
</div>


<div class="modal fade" id="skillNewModal" tabindex="-1" role="dialog" aria-labelledby="skillNewModalModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="exampleModalLabel">{{ __('content.new_skill') }}</h5>
                <button type="button" class="close" data-bs-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form action="{{ url('/').'/admin/skills' }}" method="POST" class="user">
                @csrf
                <div class="modal-body">
                    <div class="row">
                        <input type="hidden" name="order_design" value="@php echo (count($design_skills) > 0) ? $i : 1; @endphp" />
                        <input type="hidden" name="order_dev" value="@php echo (count($dev_skills) > 0) ? $j : 1; @endphp" />
                        <div class="col-md-12 mb-3">
                            <label for="type" class="form-label">{{ __('content.type') }}</label>
                            <select class="form-select @error('type') is-invalid @enderror" name="type">
                                <option value="design">{{ __('content.design') }}</option>
                                <option value="development">{{ __('content.development') }}</option>
                            </select>
                        </div>
                        <div class="col-md-12 mb-3">
                            <label for="title" class="form-label">{{ __('content.title') }}</label>
                            <input class="form-control @error('title') is-invalid @enderror" type="text" name="title" required value="{{old('title')}}"/>
                            @error('title')
                                <div class="invalid-feedback">
                                    {{ __('content.text_not_valid') }}
                                </div>
                            @enderror
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
    <input class="openModal" data-id="skillNewModal" type="hidden" val="1" />
@endif

@endsection
