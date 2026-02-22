<div class="row">
    <div class="col-12">
        @if (Session::has('ok-delete'))
            <div id="alert_message" class="alert alert-success alert-message alert-dismissible fade show" role="alert">
                <span>{{ __('content.alert_ok_delete') }}</span>
                <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
        @endif
        @if (Session::has('no-delete'))
            <div id="alert_message" class="alert alert-danger alert-message alert-dismissible fade show" role="alert">
                <span>{{ __('content.alert_no_delete') }}</span>
                <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
        @endif
        @if (Session::has('error-validation'))
            <div id="alert_message" class="alert alert-danger alert-message alert-dismissible fade show" role="alert">
                <span>{{ __('content.error_validation') }}</span>
                <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
        @endif
        @if (Session::has('error-validation-image'))
            <div id="alert_message" class="alert alert-danger alert-message alert-dismissible fade show" role="alert">
                <span>{{ __('content.error_validation_image') }}</span>
                <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
        @endif
        @if (Session::has('error-validation-video'))
            <div id="alert_message" class="alert alert-danger alert-message alert-dismissible fade show" role="alert">
                <span>{{ __('content.error_validation_video') }}</span>
                <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
        @endif
        @if (Session::has('error-validation-cv'))
            <div id="alert_message" class="alert alert-danger alert-message alert-dismissible fade show" role="alert">
                <span>{{ __('content.error_validation_cv') }}</span>
                <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
        @endif
        @if (Session::has('ok-add'))
        <div id="alert_message" class="alert alert-success alert-message alert-dismissible fade show" role="alert">
            <span>{{ __('content.alert_ok_add') }}</span>
            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                <span aria-hidden="true">&times;</span>
            </button>
        </div>
        @endif
        @if (Session::has('ok-update'))
        <div id="alert_message" class="alert alert-success alert-message alert-dismissible fade show" role="alert">
            <span>{{ __('content.alert_ok_update') }}</span>
            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                <span aria-hidden="true">&times;</span>
            </button>
        </div>
        @endif
    </div>
</div>