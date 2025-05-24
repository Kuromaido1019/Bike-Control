<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Foundation\Auth\AuthenticatesUsers;
use Illuminate\Http\Request;

class LoginController extends Controller
{
    use AuthenticatesUsers;

    protected $redirectTo = '/home';

    protected function validateLogin(Request $request)
    {
        $request->validate([
            $this->username() => 'required|email|max:100',
            'password' => 'required|string|max:100',
        ]);
    }


    protected function credentials(Request $request)
    {
        return array_merge($request->only($this->username(), 'password'), []);
    }

    public function logout(Request $request)
    {
        $this->guard()->logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect('/login');
    }
}
