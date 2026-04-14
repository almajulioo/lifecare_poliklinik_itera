<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <style>
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Helvetica, Arial, sans-serif;
            background-color: #f5f5f5;
        }
        .container {
            max-width: 600px;
            margin: 20px auto;
            background-color: white;
            padding: 30px;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        .header {
            text-align: center;
            margin-bottom: 30px;
        }
        .header h1 {
            color: #333;
            margin: 0;
            font-size: 24px;
        }
        .content {
            color: #666;
            line-height: 1.6;
            margin-bottom: 20px;
        }
        .button-container {
            text-align: center;
            margin: 30px 0;
        }
        .reset-button {
            display: inline-block;
            padding: 12px 30px;
            background-color: #3b82f6;
            color: white;
            text-decoration: none;
            border-radius: 5px;
            font-weight: bold;
        }
        .reset-button:hover {
            background-color: #2563eb;
        }
        .footer {
            border-top: 1px solid #eee;
            margin-top: 30px;
            padding-top: 20px;
            color: #999;
            font-size: 12px;
            text-align: center;
        }
        .warning {
            background-color: #fff3cd;
            border: 1px solid #ffc107;
            border-radius: 4px;
            padding: 12px;
            margin: 20px 0;
            color: #856404;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>🔐 Reset Password LifeCare Poliklinik</h1>
        </div>

        <div class="content">
            <p>Halo,</p>
            
            <p>Kami menerima permintaan untuk mereset password akun Anda. Jika Anda tidak membuat permintaan ini, abaikan email ini.</p>

            <p>Klik tombol di bawah untuk melanjutkan proses reset password:</p>

            <div class="button-container">
                <a href="{{ $resetUrl }}" class="reset-button">Reset Password</a>
            </div>

            <p>Atau salin dan paste URL ini di browser Anda:</p>
            <p style="word-break: break-all; background-color: #f5f5f5; padding: 10px; border-radius: 4px;">
                {{ $resetUrl }}
            </p>

            <div class="warning">
                <strong>⏰ Link ini berlaku selama 60 menit.</strong> Setelah itu, Anda perlu meminta link baru.
            </div>

            <p>Jika Anda memiliki pertanyaan, hubungi support kami.</p>
        </div>

        <div class="footer">
            <p>Surat ini dikirim secara otomatis, harap jangan membalas.</p>
            <p>&copy; {{ date('Y') }} LifeCare Poliklinik ITERA. Semua hak dilindungi.</p>
        </div>
    </div>
</body>
</html>
