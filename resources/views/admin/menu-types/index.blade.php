@extends('layouts.admin')

@section('content')
    <div class="container">
        <h1>Типы меню</h1>
        <div>
            @foreach($menuTypes as $type)
                <div class="row">
                    <div class="col-md-12">
                        <a href="{{ route('admin.menu-types.edit', $type->id) }}">{{ $type->id }} {{ $type->name }}</a>
                    </div>
                </div>
            @endforeach
            <div class="row">
                <div class="col-md-12">
                    <a href="{{ route('admin.menu-types.create') }}"><button>Добавить новый тип</button></a>
                </div>
            </div>
        </div>
    </div>
@endsection
