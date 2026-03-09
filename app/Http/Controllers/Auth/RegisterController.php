<?php

/**
 * Auth\RegisterController — Not exposed publicly (admin creates accounts)
 * Kept for admin usage via UserController
 */

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;

class RegisterController extends Controller
{
    // Registration is handled by Admin\UserController
    // This class exists only to satisfy route declarations
    public function showRegistrationForm()
    {
        return redirect()->route('login');
    }

    public function register()
    {
        return redirect()->route('login');
    }
}
