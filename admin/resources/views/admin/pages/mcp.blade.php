@extends('layouts.admin.main')

@section('content')

<div class="container-fluid">

    @include('admin.modules.alert')

    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-gray-800">{{ __('menu.mcp') }}</h1>
    </div>

    <div class="row">
        <div class="col-lg-12">
            <div class="card shadow mb-4">
                <div class="card-header py-3 d-flex justify-content-between align-items-center">
                    <h6 class="font-weight-bold text-primary m-0">{{ __('content.mcp') }}</h6>
                    <button type="button" class="btn btn-primary btn-round d-inline" data-bs-toggle="modal" data-bs-target="#mcpTokenNewModal">
                        <i class="fas fa-plus small"></i>
                        {{ __('content.mcp_new_token') }}
                    </button>
                </div>
                <div class="card-body">
                    <p class="text-muted small mb-3">{{ __('content.mcp_desc') }}</p>

                    <div class="form-group mb-4">
                        <label class="form-label">{{ __('content.mcp_endpoint') }}</label>
                        <div class="input-group">
                            <input class="form-control" type="text" id="mcp-endpoint" value="{{ url('/mcp') }}" readonly />
                            <button class="btn btn-outline-secondary mcp-copy" type="button" data-copy-target="#mcp-endpoint" data-copied-label="{{ __('content.mcp_copied') }}" data-failed-label="{{ __('content.mcp_copy_failed') }}">
                                <i class="far fa-copy"></i> {{ __('content.mcp_copy') }}
                            </button>
                        </div>
                    </div>

                    @if (Session::has('mcp-token'))
                        <div class="alert alert-success" role="alert">
                            <p class="mb-2">
                                <i class="fas fa-key me-1"></i>
                                <strong>{{ Session::get('mcp-token-name') }}</strong> — {{ __('content.mcp_token_created') }}
                            </p>
                            <div class="input-group">
                                <input class="form-control font-monospace" type="text" id="mcp-new-token" value="{{ Session::get('mcp-token') }}" readonly onclick="this.select()" />
                                <button class="btn btn-outline-secondary mcp-copy" type="button" data-copy-target="#mcp-new-token" data-copied-label="{{ __('content.mcp_copied') }}" data-failed-label="{{ __('content.mcp_copy_failed') }}">
                                    <i class="far fa-copy"></i> {{ __('content.mcp_copy') }}
                                </button>
                            </div>
                        </div>
                    @endif

                    @if (count($tokens) > 0)
                        <div class="table-responsive">
                            <table class="table table-bordered" width="100%" cellspacing="0">
                                <thead>
                                    <tr>
                                        <th class="custom-width" scope="col">#</th>
                                        <th>{{ __('content.mcp_token_name') }}</th>
                                        <th>{{ __('content.mcp_status') }}</th>
                                        <th>{{ __('content.mcp_created') }}</th>
                                        <th>{{ __('content.mcp_last_used') }}</th>
                                        <th class="custom-width-action">{{ __('content.action') }}</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @php $i = 1; @endphp
                                    @foreach ($tokens as $token)
                                        <tr>
                                            <td>{{ $i }}</td>
                                            <td>{{ $token->name }}</td>
                                            <td>
                                                @if ($token->isActive())
                                                    <span class="badge bg-success">{{ __('content.mcp_active') }}</span>
                                                @else
                                                    <span class="badge bg-secondary">{{ __('content.mcp_revoked') }}</span>
                                                @endif
                                            </td>
                                            <td>{{ $token->created_at?->format('Y-m-d H:i') }}</td>
                                            <td>
                                                @if ($token->last_used_at)
                                                    {{ $token->last_used_at->format('Y-m-d H:i') }}
                                                @else
                                                    <span class="text-muted">{{ __('content.mcp_never_used') }}</span>
                                                @endif
                                            </td>
                                            <td>
                                                <div class="btn-group">
                                                    @if ($token->isActive())
                                                        <form class="d-inline-block" action="{{ url('/admin/mcp/tokens') }}/{{ $token->id }}" method="POST">
                                                            @csrf
                                                            @method('PUT')
                                                            <button type="button" class="btn btn-warning btn-sm mr-1" title="{{ __('content.mcp_revoke') }}" data-bs-toggle="modal" data-bs-target="#revokeToken{{ $token->id }}">
                                                                <i class="fas fa-ban"></i>
                                                            </button>
                                                            <div class="modal fade" id="revokeToken{{ $token->id }}" tabindex="-1" role="dialog" aria-hidden="true">
                                                                <div class="modal-dialog modal-dialog-centered modal-sm" role="document">
                                                                    <div class="modal-content">
                                                                        <div class="modal-header">
                                                                            <h5 class="modal-title">{{ __('content.mcp_revoke') }}</h5>
                                                                            <button type="button" class="close" data-bs-dismiss="modal" aria-label="{{ __('content.close') }}">
                                                                                <span aria-hidden="true">&times;</span>
                                                                            </button>
                                                                        </div>
                                                                        <div class="modal-body text-center">
                                                                            {{ __('content.mcp_sure_revoke') }}
                                                                        </div>
                                                                        <div class="modal-footer">
                                                                            <button type="submit" class="btn btn-success">{{ __('content.mcp_revoke') }}</button>
                                                                            <button type="button" class="btn btn-danger" data-bs-dismiss="modal">{{ __('content.cancel') }}</button>
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        </form>
                                                    @endif
                                                    <form class="d-inline-block" action="{{ url('/admin/mcp/tokens') }}/{{ $token->id }}" method="POST">
                                                        @csrf
                                                        @method('DELETE')
                                                        <button type="button" class="btn btn-danger btn-sm" title="{{ __('content.delete') }}" data-bs-toggle="modal" data-bs-target="#deleteToken{{ $token->id }}">
                                                            <i class="far fa-trash-alt"></i>
                                                        </button>
                                                        <div class="modal fade" id="deleteToken{{ $token->id }}" tabindex="-1" role="dialog" aria-hidden="true">
                                                            <div class="modal-dialog modal-dialog-centered modal-sm" role="document">
                                                                <div class="modal-content">
                                                                    <div class="modal-header">
                                                                        <h5 class="modal-title">{{ __('content.delete') }}</h5>
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
                        {{ __('content.mcp_no_tokens') }}
                    @endif
                </div>
            </div>
        </div>
    </div>

</div>

<div class="modal fade" id="mcpTokenNewModal" tabindex="-1" role="dialog" aria-labelledby="mcpTokenNewModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-min" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="mcpTokenNewModalLabel">{{ __('content.mcp_new_token') }}</h5>
                <button type="button" class="close" data-bs-dismiss="modal" aria-label="{{ __('content.close') }}">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form action="{{ url('/').'/admin/mcp/tokens' }}" method="POST" class="user" autocomplete="off">
                @csrf
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-12 mb-3">
                            <label for="name" class="form-label">{{ __('content.mcp_token_name') }}</label>
                            <input class="form-control @error('name') is-invalid @enderror" type="text" name="name" required value="{{ old('name') }}" placeholder="claude-code" autocomplete="off" />
                            <div class="form-text">{{ __('content.mcp_token_name_desc') }}</div>
                            @error('name')
                                <div class="invalid-feedback">
                                    {{ __('content.text_not_valid') }} {{ __('content.max_characters') }}: 55.
                                </div>
                            @enderror
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="submit" class="btn btn-primary">{{ __('content.mcp_generate_token') }}</button>
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">{{ __('content.close') }}</button>
                </div>
            </form>
        </div>
    </div>
</div>

@if (Session::has('error-modal'))
    <input class="openModal" data-id="mcpTokenNewModal" type="hidden" val="1" />
@endif

@endsection
