<!DOCTYPE html>
<html lang="ar" dir="rtl">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>خطأ في الدفع</title>
    <link href="https://fonts.googleapis.com/css2?family=Tajawal:wght@400;500;700&display=swap" rel="stylesheet">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Tajawal', 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #64748b 0%, #94a3b8 50%, #cbd5e1 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }

        .container {
            background: white;
            border-radius: 24px;
            padding: 50px 40px;
            max-width: 600px;
            width: 100%;
            box-shadow: 0 25px 70px rgba(0, 0, 0, 0.25);
            text-align: center;
            animation: slideUp 0.6s ease-out;
        }

        @keyframes slideUp {
            from {
                opacity: 0;
                transform: translateY(30px);
            }

            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .error-icon {
            width: 100px;
            height: 100px;
            background: linear-gradient(135deg, #64748b, #475569);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 30px;
            animation: scaleIn 0.5s ease-out 0.2s both;
            box-shadow: 0 10px 30px rgba(100, 116, 139, 0.3);
        }

        .error-icon svg {
            width: 60px;
            height: 60px;
            stroke: white;
            stroke-width: 3;
            fill: none;
            stroke-linecap: round;
            stroke-linejoin: round;
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
            color: #0f172a;
            font-size: 32px;
            font-weight: 700;
            margin-bottom: 12px;
        }

        .subtitle {
            color: #64748b;
            font-size: 18px;
            line-height: 1.6;
            margin-bottom: 30px;
        }

        .error-message {
            background: linear-gradient(135deg, #fef2f2 0%, #fee2e2 100%);
            border-radius: 16px;
            padding: 24px;
            margin: 30px 0;
            text-align: center;
            border: 1px solid #fecaca;
        }

        .error-message p {
            color: #991b1b;
            font-size: 16px;
            line-height: 1.6;
            font-weight: 500;
        }

        .btn-group {
            display: flex;
            gap: 15px;
            margin-top: 30px;
            flex-wrap: wrap;
            justify-content: center;
        }

        .btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            padding: 14px 32px;
            border-radius: 12px;
            text-decoration: none;
            font-weight: 600;
            font-size: 16px;
            transition: all 0.3s ease;
            border: none;
            cursor: pointer;
        }

        .btn-primary {
            background: linear-gradient(135deg, #64748b, #475569);
            color: white;
            box-shadow: 0 4px 15px rgba(100, 116, 139, 0.3);
        }

        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(100, 116, 139, 0.4);
        }

        .btn-secondary {
            background: white;
            color: #64748b;
            border: 2px solid #64748b;
        }

        .btn-secondary:hover {
            background: #f8fafc;
            transform: translateY(-2px);
        }

        .support-box {
            background: #f1f5f9;
            border-radius: 12px;
            padding: 20px;
            margin-top: 20px;
        }

        .support-box h3 {
            color: #1e293b;
            font-size: 18px;
            margin-bottom: 12px;
            font-weight: 700;
        }

        .support-box p {
            color: #475569;
            font-size: 14px;
            line-height: 1.6;
            margin: 8px 0;
        }

        .support-box a {
            color: #3b82f6;
            font-weight: 600;
            text-decoration: none;
        }

        .support-box a:hover {
            text-decoration: underline;
        }

        @media (max-width: 640px) {
            .container {
                padding: 30px 20px;
            }

            h1 {
                font-size: 26px;
            }

            .btn-group {
                flex-direction: column;
            }

            .btn {
                width: 100%;
            }
        }
    </style>
</head>

<body>
    <div class="container">
        <div class="error-icon">
            <svg viewBox="0 0 24 24">
                <path d="M10.29 3.86L1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z">
                </path>
                <line x1="12" y1="9" x2="12" y2="13"></line>
                <line x1="12" y1="17" x2="12.01" y2="17"></line>
            </svg>
        </div>

        <h1>حدث خطأ ⚠️</h1>
        <p class="subtitle">عذراً، واجهنا مشكلة أثناء معالجة طلبك</p>

        <div class="error-message">
            <p>{{ $errorMessage ?? 'حدث خطأ غير متوقع. يرجى المحاولة مرة أخرى.' }}</p>
        </div>

        <div class="support-box">
            <h3>كيف يمكننا مساعدتك؟</h3>
            <p>
                📧 البريد الإلكتروني: <a href="mailto:support@example.com">support@example.com</a>
            </p>
            <p>
                📞 الهاتف: <a href="tel:+966500000000">+966 50 000 0000</a>
            </p>
            <p>
                🕐 ساعات العمل: من الأحد إلى الخميس، 9 صباحاً - 5 مساءً
            </p>
        </div>

        <div class="btn-group">
            <a href="javascript:history.back()" class="btn btn-primary">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <polyline points="15 18 9 12 15 6"></polyline>
                </svg>
                العودة للصفحة السابقة
            </a>
            <a href="{{ url('/admin') }}" class="btn btn-secondary">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"></path>
                </svg>
                الصفحة الرئيسية
            </a>
        </div>
    </div>
</body>

</html>