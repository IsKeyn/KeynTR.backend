@extends('layouts.admin')

@section('content')
    @foreach($articles as $article)
        <a href="{{ route('articles.edit', $article->id) }}">{{ $article->title }}</a>
    @endforeach

    <a href="{{ route('articles.create') }}">Добавить статью</a>
@endsection
