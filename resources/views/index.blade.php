@extends('statamic::layout')
@section('title', __('Redirects'))

@section('content')

    <header class="mb-3">
        <h1>{{ __('Redirects') }}</h1>
    </header>

    <div class="card p-2 content">
        <div class="flex flex-wrap">
            @foreach ($modules as $module)
                <a href="{{ $module->url() }}"
                    class="w-full lg:w-1/2 p-2 md:flex items-start hover:bg-grey-20 rounded-md group">
                    <div class="h-8 w-8 mr-2 text-grey-80">
                        @cp_svg($module->icon())
                    </div>
                    <div class="text-blue flex-1 mb-2 md:mb-0 md:mr-3">
                        <h3>{{ $module->title() }}</h3>
                        <p class="text-xs">{{ $module->description() }}</p>
                    </div>
                </a>
            @endforeach
        </div>
    </div>
@endsection
