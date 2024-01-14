<!doctype html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <link href="/css/bootstrap.min.css" rel="stylesheet">
    <link href="/css/app.css" rel="stylesheet">
    <meta name="robots" content="noindex, nofollow"/>
</head>
<body>
<div class="main">
    <nav>
        <ul>
            <li><a href="{{ route('admin.index') }}">Главная</a></li>
            <li><a href="{{ route('admin.articles.index') }}">Статьи</a></li>
            <li><a href="{{ route('admin.youtube.index') }}">YouTube</a></li>
            <li><a href="{{ route('admin.menu-types.index') }}">Типы меню</a></li>
            <li><a href="{{ route('admin.menu.index') }}">Элементы меню</a></li>
            <li><a href="{{ route('admin.errors.index') }}">Ошибки</a></li>
            <li><a href="{{ route('admin.media.index') }}">Медиа библиотека</a></li>
            <li><a href="{{ route('admin.entity.index') }}">Сущности</a></li>
        </ul>
    </nav>
    <article>
        @yield('content')
    </article>
</div>
</body>
