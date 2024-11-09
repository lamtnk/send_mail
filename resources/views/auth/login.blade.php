<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Đăng Nhập</title>
    <link href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            background-color: #f7f7f9;
            font-family: Arial, sans-serif;
        }

        .login-container {
            width: 100%;
            max-width: 400px;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
            background-color: #ffffff;
            text-align: center;
        }

        .login-container h2 {
            margin-bottom: 20px;
            font-weight: 700;
            color: #333;
        }

        .custom-select {
            width: 100%;
            height: 45px;
            margin-bottom: 20px;
            padding: 10px;
            border-radius: 5px;
            font-size: 16px;
            border: 1px solid #ced4da;
        }

        .divider {
            display: flex;
            align-items: center;
            text-align: center;
            color: #888;
            font-size: 14px;
            margin: 20px 0;
        }

        .divider::before,
        .divider::after {
            content: '';
            flex: 1;
            border-bottom: 1px solid #ddd;
        }

        .divider:not(:empty)::before {
            margin-right: .5em;
        }

        .divider:not(:empty)::after {
            margin-left: .5em;
        }

        .google-btn {
            width: 100%;
            background-color: #ff4d6d;
            color: #ffffff;
            font-size: 16px;
            font-weight: bold;
            border: none;
            padding: 10px;
            border-radius: 5px;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
        }

        .google-btn i {
            margin-right: 10px;
        }
    </style>
</head>

<body>

    <div class="login-container">
        <h2>Đăng Nhập</h2>
        <!-- Hiển thị thông báo lỗi -->
        @if (session('error'))
            <div class="alert alert-danger">
                {{ session('error') }}
            </div>
        @endif
        <!-- Dropdown chọn cơ sở -->
        <form id="loginForm" action="{{ route('auth.google') }}" method="GET">
            <select class="custom-select" id="system_type" name="system_type" required>
                <option selected disabled>Chọn cơ sở</option>
                <option value="pt">Phổ thông Cao đẳng</option>
                <option value="cd">Cao đẳng</option>
            </select>

            <!-- Divider -->
            <div class="divider">SOCIAL</div>

            <!-- Nút đăng nhập Google -->
            <button type="submit" class="google-btn">
                <i class="fab fa-google"></i> Google
            </button>
        </form>
    </div>

    <!-- Thêm link Font Awesome cho icon Google -->
    <script src="https://kit.fontawesome.com/a076d05399.js" crossorigin="anonymous"></script>
</body>

</html>
