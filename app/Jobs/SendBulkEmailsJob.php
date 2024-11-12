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

    protected $data;
    protected $userId;

    public function __construct($data, $userId)
    {
        $this->data = $data;
        $this->userId = $userId;
    }

    public function handle()
    {
        $formattedDate = $this->data->date instanceof \Carbon\Carbon ? $this->data->date->format('Y-m-d') : $this->data->date;
        $emailData = [
            'date' => $formattedDate,
            'location' => $this->data->location,
            'subject_code' => $this->data->subject_code,
            'department' => $this->data->department,
            'section' => $this->data->section,
            'evaluated_teacher_code' => $this->data->evaluated_teacher_code,
            'evaluator_teacher1' => $this->data->evaluator_teacher1,
            'score1' => $this->data->score1,
            'evaluator_email1' => $this->data->evaluator_email1,
            'evaluator_teacher2' => $this->data->evaluator_teacher2 ?? 'N/A',
            'score2' => $this->data->score2 ?? 'N/A',
            'evaluator_email2' => $this->data->evaluator_email2 ?? 'N/A',
            'lesson_name' => $this->data->lesson_name,
            'advantages' => $this->data->advantages ?? 'N/A',
            'disadvantages' => $this->data->disadvantages ?? 'N/A',
            'conclusion' => $this->data->conclusion ?? 'N/A'
        ];

        try {
            // Gửi email từ view
            Mail::send('emails.notification', $emailData, function ($message) use ($emailData) {
                $message->to('thanghq12@fe.edu.vn')
                    ->cc('to-fpolyhpg@feedu.onmicrosoft.com')
                    ->subject('Thông báo dự giờ từ Bộ môn ' . $this->data->department);
            });

            // Cập nhật thời gian gửi mail trong bảng
            $this->data->update([
                'sent_at' => now(),
                'send_by' => $this->userId
            ]);

            Log::info("Gửi thành công email cho bản ghi ID {$this->data->id}.");
        } catch (\Exception $e) {
            Log::error("Lỗi khi gửi email cho bản ghi ID {$this->data->id}: " . $e->getMessage());
        }
    }
}
