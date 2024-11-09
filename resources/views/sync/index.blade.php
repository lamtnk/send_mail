<!doctype html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Đồng Bộ Dữ Liệu</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/css/bootstrap.min.css" rel="stylesheet"
        crossorigin="anonymous">
    <style>
        body {
            background: linear-gradient(135deg, #f3f4f6, #e5e7eb);
            height: 100vh;
            display: flex;
            justify-content: center;
            align-items: center;
        }

        .sync-container {
            max-width: 800px;
            min-width: 500px;
            padding: 2rem;
            background: #ffffff;
            border-radius: 10px;
            box-shadow: 0px 4px 20px rgba(0, 0, 0, 0.1);
            text-align: center;
        }
    </style>
</head>

<body>
    <div class="sync-container">
        @if (session('success'))
            <div class="alert alert-success">{{ session('success') }}</div>
        @endif
        @if (session('error'))
            <div class="alert alert-danger">{{ session('error') }}</div>
        @endif

        <h2>Đồng Bộ Dữ Liệu</h2>

        <!-- Form Đồng Bộ -->
        <form id="syncForm" action="{{ route('sync.perform') }}" method="POST">
            @csrf
            <div class="mb-3">
                <label for="system_type" class="form-label">Chọn hệ đào tạo</label>
                <select class="form-select" id="system_type" name="system_type" required>
                    <option value="" disabled selected>Chọn hệ đào tạo</option>
                    <option value="pt">Phổ thông Cao đẳng</option>
                    <option value="cd">Cao đẳng</option>
                </select>
            </div>

            <div class="mb-3">
                <label for="spreadsheet_id" class="form-label">ID Google Sheet</label>
                <input value="1rMoO03hR97WX0gFhqwrg8RHDFXeCeAFOTthP0HFzUrY" type="text" class="form-control" id="spreadsheet_id" name="spreadsheet_id"
                    placeholder="Nhập ID Google Sheet" required>
            </div>

            <div class="mb-3">
                <label for="sheet_name" class="form-label">Tên trang tính</label>
                <input value="KQDG - FA24" type="text" class="form-control" id="sheet_name" name="sheet_name"
                    placeholder="Nhập tên trang tính" required>
            </div>

            <button type="button" class="btn btn-primary w-100 mt-3" onclick="validateAndConfirm()">Đồng bộ</button>
        </form>
    </div>

    <!-- Modal Xác Nhận -->
    <div class="modal fade" id="confirmModal" tabindex="-1" aria-labelledby="confirmModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="confirmModalLabel">Xác nhận Đồng Bộ</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <p>Bạn có chắc chắn muốn đồng bộ dữ liệu này? Hành động này sẽ ghi dữ liệu lên hệ thống?</p>
                    <p><strong>Hệ đào tạo:</strong> <span id="confirmSystemType"></span></p>
                    <p><strong>ID Google Sheet:</strong> <span id="confirmSpreadsheetId"></span></p>
                    <p><strong>Tên Trang Tính:</strong> <span id="confirmSheetName"></span></p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Hủy</button>
                    <button type="button" class="btn btn-primary" onclick="submitForm()">Xác nhận Đồng Bộ</button>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/js/bootstrap.bundle.min.js" crossorigin="anonymous">
    </script>
    <script>
        function validateAndConfirm() {
            const systemType = document.getElementById('system_type').value;
            const spreadsheetId = document.getElementById('spreadsheet_id').value;
            const sheetName = document.getElementById('sheet_name').value;

            if (!systemType || !spreadsheetId || !sheetName) {
                alert('Vui lòng điền đầy đủ thông tin');
                return;
            }

            // Hiển thị thông tin trong modal
            document.getElementById('confirmSystemType').innerText = systemType === 'pt' ? 'Phổ thông Cao đẳng' :
            'Cao đẳng';
            document.getElementById('confirmSpreadsheetId').innerText = spreadsheetId;
            document.getElementById('confirmSheetName').innerText = sheetName;

            // Hiển thị modal xác nhận
            const confirmModal = new bootstrap.Modal(document.getElementById('confirmModal'));
            confirmModal.show();
        }

        function submitForm() {
            document.getElementById('syncForm').submit();
        }
    </script>
</body>

</html>
