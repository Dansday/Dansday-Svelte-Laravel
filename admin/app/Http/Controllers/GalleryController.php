<?php

namespace App\Http\Controllers;

use App\Models\Gallery;
use App\Models\Article;
use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Storage;

class GalleryController extends Controller
{
    public function index($id)
    {
        $gallery_images = Gallery::where('article_id', $id)->get();
        $user = User::find(1);
        $post = Article::find($id);
        if(count($gallery_images) > 0){
            return view('admin.pages.articles.gallery')
                ->with('gallery_images', $gallery_images)
                ->with('post', $post)
                ->with('user', $user);
        } else {
            return redirect('/admin/articles/posts');
        }
    }

    public function store(Request $request)
    {
        $data = array(
            "image"=>$request->file("gallery_image"),
            "article_id"=>$request->input("post_id"),
        );
        if(!empty($data)){
            $validate = Validator::make($data, [
                'image' => ['required', 'file', 'mimes:jpg,jpeg,png', 'max:10240'],
            ]);
            if($validate->fails()){
                return redirect('/admin/articles/posts') -> with('error-validation-image', '');
            }else{
                $route_image = $data["image"]->storeAs('uploads/img/articles', 'gallery_image_'.mt_rand(100,9999).'.'.$data["image"]->guessExtension(), 'public');
                $gallery = new Gallery();
                $gallery->image = $route_image;
                $gallery->article_id = $data['article_id'];
                $gallery->save();
                return redirect('/admin/articles/posts') -> with('ok-add', '');
            }
        }else{
            return redirect('/admin/articles/posts') -> with('error-validation', '');
        }
    }

    public function destroy($id, Request $request){
        $validate = Gallery::where("id", $id)->get();
        $article_id = $request->input("post_id");
        if(!empty($validate)){
            $disk = Storage::disk('public');
            if ($disk->exists($validate[0]['image'])) {
                $disk->delete($validate[0]['image']);
            }
            Gallery::where("id", $id)->delete();
            return redirect('/admin/articles/gallery/'.$article_id) -> with('ok-delete', '');
        } else {
            return redirect('/admin/articles/gallery/'.$article_id) -> with('no-delete', '');
        }
    }
}
