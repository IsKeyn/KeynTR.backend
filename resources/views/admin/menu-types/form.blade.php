@extends('layouts.admin')

@section('content')
<div class="container">
    <h1>Добавление\редактирование типа меню</h1>
    <form
        method="POST"
        enctype="multipart/form-data"
        @isset($menuType) action="{{ route('admin.menu-types.update', $menuType) }}"
        @else action="{{ route('admin.menu-types.store') }}"
        @endisset
        class="admin-form"
    >
        @isset($menuType) @method('PUT') @endisset
        @csrf
        <div class="row">
            <div class="col-md-3">
                <label for="name">Название</label>
            </div>
            <div class="col-md-9">
                <input id="name" type="text" name="name" value="@isset($menuType){{ $menuType->name }}@endisset">
            </div>
        </div>
        <div class="row">
            <div class="col-md-3">
                <label for="name">Код</label>
            </div>
            <div class="col-md-9">
                <input id="code" type="text" name="code" value="@isset($menuType){{ $menuType->code }}@endisset">
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
