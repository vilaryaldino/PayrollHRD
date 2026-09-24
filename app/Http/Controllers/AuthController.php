<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{
    private function hrdAuthCredentials()
    {
        return [
            'username' => env('HRD_LOGIN_USERNAME', 'admin'),
            'password' => env('HRD_LOGIN_PASSWORD', 'admin123'),
        ];
    }

    public function showLogin()
    {
        if (session()->has('hrd_authenticated')) {
            return redirect()->route('dashboard');
        }
        return view('auth.login');
    }

    public function login(Request $request)
    {
        $credentials = $this->hrdAuthCredentials();
        
        $username = $request->input('username');
        $password = $request->input('password');

        if ($username === $credentials['username'] && $password === $credentials['password']) {
            session(['hrd_authenticated' => true]);
            session(['hrd_username' => $credentials['username']]);
            return redirect()->route('dashboard');
        }

        return redirect()->route('login')->with('login_error', 'Username atau password yang dimasukkan salah.');
    }

    public function logout(Request $request)
    {
        $request->session()->flush();
        return redirect()->route('login');
    }
}
