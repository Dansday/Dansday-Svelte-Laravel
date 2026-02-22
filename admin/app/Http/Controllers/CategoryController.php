<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class CategoryController extends Controller
{
    public function index()
    {
        $categories = Category::all();
        $user = User::find(1);
        return view('admin.pages.articles.categories')
            ->with('categories', $categories)
            ->with('user', $user);
    }

    public function store(Request $request)
    {
        $data = array(
            "name"=>$request->input("name"),
        );
        $slug = strtolower(str_replace(" ", "-", $data["name"]));

        if(!empty($data)){
            $validate = Validator::make($data, [
                "name" => ['required', 'string', 'max:55'],
            ]);
            if($validate->fails()){
                return redirect('/admin/articles/categories') 
                    -> with('error-validation', '')
                    -> with('error-modal', '')
                    -> withErrors($validate)
                    -> withInput();
            }else{
                $category = new Category();
                $category->name = $data["name"];
                $category->slug = $slug;
                $category->save();    
                return redirect('/admin/articles/categories') -> with('ok-add', '');
            }
        } else {
            return redirect('/admin/articles/categories') -> with('error-validation', '');
        }
    }

    public function show($id)
    {
        $category = Category::find($id);
        $user = User::find(1);
        if($category != null){
            return view('admin.pages.articles.category')
                ->with('category', $category)
                ->with('user', $user);
        } else {
            return redirect('/admin/articles/categories');
        }
    }

    public function update($id, Request $request)
    {
        $data = array(
            "name"=>$request->input("name"),
            "slug"=>$request->input("slug"),
        );

        if(!empty($data)){
            $validate = Validator::make($data, [
                "name" => ['required', 'string', 'max:55'],
                "slug" => ['required', 'string', 'max:55'],
            ]);
            if($validate->fails()){
                return redirect('/admin/articles/categories/'.$id) 
                    -> with('error-validation', '')
                    -> withErrors($validate)
                    -> withInput();
            }else{
                $slug = strtolower(str_replace(" ", "-", $data["slug"]));
                $data_new = array(
                    "name"=>$data['name'],
                    "slug"=>$slug
                );
                Category::where("id", $id)->update($data_new);
                return redirect('/admin/articles/categories') -> with('ok-update', '');
            }
        } else {
            return redirect('/admin/articles/categories') -> with('error-validation', '');
        }
    }

    public function destroy($id, Category $category)
    {
        $validate = Category::where("id", $id)->get();
        if(!empty($validate)){
            $type = $validate[0]['type'];
            Category::where("id", $validate[0]['id'])->delete();
            return redirect('/admin/articles/categories') -> with('ok-delete', '');
        } else {
            return redirect('/admin/articles/categories') -> with('no-delete', '');
        }
    }

}
