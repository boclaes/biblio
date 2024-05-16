<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;

class AccountController extends Controller
{
    public function showSettings()
    {
        return view('auth.settings');
    }

    public function updateName(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
        ]);

        $user = Auth::user();
        $user->name = $request->name;
        $user->save();

        return back()->with('success', 'Your name has been updated!');
    }

    public function updateEmail(Request $request)
    {
        $user = Auth::user();
    
        $request->validate([
            'email' => 'required|string|email|max:255|unique:users,email,' . $user->id,
        ]);
    
        if ($request->email === $user->email) {
            return back()->with('info', 'Email already in use');
        }
    
        $user->email = $request->email;
        $user->save();
    
        return back()->with('success', 'Your email has been updated!');
    }    

    public function updatePassword(Request $request)
    {
        $request->validate([
            'current_password' => 'required',
            'new_password' => 'required|string|min:6|confirmed',
        ]);
    
        $user = Auth::user();
    
        if (!Hash::check($request->current_password, $user->password)) {
            return back()->with('error', 'Your current password does not match our records.');
        }
    
        $user->password = Hash::make($request->new_password);
        $user->save();
    
        return back()->with('success', 'Your password has been updated!');
    }    

    public function deleteAccount()
    {
        DB::transaction(function () {
            $user = Auth::user();
            $user->delete(); // This triggers the cascading deletes defined in the User model's 'booted' method
        });
    
        return redirect('/')->with('success', 'Your account has been deleted!');
    } 
}
