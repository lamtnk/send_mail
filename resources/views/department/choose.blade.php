<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Chọn Bộ Môn</title>
    <link href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            background: linear-gradient(135deg, #f3f4f6, #e5e7eb);
            height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            font-family: Arial, sans-serif;
        }

        .choose-department-container {
            background: #fff;
            padding: 2rem;
            border-radius: 10px;
            box-shadow: 0px 4px 15px rgba(0, 0, 0, 0.1);
            max-width: 400px;
            width: 100%;
            text-align: center;
        }

        .choose-department-container h2 {
            font-size: 24px;
            color: #333;
            margin-bottom: 1.5rem;
        }

        .btn-primary {
            background-color: #007bff;
            border-color: #007bff;
            width: 100%;
            padding: 0.75rem;
            font-size: 16px;
        }

        .btn-primary:hover {
            background-color: #0056b3;
        }

        .form-group label {
            color: #555;
            font-weight: 500;
        }

        .form-select {
            padding: 0.5rem;
            font-size: 16px;
        }
    </style>
</head>

<body>
    <div class="choose-department-container">
        <h2>Chọn Bộ Môn</h2>
        <form action="{{ route('department.save') }}" method="POST">
            @csrf
            <div class="form-group">
                <label for="department">Bộ môn của bạn</label>
                <select name="department" id="department" class="form-select" required>
                    <option value="" disabled selected>Chọn bộ môn</option>
                    @foreach ($departments as $key => $value)
                        <option value="{{ $key }}">{{ $value }}</option>
                    @endforeach
                </select>
            </div>
            <button type="submit" class="btn btn-primary mt-3">Lưu bộ môn</button>
        </form>
    </div>
</body>

</html>
