@extends('layouts.admin')

@section('content')
    <div class="container">
        <h1>YouTube</h1>
        <ul>
            <li><a href="{{ route('admin.youtube.fetch-playlists-and-videos') }}">Получить все видео и плейлисты</a></li>
            <li><a href="{{ route('admin.youtube.fetch-playlists-and-videos') }}">Получить новые видео</a></li>
        </ul>
    </div>
@endsection
