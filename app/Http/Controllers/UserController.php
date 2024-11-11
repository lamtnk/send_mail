<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class UserController extends Controller
{
    // Hiển thị form cập nhật bộ môn
    public function editDepartment()
    {
        // Lấy hệ từ session để hiển thị danh sách bộ môn tương ứng
        $systemType = session('system_type');

        // Danh sách bộ môn theo hệ đào tạo
        $departments = $systemType === 'cd'
            ? ['CNTT - UDPM', 'Kinh tế', 'Cơ bản', 'Điện', 'Thiết kế đồ họa']
            : ['Văn hóa phổ thông', 'Chuyên ngành', 'Cơ bản'];

        return view('user.edit_department', compact('departments'));
    }

    // Cập nhật bộ môn cho người dùng
    public function updateDepartment(Request $request)
    {
        $request->validate([
            'department' => 'required'
        ]);

        $user = auth()->user();
        $user->department = $request->input('department');
        $user->save();

        return redirect()->route('datadugio')->with('success', 'Cập nhật bộ môn thành công.');
    }
}
