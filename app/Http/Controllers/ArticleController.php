<?php

namespace App\Http\Controllers;

use App\Artecle;
use App\Http\Requests\ArticleRequest;
use Illuminate\Http\Request;

class ArticleController extends Controller
{
    public function index()
    {

        return view('articles.index')->with('articles', Artecle::all());
    }

    public function create()
    {
        return view('articles.create');
    }


    public function add(ArticleRequest $request, Artecle $article)
    {
        $article->fill($request->all());
        $article->save();

        return redirect() -> route('admin.article.index');
    }

    public function edit($id, Artecle $article)
    {
        return view('articles.edit') -> with('article', $article->find($id));
    }

    public function update($id, ArticleRequest $request, Artecle $article)
    {
        $article = $article -> find($id);

        $article -> fill($request -> all());
        $article -> save();

        return redirect() -> route('admin.article.index');
    }

    public function delete($id, Artecle $article)
    {
        $article -> find($id) -> delete();
        return redirect() -> route('admin.article.index');
    }

    public function show(Artecle $article)
    {
        return view('articles.show') -> with('article', $article);
    }
}
