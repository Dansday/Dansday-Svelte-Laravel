<?php

namespace App\Http\Controllers;

use App\Models\ProjectCategory;
use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Validator;

class ProjectCategoryController extends Controller
{

    public function index()
    {
        $categories = ProjectCategory::all();
        $user = User::find(1);
        return view('admin.pages.projects.categories')
            ->with('categories', $categories)
            ->with('user', $user);
    }

    public function store(Request $request)
    {
        $data = array("name" => $request->input("name"));
        if (!empty($data)) {
            $validate = Validator::make($data, [
                "name" => ['required', 'string', 'max:55'],
            ]);
            if ($validate->fails()) {
                return redirect('/admin/projects/categories')
                    ->with('error-validation', '')
                    ->with('error-modal', '')
                    ->withErrors($validate)
                    ->withInput();
            } else {
                $category = new ProjectCategory();
                $category->name = $data["name"];
                $category->save();
                return redirect('/admin/projects/categories')->with('ok-add', '');
            }
        } else {
            return redirect('/admin/projects/categories')->with('error-validation', '');
        }
    }

    public function update($id, Request $request)
    {
        $data = array("name" => $request->input("name"));
        if (!empty($data)) {
            $validate = Validator::make($data, [
                "name" => ['required', 'string', 'max:55'],
            ]);
            if ($validate->fails()) {
                return redirect('/admin/projects/categories/')
                    ->with('error-validation', '')
                    ->withErrors($validate)
                    ->withInput();
            } else {
                ProjectCategory::where("id", $id)->update(["name" => $data['name']]);
                return redirect('/admin/projects/categories')->with('ok-update', '');
            }
        } else {
            return redirect('/admin/projects/categories')->with('error-validation', '');
        }
    }

    public function destroy($id, ProjectCategory $projectCategory)
    {
        $validate = ProjectCategory::where("id", $id)->get();
        if (!empty($validate)) {
            ProjectCategory::where("id", $validate[0]['id'])->delete();
            return redirect('/admin/projects/categories')->with('ok-delete', '');
        } else {
            return redirect('/admin/projects/categories')->with('no-delete', '');
        }
    }
}
