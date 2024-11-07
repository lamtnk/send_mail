<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Báo Cáo Dự Giờ</title>
    <link href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
</head>

<body>
    <div class="container-fluid mt-5">
        <h2 class="mb-4">Báo Cáo Dự Giờ</h2>
        @if (session('success'))
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                {{ session('success') }}
                <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
        @elseif (session('error'))
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                {{ session('error') }}
                <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
        @endif
        <div>
            <form action="{{ route('sendAll') }}" method="POST">
                @csrf
                <button type="submit" class="btn btn-success mb-3">Gửi tất cả (Những mail chưa gửi)</button>
            </form>

        </div>
        <table class="table table-bordered">
            <thead class="thead-dark">
                <tr>
                    <th>Ngày</th>
                    <th>Địa điểm</th>
                    <th>Mã Môn Học</th>
                    <th>Ca</th>
                    <th>Mã GV Được Dự Giờ</th>
                    <th>Giảng Viên Đánh Giá 1</th>
                    <th>Điểm 1</th>
                    <th>Email 1</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                @php
                    // Lấy danh sách mail đã gửi cho user đang đăng nhập
                    $sentMails = App\Models\SentMail::where('user_id', Auth::id())->get();
                    $sentRecords = $sentMails
                        ->map(function ($sentMail) {
                            return [
                                'date' => $sentMail->date,
                                'subject_code' => $sentMail->subject_code,
                                'section' => $sentMail->section,
                            ];
                        })
                        ->toArray();
                @endphp

                @foreach ($datas as $data)
                    @php
                        // Kiểm tra xem bản ghi này đã gửi hay chưa
                        $isSent = in_array(
                            [
                                'date' => $data['date'],
                                'subject_code' => $data['subject_code'],
                                'section' => $data['section'],
                            ],
                            $sentRecords,
                        );
                    @endphp

                    <tr>
                        <td>{{ $data['date'] }}</td>
                        <td>{{ $data['location'] }}</td>
                        <td>{{ $data['subject_code'] }}</td>
                        <td>{{ $data['section'] }}</td>
                        <td>{{ $data['evaluated_teacher_code'] }}</td>
                        <td>{{ $data['evaluator_teacher1'] }}</td>
                        <td>{{ $data['score1'] }}</td>
                        <td>{{ $data['evaluator_email1'] }}</td>
                        <td>
                            <!-- Nút Chi tiết -->
                            <button class="btn btn-info btn-sm" data-toggle="modal"
                                data-target="#detailsModal-{{ $loop->index }}">
                                Chi tiết
                            </button>

                            <!-- Nút Gửi hoặc Gửi lại -->
                            <form action="{{ route('sendMail') }}" method="POST" style="display:inline;">
                                @csrf
                                @foreach ($data as $key => $value)
                                    <input type="hidden" name="data[{{ $key }}]"
                                        value="{{ $value }}">
                                @endforeach
                                <button type="submit"
                                    class="btn {{ $isSent ? 'btn-secondary' : 'btn-primary' }} btn-sm">
                                    {{ $isSent ? 'Gửi lại' : 'Gửi' }}
                                </button>
                            </form>
                        </td>
                    </tr>

                    <!-- Modal chi tiết -->
                    <div class="modal fade" id="detailsModal-{{ $loop->index }}" tabindex="-1" role="dialog"
                        aria-labelledby="detailsModalLabel-{{ $loop->index }}" aria-hidden="true">
                        <div class="modal-dialog modal-lg" role="document">
                            <div class="modal-content">
                                <div class="modal-header">
                                    <h5 class="modal-title" id="detailsModalLabel-{{ $loop->index }}">Chi tiết Dự Giờ
                                    </h5>
                                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                        <span aria-hidden="true">&times;</span>
                                    </button>
                                </div>
                                <div class="modal-body">
                                    <p><strong>Ngày dự giờ:</strong> {{ $data['date'] }}</p>
                                    <p><strong>Địa điểm:</strong> {{ $data['location'] }}</p>
                                    <p><strong>Mã Môn Học:</strong> {{ $data['subject_code'] }}</p>
                                    <p><strong>Ca:</strong> {{ $data['section'] }}</p>
                                    <p><strong>Mã GV Được Dự Giờ:</strong> {{ $data['evaluated_teacher_code'] }}</p>
                                    <p><strong>Bộ Môn:</strong> {{ $data['department'] ?? 'N/A' }}</p>

                                    <h5 class="mt-4">Thông Tin Giảng Viên Đánh Giá</h5>
                                    <p><strong>Giảng Viên Đánh Giá 1:</strong> {{ $data['evaluator_teacher1'] }} -
                                        {{ $data['evaluator_email1'] }}</p>
                                    <p><strong>Điểm 1:</strong> {{ $data['score1'] }}</p>
                                    <p><strong>Giảng Viên Đánh Giá 2:</strong>
                                        {{ $data['evaluator_teacher2'] ?? 'N/A' }} -
                                        {{ $data['evaluator_email2'] ?? 'N/A' }}</p>
                                    <p><strong>Điểm 2:</strong> {{ $data['score2'] ?? 'N/A' }}</p>

                                    <h5 class="mt-4">Thông Tin Bài Giảng</h5>
                                    <p><strong>Tên Bài Giảng:</strong> {{ $data['lesson_name'] }}</p>
                                    <p><strong>Ưu điểm giờ giảng:</strong> {{ $data['advantages'] }}</p>
                                    <p><strong>Những điểm cần rút kinh nghiệm:</strong> {{ $data['disadvantages'] }}
                                    </p>
                                    <p><strong>Kết luận giờ giảng:</strong> {{ $data['conclusion'] }}</p>
                                </div>
                                <div class="modal-footer">
                                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Đóng</button>
                                </div>
                            </div>
                        </div>
                    </div>
                @endforeach
            </tbody>
        </table>
    </div>

    <!-- Scripts -->
    <script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.bundle.min.js"></script>
</body>

</html>
