@extends('layouts.admin.main')

@section('content')

<div class="container-fluid">

    @include('admin.modules.alert')

    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-gray-800">{{ __('content.experiences') }}</h1>
        <button type="button" class="btn btn-primary btn-round d-inline" data-bs-toggle="modal" data-bs-target="#experienceNewModal">
            <i class="fas fa-plus small"></i>
            {{ __('content.add_experience') }}
        </button>
    </div>

    <div class="row">
        
        <div class="col-lg-12 mb-4">
            <div class="card shadow mb-4">
                <div class="card-header py-3 d-flex justify-content-between align-items-center">
                    <h6 class="font-weight-bold text-primary m-0">{{ __('content.edu_experiences') }}</h6>
                </div>
                <div class="card-body">
                    @if (count($edu_experiences) > 0)
                        <div class="table-responsive">
                            <table class="table table-bordered datatable" width="100%" cellspacing="0">
                                <thead>
                                    <tr>
                                        <th class="custom-width" scope="col">#</th>
                                        <th>{{ __('content.title') }}</th>
                                        <th>{{ __('content.period') }}</th>
                                        <th>{{ __('content.description') }}</th>
                                        <th>{{ __('content.order') }}</th>
                                        <th class="custom-width-action">{{ __('content.action') }}</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @php $i = 1; @endphp
                                    @foreach ($edu_experiences as $edu_experience)
                                        <tr>
                                            <td>{{ $i }}</td>
                                            <td>{{ $edu_experience->title }}</td>
                                            <td>{{ $edu_experience->period }}</td>
                                            <td>{{ $edu_experience->description }}</td>
                                            <td>
                                                <div class="btn-group">
                                                    @if ($i < count($edu_experiences))
                                                        <a href="{{ url('/') }}/admin/experiences/order-down/{{ $edu_experience->id }}" class="btn btn-warning btn-sm mr-1">
                                                            <i class="fas fa-arrow-down"></i>
                                                        </a>
                                                    @endif
                                                    @if ($i != 1)
                                                        <a href="{{ url('/') }}/admin/experiences/order-up/{{ $edu_experience->id }}" class="btn btn-warning btn-sm mr-1">
                                                            <i class="fas fa-arrow-up"></i>
                                                        </a>
                                                    @endif
                                                </div>
                                            </td>
                                            <td>
                                                <div class="btn-group">
                                                    <a href="{{ url('/') }}/admin/experiences/{{ $edu_experience->id }}" class="btn btn-primary btn-sm mr-1">
                                                        <i class="fas fa-pencil-alt"></i>
                                                    </a>
                                                    <form class="d-inline-block" action="{{url('/admin/experiences')}}/{{ $edu_experience->id }}" method="POST">
                                                        @csrf
                                                        @method('DELETE')
                                                        <button type="button" class="btn btn-danger btn-sm" data-bs-toggle="modal" data-bs-target="#deleteExperience{{ $edu_experience->id }}">
                                                            <i class="far fa-trash-alt"></i>
                                                        </button>
                                                        <div class="modal fade" id="deleteExperience{{ $edu_experience->id }}" tabindex="-1" role="dialog" aria-labelledby="exampleModalCenterTitle" aria-hidden="true">
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
                        {{ __('content.no_experiences_created_yet') }}
                    @endif
                </div>
            </div>
        </div>

        <div class="col-lg-12">
            <div class="card shadow mb-4">
                <div class="card-header py-3 d-flex justify-content-between align-items-center">
                    <h6 class="font-weight-bold text-primary m-0">{{ __('content.emp_experiences') }}</h6>
                </div>
                <div class="card-body">
                    @if (count($emp_experiences) > 0)
                        <div class="table-responsive">
                            <table class="table table-bordered datatable" width="100%" cellspacing="0">
                                <thead>
                                    <tr>
                                        <th class="custom-width" scope="col">#</th>
                                        <th>{{ __('content.title') }}</th>
                                        <th>{{ __('content.period') }}</th>
                                        <th>{{ __('content.description') }}</th>
                                        <th>{{ __('content.order') }}</th>
                                        <th class="custom-width-action">{{ __('content.action') }}</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @php $j = 1; @endphp
                                    @foreach ($emp_experiences as $emp_experience)
                                        <tr>
                                            <td>{{ $j }}</td>
                                            <td>{{ $emp_experience->title }}</td>
                                            <td>{{ $emp_experience->period }}</td>
                                            <td>{{ $emp_experience->description }}</td>
                                            <td>
                                                <div class="btn-group">
                                                    @if ($j < count($emp_experiences))
                                                        <a href="{{ url('/') }}/admin/experiences/order-down/{{ $emp_experience->id }}" class="btn btn-warning btn-sm mr-1">
                                                            <i class="fas fa-arrow-down"></i>
                                                        </a>
                                                    @endif
                                                    @if ($j != 1)
                                                        <a href="{{ url('/') }}/admin/experiences/order-up/{{ $emp_experience->id }}" class="btn btn-warning btn-sm mr-1">
                                                            <i class="fas fa-arrow-up"></i>
                                                        </a>
                                                    @endif
                                                </div>
                                            </td>
                                            <td>
                                                <div class="btn-group">
                                                    <a href="{{ url('/') }}/admin/experiences/{{ $emp_experience->id }}" class="btn btn-primary btn-sm mr-1">
                                                        <i class="fas fa-pencil-alt"></i>
                                                    </a>
                                                    <form class="d-inline-block" action="{{url('/admin/experiences')}}/{{ $emp_experience->id }}" method="POST">
                                                        @csrf
                                                        @method('DELETE')
                                                        <button type="button" class="btn btn-danger btn-sm" data-bs-toggle="modal" data-bs-target="#deleteExperience{{ $emp_experience->id }}">
                                                            <i class="far fa-trash-alt"></i>
                                                        </button>
                                                        <div class="modal fade" id="deleteExperience{{ $emp_experience->id }}" tabindex="-1" role="dialog" aria-labelledby="exampleModalCenterTitle" aria-hidden="true">
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
                        {{ __('content.no_experiences_created_yet') }}
                    @endif
                </div>
            </div>
        </div>

    </div>
</div>

<div class="modal fade" id="experienceNewModal" tabindex="-1" role="dialog" aria-labelledby="experienceNewModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="exampleModalLabel">{{ __('content.new_experience') }}</h5>
                <button type="button" class="close" data-bs-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form action="{{ url('/').'/admin/experiences' }}" method="POST" class="user">
                @csrf
                <div class="modal-body">
                    <div class="row">
                        <input type="hidden" name="order_edu" value="@php echo (count($edu_experiences) > 0) ? $i : 1; @endphp" />
                        <input type="hidden" name="order_emp" value="@php echo (count($emp_experiences) > 0) ? $j : 1; @endphp" />
                        <div class="col-md-12 mb-3">
                            <label for="type" class="form-label">{{ __('content.type') }}</label>
                            <select class="form-select @error('type') is-invalid @enderror" name="type">
                                <option value="education">{{ __('content.education') }}</option>
                                <option value="employment">{{ __('content.employment') }}</option>
                            </select>
                            @error('type')
                                <div class="invalid-feedback">
                                    {{ __('content.text_not_valid') }} {{ __('content.max_characters') }}: 55.
                                </div>
                            @enderror
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="title" class="form-label">{{ __('content.title') }}</label>
                            <input class="form-control @error('title') is-invalid @enderror" type="text" name="title" required value="{{ old('title')}}" />
                            @error('title')
                                <div class="invalid-feedback">
                                    {{ __('content.text_not_valid') }}
                                </div>
                            @enderror
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="period" class="form-label">{{ __('content.period') }}</label>
                            <input class="form-control @error('period') is-invalid @enderror" type="text" name="period" required value="{{ old('period')}}" />
                            @error('period')
                                <div class="invalid-feedback">
                                    {{ __('content.text_not_valid') }}
                                </div>
                            @enderror
                        </div>
                        <div class="col-md-6">
                            <label for="description" class="form-label">{{ __('content.description') }}</label>
                            <textarea class="form-control @error('description') is-invalid @enderror" name="description" rows="4">{{ old('description')}}</textarea>
                            @error('description')
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
    <input class="openModal" data-id="experienceNewModal" type="hidden" val="1" />
@endif

@endsection
