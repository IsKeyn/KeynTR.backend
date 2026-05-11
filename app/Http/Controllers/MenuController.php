<?php

namespace App\Http\Controllers;

use App\Http\Resources\MenuResource;
use App\Models\Article;
use App\Models\Game;
use App\Models\MenuType;
use App\Models\Movie;
use App\Models\Page;
use App\Services\Cache\MenuCacheService;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Cache;

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

        // Извлекаем игры
        $gameQuery = Game::query();
        $gameQuery->whereHas('groups', function ($q)  {
            $q->where('groups.slug', 'main-series-games');
        });

        $gameMenu = $gameQuery->orderBy('id', 'asc')->get();
        $arGameMenu = [];

        if (count($gameMenu) > 0) {
            $arGameMenu = Arr::add($arGameMenu, 'name', 'Игры серии');
            $arGameMenu = Arr::add($arGameMenu, 'elements', []);
        }

        foreach ($gameMenu as $element) {
            $arGameMenu['elements'][] = array(
                'id' => $element->id,
                'name' => $element->name,
                'url' => "/game/{$element->slug}/",
                'link_type' => 'route',
                'icon' => $element->icon,
            );
        }

        // Извлекаем фильмы
        $movieMenu = Movie::query()->get();
        $arMovieMenu = [];

        if (count($gameMenu) > 0) {
            $arMovieMenu = Arr::add($arMovieMenu, 'name', 'Фильмы');
            $arMovieMenu = Arr::add($arMovieMenu, 'elements', []);
        }

        foreach ($movieMenu as $element) {
            $arMovieMenu['elements'][] = array(
                'id' => $element->id,
                'name' => $element->name,
                'url' => "/movie/{$element->slug}/",
                'link_type' => 'route',
                'icon' => $element->icon,
            );
        }

        // Извлекаем меню новостей
        $newsMenu = Article::latest()->where('type', Article::NEWS_TYPE)->orderBy('created_at', 'desc')->limit(5)->get();

        $arNewsMenu = [];

        if (count($newsMenu) > 0) {
            $arNewsMenu = Arr::add($arNewsMenu, 'name', 'Последние новости');
            $arNewsMenu = Arr::add($arNewsMenu, 'elements', []);
        }

        foreach ($newsMenu as $element) {
            $arNewsMenu['elements'][] = array(
                'id' => $element->id,
                'name' => $element->name,
                'url' => $this->getArticleUrl($element),
                'link_type' => 'route',
                'icon' => $element->icon,
            );
        }

        // Извлекаем меню статей
        $articlesMenu = Article::query()->where('type', Article::ARTICLE_TYPE)->orderBy('created_at', 'desc')->limit(5)->get();

        $arArticleMenu = [];

        if (count($articlesMenu) > 0) {
            $arArticleMenu = Arr::add($arArticleMenu, 'name', 'Последние статьи');
            $arArticleMenu = Arr::add($arArticleMenu, 'elements', []);
        }

        foreach ($articlesMenu as $element) {
            $arArticleMenu['elements'][] = array(
                'id' => $element->id,
                'name' => $element->name,
                'url' => $this->getArticleUrl($element),
                'link_type' => 'route',
                'icon' => $element->icon,
            );
        }

        $menu[] = $arGameMenu;
        $menu[] = $arMovieMenu;
        $menu[] = $arNewsMenu;
        $menu[] = $arArticleMenu;

        return array('data' => $menu);
    }

    private function getArticleUrl($element) {
        if ($element->type) {
            switch ($element->type) {
                case Article::NEWS_TYPE:
                    $typeSlug = 'news';
                    break;

                case Article::PROGRAM_TYPE:
                    $typeSlug = 'program';
                    break;
            }

            return "/{$typeSlug}/{$element->slug}/";

        } elseif ($element->entity_type && isset($element->entity_id)) {
            $pageSlug = array_search($element->entity_type, Page::PAGE_TO_ENTITY);
            $entitySlug = $element->entity_type::query()->where('id', $element->entity_id)->first()->slug;

            if ($pageSlug && $entitySlug && $element->slug) {
                return "/{$pageSlug}/{$entitySlug}/{$element->slug}";
            }
        } else {
            return "/article/{$element->slug}";
        }
    }

    public function getByCode(Request $request)
    {
        if ($request->code) {
            $cacheKey = MenuCacheService::ADMIN_LIST_PREFIX . '_' . $request->code;
            $time = MenuCacheService::TIME;

            return Cache::remember($cacheKey, $time, function () use ($request) {
                $menuTypes = MenuType::findByCode($request->code)
                    ->active()
                    ->with([
                        'elements',
                        'elements.permissions'
                    ])
                    ->get()
                    ->sortBy('sort');

                return MenuResource::collection($menuTypes);
            });
        }
    }
}
