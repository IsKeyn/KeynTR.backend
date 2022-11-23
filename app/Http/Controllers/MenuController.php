<?php

namespace App\Http\Controllers;

use App\Http\Resources\MenuResource;
use App\Models\Article;
use App\Models\Menu;
use App\Models\MenuType;
use Illuminate\Http\Request;

class MenuController extends Controller
{
    public function getMenuElements(Request $request) {
        $query = MenuType::query();

        $types = array();

        if ($request->arTypes) {
            foreach ($request->arTypes as $type) {
                $types[] = $type;
            }

            $query = $query->whereIn('code', $types);
        }

        $menu =  $query->get();

        return MenuResource::collection($menu);
    }

    public function getArticlesMenu(Request $request) {

        // Извлекаем меню новостей
        $newsMenu = Article::query()->where('type', 'news')->limit(5)->get();

        foreach ($newsMenu as $element) {
            echo '<pre>';
            print_r($element);
            echo '</pre>';
        }

        // Извлекаем меню статей
        $articlesMenu = Article::query()->where('type', 'article')->limit(5)->get();

        foreach ($newsMenu as $element) {
            echo '<pre>';
            print_r($element);
            echo '</pre>';
        }

        return 123;
    }
}
