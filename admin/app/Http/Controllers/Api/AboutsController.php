<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;

class AboutsController extends Controller
{
    public function index()
    {
        $design_skills = DB::table('skill')->where('type', '=', 'design')->orderBy('order', 'asc')->get();
        $dev_skills = DB::table('skill')->where('type', '=', 'development')->orderBy('order', 'asc')->get();
        $edu_experiences = DB::table('experience')->where('type', '=', 'education')->orderBy('order', 'asc')->get();
        $emp_experiences = DB::table('experience')->where('type', '=', 'employment')->orderBy('order', 'asc')->get();
        $testimonials = DB::table('testimonial')->orderBy('order', 'asc')->get();
        $services = DB::table('service')->orderBy('order', 'asc')->get();
        return response()->json([
            'design_skills' => $design_skills,
            'dev_skills' => $dev_skills,
            'edu_experiences' => $edu_experiences,
            'emp_experiences' => $emp_experiences,
            'testimonials' => $testimonials,
            'services' => $services,
        ]);
    }
}
