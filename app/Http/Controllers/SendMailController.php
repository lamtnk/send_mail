<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Mail\SendTestMail;
use Illuminate\Support\Facades\Mail;

class SendMailController extends Controller
{
    public function sendMail()
    {
        try {
            // Đưa nội dung email zô đây
            $details = [
                'title' => 'Test tiêu đề Email',
                'body' => 'Test body email'
            ];

            // Gửi email
            Mail::to('lamtnk2@fpt.edu.vn')->send(new SendTestMail($details));

            // Nếu thành công, chuyển hướng với thông báo success
            return redirect(route('index'))->with('success', 'Gửi mail thành công');
        } catch (\Exception $e) {
            // Nếu có lỗi xảy ra, bắt ngoại lệ và trả về lỗi
            return redirect(route('index'))->with('error', 'Gửi mail thất bại: ' . $e->getMessage());
        }
    }

}
