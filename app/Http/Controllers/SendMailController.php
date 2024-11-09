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


    public function dataDugio(Request $request)
    {
        $userEmail = auth()->user()->email;
        $query = ClassObservation::query();

        // Lọc theo các điều kiện bộ lọc nếu có
        if ($request->filled('year')) {
            $query->whereYear('date', $request->input('year'));
        }
        if ($request->filled('semester')) {
            $query->where('semester', $request->input('semester'));
        }
        if ($request->filled('block')) {
            $query->where('block', $request->input('block'));
        }

        // Lọc theo evaluator_email1 để chỉ lấy bản ghi của người dùng đăng nhập
        $datas = $query->where('evaluator_email1', $userEmail)->get();

        // Lấy danh sách năm học, học kỳ, và block cho dropdown
        $years = ClassObservation::selectRaw('YEAR(date) as year')->distinct()->pluck('year');
        $blocks = ClassObservation::select('block')->distinct()->pluck('block');

        return view('report.index', compact('datas', 'years', 'blocks'));
    }

    public function sendAll(Request $request)
    {
        $userId = Auth::id();

        // Lấy bộ lọc từ request
        $year = $request->input('year');
        $semester = $request->input('semester');
        $block = $request->input('block');

        // Truy vấn dữ liệu từ bảng ClassObservation dựa trên bộ lọc
        $query = ClassObservation::query()
            ->where('evaluator_email1', auth()->user()->email) // Chỉ lấy bản ghi của người đang đăng nhập
            ->whereNull('sent_at'); // Chỉ lấy những bản ghi chưa được gửi

        if ($year) {
            $query->whereYear('date', $year);
        }

        if ($semester) {
            $query->where('semester', $semester);
        }

        if ($block) {
            $query->where('block', $block);
        }

        $unsentData = $query->get();

        $successCount = 0;
        $errorCount = 0;

        foreach ($unsentData as $data) {
            $emailData = [
                'date' => $data->date,
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
                    $message->to('lamtnk2@fpt.edu.vn')
                        ->subject('Thông báo dự giờ từ ' . config('app.name'));
                });

                // Cập nhật thời gian gửi mail trong bảng ClassObservation
                $data->update(['sent_at' => now()]);

                $successCount++;
            } catch (\Exception $e) {
                $errorCount++;
            }
        }

        // Thông báo kết quả
        return redirect()->route('datadugio')->with('success', "Đã gửi thành công $successCount email. $errorCount email gặp lỗi.");
    }

}
