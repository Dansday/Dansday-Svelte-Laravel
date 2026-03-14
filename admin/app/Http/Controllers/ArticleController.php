<?php

namespace App\Http\Controllers;

use App\Models\Article;
use App\Models\Category;
use App\Models\Gallery;
use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class ArticleController extends Controller
{
    public function index()
    {
        $user = User::find(1);
        $gallery = Gallery::all();
        $categories = Category::all();
        $articles = DB::table('articles')->orderBy('created_at', 'desc')->get();
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
            'image' => ['required', 'file', 'mimes:jpg,jpeg,png'],
        ]);
        if ($validate->fails()) {
            return redirect('/admin/articles/post')
                ->with('error-validation', '')
                ->withErrors($validate)
                ->withInput();
        }

        $disk = Storage::disk('uploads');
        $route_image = $data['image']->storeAs('img/articles', 'post_image_' . mt_rand(10, 9999) . '.' . $data['image']->guessExtension(), 'uploads');

        $tempFiles = $disk->files('img/temp');
        foreach ($tempFiles as $tempPath) {
            $name = basename($tempPath);
            $destPath = 'img/articles/' . $name;
            if ($disk->exists($tempPath)) {
                $disk->put($destPath, $disk->get($tempPath));
                $disk->delete($tempPath);
            }
        }

        $post = new Article();
        $post->title = $data['title'];
        $post->short_desc = $data['short_desc'];
        $post->text = str_replace([$disk->url('img/temp'), 'uploads/img/temp'], [$disk->url('img/articles'), 'uploads/img/articles'], $data['text']);
        $post->image = 'uploads/' . $route_image;
        $post->author = $data['author'];
        $post->slug = $slug;
        $post->status = $data['status'];
        $post->images_code = $data['images_code'];
        $post->category_id = $data['category'];
        $post->order = 0;
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
            $validate2 = Validator::make($data, ['image' => ['file', 'mimes:jpg,jpeg,png']]);
            if ($validate2->fails()) {
                return redirect('/admin/articles/post/' . $data['id'])
                    ->with('error-validation', '')
                    ->withErrors($validate2)
                    ->withInput();
            }
        }

        $disk = Storage::disk('uploads');
        $route_image = $route_image_current;
        if ($data['image'] != '') {
            if ($route_image_current != '' && uploads_path_safe_to_delete($route_image_current)) {
                $p = uploads_path_for_disk($route_image_current);
                if ($p !== '' && $disk->exists($p)) {
                    $disk->delete($p);
                }
            }
            $route_image = 'uploads/' . $data['image']->storeAs('img/articles', 'post_image_' . mt_rand(10, 9999) . '.' . $data['image']->guessExtension(), 'uploads');
        } elseif ($route_image_current == '' || $route_image_current === null) {
            $post = Article::find($id);
            if ($post && $post->image != '' && uploads_path_safe_to_delete($post->image)) {
                $p = uploads_path_for_disk($post->image);
                if ($p !== '' && $disk->exists($p)) {
                    $disk->delete($p);
                }
            }
            $route_image = '';
        }

        $tempFiles = $disk->files('img/temp');
        foreach ($tempFiles as $tempPath) {
            $name = basename($tempPath);
            $destPath = 'img/articles/' . $name;
            if ($disk->exists($tempPath)) {
                $disk->put($destPath, $disk->get($tempPath));
                $disk->delete($tempPath);
            }
        }
        $slug_no_spaces = strtolower(str_replace(' ', '-', $data['slug']));
        $slug = preg_replace('/[^A-Za-z0-9\-]/', '', $slug_no_spaces);

        Article::where('id', $id)->update([
            'title' => $data['title'],
            'short_desc' => $data['short_desc'],
            'text' => str_replace([$disk->url('img/temp'), 'uploads/img/temp'], [$disk->url('img/articles'), 'uploads/img/articles'], $data['text']),
            'image' => $route_image,
            'author' => $data['author'],
            'slug' => $slug,
            'status' => $data['status'],
            'category_id' => $data['category'],
        ]);
        return redirect('/admin/articles/posts')->with('ok-update', '');
    }


    public function destroy($id, Request $request)
    {
        $validate = Article::where('id', $id)->get();
        if (! empty($validate)) {
            $disk = Storage::disk('uploads');
            $files = $disk->files('img/articles');
            foreach ($files as $file) {
                if (str_contains($file, $validate[0]['images_code']) && $disk->exists($file)) {
                    $disk->delete($file);
                }
            }
            if (! empty($validate[0]['image']) && uploads_path_safe_to_delete($validate[0]['image'])) {
                $p = uploads_path_for_disk($validate[0]['image']);
                if ($p !== '' && $disk->exists($p)) {
                    $disk->delete($p);
                }
            }
            Article::where('id', $validate[0]['id'])->delete();
            return redirect('/admin/articles/posts')->with('ok-delete', '');
        }
        return redirect('/admin/articles/posts')->with('no-delete', '');
    }
}
