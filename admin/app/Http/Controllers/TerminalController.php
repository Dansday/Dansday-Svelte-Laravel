<?php

namespace App\Http\Controllers;

use App\Models\General;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class TerminalController extends Controller
{
    public function index()
    {
        $general = General::find(1);
        if (! $general) {
            abort(500, 'Initial data not found.');
        }
        $user = User::find(1);
        return view('admin.pages.terminal')
            ->with('general', $general)
            ->with('user', $user);
    }

    public function update(Request $request)
    {
        $validate = Validator::make($request->all(), [
            'terminal_username' => ['nullable', 'string', 'max:100'],
        ]);
        if ($validate->fails()) {
            return redirect('/admin/terminal')
                ->with('error-validation', '')
                ->withErrors($validate)
                ->withInput();
        }

        General::where('id', 1)->update([
            'terminal_username' => $request->input('terminal_username') ? trim($request->input('terminal_username')) : null,
        ]);
        return redirect('/admin/terminal')->with('ok-update', '');
    }
}
