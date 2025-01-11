<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use App\Models\User;

class AdminAuthController extends Controller
{
    /**
     * Login for admin.
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function login(Request $request)
    {
        $credentials = $request->only('email', 'password');

        // Cek apakah email dan password valid
        if (Auth::guard('web')->attempt($credentials)) {
            $user = Auth::guard('web')->user();

            // Jika user adalah admin, arahkan ke dashboard admin
            if ($user->isAdmin()) {
                return redirect()->route('admin.dashboard'); // Redirect ke halaman admin dashboard
            }
        }

        // Jika login gagal atau bukan admin
        return redirect()->route('admin.login')->withErrors('Invalid credentials or you are not an admin');
    }

    /**
     * Register a new admin user.
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function register(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users,email',
            'password' => 'required|min:6|confirmed', // Ensure confirmation of password
        ]);

        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()], 400);
        }

        // Create a new user with the role as admin
        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'role' => 'admin', // Assigning the admin role to the user
        ]);

        // Log the user in after registration
        Auth::guard('web')->login($user);

        // Redirect to admin dashboard
        return redirect()->route('admin.dashboard');
    }
}
