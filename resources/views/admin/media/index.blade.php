@extends('layouts.admin')

@section('content')
    <div class="container">
    {{--    Форма для добавления файлов    --}}

        <h1>Медиа библиотека</h1>

        <form method="POST" enctype="multipart/form-data" action="{{ route('admin.media.store') }}">
            @csrf
            <div class="input-group row">
                <label for="image" class="col-sm-2 col-form-label">Картинка: </label>
                <div class="col-sm-10">
                    <label class="btn btn-default btn-file">
                        Загрузить <input type="file" style="display: none;" name="image" id="image">
                    </label>
                </div>
            </div>
            <button class="btn btn-success">Сохранить</button>
        </form>

        <div>
            @foreach($medias as $media)
                <div class="row">
                    <div class="col-md-12">
                        {{ $media->id }}. {{ $media->name }} [{{ $media->url }}] [{{ $media->created_at->format('d.m.Y H:i:s') }}]
                        <img src="http://localhost:8000{{ $media->url }}" width="400px" />
                    </div>
                </div>
            @endforeach
        </div>
    </div>
@endsection
