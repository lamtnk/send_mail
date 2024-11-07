<!-- resources/views/emails/report.blade.php -->
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Báo Cáo Dự Giờ</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f4f4f7;
            color: #333;
            margin: 0;
            padding: 0;
        }
        .email-wrapper {
            width: 100%;
            padding: 20px;
            background-color: #f4f4f7;
        }
        .email-content {
            max-width: 600px;
            margin: 0 auto;
            background-color: #ffffff;
            border-radius: 8px;
            overflow: hidden;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
        }
        .email-header {
            background-color: #007bff;
            color: #ffffff;
            text-align: center;
            padding: 20px;
        }
        .email-header h1 {
            margin: 0;
            font-size: 24px;
        }
        .email-body {
            padding: 20px;
            color: #333;
        }
        .email-body p {
            font-size: 16px;
            line-height: 1.6;
            margin: 10px 0;
        }
        .highlight {
            font-weight: bold;
        }
        .email-footer {
            text-align: center;
            padding: 10px;
            font-size: 14px;
            color: #777;
        }
    </style>
</head>
<body>
    <div class="email-wrapper">
        <div class="email-content">
            <!-- Header -->
            <div class="email-header">
                <h1 style="text-transform:uppercase">BÁO CÁO DỰ GIỜ BỘ MÔN {{ $department }}</h1>
            </div>

            <!-- Body -->
            <div class="email-body">
                <p><span class="highlight">Ngày dự giờ:</span> {{ $date }}</p>
                <p><span class="highlight">Ca:</span> {{ $section }}</p>
                <p><span class="highlight">Địa điểm:</span> {{ $location }}</p>
                <p><span class="highlight">Mã Môn + Tên Môn:</span> {{ $subject_code }}</p>
                <p><span class="highlight">Mã GV được dự giờ:</span> {{ $evaluated_teacher_code }}</p>

                <p><span class="highlight">GV1:</span> {{ $evaluator_teacher1 }} - {{ $evaluator_email1 }}</p>
                <p><span class="highlight">GV2:</span> {{ $evaluator_teacher2 ?? 'N/A' }} - {{ $evaluator_email2 ?? 'N/A' }}</p>

                <p><span class="highlight">Điểm GV1:</span> {{ $score1 }}</p>
                <p><span class="highlight">Điểm GV2:</span> {{ $score2 ?? 'N/A' }}</p>

                <p><span class="highlight">Ưu điểm giờ giảng:</span> {{ $advantages ?? 'N/A' }}</p>
                <p><span class="highlight">Những điểm cần rút kinh nghiệm:</span> {{ $disadvantages ?? 'N/A' }}</p>
                <p><span class="highlight">Kết luận giờ giảng:</span> {{ $conclusion ?? 'N/A' }}</p>

                <p>Nếu không thắc mắc về thông tin kết quả dự giờ thì GV được dự giờ sẽ coi như đồng ý kết quả.</p>
            </div>

            <!-- Footer -->
            <div class="email-footer">
                <p>&copy; {{ date('Y') }} {{ config('app.name') }}. All rights reserved.</p>
            </div>
        </div>
    </div>
</body>
</html>
