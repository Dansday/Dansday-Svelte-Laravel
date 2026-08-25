<?php

namespace App\Http\Controllers;

use App\Models\McpToken;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class McpController extends Controller
{
    public function index()
    {
        $user = User::find(1);

        return view('admin.pages.mcp')
            ->with('tokens', McpToken::orderByRaw('revoked_at IS NOT NULL')->orderByDesc('id')->get())
            ->with('user', $user);
    }

    public function store(Request $request)
    {
        $data = ['name' => $request->input('name')];

        $validate = Validator::make($data, [
            'name' => ['required', 'string', 'max:55'],
        ]);
        if ($validate->fails()) {
            return redirect('/admin/mcp')
                ->with('error-validation', '')
                ->with('error-modal', '')
                ->withErrors($validate)
                ->withInput();
        }

        [$token, $plain] = McpToken::mint(trim($data['name']));

        return redirect('/admin/mcp')
            ->with('ok-add', '')
            ->with('mcp-token', $plain)
            ->with('mcp-token-name', $token->name);
    }

    public function revoke($id)
    {
        $token = McpToken::find($id);
        if (! $token) {
            return redirect('/admin/mcp')->with('no-delete', '');
        }

        $token->revoke();

        return redirect('/admin/mcp')->with('ok-update', '');
    }

    public function destroy($id)
    {
        $token = McpToken::find($id);
        if (! $token) {
            return redirect('/admin/mcp')->with('no-delete', '');
        }

        $token->delete();

        return redirect('/admin/mcp')->with('ok-delete', '');
    }
}
