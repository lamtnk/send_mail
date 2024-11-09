<?php
namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Laravel\Socialite\Facades\Socialite;

class GoogleController extends Controller
{
    // Redirect to Google for authentication
    public function redirectToGoogle(Request $request)
    {
        // Kiểm tra nếu `system_type` không được chọn
        if (!$request->has('system_type') || empty($request->input('system_type'))) {
            return redirect()->back()->with('error', 'Vui lòng chọn cơ sở trước khi tiếp tục.');
        }
        session(['system_type' => $request->input('system_type')]);
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
