@extends('layouts.admin')

@section('content')
    <div class="container">
        <h1>YouTube</h1>
        <ul>
            <li><a href="{{ route('admin.entity.', 'game') }}">Сущность "Game"</a></li>
        </ul>
    </div>
@endsection
