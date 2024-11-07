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
            return false;
//            return response()->json(['error' => $e->getMessage()], 500);
        }
    }
    public function dataDugio()
    {
        $datas = $this->readGoogleSheet();
        // Lọc dữ liệu để lấy những cột cần thiết
        dd($datas);
        $filteredData = array_map(function ($item) {
            return [
                'email' => $item[9],        // Email của giảng viên
                'teacher_code' => $item[15], // Mã GV
                'teacher_name' => $item[16], // Tên GV
                'subject_code' => $item[0],  // Mã môn học
                'date' => $item[11],         // Ngày dự giờ
                'time' => $item[12],         // Ca dự giờ
                'location' => $item[13],     // Địa điểm
                'score' => $item[1]          // Điểm GV
            ];
        }, $datas);
        unset($filteredData[0]);
        return view('report.index', compact('filteredData'));
    }
}
