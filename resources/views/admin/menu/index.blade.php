@extends('layouts.admin')

@section('content')
    <div class="container">
        <h1>Элементы меню</h1>
        <div>
            @foreach($menuElements as $element)
                <div class="row">
                    <div class="col-md-12">
                        <a href="{{ route('admin.menu.edit', $element->id) }}">{{ $element->id }} {{ $element->name }}</a>
                    </div>
                </div>
            @endforeach
            <div class="row">
                <div class="col-md-12">
                    <a href="{{ route('admin.menu.create') }}"><button>Добавить элемент меню</button></a>
                </div>
            </div>
        </div>
    </div>
@endsection
