<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class SendBulkEmailsJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $unsentData;
    protected $userEmail;

    public function __construct($unsentData, $userEmail)
    {
        $this->unsentData = $unsentData;
        $this->userEmail = $userEmail;
    }

    public function handle()
    {
        $successCount = 0;
        $errorCount = 0;

        foreach ($this->unsentData as $data) {
            $formattedDate = $data->date instanceof \Carbon\Carbon ? $data->date->format('Y-m-d') : $data->date;
            $emailData = [
                'date' => $formattedDate,
                'location' => $data->location,
                'subject_code' => $data->subject_code,
                'department' => $data->department,
                'section' => $data->section,
                'evaluated_teacher_code' => $data->evaluated_teacher_code,
                'evaluator_teacher1' => $data->evaluator_teacher1,
                'score1' => $data->score1,
                'evaluator_email1' => $data->evaluator_email1,
                'evaluator_teacher2' => $data->evaluator_teacher2 ?? 'N/A',
                'score2' => $data->score2 ?? 'N/A',
                'evaluator_email2' => $data->evaluator_email2 ?? 'N/A',
                'lesson_name' => $data->lesson_name,
                'advantages' => $data->advantages ?? 'N/A',
                'disadvantages' => $data->disadvantages ?? 'N/A',
                'conclusion' => $data->conclusion ?? 'N/A'
            ];

            try {
                // Gửi email từ view
                Mail::send('emails.notification', $emailData, function ($message) use ($emailData) {
                    $message->to('thanghq12@fe.edu.vn')
                        ->cc('task-bmcn-ptcd-hpg@feedu.onmicrosoft.com')
                        ->subject('Thông báo dự giờ từ ' . config('app.name'));
                });

                // Cập nhật thời gian gửi mail trong bảng
                $data->update(['sent_at' => now()]);

                $successCount++;
            } catch (\Exception $e) {
                $errorCount++;
            }
        }

        // Log kết quả gửi email (nếu cần thiết)
        Log::info("Gửi thành công $successCount email, $errorCount email gặp lỗi.");
    }
}
