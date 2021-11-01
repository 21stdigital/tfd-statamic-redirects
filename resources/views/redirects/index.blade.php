@extends('statamic::layout')
@section('title', __('statamic-redirects::default.title'))

@section('content')
    <div class="flex items-center mb-3">
        <h1 class="flex-1">{{ $title }}</h1>
        @if ($canCreate)
            <div>
                <a href="{{ cp_route('statamic-redirects.create') }}"
                    class="btn-primary">{{ __('statamic-redirects::default.actions.create') }}</a>
            </div>
        @endif
    </div>

    <redirects :redirects='@json($redirects)' :columns='@json($columns)'
        create-url='{{ cp_route('statamic-redirects.create') }}' can-create="{{ $canCreate }}"
        can-delete="{{ $canDelete }}"></redirects>
@stop
