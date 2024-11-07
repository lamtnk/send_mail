<?php

namespace App\Http\Controllers;

use App\Services\GoogleSheetService;
use Exception;
use Illuminate\Support\Facades\Mail;

class SendMailController extends Controller
{
    protected $googleSheetService;
    public function __construct(GoogleSheetService $googleSheetService)
    {
        $this->googleSheetService = $googleSheetService;

        //thanghq12
    }
    public function sendMail()
    {
        $data = [
            'subject' => 'Thông báo quan trọng từ ' . config('app.name'),
            'email' => 'thanghq12@fe.edu.vn',
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

    public function readGoogleSheet()
    {
        // ID của Google Sheet
        $spreadsheetId = '1rMoO03hR97WX0gFhqwrg8RHDFXeCeAFOTthP0HFzUrY';
        // Phạm vi muốn đọc
        $range = 'KQDG - FA24!A2:AV49';

        try {
            $values = $this->googleSheetService->readSheet($spreadsheetId, $range);
            return  $values;
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
        dd($datas);
        return view('report.index', compact('datas'));
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
                    'evaluated_teacher_code' => $rows[0]['evaluated_teacher_code'],
                    'evaluator_teacher1' => $rows[0]['evaluator_teacher'],
                    'score1' => $rows[0]['score'],
                    'evaluator_email1' => $rows[0]['evaluator_email'],
                    'evaluator_teacher2' => $rows[1]['evaluator_teacher'],
                    'score2' => $rows[1]['score'],
                    'evaluator_email2' => $rows[1]['evaluator_email'],
                    'lesson_name' => $rows[0]['lesson_name'], // Giả sử cả hai bản ghi có cùng tên bài giảng
                    'section' => $rows[0]['section'],        // Giả sử cả hai bản ghi có cùng section
                ];
            } elseif (count($rows) == 1) {
                // Nếu chỉ có 1 người dự giờ, giữ nguyên dữ liệu đó (nếu cần)
                $transformedData[] = [
                    'date' => $rows[0]['date'],
                    'location' => $rows[0]['location'],
                    'subject_code' => $rows[0]['subject_code'],
                    'evaluated_teacher_code' => $rows[0]['evaluated_teacher_code'],
                    'evaluator_teacher1' => $rows[0]['evaluator_teacher'],
                    'score1' => $rows[0]['score'],
                    'evaluator_email1' => $rows[0]['evaluator_email'],
                    'lesson_name' => $rows[0]['lesson_name'],
                    'section' => $rows[0]['section'],
                ];
            }
        }

        return $transformedData;
    }

    // Sử dụng hàm

}
