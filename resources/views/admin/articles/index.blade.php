@extends('layouts.admin')

@section('content')
    <div class="container">
        <h1>Статьи</h1>
        <div>
            @foreach($articles as $article)
                <div class="row">
                    <div class="col-md-12">
                        <a href="{{ route('admin.articles.edit', $article->id) }}">{{ $article->title }}</a>
                    </div>
                </div>
            @endforeach
            <div class="row">
                <div class="col-md-12">
                    <a href="{{ route('admin.articles.create') }}"><button>Добавить статью</button></a>
                </div>
            </div>
        </div>
    </div>
@endsection
