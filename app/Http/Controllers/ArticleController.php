<?php

namespace App\Http\Controllers;

use App\Http\Resources\ArticleListResource;
use App\Http\Resources\ArticleResource;
use App\Models\Article;
use Illuminate\Http\Request;

class ArticleController extends Controller
{
    protected $model = Article::class;

    public function get() {

        $articles = $this->model::all();

        $returnData = array();

        foreach ($articles as $article) {
            $returnData[] = ArticleListResource::make($article);
        }

        return $returnData;
    }

    public function getByFilter(Request $request) {

        if ($request->filter['type']) {
            $article = $this->model::where('code', $request->filter['code'])->where('type', $request->filter['type'])->first();
        } else {
            $article = $this->model::where('code', '=', $request->filter['code'])->first();
        }

        return ArticleResource::make($article);
    }

    public function update(Request $request) {
        echo '<pre>';
        print_r($request);
        echo '</pre>';
    }
}
