@extends('layouts.admin')

@section('content')
<div class="container">
    <h1>Добавление\редактирование {{ $data['name'] }}</h1>
    <h2>Основные поля</h2>
    <form
        method="POST"
        enctype="multipart/form-data"
        @isset($data['element']) action="{{
            route(
                'admin.entity.update-element',
                [
                    'entityName' => $data['name'],
                    'id' => $data['element']->id
                ]
            )
        }}"
        @else action="{{ route('admin.entity.store-element', $data['name']) }}"
        @endisset
        class="admin-form"
    >
        @csrf
        @if (isset($data['editableFields']) && is_array($data['editableFields']))
            @foreach($data['editableFields'] as $field)
                @if ($field['type'] === 'input')
                    <div class="row">
                        <div class="col-md-3">
                            <label for="{{ $field['name'] }}">{{ $field['description'] }}</label>
                        </div>
                        <div class="col-md-9">
                            <input
                                id="{{ $field['name'] }}"
                                type="text"
                                name="{{ $field['name'] }}"
                                value="@isset($data['element']){{ $data['element'][$field['name']] }}@endisset">
                        </div>
                    </div>
                @endif
            @endforeach
        @else
            Для работы с сущностью у модели должен быть заполнен $editableField
        @endif
        <div class="row">
            <div class="col-md-3">
                <button>Сохранить</button>
            </div>
        </div>
    </form>

    <h2>Добавить дополнительное поля</h2>
    <form
        method="POST"
        enctype="multipart/form-data"
        action="{{ route('admin.entity.store-element-additional-field',
            [
                'entityName' => $data['name'],
                'id' => $data['element']->id
            ])
        }}"
        class="admin-form"
    >
        @csrf
            <div class="row">
                <div class="col-md-4">
                    <div class="col-md-3">
                        <label for="name">Название</label>
                    </div>
                    <input
                        id="name"
                        type="text"
                        name="name"
                    >
                </div>
                <div class="col-md-4">
                    <div class="col-md-3">
                        <label for="value">Значение</label>
                    </div>
                    <input
                        id="value"
                        type="text"
                        name="value"
                    >
                </div>
                <div class="col-md-4">
                    <div class="col-md-3">
                        <label for="sort">Сортировка</label>
                    </div>
                    <input
                        id="sort"
                        type="text"
                        name="sort"
                    >
                </div>
            </div>
            <div class="row">
                <div class="col-md-3">
                    <button>Сохранить</button>
                </div>
            </div>
    </form>

    <h2>Дополнительные поля</h2>
    @if (isset($data['editableFields']) && is_array($data['editableFields']))
        @foreach($data['editableFields'] as $field)
            @if ($field['type'] === 'additional_fields')
                @foreach($data['element'][$field['name']] as $arElement)
                    <div class="row">
                        <form
                            method="POST"
                            enctype="multipart/form-data"
                            class="admin-form"
                            action="{{ route('admin.entity.update-element-additional-field',
                                [
                                    'entityName' => $data['name'],
                                    'id' => $data['element']->id
                                ])
                            }}"
                        >
                            @csrf
                            <div class="col-md-1">
                                <input
                                    id="id"
                                    type="text"
                                    name="id"
                                    value="{{ $arElement['id'] }}"
                                >
                            </div>
                            @foreach($field['array_fields'] as $array_field)
                                <div class="col-md-3">
                                    <input
                                        id="{{ $array_field['name'] }}"
                                        type="text"
                                        name="{{$array_field['name'] }}"
                                        value="{{ $arElement[$array_field['name']] }}">
                                </div>
                            @endforeach
                                <div class="col-md-1">
                                    <button>Изменить</button>
                                </div>
                        </form>
                        <form
                            method="POST"
                            enctype="multipart/form-data"
                            class="admin-form"
                            action="{{ route('admin.entity.delete-element-additional-field',
                                [
                                    'entityName' => $data['name'],
                                    'id' => $data['element']->id
                                ])
                            }}"
                        >
                            <div class="col-md-1">
                                @csrf
                                <input
                                    type="text"
                                    name="id"
                                    value="{{ $arElement['id'] }}"
                                    style="display: none"
                                >
                                <button>Удалить</button>
                            </div>
                        </form>
                    </div>
                @endforeach
            @endif
        @endforeach
    @endif
</div>
@endsection
