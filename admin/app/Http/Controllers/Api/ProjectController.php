<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Project;
use App\Models\ProjectCategory;
use Illuminate\Support\Facades\DB;

class ProjectController extends Controller
{
    public function index()
    {
        $projects = DB::table('project')->where('enable', '=', 1)->orderBy('order', 'asc')->get();
        $categories = ProjectCategory::all();
        return response()->json(['projects' => $projects, 'projects_categories' => $categories]);
    }

    public function show(int $id)
    {
        $project = Project::find($id);
        if (! $project) {
            return response()->json(['error' => 'Not found'], 404);
        }
        $category_project = ProjectCategory::find($project->category_id);
        $data = $project->toArray();
        $data['category_name'] = $category_project ? $category_project->name : null;
        return response()->json($data);
    }
}
