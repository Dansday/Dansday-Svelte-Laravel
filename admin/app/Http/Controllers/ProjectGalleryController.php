<?php

namespace App\Http\Controllers;

use App\Models\ProjectGallery;
use App\Models\Project;
use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Storage;

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
                'image' => ['required', 'file', 'mimes:jpg,jpeg,png'],
            ]);
            if ($validate->fails()) {
                return redirect('/admin/projects/projects')->with('error-validation-image', '');
            } else {
                $route_image = $data["image"]->storeAs('uploads/img/projects', 'project_image_' . mt_rand(100, 9999) . '.' . $data["image"]->guessExtension(), 'public');
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
            $disk = Storage::disk('public');
            if ($disk->exists($validate[0]['image'])) {
                $disk->delete($validate[0]['image']);
            }
            ProjectGallery::where("id", $id)->delete();
            return redirect('/admin/projects/gallery/' . $project_id)->with('ok-delete', '');
        } else {
            return redirect('/admin/projects/gallery/' . $project_id)->with('no-delete', '');
        }
    }
}
