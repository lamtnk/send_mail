<?php

namespace App\Http\Controllers;

use App\Jobs\SendBulkEmailsJob;
use App\Models\ClassObservation;
use App\Models\ClassObservationPoly;
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

        // Kiểm tra session `system_type` để chọn bảng
        $model = session('system_type') === 'cd' ? ClassObservationPoly::class : ClassObservation::class;

        // Lấy bản ghi từ bảng tương ứng theo id
        $record = $model::find($recordId);

        // Kiểm tra nếu bản ghi không tồn tại hoặc người dùng không phải là evaluator_teacher1
        // if (!$record || $record->evaluator_email1 !== $userEmail) {
        //     return redirect()->back()->with('error', 'Bạn không có quyền gửi email cho bản ghi này.');
        // }

        // Kiểm tra nếu các trường điểm không đầy đủ
        if (empty($record->score1) || empty($record->score2)) {
            return redirect()->back()->with('error', 'Bản ghi chưa đủ đầu điểm để gửi.');
        }

        // Định dạng lại trường date để chỉ lấy ngày
        $formattedDate = $record->date instanceof \Carbon\Carbon ? $record->date->format('Y-m-d') : $record->date;

        // Chuẩn bị dữ liệu email từ bản ghi
        $emailData = [
            'date' => $formattedDate,
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

        $mailTo = $this->ensureFeDomain($emailData['evaluated_teacher_code']);

        try {
            // Gửi email từ view
            Mail::send('emails.notification', $emailData, function ($message) use ($emailData, $record, $mailTo) {
                $message->to($mailTo)
                    ->cc('to-fpolyhpg@feedu.onmicrosoft.com')
                    ->subject('Thông báo dự giờ từ Bộ môn ' . $record->department);
            });

            // Cập nhật lại trường sent_at và send_by cho bản ghi sau khi gửi email thành công
            $record->update([
                'sent_at' => now(),
                'send_by' => Auth::id(),
            ]);

            return redirect()->back()->with('success', 'Gửi mail thành công');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Gửi mail thất bại: ' . $e->getMessage());
        }
    }




    public function dataDugio(Request $request)
    {
        $userEmail = auth()->user()->email;

        // Kiểm tra loại hệ từ session để chọn bảng dữ liệu phù hợp
        $model = session('system_type') === 'cd' ? ClassObservationPoly::query() : ClassObservation::query();

        // Áp dụng bộ lọc nếu có
        if ($request->filled('year')) {
            $model->whereYear('date', $request->input('year'));
        }
        if ($request->filled('semester')) {
            $model->where('semester', $request->input('semester'));
        }
        if ($request->filled('block')) {
            $model->where('block', $request->input('block'));
        }

        // Lọc theo evaluator_email1 để chỉ lấy bản ghi của người dùng đăng nhập
        $datas = $model->where(function ($query) use ($userEmail) {
            $query->where('evaluator_email1', $userEmail)
                ->orWhere('evaluator_email2', $userEmail);
        })->get();

        // Lấy danh sách năm học, học kỳ, và block cho dropdown từ bảng tương ứng
        $years = $model->selectRaw('YEAR(date) as year')->distinct()->pluck('year');
        $semesters = $model->select('semester')->distinct()->pluck('semester');
        $blocks = $model->select('block')->distinct()->pluck('block');

        return view('report.index', compact('datas', 'years', 'semesters', 'blocks'));
    }


    public function sendAll(Request $request)
    {
        $userEmail = Auth::user()->email;
        $userId = Auth::id();
        $model = session('system_type') === 'cd' ? ClassObservationPoly::class : ClassObservation::class;

        // Lấy bộ lọc từ request
        $year = $request->input('year');
        $semester = $request->input('semester');
        $block = $request->input('block');

        // Truy vấn dữ liệu từ bảng tương ứng dựa trên bộ lọc
        $query = $model::query()
            ->where(function ($query) use ($userEmail) {
                $query->where('evaluator_email1', $userEmail)
                    ->orWhere('evaluator_email2', $userEmail);
            })
            ->whereNotNull('score1')
            ->whereNotNull('score2')
            ->where('score1', '!=', '')
            ->where('score2', '!=', '')
            ->whereNull('sent_at');

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

        foreach ($unsentData as $data) {
            dispatch(new SendBulkEmailsJob($data, $userId));
        }

        return redirect()->route('datadugio')->with('success', 'Các email sẽ được gửi trong nền.');
    }

    private function ensureFeDomain($email)
    {
        // Kiểm tra nếu chuỗi đã chứa '@fe.edu.vn'
        if (strpos($email, '@fe.edu.vn') === false) {
            // Nếu chưa, thêm '@fe.edu.vn' vào cuối chuỗi
            $email .= '@fe.edu.vn';
        }

        return $email;
    }
}
