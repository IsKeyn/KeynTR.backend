<?php

namespace App\Http\Controllers;

use App\Http\Resources\MenuResource;
use App\Models\Article;
use App\Models\Menu;
use App\Models\MenuType;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;

class MenuController extends Controller
{
    public function getMenuElements(Request $request) {
        $query = MenuType::query();

        $types = array();

        if ($request->arTypes) {
            foreach (json_decode($request->arTypes) as $type) {
                $types[] = $type;
            }

            $query = $query->whereIn('code', $types);
        }

        $menu =  $query->get();

        return MenuResource::collection($menu);
    }

    public function getArticlesMenu(Request $request) {

        $menu = [];

        // Извлекаем меню новостей
        $newsMenu = Article::latest()->where('type', 'news')->limit(5)->get();

        $arNewsMenu = [];

        if (count($newsMenu) > 0) {
            $arNewsMenu = Arr::add($arNewsMenu, 'name', 'Последние новости');
            $arNewsMenu = Arr::add($arNewsMenu, 'elements', []);
        }

        foreach ($newsMenu as $element) {
            $arNewsMenu['elements'][] = array(
                'id' => $element->id,
                'name' => $element->title,
                'url' => '/' . $element->type . '/' . $element->code . '/',
                'link_type' => 'route',
            );
        }

        // Извлекаем меню статей
        $articlesMenu = Article::query()->where('type', 'article')->limit(5)->get();

        $arArticleMenu = [];

        if (count($articlesMenu) > 0) {
            $arArticleMenu = Arr::add($arArticleMenu, 'name', 'Последние статьи');
            $arArticleMenu = Arr::add($arArticleMenu, 'elements', []);
        }

        foreach ($articlesMenu as $element) {
            $arArticleMenu['elements'][] = array(
                'id' => $element->id,
                'name' => $element->title,
                'url' => '/' . $element->type . '/' . $element->code . '/',
                'link_type' => 'route',
            );
        }

        $menu[] = $arNewsMenu;
        $menu[] = $arArticleMenu;

        return array('data' => $menu);
    }
}
