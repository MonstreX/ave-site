@extends('ave::layouts.master')

@section('page_title', $title)

@section('page_header')
<div class="page-header">
    <h1 class="page-title">
        <i class="voyager-settings"></i> {{ $title }}
    </h1>
</div>
@endsection

@section('content')
<div class="page-content">
    @if (session('success'))
        <div class="alert alert-success">
            <button type="button" class="close" data-dismiss="alert">&times;</button>
            <h4>Success!</h4>
            <p>{{ session('success') }}</p>
        </div>
    @endif

    @if (session('error'))
        <div class="alert alert-danger">
            <button type="button" class="close" data-dismiss="alert">&times;</button>
            <h4>Error!</h4>
            <p>{{ session('error') }}</p>
        </div>
    @endif

    @if ($errors->any())
        <div class="alert alert-danger">
            <button type="button" class="close" data-dismiss="alert">&times;</button>
            <h4>Please fix the following errors:</h4>
            <ul>
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form action="{{ route('ave-site.settings.update', $key) }}" method="POST" enctype="multipart/form-data">
        {{ csrf_field() }}
        {{ method_field('PUT') }}

        <div class="panel panel-bordered">
            <div class="panel-body">
                <div class="row">
                    @foreach($config->fields as $key_field => $field)

                        @php $class = isset($field->class) ? $field->class : "col-md-12" @endphp

                        @if(isset($field->type) && $field->type === 'section')
                            <h3 class="col-md-12">
                                @if(isset($field->icon))
                                    <i class="{{ $field->icon }}"></i>
                                @endif
                                <span>{{ $field->label }}</span>
                            </h3>
                        @else

                            @php $help_code = "<span class='config-help'><strong>site_setting</strong>(<i>'" . $key . "." . $key_field . "'</i>)</span>" @endphp

                            @if($field->type === 'text')
                                <div class="form-group {{ $class }}">
                                    <label for="{{$key_field}}">{{ $field->label }}</label>
                                    {!! $help_code !!}
                                    <input type="text" id="{{$key_field}}" class="form-control" name="{{$key_field}}" value="{{ $field->value ?? '' }}">
                                </div>

                            @elseif($field->type === 'number')
                                <div class="form-group {{ $class }}">
                                    <label for="{{$key_field}}">{{ $field->label }}</label>
                                    {!! $help_code !!}
                                    <input type="number" id="{{$key_field}}" class="form-control" name="{{$key_field}}" value="{{ $field->value ?? '' }}">
                                </div>

                            @elseif($field->type === 'textarea')
                                <div class="form-group {{ $class }}">
                                    <label for="{{$key_field}}">{{ $field->label }}</label>
                                    {!! $help_code !!}
                                    <textarea id="{{$key_field}}" class="form-control" name="{{$key_field}}" rows="5">{{ $field->value ?? '' }}</textarea>
                                </div>

                            @elseif($field->type === 'checkbox')
                                <div class="form-group {{ $class }}">
                                    <div class="custom-control custom-checkbox">
                                        <input type="checkbox" class="custom-control-input" id="{{$key_field}}" name="{{$key_field}}" value="1"
                                            {!! ($field->value === '1' || $field->value === true) ? 'checked="checked"' : '' !!}>
                                        <label class="custom-control-label" for="{{$key_field}}">{{ $field->label }}</label>
                                    </div>
                                    {!! $help_code !!}
                                </div>

                            @elseif($field->type === 'radio')
                                <div class="form-group {{ $class }}">
                                    <label>{{ $field->label }}</label>
                                    {!! $help_code !!}
                                    <div class="custom-control custom-radio">
                                        @if(isset($field->options))
                                            @foreach($field->options as $key_options => $option)
                                                <div class="custom-control custom-radio">
                                                    <input type="radio" class="custom-control-input" id="option-{{$key_options}}"
                                                        name="{{$key_field}}" value="{{ $key_options }}"
                                                        {!! $field->value == $key_options ? 'checked' : '' !!}>
                                                    <label class="custom-control-label" for="option-{{$key_options}}">{{ $option }}</label>
                                                </div>
                                            @endforeach
                                        @endif
                                    </div>
                                </div>

                            @elseif($field->type === 'dropdown')
                                <div class="form-group {{ $class }}">
                                    <label for="{{$key_field}}">{{ $field->label }}</label>
                                    {!! $help_code !!}
                                    <select class="form-control" id="{{$key_field}}" name="{{$key_field}}">
                                        <option value="">-- Select --</option>
                                        @if(isset($field->options))
                                            @foreach($field->options as $key_options => $option)
                                                <option value="{{ $key_options }}"
                                                    {!! $field->value == $key_options ? 'selected="selected"' : '' !!}>
                                                    {{ $option }}
                                                </option>
                                            @endforeach
                                        @endif
                                    </select>
                                </div>

                            @elseif($field->type === 'rich_text_box')
                                <div class="form-group {{ $class }}">
                                    <label for="{{$key_field}}">{{ $field->label }}</label>
                                    {!! $help_code !!}
                                    <textarea class="form-control richTextBox" name="{{$key_field}}" id="richtext{{$key_field}}">{{ $field->value ?? '' }}</textarea>
                                </div>

                            @elseif($field->type === 'code_editor')
                                <div class="form-group {{ $class }}">
                                    <label for="{{$key_field}}">{{ $field->label }}</label>
                                    {!! $help_code !!}
                                    <textarea class="form-control code-editor" name="{{$key_field}}" id="{{$key_field}}" rows="8">{{ $field->value ?? '' }}</textarea>
                                </div>

                            @elseif($field->type === 'media')
                                <div class="form-group {{ $class }}">
                                    <label for="{{$key_field}}">{{ $field->label }}</label>
                                    {!! $help_code !!}

                                    @if(isset($field->value) && !empty($field->value))
                                        <div class="media-preview-wrapper" data-field-name="{{$key_field}}">
                                            @php
                                                $isImage = strpos($field->value, '.jpg') !== false ||
                                                           strpos($field->value, '.jpeg') !== false ||
                                                           strpos($field->value, '.png') !== false ||
                                                           strpos($field->value, '.gif') !== false;
                                            @endphp
                                            @if($isImage)
                                                <div class="media-preview-image">
                                                    <img src="{{ $field->value }}" alt="Preview">
                                                    <a href="#" class="media-preview-delete" data-field="{{$key_field}}" title="Remove media">
                                                        <i class="voyager-x"></i>
                                                    </a>
                                                </div>
                                            @else
                                                <div class="media-preview-file">
                                                    <i class="voyager-file-text"></i>
                                                    <span>{{ basename($field->value) }}</span>
                                                    <a href="#" class="media-preview-delete" data-field="{{$key_field}}" title="Remove media">
                                                        <i class="voyager-x"></i>
                                                    </a>
                                                </div>
                                            @endif
                                        </div>
                                    @endif

                                    <input type="file" id="{{$key_field}}" class="form-control-file" name="{{$key_field}}">
                                </div>

                            @elseif($field->type === 'route')
                                <div class="form-group {{ $class }}">
                                    @php
                                        $routeName = $field->value ?? '';
                                        $routeUrl = route($routeName);
                                        $icon = $field->icon ?? 'voyager-link';
                                    @endphp
                                    <a href="{{ $routeUrl }}" class="btn btn-primary btn-setting-route">
                                        <i class="{{ $icon }}"></i>
                                        <span>{{ $field->label }}</span>
                                    </a>
                                </div>

                            @endif

                        @endif
                    @endforeach
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-md-12 mb-30">
                <button type="submit" class="btn btn-primary">
                    <i class="voyager-save"></i> Save Settings
                </button>
            </div>
        </div>
    </form>
</div>

<script type="module">
import { confirm } from '/vendor/ave/js/modules/ui/modals.js';

document.addEventListener('DOMContentLoaded', function() {
    // Handle media removal
    document.querySelectorAll('.media-preview-delete').forEach(function(btn) {
        btn.addEventListener('click', async function(e) {
            e.preventDefault();
            const field = this.dataset.field;

            const confirmed = await confirm('Are you sure you want to remove this media?', {
                title: 'Remove Media',
                confirmText: 'Remove',
                cancelText: 'Cancel'
            });

            if (confirmed) {
                const input = document.createElement('input');
                input.type = 'hidden';
                input.name = 'remove_media';
                input.value = field;
                document.querySelector('form').appendChild(input);
                document.querySelector('form').submit();
            }
        });
    });
});
</script>

@endsection
