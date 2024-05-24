<?php
namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class RegisterController extends Controller
{
    public function showRegistrationForm()
    {
        return view('auth.register');
    }

    public function register(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:8|confirmed',
        ]);
    
        if (User::where('email', $request->email)->exists()) {
            logger()->info('Email already exists in the database.');
            Auth::logout();
            $request->session()->invalidate();
            return back()->withInput()->withErrors(['email' => 'The email address is already in use. Please use a different email address.']);
        }
    
        try {
            $user = User::create([
                'name' => $request->name,
                'email' => $request->email,
                'password' => Hash::make($request->password),
            ]);
    
            Auth::login($user);
            logger()->info('User registration and login successful.');
    
            return redirect()->route('books')->with('success', 'Registration successful. Welcome!');
        } catch (\Exception $e) {
            logger()->error('Registration failed: ' . $e->getMessage());
            // Logout and clear session here in case of an exception
            Auth::logout();
            $request->session()->invalidate();
            return back()->withInput()->withErrors(['email' => 'Registration error occurred.']);
        }
    }    
}
