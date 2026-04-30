<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Hệ thống đang bảo trì</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        body { background-color: #f8f9fa; display: flex; align-items: center; justify-content: center; height: 100vh; margin: 0; }
        .maintenance-card { max-width: 600px; padding: 40px; text-align: center; background: white; border-radius: 10px; box-shadow: 0 4px 6px rgba(0,0,0,0.1); }
        .icon { font-size: 80px; color: #ffc107; margin-bottom: 20px; }
    </style>
</head>
<body>
    <div class="maintenance-card">
        <i class="fas fa-tools icon"></i>
        <h2 class="mb-3">Hệ thống đang bảo trì!</h2>
        <p class="text-muted mb-4">Chúng tôi đang nâng cấp và bảo trì hệ thống để mang lại trải nghiệm tốt hơn. Vui lòng quay lại sau ít phút.</p>
        <button onclick="window.location.reload()" class="btn btn-primary"><i class="fas fa-sync-alt"></i> Tải lại trang</button>
    </div>
</body>
</html>
