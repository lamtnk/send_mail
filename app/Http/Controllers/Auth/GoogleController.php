<?php
namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Laravel\Socialite\Facades\Socialite;

class GoogleController extends Controller
{
    // Redirect to Google for authentication
    public function redirectToGoogle()
    {
        return Socialite::driver('google')->redirect();
    }

    // Handle callback from Google
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
                    'password' => Hash::make(uniqid()),
                    'avatar' => $googleUser->getAvatar(),
                ]);
                Auth::login($user);
            }

            return redirect()->route('datadugio')->with('success', 'Đăng nhập thành công');
        } catch (\Exception $e) {
            Log::error('Error during Google login: ' . $e->getMessage(), [
                'exception' => $e,
            ]);
            return redirect()->route('login')->with('error', 'Đăng nhập thất bại');
        }
    }

    // Logout user
    public function logout()
    {
        Auth::logout();
        return redirect()->route('login')->with('success', 'Đã đăng xuất');
    }
}
