<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>IMS - Tappo Market</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { 
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .container {
            background: white;
            border-radius: 20px;
            padding: 40px;
            box-shadow: 0 20px 40px rgba(0,0,0,0.1);
            text-align: center;
            max-width: 500px;
            width: 90%;
        }
        h1 {
            color: #333;
            margin-bottom: 10px;
            font-size: 2.5em;
        }
        .subtitle {
            color: #666;
            margin-bottom: 30px;
            font-size: 1.1em;
        }
        .btn {
            display: inline-block;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 15px 30px;
            text-decoration: none;
            border-radius: 50px;
            font-weight: 600;
            margin: 10px;
            transition: transform 0.2s;
            box-shadow: 0 5px 15px rgba(0,0,0,0.2);
        }
        .btn:hover {
            transform: translateY(-2px);
        }
        .btn-secondary {
            background: #28a745;
        }
        .features {
            margin-top: 30px;
            text-align: left;
        }
        .feature {
            margin: 10px 0;
            padding: 10px;
            background: #f8f9fa;
            border-radius: 10px;
            border-left: 4px solid #667eea;
        }
        .stats {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(100px, 1fr));
            gap: 20px;
            margin: 30px 0;
        }
        .stat {
            text-align: center;
        }
        .stat-number {
            font-size: 2em;
            font-weight: bold;
            color: #667eea;
        }
        .stat-label {
            font-size: 0.9em;
            color: #666;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>🚀 IMS System</h1>
        <p class="subtitle">Hệ Thống Quản Lý Kho Hàng - Tappo Market</p>
        
        <div class="stats">
            <div class="stat">
                <div class="stat-number">793</div>
                <div class="stat-label">Sản Phẩm</div>
            </div>
            <div class="stat">
                <div class="stat-number">371</div>
                <div class="stat-label">Đơn Hàng</div>
            </div>
            <div class="stat">
                <div class="stat-number">⚡</div>
                <div class="stat-label">Tối Ưu</div>
            </div>
        </div>

        <a href="/ims-dashboard/login.php" class="btn">🔐 Đăng Nhập Hệ Thống</a>
        <a href="/ims-dashboard/performance-test.html" class="btn btn-secondary">📊 Kiểm Tra Hiệu Suất</a>

        <div class="features">
            <div class="feature">
                ⚡ <strong>Tối Ưu Hiệu Suất:</strong> Tải dữ liệu nhanh chóng với phân trang và cache
            </div>
            <div class="feature">
                🔍 <strong>Tìm Kiếm Thông Minh:</strong> Index database cho tìm kiếm < 50ms
            </div>
            <div class="feature">
                📊 <strong>Dashboard Thông Minh:</strong> Giao diện tối ưu với AJAX loading
            </div>
            <div class="feature">
                🛡️ <strong>Bảo Mật:</strong> Authentication với Laravel Sanctum
            </div>
        </div>

        <p style="margin-top: 30px; color: #999; font-size: 0.9em;">
            Phiên bản tối ưu - Thời gian tải < 1 giây
        </p>
    </div>
</body>
</html>