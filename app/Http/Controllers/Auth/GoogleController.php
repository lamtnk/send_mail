<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Socialite;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;

class GoogleController extends Controller
{
    /**
     * Redirect to Google for authentication.
     *
     * @return \Illuminate\Http\Response
     */
    public function redirectToGoogle()
    {
        return Socialite::driver('google')->redirect();
    }

    /**
     * Handle Google callback and log in or register user.
     *
     * @return \Illuminate\Http\Response
     */
    public function handleGoogleCallback()
    {
        try {
            $googleUser = Socialite::driver('google')->stateless()->user();
            $user = User::where('email', $googleUser->getEmail())->first();

            if ($user) {
                // Đăng nhập nếu người dùng đã tồn tại
                Auth::login($user);
            } else {
                // Đăng ký tài khoản mới nếu người dùng chưa tồn tại
                $user = User::create([
                    'name' => $googleUser->getName(),
                    'email' => $googleUser->getEmail(),
                    'google_id' => $googleUser->getId(),
                    'password' => Hash::make(uniqid())
                ]);
                Auth::login($user);
            }

            return redirect()->route('index'); 
        } catch (\Exception $e) {
            Log::error('Error during Google login: ' . $e->getMessage(), [
                'exception' => $e,
            ]);
            return redirect('/login')->with('error', 'Có lỗi xảy ra khi đăng nhập với Google.');
        }
    }
}
