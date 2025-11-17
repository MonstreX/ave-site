@extends('ave::layouts.master')

@section('page_title', $title)

@section('page_header')
<div class="page-header">
    <h1 class="page-title">
        <i class="voyager-mail"></i> {{ $title }}
    </h1>
</div>
@endsection

@section('content')
<div class="page-content">
    <div class="panel panel-bordered">
        <div class="panel-body">
            @if($error)
                <div class="alert alert-danger">
                    <i class="voyager-x"></i>
                    <strong>@lang('ave-site::notifications.error'):</strong>
                    @if(is_object($error) && method_exists($error, 'getMessage'))
                        {{ $error->getMessage() }}
                    @else
                        {{ $error }}
                    @endif
                </div>
            @else
                <div class="alert alert-success">
                    <i class="voyager-check"></i>
                    <strong>@lang('ave-site::notifications.success'):</strong>
                    {{ $content ?? '' }}
                </div>
            @endif

            <div class="row mt-3">
                <div class="col-md-12">
                    <a href="{{ route('ave-site.settings.edit', 'mail') }}" class="btn btn-primary">
                        <i class="voyager-arrow-left"></i> @lang('ave-site::notifications.back_to_settings')
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
