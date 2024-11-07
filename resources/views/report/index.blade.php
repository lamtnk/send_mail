<!-- resources/views/report/index.blade.php -->
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Báo Cáo Dự Giờ</title>
    <link href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
<div class="container mt-5">
    <h2 class="mb-4">Báo Cáo Dự Giờ Bộ Môn CNTT</h2>
    <table class="table table-bordered">
        <thead class="thead-dark">
            <tr>
                <th>Ngày</th>
                <th>Địa điểm</th>
                <th>Mã Môn Học</th>
                <th>Mã GV Được Dự Giờ</th>
                <th>Giảng Viên Đánh Giá 1</th>
                <th>Điểm 1</th>
                <th>Email 1</th>
                <th>Giảng Viên Đánh Giá 2</th>
                <th>Điểm 2</th>
                <th>Email 2</th>
                <th>Tên Bài Giảng</th>
                <th>Section</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($datas as $data)
                <tr>
                    <td>{{ $data['date'] }}</td>
                    <td>{{ $data['location'] }}</td>
                    <td>{{ $data['subject_code'] }}</td>
                    <td>{{ $data['evaluated_teacher_code'] }}</td>
                    <td>{{ $data['evaluator_teacher1'] }}</td>
                    <td>{{ $data['score1'] }}</td>
                    <td>{{ $data['evaluator_email1'] }}</td>
                    <td>{{ $data['evaluator_teacher2'] ?? 'N/A' }}</td>
                    <td>{{ $data['score2'] ?? 'N/A' }}</td>
                    <td>{{ $data['evaluator_email2'] ?? 'N/A' }}</td>
                    <td>{{ $data['lesson_name'] }}</td>
                    <td>{{ $data['section'] }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>
</div>
</body>
</html>
