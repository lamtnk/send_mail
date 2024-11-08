<?php

namespace App\Http\Controllers;

use App\Models\ClassObservation;
use App\Models\SentMail;
use App\Services\GoogleSheetService;
use Exception;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class SendMailController extends Controller
{
    protected $googleSheetService;
    public function __construct(GoogleSheetService $googleSheetService)
    {
        $this->googleSheetService = $googleSheetService;
    }
    public function sendMail(Request $request)
    {
        $recordId = $request->input('record_id');
        $userEmail = Auth::user()->email;

        // Lấy bản ghi từ bảng ClassObservation theo id
        $record = ClassObservation::find($recordId);

        // Kiểm tra nếu bản ghi không tồn tại hoặc người dùng không phải là evaluator_teacher1
        if (!$record || $record->evaluator_email1 !== $userEmail) {
            return redirect()->back()->with('error', 'Bạn không có quyền gửi email cho bản ghi này.');
        }

        // Chuẩn bị dữ liệu email từ bản ghi
        $emailData = [
            'date' => $record->date,
            'location' => $record->location,
            'subject_code' => $record->subject_code,
            'department' => $record->department,
            'section' => $record->section,
            'evaluated_teacher_code' => $record->evaluated_teacher_code,
            'evaluator_teacher1' => $record->evaluator_teacher1,
            'score1' => $record->score1,
            'evaluator_email1' => $record->evaluator_email1,
            'evaluator_teacher2' => $record->evaluator_teacher2 ?? 'N/A',
            'score2' => $record->score2 ?? 'N/A',
            'evaluator_email2' => $record->evaluator_email2 ?? 'N/A',
            'lesson_name' => $record->lesson_name,
            'advantages' => $record->advantages ?? 'N/A',
            'disadvantages' => $record->disadvantages ?? 'N/A',
            'conclusion' => $record->conclusion ?? 'N/A'
        ];

        try {
            // Gửi email từ view
            Mail::send('emails.notification', $emailData, function ($message) use ($emailData) {
                $message->to('lamtnk2@fpt.edu.vn')
                    ->subject('Thông báo dự giờ từ ' . config('app.name'));
            });

            // Cập nhật lại trường sent_at cho bản ghi sau khi gửi email thành công
            $record->update(['sent_at' => now()]);

            return redirect()->back()->with('success', 'Gửi mail thành công');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Gửi mail thất bại: ' . $e->getMessage());
        }
    }


    public function dataDugio()
    {
        $userEmail = auth()->user()->email;

        // Lấy dữ liệu từ bảng class_observations, chỉ lấy các bản ghi có evaluator_email1 trùng với email của người đăng nhập
        $datas = ClassObservation::where('evaluator_email1', $userEmail)->get();
        return view('report.index', compact('datas'));
    }

    public function sendAll()
    {
        $userId = Auth::id();

        // Đọc dữ liệu từ Google Sheet, sau đó lọc và chuyển đổi dữ liệu
        $rawData = $this->readGoogleSheet();
        $filteredData = $this->extractRelevantFields($rawData);
        $transformedData = $this->transformData($filteredData);
        $userEmail = auth()->user()->email;

        $transformedData = array_filter($transformedData, function ($data) use ($userEmail) {
            return isset($data['evaluator_email1']) && $data['evaluator_email1'] === $userEmail;
        });
        // Lọc các bản ghi chưa gửi cho người dùng hiện tại
        $unsentData = collect($transformedData)->filter(function ($data) use ($userId) {
            return !SentMail::where('user_id', $userId)
                ->where('date', $data['date'])
                ->where('subject_code', $data['subject_code'])
                ->where('section', $data['section'])
                ->exists();
        });

        $successCount = 0;
        $errorCount = 0;

        foreach ($unsentData as $data) {
            // Cấu trúc email từ mảng $data
            $emailData = [
                'date' => $data['date'],
                'location' => $data['location'],
                'subject_code' => $data['subject_code'],
                'department' => $data['department'],
                'section' => $data['section'],
                'evaluated_teacher_code' => $data['evaluated_teacher_code'],
                'evaluator_teacher1' => $data['evaluator_teacher1'],
                'score1' => $data['score1'],
                'evaluator_email1' => $data['evaluator_email1'],
                'evaluator_teacher2' => $data['evaluator_teacher2'] ?? 'N/A',
                'score2' => $data['score2'] ?? 'N/A',
                'evaluator_email2' => $data['evaluator_email2'] ?? 'N/A',
                'lesson_name' => $data['lesson_name'],
                'advantages' => $data['advantages'] ?? 'N/A',
                'disadvantages' => $data['disadvantages'] ?? 'N/A',
                'conclusion' => $data['conclusion'] ?? 'N/A'
            ];

            try {
                // Gửi email từ view
                Mail::send('emails.notification', $emailData, function ($message) use ($emailData) {
                    $message->to('lamtnk2@fpt.edu.vn')
                        ->subject('Thông báo dự giờ từ ' . config('app.name'));
                });

                // Sau khi gửi thành công, lưu vào bảng sent_mails
                SentMail::create([
                    'user_id' => $userId,
                    'date' => $data['date'],
                    'subject_code' => $data['subject_code'],
                    'section' => $data['section'],
                    'sent_at' => now(),
                ]);

                $successCount++;
            } catch (\Exception $e) {
                $errorCount++;
            }
        }

        // Thông báo kết quả
        return redirect()->route('datadugio')->with('success', "Đã gửi thành công $successCount email. $errorCount email gặp lỗi.");
    }
}
