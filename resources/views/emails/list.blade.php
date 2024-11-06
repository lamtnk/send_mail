<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Bảng dữ liệu</title>
    <!-- Nhúng Bootstrap từ CDN -->
    <link href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>

<div class="container mt-5">
    <h2 class="text-center mb-4">Bảng dữ liệu mẫu</h2>
    <table class="table table-bordered">
        <thead class="thead-dark">
{{--        <tr>--}}
{{--            <th>Cột 1</th>--}}
{{--            <th>Cột 2</th>--}}
{{--            <th>Cột 3</th>--}}
{{--            <th>Cột 4</th>--}}
{{--            <th>Cột 5</th>--}}
{{--        </tr>--}}
        </thead>
        <tbody>
        @foreach($datas as $key => $dt)
        <tr class="thead-dark">
            <td>{{ $dt[0] }}</td>
            <td>{{ $dt[1] }}</td>
            <td>{{ $dt[2] }}</td>
            <td>{{ $dt[3] }}</td>
            <td>{{ $dt[4] }}</td>
            <td>{{ $dt[5] }}</td>
            <td>{{ $dt[6] }}</td>
            <td>{{ $dt[7] }}</td>
            <td>{{ $dt[8] }}</td>
            <td>{{ $dt[9] }}</td>
            <td>{{ $dt[10] }}</td>
        </tr>
        @endforeach
        </tbody>
    </table>
</div>

<!-- Nhúng Bootstrap JavaScript (tùy chọn nếu cần) -->
<script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.9.1/dist/umd/popper.min.js"></script>
<script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>

</body>
</html>
