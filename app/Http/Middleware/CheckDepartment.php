<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Support\Facades\Auth;

class CheckDepartment
{
    public function handle($request, Closure $next)
    {
        // Kiểm tra nếu người dùng đã đăng nhập và chưa chọn department
        if (Auth::check() && is_null(Auth::user()->department)) {
            return redirect()->route('department.choose');
        }

        return $next($request);
    }
}
