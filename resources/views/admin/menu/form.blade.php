@extends('layouts.admin')

@section('content')
<div class="container">
    <h1>Добавление\редактирование Элемента меню</h1>
    <form
        method="POST"
        enctype="multipart/form-data"
        @isset($menu) action="{{ route('admin.menu.update', $menu) }}"
        @else action="{{ route('admin.menu.store') }}"
        @endisset
        class="admin-form"
    >
        @isset($menu) @method('PUT') @endisset
        @csrf
        <div class="row">
            <div class="col-md-3">
                <label for="name">Название</label>
            </div>
            <div class="col-md-9">
                <input id="name" type="text" name="name" value="@isset($menu){{ $menu->name }}@endisset">
            </div>
        </div>
        <div class="row">
            <div class="col-md-3">
                <label for="name">Url</label>
            </div>
            <div class="col-md-9">
                <input id="url" type="text" name="url" value="@isset($menu){{ $menu->url }}@endisset">
            </div>
        </div>
            <div class="row">
                <div class="col-md-3">
                    <label for="name">ID типа</label>
                </div>
                <div class="col-md-9">
                    <input id="menu_type_id" type="text" name="menu_type_id" value="@isset($menu){{ $menu->menu_type_id }}@endisset">
                </div>
            </div>
            <div class="row">
                <div class="col-md-3">
                    <label for="name">Тип ссылки (externalLink - для внешних ссылок)</label>
                </div>
                <div class="col-md-9">
                    <input id="link_type" type="text" name="link_type" value="@isset($menu){{ $menu->link_type }}@endisset">
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
