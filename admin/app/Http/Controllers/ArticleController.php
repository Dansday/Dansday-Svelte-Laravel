<?php

namespace App\Http\Controllers;

use App\Models\Article;
use App\Models\Category;
use App\Models\Gallery;
use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;

class ArticleController extends Controller
{
    public function index()
    {
        $user = User::find(1);
        $gallery = Gallery::all();
        $categories = Category::all();
        $articles = DB::table('articles')->orderBy('order', 'asc')->get();
        return view('admin.pages.articles.posts')
            ->with('articles', $articles)
            ->with('categories', $categories)
            ->with('gallery', $gallery)
            ->with('user', $user);
    }

    public function create()
    {
        $user = User::find(1);
        $categories = Category::all();
        $articles = Article::all();
        return view('admin.pages.articles.post')
            ->with('categories', $categories)
            ->with('articles', $articles)
            ->with('user', $user);
    }

    public function store(Request $request)
    {
        $data = [
            'title' => $request->input('title'),
            'short_desc' => $request->input('short_desc'),
            'author' => $request->input('author'),
            'category' => $request->input('category'),
            'status' => $request->input('status'),
            'images_code' => $request->input('images_code'),
            'text' => $request->input('text'),
            'image' => $request->file('image'),
            'order' => $request->input('order'),
        ];
        $slug_no_spaces = strtolower(str_replace(' ', '-', $data['title']));
        $slug = preg_replace('/[^A-Za-z0-9\-]/', '', $slug_no_spaces);

        $validate = Validator::make($data, [
            'title' => ['string', 'max:55'],
            'short_desc' => ['string', 'max:255'],
            'author' => ['string', 'max:55'],
            'category' => ['string', 'max:55'],
            'status' => ['string', 'max:55'],
            'text' => ['required'],
            'image' => ['required', 'file', 'mimes:jpg,jpeg,png', 'max:10240'],
        ]);
        if ($validate->fails()) {
            return redirect('/admin/articles/post')
                ->with('error-validation', '')
                ->withErrors($validate)
                ->withInput();
        }

        $directory = 'uploads/img/articles';
        $dirFull = public_path($directory);
        File::ensureDirectoryExists($dirFull);
        $random = mt_rand(10, 9999);
        $route_image = $directory . '/post_image_' . $random . '.' . $data['image']->guessExtension();
        $data['image']->move($dirFull, basename($route_image));

        $tempFull = public_path('uploads/img/temp');
        foreach (glob($tempFull . '/*') ?: [] as $file) {
            $name = basename($file);
            copy($file, $dirFull . '/' . $name);
            if (File::exists($file)) {
                unlink($file);
            }
        }

        $post = new Article();
        $post->title = $data['title'];
        $post->short_desc = $data['short_desc'];
        $post->text = str_replace('uploads/img/temp', 'uploads/img/articles', $data['text']);
        $post->image = $route_image;
        $post->author = $data['author'];
        $post->slug = $slug;
        $post->status = $data['status'];
        $post->images_code = $data['images_code'];
        $post->category_id = $data['category'];
        $post->order = $data['order'];
        $post->save();
        return redirect('/admin/articles/posts')->with('ok-add', '');
    }

    public function show($id)
    {
        $post = Article::find($id);
        $user = User::find(1);
        $categories = Category::all();
        if ($post != null) {
            return view('admin.pages.articles.single')
                ->with('post', $post)
                ->with('categories', $categories)
                ->with('user', $user);
        }
        return redirect('/admin/articles/posts');
    }

    public function update($id, Request $request)
    {
        $data = [
            'id' => $request->input('id'),
            'title' => $request->input('title'),
            'short_desc' => $request->input('short_desc'),
            'author' => $request->input('author'),
            'category' => $request->input('category'),
            'status' => $request->input('status'),
            'slug' => $request->input('slug'),
            'text' => $request->input('text'),
            'image' => $request->file('image'),
            'image_current' => $request->input('image_current'),
        ];
        $route_image_current = $data['image_current'];

        $validate = Validator::make($data, [
            'title' => ['string', 'max:55'],
            'short_desc' => ['string', 'max:255'],
            'author' => ['string', 'max:55'],
            'category' => ['string', 'max:55'],
            'status' => ['string', 'max:55'],
            'slug' => ['string', 'max:55'],
            'text' => ['required'],
        ]);
        if ($validate->fails()) {
            return redirect('/admin/articles/post/' . $data['id'])
                ->with('error-validation', '')
                ->withErrors($validate)
                ->withInput();
        }
        if ($data['image'] != '') {
            $validate2 = Validator::make($data, ['image' => ['file', 'mimes:jpg,jpeg,png', 'max:10240']]);
            if ($validate2->fails()) {
                return redirect('/admin/articles/post/' . $data['id'])
                    ->with('error-validation', '')
                    ->withErrors($validate2)
                    ->withInput();
            }
        }

        $route_image = $route_image_current;
        if ($data['image'] != '') {
            if ($route_image_current != '' && File::exists(public_path($route_image_current))) {
                unlink(public_path($route_image_current));
            }
            $directory = 'uploads/img/articles';
            $dirFull = public_path($directory);
            File::ensureDirectoryExists($dirFull);
            $random = mt_rand(10, 9999);
            $route_image = $directory . '/post_image_' . $random . '.' . $data['image']->guessExtension();
            $data['image']->move($dirFull, basename($route_image));
        } elseif ($route_image_current == '' || $route_image_current === null) {
            $post = Article::find($id);
            if ($post && $post->image != '' && File::exists(public_path($post->image))) {
                unlink(public_path($post->image));
            }
            $route_image = '';
        }

        $tempFull = public_path('uploads/img/temp');
        $dirFull = public_path('uploads/img/articles');
        foreach (glob($tempFull . '/*') ?: [] as $file) {
            $name = basename($file);
            copy($file, $dirFull . '/' . $name);
            if (File::exists($file)) {
                unlink($file);
            }
        }
        $slug_no_spaces = strtolower(str_replace(' ', '-', $data['slug']));
        $slug = preg_replace('/[^A-Za-z0-9\-]/', '', $slug_no_spaces);

        Article::where('id', $id)->update([
            'title' => $data['title'],
            'short_desc' => $data['short_desc'],
            'text' => str_replace('uploads/img/temp', 'uploads/img/articles', $data['text']),
            'image' => $route_image,
            'author' => $data['author'],
            'slug' => $slug,
            'status' => $data['status'],
            'category_id' => $data['category'],
        ]);
        return redirect('/admin/articles/posts')->with('ok-update', '');
    }

    public function orderUp($id)
    {
        $post_1 = Article::where('id', $id)->get();
        $post_2 = DB::table('articles')->where('order', '=', $post_1[0]['order'] - 1)->get();
        Article::where('id', $post_1[0]['id'])->update(['order' => $post_2[0]->order]);
        Article::where('id', $post_2[0]->id)->update(['order' => $post_1[0]['order']]);
        return redirect('/admin/articles/posts')->with('ok-update', '');
    }

    public function orderDown($id)
    {
        $post_1 = Article::where('id', $id)->get();
        $post_2 = DB::table('articles')->where('order', '=', $post_1[0]['order'] + 1)->get();
        Article::where('id', $post_1[0]['id'])->update(['order' => $post_2[0]->order]);
        Article::where('id', $post_2[0]->id)->update(['order' => $post_1[0]['order']]);
        return redirect('/admin/articles/posts')->with('ok-update', '');
    }

    public function destroy($id, Request $request)
    {
        $validate = Article::where('id', $id)->get();
        if (! empty($validate)) {
            foreach (glob(public_path('uploads/img/articles') . '/*') ?: [] as $file) {
                $pos = strpos($file, $validate[0]['images_code']);
                if ($pos !== false && File::exists($file)) {
                    unlink($file);
                }
            }
            $imgPath = $validate[0]['image'] ? public_path($validate[0]['image']) : null;
            if ($imgPath && File::exists($imgPath)) {
                unlink($imgPath);
            }
            Article::where('id', $validate[0]['id'])->delete();
            $posts = DB::table('articles')->orderBy('order', 'asc')->get();
            $i = 1;
            foreach ($posts as $post) {
                Article::where('id', $post->id)->update(['order' => $i]);
                $i++;
            }
            return redirect('/admin/articles/posts')->with('ok-delete', '');
        }
        return redirect('/admin/articles/posts')->with('no-delete', '');
    }
}
