<?php

namespace App\Http\Controllers;

use App\Models\Project;
use App\Models\User;
use Illuminate\Support\Facades\File;
use App\Models\ProjectCategory;
use App\Models\ProjectGallery;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class ProjectController extends Controller
{

    public function index()
    {
        $user = User::find(1);
        $gallery = ProjectGallery::all();
        $categories = ProjectCategory::all();
        $projects = DB::table('project')
            ->orderBy('order', 'asc')
            ->get();
        return view('admin.pages.projects.projects')
            ->with('projects', $projects)
            ->with('categories', $categories)
            ->with('gallery', $gallery)
            ->with('user', $user);
    }

    public function create()
    {
        $user = User::find(1);
        $categories = ProjectCategory::all();
        $projects = Project::all();
        return view('admin.pages.projects.project')
            ->with('categories', $categories)
            ->with('projects', $projects)
            ->with('user', $user);
    }

    public function store(Request $request)
    {
        $data = [
            'enable' => $request->input('enable_project'),
            'title' => $request->input('title'),
            'category' => $request->input('category'),
            'images_code' => $request->input('images_code'),
            'description' => $request->input('description'),
            'short_desc' => $request->input('short_desc'),
            'image' => $request->file('image'),
            'order' => $request->input('order'),
        ];

        $validate = Validator::make($data, [
            'title' => ['string', 'max:55'],
            'short_desc' => ['string', 'max:110'],
            'description' => ['required'],
            'image' => ['required', 'file', 'mimes:jpg,jpeg,png'],
        ]);
        if ($validate->fails()) {
            return redirect('/admin/projects/project')
                ->with('error-validation', '')
                ->withErrors($validate)
                ->withInput();
        }

        $disk = Storage::disk('uploads');
        $route_image = $data['image']->storeAs('img/projects', 'project_image_' . mt_rand(10, 9999) . '.' . $data['image']->guessExtension(), 'uploads');

        $tempFiles = $disk->files('img/temp');
        foreach ($tempFiles as $tempPath) {
            $name = basename($tempPath);
            $destPath = 'img/projects/' . $name;
            if ($disk->exists($tempPath)) {
                $disk->put($destPath, $disk->get($tempPath));
                $disk->delete($tempPath);
            }
        }

        $project = new Project();
        $project->enable = ($data['enable'] == 'on') ? 1 : 0;
        $project->title = $data['title'];
        $project->short_desc = $data['short_desc'];
        $project->images_code = $data['images_code'];
        $project->description = str_replace([$disk->url('img/temp'), 'uploads/img/temp'], [$disk->url('img/projects'), 'uploads/img/projects'], $data['description']);
        $project->image = 'uploads/' . $route_image;
        $project->order = $data['order'];
        $project->category_id = $data['category'];
        $project->save();
        return redirect('/admin/projects/projects')->with('ok-add', '');
    }

    public function show($id)
    {
        $project = Project::find($id);
        $user = User::find(1);
        $categories = ProjectCategory::all();
        if ($project != null) {
            return view('admin.pages.projects.single')
                ->with('project', $project)
                ->with('categories', $categories)
                ->with('user', $user);
        } else {
            return redirect('/admin/projects/projects');
        }
    }

    public function update($id, Request $request)
    {
        $project = Project::find($id);
        $data = [
            'id' => $request->input('id'),
            'enable' => $request->input('enable_project'),
            'title' => $request->input('title'),
            'category' => $request->input('category'),
            'description' => $request->input('description'),
            'short_desc' => $request->input('short_desc'),
            'image' => $request->file('image'),
            'image_current' => $request->input('image_current'),
        ];

        $validate = Validator::make($data, [
            'title' => ['required', 'string', 'max:55'],
            'short_desc' => ['string', 'max:110'],
            'description' => ['required'],
        ]);
        if ($validate->fails()) {
            return redirect('/admin/projects/project/' . $data['id'])
                ->with('error-validation', '')
                ->withErrors($validate)
                ->withInput();
        }
        if ($data['image'] != '') {
            $validate2 = Validator::make($data, ['image' => ['file', 'mimes:jpg,jpeg,png']]);
            if ($validate2->fails()) {
                return redirect('/admin/projects/project/' . $data['id'])
                    ->with('error-validation', '')
                    ->withErrors($validate2)
                    ->withInput();
            }
        }

        $disk = Storage::disk('uploads');
        $route_image = $data['image_current'];
        if ($data['image'] != '') {
            if ($data['image_current'] != '' && uploads_path_safe_to_delete($data['image_current'])) {
                $p = uploads_path_for_disk($data['image_current']);
                if ($p !== '' && $disk->exists($p)) {
                    $disk->delete($p);
                }
            }
            $route_image = 'uploads/' . $data['image']->storeAs('img/projects', 'project_image_' . mt_rand(10, 9999) . '.' . $data['image']->guessExtension(), 'uploads');
        } elseif ($data['image_current'] == '' || $data['image_current'] === null) {
            $proj = Project::find($id);
            if ($proj && $proj->image != '' && uploads_path_safe_to_delete($proj->image)) {
                $p = uploads_path_for_disk($proj->image);
                if ($p !== '' && $disk->exists($p)) {
                    $disk->delete($p);
                }
            }
            $route_image = '';
        }

        $tempFiles = $disk->files('img/temp');
        foreach ($tempFiles as $tempPath) {
            $name = basename($tempPath);
            $destPath = 'img/projects/' . $name;
            if ($disk->exists($tempPath)) {
                $disk->put($destPath, $disk->get($tempPath));
                $disk->delete($tempPath);
            }
        }

        Project::where('id', $id)->update([
            'enable' => ($data['enable'] == 'on') ? 1 : 0,
            'title' => $data['title'],
            'short_desc' => $data['short_desc'],
            'description' => str_replace('uploads/img/temp', 'uploads/img/projects', $data['description']),
            'image' => $route_image,
            'category_id' => $data['category'],
        ]);
        return redirect('/admin/projects/projects')->with('ok-update', '');
    }

    public function orderUp($id)
    {
        $project_1 = Project::where("id", $id)->get();
        $project_2 = DB::table('project')->where('order', '=', $project_1[0]['order'] - 1)->get();
        $data_new_1 = array("order" => $project_2[0]->order);
        Project::where("id", $project_1[0]['id'])->update($data_new_1);
        $data_new_2 = array("order" => $project_1[0]['order']);
        Project::where("id", $project_2[0]->id)->update($data_new_2);
        return redirect('/admin/projects/projects')->with('ok-update', '');
    }

    public function orderDown($id)
    {
        $project_1 = Project::where("id", $id)->get();
        $project_2 = DB::table('project')->where('order', '=', $project_1[0]['order'] + 1)->get();
        $data_new_1 = array("order" => $project_2[0]->order);
        Project::where("id", $project_1[0]['id'])->update($data_new_1);
        $data_new_2 = array("order" => $project_1[0]['order']);
        Project::where("id", $project_2[0]->id)->update($data_new_2);
        return redirect('/admin/projects/projects')->with('ok-update', '');
    }

    public function destroy($id, Request $request)
    {
        $validate = Project::where("id", $id)->get();
        if (! empty($validate)) {
            $disk = Storage::disk('uploads');
            $dirs = ['img/projects', 'img/work'];
            foreach ($dirs as $dir) {
                if (! $disk->exists($dir)) {
                    continue;
                }
                $files = $disk->files($dir);
                foreach ($files as $file) {
                    if (str_contains($file, $validate[0]['images_code']) && $disk->exists($file)) {
                        $disk->delete($file);
                    }
                }
            }
            if (! empty($validate[0]['image']) && uploads_path_safe_to_delete($validate[0]['image'])) {
                $p = uploads_path_for_disk($validate[0]['image']);
                if ($p !== '' && $disk->exists($p)) {
                    $disk->delete($p);
                }
            }
            Project::where("id", $validate[0]['id'])->delete();
            $projects = DB::table('project')->orderBy('order', 'asc')->get();
            $i = 1;
            foreach ($projects as $project) {
                Project::where("id", $project->id)->update(['order' => $i]);
                $i++;
            }
            return redirect('/admin/projects/projects')->with('ok-delete', '');
        }
        return redirect('/admin/projects/projects')->with('no-delete', '');
    }
}
