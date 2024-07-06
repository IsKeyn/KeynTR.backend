<?php
namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Resources\Admin\ArticleResource;
use App\Models\Article;
use App\Models\Media;
use App\Models\Tag;
use App\Services\BlockService;
use App\Services\TagService;
use Illuminate\Http\Request;

class AdminArticlePagesController extends Controller {
    /*
     * Котроллер для создания страниц в админке, управления статьями
     */

    protected $model = Article::class;

    public function index(Article $article) {
        return $article::all();

//        return view('admin.articles.index', compact('articles'));
    }

    public function create() {
        return view('admin.articles.form');
    }

    public function store(Request $request)
    {
        $fields = $request->validate([
            'name' => 'required',
            'slug' => 'required',
            'text_preview' => 'sometimes',
            'text_full' => 'sometimes',
            'title_image' => 'sometimes',
            'type' => 'sometimes',
            'tags' => 'sometimes',
        ]);

        $fields['created_by'] = $request->user()->id;

        if ($article = Article::create($fields)) {

            if (isset($fields['title_image'])) {
                $media = Media::query()->where('id', $fields['title_image'])->first();
                $article->media()->syncWithPivotValues($media->id, ['type' => 1], false);
            }

            if (isset($fields['tags'])) {
                TagService::attacheTagsToEntity($article, $fields['tags']);
            }

            return $article;
        }
    }

    public function update(Request $request, Article $article) {
        $fields = $request->validate([
            'name' => 'required',
            'slug' => 'required',
            'text_preview' => 'sometimes',
            'text_full' => 'sometimes',
            'title_image' => 'sometimes',
            'type' => 'sometimes',
            'tags' => 'sometimes',
            'blocks' => 'sometimes',
        ]);

        if (!isset($fields['type'])) {
            $fields['type'] = 0;
        }

        if (isset($fields['title_image'])) {
            $media = Media::query()->where('id', $fields['title_image'])->first();
            $article->media()->syncWithPivotValues($media->id, ['type' => 1]);
        }

        if (isset($fields['tags'])) {
            TagService::attacheTagsToEntity($article, $fields['tags']);
        }

        if (isset($fields['blocks'])) {
            BlockService::set($article, $fields['blocks']);
        }

        return $article->update($fields);
//        return redirect()->route('admin.articles.index');

//        if ($id) {
//            if ($article = $this->model::where('id', $id)->first()) {
//                return view('admin.article.update', compact('article'));
//            } else {
//                echo 'Такой статьи нет'; // TODO Сделать общий вывод ошибок, типа error();
//            }
//        } else {
//            echo 'Не получен ID статьи'; // TODO Сделать общий вывод ошибок, типа error();
//        }
    }

    public function edit(Article $article)
    {
        return ArticleResource::make($article);
//        return view('admin.articles.form', compact('article'));
    }
}
