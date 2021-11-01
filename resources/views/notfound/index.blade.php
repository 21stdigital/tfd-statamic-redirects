@extends('statamic::layout')
@section('title', __('statamic-redirects::default.title'))

@section('content')
    <div class="flex items-center mb-3">
        <h1 class="flex-1">{{ $title }}</h1>
    </div>

    @if ($items)
        <div class="card p-0">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>URL</th>
                        <th>Hits</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($items as $item)
                        <tr>
                            <td>{{ $item['url'] }}</td>
                            <td>{{ $item['hits'] }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>

        </div>
    @endif
@stop
