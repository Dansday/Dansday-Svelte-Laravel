<?php

namespace App\Http\Controllers;

use App\Models\Home;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class PageHomeController extends Controller
{
    public function index()
    {
        $home = Home::find(1);
        if (! $home) {
            return redirect()->route('login')->with('message', 'Initial data not found. Please run: php artisan db:seed');
        }
        $user = User::find(1);
        return view('admin.pages.home')
            ->with('home', $home)
            ->with('user', $user);
    }

    public function update(Request $request)
    {
        $home = Home::find(1);
        $data = [
            'title'       => $request->input('title'),
            'description' => $request->input('description'),
        ];

        $validate = Validator::make($data, [
            'title'       => ['required', 'string', 'max:75'],
            'description' => ['nullable', 'string', 'max:5000'],
        ]);
        if ($validate->fails()) {
            return redirect('/admin/home')
                ->with('error-validation', '')
                ->withErrors($validate)
                ->withInput();
        }

        Home::where('id', 1)->update([
            'title'       => $data['title'],
            'description' => $data['description'],
        ]);
        return redirect('/admin/home')->with('ok-update', '');
    }
}
