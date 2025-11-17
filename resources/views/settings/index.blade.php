@extends('ave::layouts.app')

@section('title', $title)

@section('content')
<div class="container-fluid">
    <div class="row mb-3">
        <div class="col-12">
            <a href="{{ url()->previous() }}" class="btn btn-secondary btn-sm">
                <i class="voyager-arrow-left"></i> Back
            </a>
            <h1 class="page-title">
                <i class="voyager-settings"></i> {{ $title }}
            </h1>
        </div>
    </div>

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

        <div class="card">
            <div class="card-body">
                <div class="row">
                    @foreach($config->fields as $key_field => $field)

                        @php $class = isset($field->class) ? $field->class : "col-md-12" @endphp

                        @if(isset($field->type) && $field->type === 'section')
                            <div class="col-md-12">
                                <h3 class="settings-section-title">
                                    @if(isset($field->icon))
                                        <i class="{{ $field->icon }}"></i>
                                    @endif
                                    <span>{{ $field->label }}</span>
                                </h3>
                                <hr>
                            </div>
                        @else

                            @php $help_code = "<span class='settings-help text-muted'><small><strong>site_setting</strong>(<i>'" . $key . "." . $key_field . "'</i>)</small></span>" @endphp

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
                                        <div class="form-group mb-2" data-field-name="{{$key_field}}">
                                            @php
                                                $isImage = strpos($field->value, '.jpg') !== false ||
                                                           strpos($field->value, '.jpeg') !== false ||
                                                           strpos($field->value, '.png') !== false ||
                                                           strpos($field->value, '.gif') !== false;
                                            @endphp
                                            @if($isImage)
                                                <img src="{{ $field->value }}" style="max-width: 200px; height: auto; display: block; margin-bottom: 10px;">
                                            @else
                                                <div style="margin-bottom: 10px;">
                                                    <i class="voyager-file-text"></i> {{ basename($field->value) }}
                                                </div>
                                            @endif
                                            <a href="#" class="btn btn-danger btn-sm remove-media" data-field="{{$key_field}}">
                                                <i class="voyager-trash"></i> Remove
                                            </a>
                                        </div>
                                    @endif

                                    <input type="file" id="{{$key_field}}" class="form-control-file" name="{{$key_field}}">
                                </div>

                            @endif

                        @endif
                    @endforeach
                </div>
            </div>
        </div>

        <div class="form-group mt-3">
            <button type="submit" class="btn btn-primary">
                <i class="voyager-save"></i> Save Settings
            </button>
            <a href="{{ url()->previous() }}" class="btn btn-secondary">
                Cancel
            </a>
        </div>

    </form>
</div>

<style>
    .settings-section-title {
        font-size: 1.3rem;
        color: #2c3e50;
        margin-top: 20px;
        margin-bottom: 10px;
    }

    .settings-section-title i {
        margin-right: 10px;
    }

    .settings-help {
        display: block;
        margin-top: 5px;
        font-size: 0.85rem;
    }

    .form-group {
        margin-bottom: 1.5rem;
    }
</style>

@endsection

@section('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Handle media removal
    document.querySelectorAll('.remove-media').forEach(function(btn) {
        btn.addEventListener('click', function(e) {
            e.preventDefault();
            const field = this.dataset.field;
            const input = document.createElement('input');
            input.type = 'hidden';
            input.name = 'remove_media';
            input.value = field;
            document.querySelector('form').appendChild(input);
            document.querySelector('form').submit();
        });
    });
});
</script>
@endsection
