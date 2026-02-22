<?php

namespace App\Http\Controllers;

use App\Models\ProjectGallery;
use App\Models\Project;
use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Validator;

class ProjectGalleryController extends Controller
{

    public function index($id)
    {
        $gallery_images = ProjectGallery::where("project_id", $id)->get();
        $user = User::find(1);
        $project = Project::find($id);
        if (count($gallery_images) > 0) {
            return view('admin.pages.projects.gallery')
                ->with('gallery_images', $gallery_images)
                ->with('project', $project)
                ->with('user', $user);
        } else {
            return redirect('/admin/projects/projects');
        }
    }

    public function store(Request $request)
    {
        $data = array(
            "image" => $request->file("gallery_image"),
            "project_id" => $request->input("project_id"),
        );
        if (!empty($data)) {
            $validate = Validator::make($data, [
                'image' => ['required', 'file', 'mimes:jpg,jpeg,png', 'max:10240'],
            ]);
            if ($validate->fails()) {
                return redirect('/admin/projects/projects')->with('error-validation-image', '');
            } else {
                $directory = "uploads/img/projects";
                $dirFull = public_path($directory);
                File::ensureDirectoryExists($dirFull);
                $random = mt_rand(100, 9999);
                $route_image = $directory . "/project_image_" . $random . "." . $data["image"]->guessExtension();
                $data["image"]->move($dirFull, basename($route_image));
                $gallery = new ProjectGallery();
                $gallery->image = $route_image;
                $gallery->project_id = $data['project_id'];
                $gallery->save();
                return redirect('/admin/projects/projects')->with('ok-add', '');
            }
        } else {
            return redirect('/admin/projects/projects')->with('error-validation', '');
        }
    }

    public function destroy($id, Request $request)
    {
        $validate = ProjectGallery::where("id", $id)->get();
        $project_id = $request->input("project_id");
        if (!empty($validate)) {
            $imgPath = public_path($validate[0]['image']);
            if (File::exists($imgPath)) {
                unlink($imgPath);
            }
            ProjectGallery::where("id", $id)->delete();
            return redirect('/admin/projects/gallery/' . $project_id)->with('ok-delete', '');
        } else {
            return redirect('/admin/projects/gallery/' . $project_id)->with('no-delete', '');
        }
    }
}
