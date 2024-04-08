<?php
namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Article;
use Illuminate\Http\Request;

class AdminArticlePagesController extends Controller {
    /*
     * Котроллер для создания страниц в админке, управления статей
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
        $params = $request->all();

        return Article::create($params);

//        return redirect()->route('admin.articles.index');
    }

    public function update(Request $request, Article $article) {
        $params = $request->all();

        return $article->update($params);
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
        return $article;
//        return view('admin.articles.form', compact('article'));
    }
}
