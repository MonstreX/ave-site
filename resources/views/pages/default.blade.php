<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $page->getSeoTitle() }}</title>

    @if($page->getSeoDescription())
    <meta name="description" content="{{ $page->getSeoDescription() }}">
    @endif

    @if($page->getSeoKeywords())
    <meta name="keywords" content="{{ $page->getSeoKeywords() }}">
    @endif
</head>
<body>
    <main>
        <h1>{{ $page->title }}</h1>

        <div class="page-content">
            {!! $content !!}
        </div>
    </main>
</body>
</html>
