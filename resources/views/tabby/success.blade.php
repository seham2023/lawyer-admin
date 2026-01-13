<!DOCTYPE html>
<html lang="ar" dir="rtl">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>نجاح الدفع - Tabby</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }

        .container {
            background: white;
            border-radius: 20px;
            padding: 40px;
            max-width: 500px;
            width: 100%;
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3);
            text-align: center;
        }

        .success-icon {
            width: 80px;
            height: 80px;
            background: #10b981;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 20px;
            animation: scaleIn 0.5s ease-out;
        }

        .success-icon svg {
            width: 50px;
            height: 50px;
            stroke: white;
            stroke-width: 3;
            fill: none;
        }

        @keyframes scaleIn {
            from {
                transform: scale(0);
            }

            to {
                transform: scale(1);
            }
        }

        h1 {
            color: #1f2937;
            font-size: 28px;
            margin-bottom: 10px;
        }

        p {
            color: #6b7280;
            font-size: 16px;
            line-height: 1.6;
            margin-bottom: 20px;
        }

        .details {
            background: #f9fafb;
            border-radius: 10px;
            padding: 20px;
            margin: 20px 0;
            text-align: right;
        }

        .detail-row {
            display: flex;
            justify-content: space-between;
            padding: 10px 0;
            border-bottom: 1px solid #e5e7eb;
        }

        .detail-row:last-child {
            border-bottom: none;
        }

        .detail-label {
            color: #6b7280;
            font-weight: 600;
        }

        .detail-value {
            color: #1f2937;
        }

        .btn {
            display: inline-block;
            background: #667eea;
            color: white;
            padding: 12px 30px;
            border-radius: 8px;
            text-decoration: none;
            font-weight: 600;
            transition: background 0.3s;
            margin-top: 10px;
        }

        .btn:hover {
            background: #5568d3;
        }
    </style>
</head>

<body>
    <div class="container">
        <div class="success-icon">
            <svg viewBox="0 0 24 24">
                <polyline points="20 6 9 17 4 12"></polyline>
            </svg>
        </div>

        <h1>تم الدفع بنجاح!</h1>
        <p>شكراً لك، تم استلام دفعتك بنجاح</p>

        @if($session)
            <div class="details">
                <div class="detail-row">
                    <span class="detail-label">رقم الجلسة:</span>
                    <span class="detail-value">{{ $session->session_id }}</span>
                </div>
                <div class="detail-row">
                    <span class="detail-label">المبلغ:</span>
                    <span class="detail-value">{{ number_format($session->amount, 2) }} {{ $session->currency }}</span>
                </div>
                @if($caseRecord)
                    <div class="detail-row">
                        <span class="detail-label">القضية:</span>
                        <span class="detail-value">{{ $caseRecord->subject }}</span>
                    </div>
                @endif
                <div class="detail-row">
                    <span class="detail-label">الحالة:</span>
                    <span class="detail-value">{{ $session->status }}</span>
                </div>
            </div>
        @endif

        <a href="/" class="btn">العودة للصفحة الرئيسية</a>
    </div>
</body>

</html>