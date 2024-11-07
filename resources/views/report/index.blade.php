<!-- resources/views/report/index.blade.php -->
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Báo Cáo Dự Giờ</title>
    <!-- Thêm Bootstrap CSS -->
    <link href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
<div class="container mt-5">
    <h2 class="mb-4">Báo Cáo Dự Giờ Bộ Môn CNTT</h2>
    <table class="table table-bordered">
        <thead class="thead-dark">
            <tr>
                <th>Email</th>
                <th>Mã GV</th>
                <th>Tên GV</th>
                <th>Mã Môn Học</th>
                <th>Ngày Dự Giờ</th>
                <th>Ca</th>
                <th>Địa Điểm</th>
                <th>Điểm</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($filteredData as $row)
                <tr>
                    <td>{{ $row['email'] }}</td>
                    <td>{{ $row['teacher_code'] }}</td>
                    <td>{{ $row['teacher_name'] }}</td>
                    <td>{{ $row['subject_code'] }}</td>
                    <td>{{ $row['date'] }}</td>
                    <td>{{ $row['time'] }}</td>
                    <td>{{ $row['location'] }}</td>
                    <td>{{ $row['score'] }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>
</div>

<!-- Thêm Bootstrap và jQuery JavaScript -->
<script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@4.5.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
