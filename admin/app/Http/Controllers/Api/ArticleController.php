<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Article;
use App\Models\Category;
use Illuminate\Support\Facades\DB;

class ArticleController extends Controller
{
    public function index()
    {
        $articles = DB::table('articles')->where('status', '=', 'published')->orderBy('order', 'asc')->get();
        return response()->json($articles);
    }

    public function show(string $slug)
    {
        $post = Article::where('slug', $slug)->first();
        if (! $post) {
            return response()->json(['error' => 'Not found'], 404);
        }
        $category = Category::find($post->category_id);
        $data = $post->toArray();
        $data['category_name'] = $category ? $category->name : null;
        $data['date_formated'] = date('M d, Y', strtotime($post->created_at));
        return response()->json($data);
    }
}
