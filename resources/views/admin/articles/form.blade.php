@extends('layouts.admin')

@section('content')
    <form method="POST" enctype="multipart/form-data"
          @isset($article)
          action="{{ route('articles.update', $article) }}"
          @else
          action="{{ route('articles.store') }}"
        @endisset
    >
        @isset($article)
            @method('PUT')
        @endisset
        @csrf
        <label>
            Тип
            <input type="text" name="type" value="@isset($article){{ $article->type }}@endisset">
        </label>
        <label>
            Заголовок
            <input type="text" name="title" value="@isset($article){{ $article->title }}@endisset">
        </label>
        <label>
            Code
            <input type="text" name="code" value="@isset($article){{ $article->code }}@endisset">
        </label>
        <label>
            Теги
            <input type="text" name="tags" value="@isset($article){{ $article->tags }}@endisset">
        </label>
        <label>
            Изображение
            <input type="text" name="image" value="@isset($article){{ $article->image }}@endisset">
        </label>

        <img src="@isset($article){{ $article->image }}@endisset">
        <label>
            Текст предпросмотра
            <textarea name="text_preview">
                @isset($article){{ $article->text_preview }}@endisset
            </textarea>
        </label>
        <label>
            Полный текст
            <textarea name="text_full">
               @isset($article){{ $article->text_full }}@endisset
            </textarea>
        </label>
        <button>Сохранить</button>
    </form>
@endsection
