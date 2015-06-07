@extends('master')

@section('centerContent')
    <div>

        <p>Total entries: {{count($entries)}}</p>

        @foreach ($entries as $entry)
            <p style="background-color: {{$entry['color']}}">
                @include('entry')
            </p>
        @endforeach

    </div>
@stop