@extends('layouts.admin.main')

@section('content')

<div class="container-fluid">

    @include('admin.modules.alert')

    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-gray-800">{{ __('menu.linkedin') }}</h1>
    </div>

    <div class="row">
        <div class="col-lg-12">
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="font-weight-bold text-primary m-0">{{ __('content.li_connection') }}</h6>
                </div>
                <div class="card-body">
                    <p class="text-muted small mb-3">{{ __('content.li_desc') }}</p>

                    @if (! $configured)
                        <div class="alert alert-warning mb-0" role="alert">
                            <i class="fas fa-exclamation-triangle me-1"></i>
                            {{ __('content.li_not_configured') }}
                        </div>
                    @elseif (empty($status['connected']))
                        <div class="alert alert-secondary" role="alert">
                            <i class="fas fa-unlink me-1"></i>
                            {{ $status['message'] ?? __('content.li_not_connected') }}
                        </div>
                        <a href="{{ url('/admin/linkedin/connect') }}" class="btn btn-primary btn-round">
                            <i class="fab fa-linkedin me-1"></i> {{ __('content.li_connect') }}
                        </a>
                    @else
                        @if (! empty($status['expiring_soon']))
                            <div class="alert alert-warning" role="alert">
                                <i class="fas fa-clock me-1"></i>
                                <strong>{{ __('content.li_expiry_warning') }}</strong>
                            </div>
                        @endif

                        <div class="row mb-3">
                            <div class="col-md-4">
                                <label class="form-label text-muted small mb-1">{{ __('content.li_connected_as') }}</label>
                                <p class="font-monospace small mb-0">{{ $status['as'] }}</p>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label text-muted small mb-1">{{ __('content.li_expires') }}</label>
                                <p class="mb-0">{{ $status['expires_at'] ?? '—' }}</p>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label text-muted small mb-1">{{ __('content.li_days_left') }}</label>
                                <p class="mb-0">
                                    @php $days = $status['days_left'] ?? null; @endphp
                                    @if ($days === null)
                                        <span class="text-muted">—</span>
                                    @elseif ($days <= 0)
                                        <span class="badge bg-danger">{{ __('content.li_expires_today') }}</span>
                                    @else
                                        <span class="badge {{ $days <= 14 ? 'bg-warning text-dark' : 'bg-success' }}">
                                            {{ $days }} {{ __('content.li_days_left') }}
                                        </span>
                                    @endif
                                </p>
                            </div>
                        </div>

                        <p class="text-muted small">{{ __('content.li_expiry_note') }}</p>

                        <a href="{{ url('/admin/linkedin/connect') }}" class="btn btn-primary btn-round">
                            <i class="fas fa-sync me-1"></i> {{ __('content.li_reconnect') }}
                        </a>

                        <form class="d-inline-block" action="{{ url('/admin/linkedin/disconnect') }}" method="POST">
                            @csrf
                            <button type="button" class="btn btn-outline-danger btn-round" data-bs-toggle="modal" data-bs-target="#linkedinDisconnect">
                                <i class="fas fa-unlink me-1"></i> {{ __('content.li_disconnect') }}
                            </button>
                            <div class="modal fade" id="linkedinDisconnect" tabindex="-1" role="dialog" aria-hidden="true">
                                <div class="modal-dialog modal-dialog-centered" role="document">
                                    <div class="modal-content">
                                        <div class="modal-header">
                                            <h5 class="modal-title">{{ __('content.li_disconnect') }}</h5>
                                            <button type="button" class="close" data-bs-dismiss="modal" aria-label="{{ __('content.close') }}">
                                                <span aria-hidden="true">&times;</span>
                                            </button>
                                        </div>
                                        <div class="modal-body text-center">
                                            {{ __('content.li_sure_disconnect') }}
                                        </div>
                                        <div class="modal-footer">
                                            <button type="submit" class="btn btn-success">{{ __('content.li_disconnect') }}</button>
                                            <button type="button" class="btn btn-danger" data-bs-dismiss="modal">{{ __('content.cancel') }}</button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </form>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-lg-12">
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="font-weight-bold text-primary m-0">{{ __('content.li_scheduled') }}</h6>
                </div>
                <div class="card-body">
                    <p class="text-muted small mb-3">{{ __('content.li_scheduled_desc') }}</p>

                    @if (count($scheduled) > 0)
                        <div class="table-responsive">
                            <table class="table table-bordered" width="100%" cellspacing="0">
                                <thead>
                                    <tr>
                                        <th class="custom-width" scope="col">#</th>
                                        <th>{{ __('content.li_publish_at') }}</th>
                                        <th>{{ __('content.li_commentary') }}</th>
                                        <th>{{ __('content.li_status') }}</th>
                                        <th>{{ __('content.li_error') }}</th>
                                        <th class="custom-width-action">{{ __('content.action') }}</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($scheduled as $row)
                                        <tr>
                                            <td>{{ $row->id }}</td>
                                            <td>{{ $row->publish_at?->format('Y-m-d H:i') }}</td>
                                            <td>{{ Str::limit($row->commentary, 80) }}</td>
                                            <td>
                                                @if ($row->status === 'pending')
                                                    <span class="badge bg-info text-dark">{{ __('content.li_pending') }}</span>
                                                @elseif ($row->status === 'published')
                                                    <span class="badge bg-success">{{ __('content.li_published') }}</span>
                                                @elseif ($row->status === 'failed')
                                                    <span class="badge bg-danger">{{ __('content.li_failed') }}</span>
                                                @else
                                                    <span class="badge bg-secondary">{{ __('content.li_cancelled') }}</span>
                                                @endif
                                            </td>
                                            <td class="small text-muted">{{ Str::limit($row->last_error ?? '', 60) }}</td>
                                            <td>
                                                @if ($row->isPending())
                                                    <form class="d-inline-block" action="{{ url('/admin/linkedin/scheduled') }}/{{ $row->id }}" method="POST">
                                                        @csrf
                                                        @method('DELETE')
                                                        <button type="button" class="btn btn-danger btn-sm" title="{{ __('content.li_cancel') }}" data-bs-toggle="modal" data-bs-target="#cancelScheduled{{ $row->id }}">
                                                            <i class="fas fa-times"></i>
                                                        </button>
                                                        <div class="modal fade" id="cancelScheduled{{ $row->id }}" tabindex="-1" role="dialog" aria-hidden="true">
                                                            <div class="modal-dialog modal-dialog-centered modal-sm" role="document">
                                                                <div class="modal-content">
                                                                    <div class="modal-header">
                                                                        <h5 class="modal-title">{{ __('content.li_cancel') }}</h5>
                                                                        <button type="button" class="close" data-bs-dismiss="modal" aria-label="{{ __('content.close') }}">
                                                                            <span aria-hidden="true">&times;</span>
                                                                        </button>
                                                                    </div>
                                                                    <div class="modal-body text-center">
                                                                        {{ __('content.li_sure_cancel') }}
                                                                    </div>
                                                                    <div class="modal-footer">
                                                                        <button type="submit" class="btn btn-success">{{ __('content.li_cancel') }}</button>
                                                                        <button type="button" class="btn btn-danger" data-bs-dismiss="modal">{{ __('content.cancel') }}</button>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </form>
                                                @endif
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                        <p class="text-muted small mb-0">{{ __('content.li_worker_hint') }}</p>
                    @else
                        <p class="text-muted mb-0">{{ __('content.li_no_scheduled') }}</p>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-lg-12">
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="font-weight-bold text-primary m-0">{{ __('content.li_posts') }}</h6>
                </div>
                <div class="card-body">
                    <p class="text-muted small mb-3">{{ __('content.li_posts_desc') }}</p>

                    @if (count($posts) > 0)
                        <div class="table-responsive">
                            <table class="table table-bordered" width="100%" cellspacing="0">
                                <thead>
                                    <tr>
                                        <th class="custom-width" scope="col">#</th>
                                        <th>{{ __('content.li_posted_at') }}</th>
                                        <th>{{ __('content.li_commentary') }}</th>
                                        <th>{{ __('content.li_media') }}</th>
                                        <th>{{ __('content.li_visibility') }}</th>
                                        <th class="custom-width-action">{{ __('content.action') }}</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($posts as $post)
                                        <tr class="{{ $post->isDeleted() ? 'text-muted' : '' }}">
                                            <td>{{ $post->id }}</td>
                                            <td>
                                                {{ $post->posted_at?->format('Y-m-d H:i') }}
                                                @if ($post->edited_at)
                                                    <span class="badge bg-light text-dark">{{ __('content.li_edited') }}</span>
                                                @endif
                                            </td>
                                            <td>{{ Str::limit($post->commentary, 80) }}</td>
                                            <td><span class="badge bg-light text-dark">{{ $post->media_type }}</span></td>
                                            <td class="small">{{ $post->visibility }}</td>
                                            <td>
                                                @if ($post->isDeleted())
                                                    <span class="badge bg-secondary">{{ __('content.li_deleted') }}</span>
                                                @else
                                                    <a href="{{ $post->url() }}" target="_blank" rel="noopener noreferrer" class="btn btn-outline-primary btn-sm" title="{{ __('content.li_view') }}">
                                                        <i class="fas fa-external-link-alt"></i>
                                                    </a>
                                                @endif
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @else
                        <p class="text-muted mb-0">{{ __('content.li_no_posts') }}</p>
                    @endif
                </div>
            </div>
        </div>
    </div>

</div>

@endsection
