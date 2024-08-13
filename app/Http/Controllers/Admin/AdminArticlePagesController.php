<?php
namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Resources\Admin\ArticleResource;
use App\Models\Article;
use App\Models\Media;
use App\Models\Seo;
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
        $validated = $this->validateFields($request);

        $validated['created_by'] = $validated['created_by'] ? $validated['created_by'] : $request->user()->id;

        if (!$validated['created_at']) {
            unset($validated['created_at']);
        }

        if ($article = Article::create($validated)) {

            $this->setAdditionalFields($article, $validated);
            return $article;
        }
    }

    public function update(Request $request, Article $article) {
        $validated = $this->validateFields($request);

        $this->setAdditionalFields($article, $validated);
        return $article->update($validated);
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

    public function validateFields($request) {
        return $request->validate([
            'name' => 'required',
            'slug' => 'required',
            'text_preview' => 'sometimes',
            'text_full' => 'sometimes',
            'title_image' => 'sometimes',
            'type' => 'sometimes',
            'tags' => 'sometimes',
            'seo' => 'sometimes',
            'blocks' => 'sometimes',
            'entity_type' => 'nullable|string',
            'entity_id' => 'nullable|integer',
            'created_at' => 'nullable',
            'created_by' => 'nullable',
            'editor' => 'nullable',
            'show_author' => 'nullable',
            'show_editor' => 'nullable',
        ]);
    }

    public function setAdditionalFields($model, $validated) {
        if (!isset($validated['type'])) {
            $validated['type'] = 0;
        }

        if (isset($validated['title_image'])) {
            $media = Media::query()->where('id', $validated['title_image'])->first();
            $model->media()->syncWithPivotValues($media->id, ['type' => 1]);
        }

        if (isset($validated['tags'])) {
            TagService::attacheTagsToEntity($model, $validated['tags']);
        }

        if (isset($validated['blocks'])) {
            BlockService::set($model, $validated['blocks']);
        }

        if (isset($validated['seo']) && $validated['seo']) {
            if ($model->seo) {
                $model->seo()->update($validated['seo']);
            } else {
                $meta = new Seo($validated['seo']);
                $model->seo()->save($meta);
            }
        }
    }

    public function edit(Article $article)
    {
        return ArticleResource::make($article);
//        return view('admin.articles.form', compact('article'));
    }
}
