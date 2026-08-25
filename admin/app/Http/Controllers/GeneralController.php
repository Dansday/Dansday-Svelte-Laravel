<?php

namespace App\Http\Controllers;

use App\Models\General;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

class GeneralController extends Controller
{
    public function index()
    {
        $general = General::find(1);
        if (! $general) {
            if (! auth()->check()) {
                return redirect()->route('login')->with('message', 'Initial data not found. Please run: php artisan db:seed');
            }
            Log::error('Initial data not found (General). Please run: php artisan db:seed', [
                'user_id' => auth()->id(),
                'path' => request()->path(),
            ]);
            abort(500, 'Initial data not found. Please run: php artisan db:seed');
        }
        $user = User::find(1);
        return view('admin.pages.general')
            ->with('general', $general)
            ->with('user', $user);
    }

    public function update(Request $request, General $general)
    {
        $data = [
            'title'          => $request->input('title'),
            'description'    => $request->input('description'),
            'analytics_code' => $request->input('analytics_code'),
            'social_links'   => $request->input('social_links'),
        ];

        $validate = Validator::make($data, [
            'title'          => ['string', 'max:55'],
            'description'    => ['string', 'max:255'],
            'analytics_code' => ['nullable', 'string', 'max:55'],
            'social_links'   => ['string'],
        ]);
        if ($validate->fails()) {
            return redirect('/admin/general')
                ->with('error-validation', '')
                ->withErrors($validate)
                ->withInput();
        }

        $data_new = [
            'title'          => $data['title'],
            'description'    => $data['description'],
            'analytics_code' => $data['analytics_code'],
            'social_links'   => $data['social_links'],
        ];
        General::where('id', 1)->update($data_new);
        return redirect('/admin/general')->with('ok-update', '');
    }
}
