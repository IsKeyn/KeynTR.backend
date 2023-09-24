@extends('layouts.admin')

@section('content')
    <div class="container">
        <h1>Ошибки</h1>
        <div>
            @foreach($errors as $error)
                <div class="row">
                    <div class="col-md-12">
                        {{ $error->id }}. {{ $error->message }} [{{ $error->type }}] [{{ $error->created_at->format('d.m.Y H:i:s') }}]
                    </div>
                </div>
            @endforeach
        </div>
    </div>
@endsection
