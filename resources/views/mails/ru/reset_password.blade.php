@extends('layouts.app')

@section('content')
    <h2 style="text-align: center">Здравствуйте!</h2>
    <h3 style="text-align: center">Пожалуйста, нажмите кнопку ниже, чтобы перейти на страницу сброса пароля</h3>
    <h3 style="text-align: center">Ссылка действует в течение часа.</h3>
    @component('mail::button', ['url' => $url, 'color' => 'green'])
        Сбросить пароль
    @endcomponent
    <h3 style="text-align: center">Если вы не запрашивали сброс пароля, никаких дальнейших действий не требуется.</h3>
    <h3 style="text-align: center">Если у вас возникли проблемы с нажатием кнопки "Сбросить пароль" скоприруйте и
        вставьте URL-адрес в ваш браузер:
        <a href="{{ $url }}">{{ $url }}</a>
    </h3>
@endsection
