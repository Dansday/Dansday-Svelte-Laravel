@extends('layouts.admin.main')

@section('content')

<div class="container-fluid">

    @include('admin.modules.alert')

    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-gray-800">{{ $service->title }}</h1>
    </div>

    <div class="row">
        <div class="col-lg-12">
            <div class="card shadow mb-4">
                <div class="card-header py-3 d-flex justify-content-between align-items-center">
                    <h6 class="font-weight-bold text-primary m-0">{{ __('content.edit_service') }}</h6>
                </div>
                <div class="card-body">
                    <form action="{{ url('/').'/admin/services' }}/{{ $service->id }}" method="POST" class="user">
                        @csrf
                        @method('PUT')
                        <div class="modal-body">
                            <div class="row">
                                <input type="hidden" name="order" value="{{ $service->order }}" />
                                <div class="col-md-6 mb-3">
                                    <label for="title" class="form-label">{{ __('content.title') }}</label>
                                    <input class="form-control @error('title') is-invalid @enderror" type="text" name="title" required  value="{{ $service->title }}"/>
                                    @error('title')
                                        <div class="invalid-feedback">
                                            {{ __('content.text_not_valid') }} {{ __('content.max_characters') }}: 55.
                                        </div>
                                    @enderror
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label for="description" class="form-label">{{ __('content.description') }}</label>
                                    <textarea class="form-control @error('description') is-invalid @enderror" name="description" rows="6" required>{{ $service->description }}</textarea>
                                    @error('description')
                                        <div class="invalid-feedback">
                                            {{ __('content.text_not_valid') }} {{ __('content.max_characters') }}: 255.
                                        </div>
                                    @enderror
                                </div>
                                <div class="col-md-6">
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
                                        <input type="hidden" name="info" value="{{$service->info}}" id="info-list" />
                                        <div class="table-elements p-4">
                                            <table class="table table-info-list w-100 p-4" data-target="info-list">
                                                <tbody>
                                                    @php
                                                    $info = json_decode($service->info, true);
                                                    if(!empty($info)):
                                                        foreach($info as $key => $value){
                                                            echo '
                                                            <tr>
                                                                <td class="fw-bold">'.$value["title"].'</td>
                                                                <td>'.$value["text"].'</td>
                                                                <td class="text-right">
                                                                    <button type="button" class="btn btn-outline-danger btn-sm rounded-circle deleteInfo" data-info="'.$value["title"].'" data-value="'.$value["text"].'">
                                                                        <i class="fas fa-times"></i>
                                                                    </button>                                                            
                                                            </tr>';
                                                        }  
                                                    endif; 
                                                    @endphp
                                                </tbody>
                                            </table>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="submit" class="btn btn-primary">
                                {{ __('content.update') }}
                            </button>
                            <a href="{{ url('/') }}/admin/services">
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
