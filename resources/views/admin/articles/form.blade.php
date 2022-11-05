@extends('layouts.admin')

@section('content')
<div class="container">
    <h1>Добавление\редактирование статьи</h1>
    <form
        method="POST"
        enctype="multipart/form-data"
        @isset($article) action="{{ route('admin.articles.update', $article) }}"
        @else action="{{ route('admin.articles.store') }}"
        @endisset
        class="admin-form"
    >
        @isset($article) @method('PUT') @endisset
        @csrf
        <div class="row">
            <div class="col-md-3">
                <label for="type">Тип</label>
            </div>
            <div class="col-md-9">
                <input id="type" type="text" name="type" value="@isset($article){{ $article->type }}@endisset">
            </div>
        </div>
        <div class="row">
            <div class="col-md-3">
                <label for="title">Заголовок</label>
            </div>
            <div class="col-md-9">
                <input id="title" type="text" name="title" value="@isset($article){{ $article->title }}@endisset">
            </div>
        </div>
        <div class="row">
            <div class="col-md-3">
                <label for="code">Code</label>
            </div>
            <div class="col-md-9">
                <input id="code" type="text" name="code" value="@isset($article){{ $article->code }}@endisset">
            </div>
        </div>
        <div class="row">
            <div class="col-md-3">
                <label for="tags">Теги</label>
            </div>
            <div class="col-md-9">
                <input id="tags" type="text" name="tags" value="@isset($article){{ $article->tags }}@endisset">
            </div>
        </div>
        <div class="row">
            <div class="col-md-3">
                <label for="image">Изображение</label>
            </div>
            <div class="col-md-9">
                <input id="image" type="text" name="image" value="@isset($article){{ $article->image }}@endisset">
            </div>
        </div>
        <div class="row">
            <div class="col-md-3"></div>
            <div class="col-md-9">
                <img width="50%" src="@isset($article){{ $article->image }}@endisset">
            </div>
        </div>
        <div class="row">
            <div class="col-md-3">
                <label for="text_preview">Текст предпросмотра</label>
            </div>
            <div class="col-md-9">
                <textarea id="text_preview" name="text_preview">
                    @isset($article){{ $article->text_preview }}@endisset
                </textarea>
            </div>
        </div>
        <div class="row">
            <div class="col-md-3">
                <label for="text_full">Полный текст</label>
            </div>
            <div class="col-md-9">
                <textarea id="text_full" name="text_full">
                    @isset($article){{ $article->text_full }}@endisset
                </textarea>
            </div>
        </div>
        <div class="row">
            <div class="col-md-3">
                <button>Сохранить</button>
            </div>
        </div>
    </form>
</div>
@endsection
