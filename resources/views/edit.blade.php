@extends('statamic::layout')

@section('content')
    <redirect-create title="{{ $title }}" action="{{ cp_route('statamic-redirects.update', ['id' => $id]) }}"
        :blueprint='@json($blueprint)' :meta='@json($meta)' :values='@json($values)'></redirect-create>
@stop
