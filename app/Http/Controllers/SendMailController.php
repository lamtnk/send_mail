<?php

namespace App\Http\Controllers;

use Exception;
use Illuminate\Support\Facades\Mail;

class SendMailController extends Controller
{
    public function sendMail()
    {
        $data = [
            'subject' => 'Thông báo quan trọng từ ' . config('app.name'),
            'email' => 'lamtnk2@fe.edu.vn',
            'messageBody' => 'Bạn có một thông báo mới từ hệ thống.',
            'actionUrl' => url('/')
        ];
        try {
            Mail::send('emails.notification', $data, function ($message) use ($data) {
                $message->to($data['email'])
                    ->subject($data['subject']);
            });

            return redirect(route('index'))->with('success', 'Gửi mail thành công');
        } catch (Exception $e) {
            return redirect(route('index'))->with('error', 'Gửi mail thất bại: ' . $e->getMessage());

        }
    }
}
