<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Foundation\Auth\RegistersUsers;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

class RegisterController extends Controller
{
    use RegistersUsers;

    protected $redirectTo = '/admin/home';

    public function __construct()
    {
        $this->middleware('guest');
        $this->middleware('throttle:5,1')->only('register');
    }

    public function showRegistrationForm()
    {
        if (User::count() > 0) {
            return redirect()->route('login')->with('message', 'Only one admin account can exist. Please log in.');
        }
        return view('auth.register');
    }

    public function register(Request $request)
    {
        if (User::count() > 0) {
            return redirect()->route('login')->with('message', 'Only one admin account can exist. Please log in.');
        }
        $this->validator($request->all())->validate();
        event(new Registered($user = $this->create($request->all())));
        $this->guard()->login($user);
        $this->registered($request, $user);
        return $request->wantsJson()
            ? new JsonResponse([], 201)
            : redirect($this->redirectPath());
    }

    protected function validator(array $data)
    {
        return Validator::make($data, [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ]);
    }

    protected function create(array $data)
    {
        return User::create([
            'name' => $data['name'],
            'email' => $data['email'],
            'password' => Hash::make($data['password']),
        ]);
    }
}
