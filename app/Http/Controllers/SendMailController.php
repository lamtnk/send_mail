<?php

namespace App\Http\Controllers;

use App\Models\SentMail;
use App\Services\GoogleSheetService;
use Exception;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;

class SendMailController extends Controller
{
    protected $googleSheetService;
    public function __construct(GoogleSheetService $googleSheetService)
    {
        $this->googleSheetService = $googleSheetService;

        //thanghq12
    }
    public function sendMail(Request $request)
    {
        $data = $request->input('data');

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

            // Kiểm tra nếu mail đã được gửi trước đó
            $userId = Auth::id();
            $existingMail = SentMail::where('user_id', $userId)
                ->where('date', $data['date'])
                ->where('subject_code', $data['subject_code'])
                ->where('section', $data['section'])
                ->first();

            if (!$existingMail) {
                // Nếu chưa có bản ghi trong sent_mails, lưu lại
                SentMail::create([
                    'user_id' => $userId,
                    'date' => $data['date'],
                    'subject_code' => $data['subject_code'],
                    'section' => $data['section'],
                    'sent_at' => now()
                ]);
            }

            return redirect()->back()->with('success', 'Gửi mail thành công');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Gửi mail thất bại: ' . $e->getMessage());
        }
    }


    public function readGoogleSheet()
    {
        // ID của Google Sheet
        $spreadsheetId = '1rMoO03hR97WX0gFhqwrg8RHDFXeCeAFOTthP0HFzUrY';
        // Phạm vi muốn đọc
        $range = 'KQDG - FA24!A2:AV49';

        try {
            $values = $this->googleSheetService->readSheet($spreadsheetId, $range);
            return $values;
            if (empty($values)) {
                return false;
                //                return response()->json(['message' => 'Không thấy dư liệu'], 404);
            }

            //            return response()->json(['data' => $values], 200);
        } catch (\Exception $e) {
            // return false;
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }
    public function dataDugio()
    {
        $datas = $this->readGoogleSheet();
        // Lọc dữ liệu để lấy những cột cần thiết
        $datas = $this->extractRelevantFields($datas);
        $datas = $this->transformData($datas);

        $userEmail = auth()->user()->email;
        // Lọc dữ liệu để chỉ lấy các bản ghi mà evaluator_email1 trùng với email của người đăng nhập
        $datas = array_filter($datas, function ($data) use ($userEmail) {
            return isset($data['evaluator_email1']) && $data['evaluator_email1'] === $userEmail;
        });
        // dd($datas);
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


    private function extractRelevantFields($data)
    {
        $filteredData = array_map(function ($row) {
            return [
                'date' => $row[11],            // Ngày dự giờ
                'location' => $row[13],        // Địa điểm
                'subject_code' => $row[0],     // Mã môn học
                'evaluated_teacher_code' => $row[15], // Mã GV được dự giờ
                'evaluator_teacher' => $row[10],      // GV dự giờ
                'evaluator_email' => $row[9],      // Email GV dự giờ
                'score' => $row[1],            // Điểm đánh giá
                'lesson_name' => $row[17],     // Tên bài giảng
                'section' => $row[12],     // Ca giảng
                'advantages' => $row[45], // Thêm trường "Ưu điểm giờ giảng"
                'disadvantages' => $row[46], // Thêm trường "Những điểm rút kinh nghiệm"
                'conclusion' => $row[47], // Thêm trường "Đánh giá chung"
                'department' => $row[18],
            ];
        }, $data);

        return $filteredData;
    }

    private function transformData($data)
    {
        $transformedData = [];
        $temp = [];

        // Phân nhóm các bản ghi theo khóa: date, subject_code, section, evaluated_teacher_code
        foreach ($data as $row) {
            $key = $row['date'] . '-' . $row['subject_code'] . '-' . $row['section'] . '-' . $row['evaluated_teacher_code'];

            if (!isset($temp[$key])) {
                $temp[$key] = [];
            }
            $temp[$key][] = $row;
        }

        // Xử lý từng nhóm đã phân loại
        foreach ($temp as $key => $rows) {
            if (count($rows) == 2) {
                // Nếu có 2 người dự giờ, gộp dữ liệu
                $transformedData[] = [
                    'date' => $rows[0]['date'],
                    'location' => $rows[0]['location'],
                    'subject_code' => $rows[0]['subject_code'],
                    'department' => $rows[0]['department'],
                    'evaluated_teacher_code' => $rows[0]['evaluated_teacher_code'],
                    'evaluator_teacher1' => $rows[0]['evaluator_teacher'],
                    'score1' => $rows[0]['score'],
                    'evaluator_email1' => $rows[0]['evaluator_email'],
                    'evaluator_teacher2' => $rows[1]['evaluator_teacher'],
                    'score2' => $rows[1]['score'],
                    'evaluator_email2' => $rows[1]['evaluator_email'],
                    'lesson_name' => $rows[0]['lesson_name'], // Giả sử cả hai bản ghi có cùng tên bài giảng
                    'section' => $rows[0]['section'],        // Giả sử cả hai bản ghi có cùng section
                    'advantages' => $rows[0]['advantages'] . ', ' . $rows[1]['advantages'],
                    'disadvantages' => $rows[0]['disadvantages'] . ', ' . $rows[1]['disadvantages'],
                    'conclusion' => $rows[0]['conclusion'] . ', ' . $rows[1]['conclusion'],
                ];
            } elseif (count($rows) == 1) {
                // Nếu chỉ có 1 người dự giờ, giữ nguyên dữ liệu đó (nếu cần)
                $transformedData[] = [
                    'date' => $rows[0]['date'],
                    'location' => $rows[0]['location'],
                    'subject_code' => $rows[0]['subject_code'],
                    'department' => $rows[0]['department'],
                    'evaluated_teacher_code' => $rows[0]['evaluated_teacher_code'],
                    'evaluator_teacher1' => $rows[0]['evaluator_teacher'],
                    'score1' => $rows[0]['score'],
                    'evaluator_email1' => $rows[0]['evaluator_email'],
                    'lesson_name' => $rows[0]['lesson_name'],
                    'section' => $rows[0]['section'],
                    'advantages' => $rows[0]['advantages'],
                    'disadvantages' => $rows[0]['disadvantages'],
                    'conclusion' => $rows[0]['conclusion'],
                ];
            }
        }

        return $transformedData;
    }

    // Sử dụng hàm

}
