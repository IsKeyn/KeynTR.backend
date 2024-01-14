@extends('layouts.admin')

@section('content')
<div class="container">
    <h1>Список {{ $data['name'] }}</h1>
    <div>
        @if ($data['list'])
            @foreach($data['list'] as $element)
                <div class="row">
                    <div class="col-md-12">
                        <a href="{{ route('admin.entity.edit-element', ['entityName' => $data['name'], 'id' => $element->id]) }}">{{ $element->id }}. {{ $element->name }}</a>
                    </div>
                </div>
            @endforeach
        @endif
        <div class="row">
            <div class="col-md-12">
                <a href="{{ route('admin.entity.add-element', $data['name']) }}"><button>Добавить элемент {{ $data['name'] }}</button></a>
            </div>
        </div>
    </div>
</div>
@endsection
