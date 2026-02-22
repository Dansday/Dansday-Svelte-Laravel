<?php

namespace App\Http\Controllers;

use App\Models\General;
use App\Models\User;
use Illuminate\Support\Facades\File;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class GeneralController extends Controller
{
    public function index()
    {
        $general = General::find(1);
        if (! $general) {
            return redirect()->route('login')->with('message', 'Initial data not found. Please run: php artisan db:seed');
        }
        $user = User::find(1);
        $social_icons = config('social_icons', []);
        return view('admin.pages.general')
            ->with('general', $general)
            ->with('social_icons', $social_icons)
            ->with('user', $user);
    }

    public function update(Request $request, General $general)
    {
        $general = General::find(1);
        $data = [
            'title'          => $request->input('title'),
            'description'    => $request->input('description'),
            'analytics_code' => $request->input('analytics_code'),
            'social_links'   => $request->input('social_links'),
            'image_favicon'  => $request->file('image_favicon'),
            'image_favicon_current' => $request->input('image_favicon_current'),
        ];
        $route_image_favicon = $data['image_favicon_current'];

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

        if (! empty($data['image_favicon'])) {
            $validate = Validator::make($data, [
                'image_favicon' => ['file', 'mimes:png', 'max:1024', 'dimensions:min_width=155,min_height=155'],
            ]);
            if ($validate->fails()) {
                return redirect('/admin/general')
                    ->with('error-validation', '')
                    ->withErrors($validate)
                    ->withInput();
            }
            $directory = 'uploads/img/general/favicon';
            $directoryFull = public_path($directory);
            if ($route_image_favicon != '') {
                foreach (glob($directoryFull . '/*') ?: [] as $file) {
                    if (File::exists($file)) {
                        unlink($file);
                    }
                }
            }
            if (! file_exists($directoryFull)) {
                mkdir($directoryFull, 0777, true);
            }
            $route_image_favicon = $directory . '/favicon.' . $data['image_favicon']->guessExtension();
            list($width, $height) = getimagesize($data['image_favicon']);
            $favicon_dimensions = ['96', '57', '72', '76', '114', '120', '144', '152'];
            $source = imagecreatefrompng($data['image_favicon']);
            foreach ($favicon_dimensions as $dimension) {
                $filename = ($dimension == '96')
                    ? 'favicon.' . $data['image_favicon']->guessExtension()
                    : 'apple-touch-icon-' . $dimension . 'x' . $dimension . '-precomposed.' . $data['image_favicon']->guessExtension();
                $destiny = imagecreatetruecolor((int) $dimension, (int) $dimension);
                imagealphablending($destiny, false);
                imagesavealpha($destiny, true);
                imagecopyresampled($destiny, $source, 0, 0, 0, 0, (int) $dimension, (int) $dimension, $width, $height);
                imagepng($destiny, $directoryFull . '/' . $filename);
            }
        }
        if (empty($data['image_favicon']) && empty($data['image_favicon_current']) && ! empty($general->image_favicon)) {
            foreach (glob(public_path('uploads/img/general/favicon') . '/*') ?: [] as $file) {
                if (File::exists($file)) {
                    unlink($file);
                }
            }
        }

        $data_new = [
            'title'          => $data['title'],
            'description'    => $data['description'],
            'analytics_code' => $data['analytics_code'],
            'image_favicon'  => $route_image_favicon,
            'social_links'   => $data['social_links'],
        ];
        General::where('id', 1)->update($data_new);
        return redirect('/admin/general')->with('ok-update', '');
    }
}
