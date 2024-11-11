<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class DepartmentController extends Controller
{
    public function showChooseForm()
    {
        $userType = session()->get('system_type');
        $departments = $userType === 'cd'
            ? [
                4 => 'CNTT - UDPM',
                5 => 'Kinh tế',
                6 => 'Cơ bản',
                7 => 'Điện',
                8 => 'Thiết kế đồ họa'
            ]
            : [
                1 => 'Văn hóa phổ thông',
                2 => 'Chuyên ngành',
                3 => 'Cơ bản'
            ];

        return view('department.choose', compact('departments'));
    }

    public function saveDepartment(Request $request)
    {
        $request->validate([
            'department' => 'required|integer',
        ]);

        // Lưu bộ môn cho người dùng
        $user = Auth::user();
        $user->department = $request->input('department');
        $user->save();

        return redirect()->route('datadugio')->with('success', 'Thông tin bộ môn của bạn đã được lưu thành công.');
    }
}
