<?php

namespace App\Http\Controllers;

use App\Models\ClassObservation;
use Illuminate\Http\Request;
use App\Services\GoogleSheetService; // Giả định bạn có một service để đồng bộ dữ liệu
use Exception;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class DataSyncController extends Controller
{
    protected $googleSheetService;

    public function __construct(GoogleSheetService $googleSheetService)
    {
        $this->googleSheetService = $googleSheetService;
    }

    // Hiển thị trang với nút đồng bộ
    public function index()
    {
        return view('sync.index');
    }

    // Đồng bộ dữ liệu khi nhấn nút
    public function sync(Request $request)
    {
        try {
            $spreadsheetId = '1rMoO03hR97WX0gFhqwrg8RHDFXeCeAFOTthP0HFzUrY'; // Cập nhật với ID của Google Sheets
            $this->readGoogleSheet($spreadsheetId); // Gọi hàm đồng bộ

            return redirect()->route('sync.index')->with('success', 'Đồng bộ dữ liệu thành công.');
        } catch (Exception $e) {
            Log::error('Error reading Google Sheet: ' . $e->getMessage());
            return redirect()->route('sync.index')->with('error', 'Đồng bộ dữ liệu thất bại: ' . $e->getMessage());
        }
    }

    public function readGoogleSheet($spreadsheetId = '1rMoO03hR97WX0gFhqwrg8RHDFXeCeAFOTthP0HFzUrY', $range = 'KQDG - FA24!A2:AV49')
    {
        try {
            // Đọc dữ liệu từ Google Sheets
            $values = $this->googleSheetService->readSheet($spreadsheetId, $range);
            unset($values[0]); // Xóa tiêu đề của dữ liệu
            // Kiểm tra nếu không có dữ liệu
            if (empty($values)) {
                return response()->json(['message' => 'Không tìm thấy dữ liệu'], 404);
            }

            // Xử lý dữ liệu: lọc và chuyển đổi định dạng
            $filteredData = $this->extractRelevantFields($values);
            $transformedData = $this->transformData($filteredData);

            // Lưu vào CSDL nếu không trùng lặp
            foreach ($transformedData as $data) {
                $exists = ClassObservation::where('date', $data['date'])
                    ->where('subject_code', $data['subject_code'])
                    ->where('section', $data['section'])
                    ->where('block', $data['block'])
                    ->where('semester', $data['semester'])
                    ->exists();

                if (!$exists) {
                    // Nếu chưa có bản ghi trong `class_observations`, lưu vào CSDL
                    ClassObservation::create([
                        'date' => $data['date'],
                        'location' => $data['location'],
                        'subject_code' => $data['subject_code'],
                        'department' => $data['department'],
                        'section' => $data['section'],
                        'evaluated_teacher_code' => $data['evaluated_teacher_code'],
                        'evaluator_teacher1' => $data['evaluator_teacher1'],
                        'score1' => $data['score1'],
                        'evaluator_email1' => $data['evaluator_email1'],
                        'evaluator_teacher2' => $data['evaluator_teacher2'] ?? null,
                        'score2' => $data['score2'] ?? null,
                        'evaluator_email2' => $data['evaluator_email2'] ?? null,
                        'lesson_name' => $data['lesson_name'],
                        'advantages' => $data['advantages'] ?? null,
                        'disadvantages' => $data['disadvantages'] ?? null,
                        'conclusion' => $data['conclusion'] ?? null,
                        'block' => $data['block'],
                        'semester' => $data['semester'],
                        'sent_at' => null, // Đặt null vì chưa gửi mail
                    ]);
                }
            }

            // Trả về dữ liệu đã được xử lý và lưu vào CSDL
            return response()->json(['message' => 'Dữ liệu đã được đọc và lưu thành công'], 200);
        } catch (\Exception $e) {
            // Xử lý lỗi nếu có và trả về thông báo lỗi
            Log::error('Error reading Google Sheet: ' . $e->getMessage());
            return response()->json(['error' => $e->getMessage()], 500);
        }
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
                'block' => $row[6] ?? null,        // Thêm Block
                'semester' => $row[7] ?? null,
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
            // Kiểm tra và chuyển đổi định dạng ngày
            $date = $row['date'];
            try {
                $date = Carbon::createFromFormat('d/m/Y', $date)->format('Y-m-d');
            } catch (\Exception $e) {
                // Xử lý lỗi nếu ngày không thể phân tích cú pháp
                continue; // Bỏ qua bản ghi này nếu ngày không hợp lệ
            }

            $key = $date . '-' . $row['subject_code'] . '-' . $row['section'] . '-' . $row['evaluated_teacher_code'];

            if (!isset($temp[$key])) {
                $temp[$key] = [];
            }

            $temp[$key][] = $row;
        }

        foreach ($temp as $key => $rows) {
            if (count($rows) == 2) {
                // Nếu có 2 người dự giờ, gộp dữ liệu
                $transformedData[] = [
                    'date' => $date,
                    'location' => $rows[0]['location'],
                    'subject_code' => $rows[0]['subject_code'],
                    'evaluated_teacher_code' => $rows[0]['evaluated_teacher_code'],
                    'department' => $rows[0]['department'],
                    'evaluator_teacher1' => $rows[0]['evaluator_teacher'],
                    'score1' => $rows[0]['score'],
                    'evaluator_email1' => $rows[0]['evaluator_email'],
                    'evaluator_teacher2' => $rows[1]['evaluator_teacher'],
                    'score2' => $rows[1]['score'],
                    'evaluator_email2' => $rows[1]['evaluator_email'],
                    'lesson_name' => $rows[0]['lesson_name'],
                    'section' => $rows[0]['section'],
                    'advantages' => $rows[0]['advantages'] . ', ' . $rows[1]['advantages'],
                    'disadvantages' => $rows[0]['disadvantages'] . ', ' . $rows[1]['disadvantages'],
                    'conclusion' => $rows[0]['conclusion'] . ', ' . $rows[1]['conclusion'],
                    'block' => $rows[0]['block'],
                    'semester' => $rows[0]['semester'],
                ];
            } elseif (count($rows) == 1) {
                // Nếu chỉ có 1 người dự giờ, giữ nguyên dữ liệu đó
                $transformedData[] = [
                    'date' => $date,
                    'location' => $rows[0]['location'],
                    'subject_code' => $rows[0]['subject_code'],
                    'evaluated_teacher_code' => $rows[0]['evaluated_teacher_code'],
                    'department' => $rows[0]['department'], // Thêm trường department
                    'evaluator_teacher1' => $rows[0]['evaluator_teacher'],
                    'score1' => $rows[0]['score'],
                    'evaluator_email1' => $rows[0]['evaluator_email'],
                    'lesson_name' => $rows[0]['lesson_name'],
                    'section' => $rows[0]['section'],
                    'advantages' => $rows[0]['advantages'] ?? 'N/A',
                    'disadvantages' => $rows[0]['disadvantages'] ?? 'N/A',
                    'conclusion' => $rows[0]['conclusion'] ?? 'N/A',
                    'block' => $rows[0]['block'],
                    'semester' => $rows[0]['semester'],
                ];
            }
        }

        return $transformedData;
    }
}
