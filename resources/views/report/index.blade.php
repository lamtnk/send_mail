<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Báo Cáo Dự Giờ</title>
    <link href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/1.10.21/css/jquery.dataTables.css">
    <style>
        body {
            font-family: Arial, sans-serif;
            background: linear-gradient(135deg, #f3f4f6, #e5e7eb);
            color: #333;
            margin: 0;
            padding: 0;
        }
    </style>
</head>

<body>
    <div class="container-fluid mt-2">
        <div class="d-flex justify-content-between align-items-center mb-3">
            <h2 class="mb-0">Báo Cáo Dự Giờ</h2>
            <div class="d-flex align-items-center">
                <img src="{{ Auth::user()->avatar }}" alt="Avatar" class="rounded-circle mr-2" width="40"
                    height="40">
                <div class="dropdown ml-2">
                    <button class="btn btn-outline-secondary dropdown-toggle btn-sm" type="button" id="userDropdown"
                        data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                        {{ Auth::user()->name }}
                    </button>
                    <div class="dropdown-menu dropdown-menu-right" aria-labelledby="userDropdown">
                        <a class="dropdown-item" href="{{ route('department.choose') }}">Cập nhật bộ môn</a>
                        <div class="dropdown-divider"></div>
                        <form action="{{ route('logout') }}" method="POST" style="display: inline;">
                            @csrf
                            <button type="submit" class="dropdown-item text-danger">Logout</button>
                        </form>
                    </div>
                </div>
            </div>

        </div>

        <!-- Thông báo và form bộ lọc -->
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

        <form id="filterForm" method="GET" action="{{ route('datadugio') }}" class="mb-4">
            <!-- Bộ lọc -->
            <div class="form-row">
                <div class="col-md-3">
                    <select name="year" class="form-control">
                        <option value="">Chọn Năm Học</option>
                        @foreach ($years as $year)
                            <option value="{{ $year }}" {{ request('year') == $year ? 'selected' : '' }}>
                                {{ $year }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3">
                    <select name="semester" class="form-control">
                        <option value="">Chọn Học Kỳ</option>
                        <option value="FA" {{ request('semester') == 'FA' ? 'selected' : '' }}>FA</option>
                        <option value="SP" {{ request('semester') == 'SP' ? 'selected' : '' }}>SP</option>
                        <option value="SU" {{ request('semester') == 'SU' ? 'selected' : '' }}>SU</option>
                    </select>
                </div>
                <div class="col-md-3">
                    <select name="block" class="form-control">
                        <option value="">Chọn Block</option>
                        @foreach ($blocks as $block)
                            <option value="{{ $block }}" {{ request('block') == $block ? 'selected' : '' }}>
                                {{ $block }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3 d-flex align-items-center">
                    <button type="submit" class="btn btn-primary mr-2">Apply Filter</button>
                    <a href="{{ route('datadugio') }}" class="btn btn-secondary">Clear Filter</a>
                </div>
            </div>
        </form>

        <!-- Nút gửi tất cả -->
        <button type="button" class="btn btn-success mb-3" id="sendAllBtn">Gửi tất cả (Những mail chưa gửi)</button>
        <button type="button" class="btn btn-warning mb-3" data-toggle="modal" data-target="#confirmSyncModal">
            Đồng bộ dữ liệu
        </button>
        <!-- Bảng dữ liệu báo cáo -->
        <table id="reportTable" class="table table-bordered">
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
                @foreach ($datas as $data)
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
                                <input type="hidden" name="record_id" value="{{ $data['id'] }}">
                                <button type="submit"
                                    class="btn {{ $data['sent_at'] ? 'btn-secondary' : 'btn-primary' }} btn-sm">
                                    {{ $data['sent_at'] ? 'Gửi lại' : 'Gửi' }}
                                </button>
                            </form>
                        </td>
                    </tr>
                    @if (!empty($data))
                        <!-- Modal chi tiết -->
                        <div class="modal fade" id="detailsModal-{{ $loop->index }}" tabindex="-1" role="dialog"
                            aria-labelledby="detailsModalLabel-{{ $loop->index }}" aria-hidden="true">
                            <div class="modal-dialog modal-lg" role="document">
                                <div class="modal-content">
                                    <div class="modal-header">
                                        <h5 class="modal-title" id="detailsModalLabel-{{ $loop->index }}">Chi tiết
                                            Dự Giờ</h5>
                                        <button type="button" class="close" data-dismiss="modal"
                                            aria-label="Close">
                                            <span aria-hidden="true">&times;</span>
                                        </button>
                                    </div>
                                    <div class="modal-body">
                                        <p><strong>Ngày dự giờ:</strong> {{ $data['date'] }}</p>
                                        <p><strong>Địa điểm:</strong> {{ $data['location'] }}</p>
                                        <p><strong>Mã Môn Học:</strong> {{ $data['subject_code'] }}</p>
                                        <p><strong>Ca:</strong> {{ $data['section'] }}</p>
                                        <p><strong>Mã GV Được Dự Giờ:</strong> {{ $data['evaluated_teacher_code'] }}
                                        </p>
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
                                        <p><strong>Những điểm cần rút kinh nghiệm:</strong>
                                            {{ $data['disadvantages'] }}</p>
                                        <p><strong>Kết luận giờ giảng:</strong> {{ $data['conclusion'] }}</p>
                                    </div>
                                    <div class="modal-footer">
                                        <button type="button" class="btn btn-secondary"
                                            data-dismiss="modal">Đóng</button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endif
                @endforeach
            </tbody>
        </table>
    </div>
    <!-- Modal xác nhận đồng bộ -->
    <div class="modal fade" id="confirmSyncModal" tabindex="-1" role="dialog"
        aria-labelledby="confirmSyncModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="confirmSyncModalLabel">Xác nhận đồng bộ dữ liệu</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <p>Bạn có chắc chắn muốn đồng bộ dữ liệu không? Hành động này sẽ ghi dữ liệu từ
                        Google Sheet vào hệ thống.</p>
                    <p><strong>Thông tin đồng bộ:</strong></p>
                    <ul>
                        <li><strong>Hệ:</strong>
                            {{ session('system_type') === 'cd' ? 'Cao đẳng' : 'Phổ thông Cao đẳng' }}
                        </li>
                        <li><strong>ID Google Sheet:</strong>
                            1Vix2IMBTbhUEQ0YajiZn09wUKGVSZAG3FSZeJAruJ-8</li>
                        <li><strong>Tên trang tính:</strong> KQDG - FA24</li>
                    </ul>
                </div>
                <div class="modal-footer">
                    <form action="{{ route('hardSync') }}" method="POST">
                        @csrf
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Hủy</button>
                        <button type="submit" class="btn btn-primary">Đồng ý đồng bộ</button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal xác nhận gửi tất cả -->
    <div class="modal fade" id="confirmSendAllModal" tabindex="-1" role="dialog"
        aria-labelledby="confirmSendAllModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="confirmSendAllModalLabel">Xác nhận gửi tất cả</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <p>Bạn có chắc chắn muốn gửi tất cả email cho:</p>
                    <ul>
                        <li><strong>Năm học:</strong> <span id="selectedYear">N/A</span></li>
                        <li><strong>Học kỳ:</strong> <span id="selectedSemester">N/A</span></li>
                        <li><strong>Block:</strong> <span id="selectedBlock">N/A</span></li>
                    </ul>
                    <p>Hãy kiểm tra kỹ bộ lọc để tránh gửi nhầm email.</p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Hủy</button>
                    <form action="{{ route('sendAll') }}" method="POST" id="sendAllForm">
                        @csrf
                        <input type="hidden" name="year" value="{{ request('year') }}">
                        <input type="hidden" name="semester" value="{{ request('semester') }}">
                        <input type="hidden" name="block" value="{{ request('block') }}">
                        <button type="submit" class="btn btn-primary">Xác nhận gửi</button>
                    </form>
                </div>
            </div>
        </div>
    </div>



    <!-- Scripts -->
    <script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.bundle.min.js"></script>
    <script type="text/javascript" charset="utf8" src="https://cdn.datatables.net/1.10.21/js/jquery.dataTables.js"></script>
    <script>
        $(document).ready(function() {
            $('#reportTable').DataTable({
                "language": {
                    "url": "//cdn.datatables.net/plug-ins/1.10.21/i18n/Vietnamese.json"
                }
            });
        });

        // Show modal xác nhận khi bấm Gửi tất cả
        $('#sendAllBtn').on('click', function() {
            // Lấy giá trị bộ lọc để hiển thị trong modal
            let year = $('[name="year"]').val() || 'Tất cả';
            let semester = $('[name="semester"]').val() || 'Tất cả';
            let block = $('[name="block"]').val() || 'Tất cả';

            $('#selectedYear').text(year);
            $('#selectedSemester').text(semester);
            $('#selectedBlock').text(block);

            $('#confirmSendAllModal').modal('show');
        });
    </script>
</body>

</html>
