<!DOCTYPE html>
<html lang="ar" dir="rtl">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>فشل الدفع</title>
    <link href="https://fonts.googleapis.com/css2?family=Tajawal:wght@400;500;700&display=swap" rel="stylesheet">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Tajawal', 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #dc2626 0%, #ef4444 50%, #f87171 100%);
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

        .failed-icon {
            width: 100px;
            height: 100px;
            background: linear-gradient(135deg, #ef4444, #dc2626);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 30px;
            animation: shake 0.5s ease-out 0.2s both;
            box-shadow: 0 10px 30px rgba(239, 68, 68, 0.3);
        }

        .failed-icon svg {
            width: 60px;
            height: 60px;
            stroke: white;
            stroke-width: 3;
            fill: none;
            stroke-linecap: round;
            stroke-linejoin: round;
        }

        @keyframes shake {

            0%,
            100% {
                transform: translateX(0);
            }

            10%,
            30%,
            50%,
            70%,
            90% {
                transform: translateX(-5px);
            }

            20%,
            40%,
            60%,
            80% {
                transform: translateX(5px);
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

        .payment-details {
            background: linear-gradient(135deg, #fef2f2 0%, #fee2e2 100%);
            border-radius: 16px;
            padding: 30px;
            margin: 30px 0;
            text-align: right;
            border: 1px solid #fecaca;
        }

        .detail-row {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 16px 0;
            border-bottom: 1px solid #fecaca;
        }

        .detail-row:last-child {
            border-bottom: none;
        }

        .detail-label {
            color: #64748b;
            font-weight: 600;
            font-size: 15px;
        }

        .detail-value {
            color: #0f172a;
            font-weight: 700;
            font-size: 16px;
        }

        .amount-highlight {
            background: linear-gradient(135deg, #ef4444, #dc2626);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
            font-size: 24px;
        }

        .status-badge {
            display: inline-block;
            padding: 6px 16px;
            background: linear-gradient(135deg, #ef4444, #dc2626);
            color: white;
            border-radius: 20px;
            font-size: 14px;
            font-weight: 600;
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
            background: linear-gradient(135deg, #dc2626, #ef4444);
            color: white;
            box-shadow: 0 4px 15px rgba(220, 38, 38, 0.3);
        }

        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(220, 38, 38, 0.4);
        }

        .btn-secondary {
            background: white;
            color: #dc2626;
            border: 2px solid #dc2626;
        }

        .btn-secondary:hover {
            background: #fef2f2;
            transform: translateY(-2px);
        }

        .error-reasons {
            background: #fffbeb;
            border-left: 4px solid #f59e0b;
            padding: 20px;
            border-radius: 8px;
            margin-top: 20px;
            text-align: right;
        }

        .error-reasons h3 {
            color: #92400e;
            font-size: 16px;
            margin-bottom: 12px;
            font-weight: 700;
        }

        .error-reasons ul {
            list-style: none;
            padding: 0;
        }

        .error-reasons li {
            color: #78350f;
            font-size: 14px;
            line-height: 1.8;
            padding: 6px 0;
            padding-right: 20px;
            position: relative;
        }

        .error-reasons li:before {
            content: "•";
            position: absolute;
            right: 0;
            color: #f59e0b;
            font-weight: bold;
        }

        .support-box {
            background: #f1f5f9;
            border-radius: 12px;
            padding: 20px;
            margin-top: 20px;
        }

        .support-box p {
            color: #475569;
            font-size: 14px;
            line-height: 1.6;
        }

        .support-box a {
            color: #dc2626;
            font-weight: 600;
            text-decoration: none;
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
        <div class="failed-icon">
            <svg viewBox="0 0 24 24">
                <circle cx="12" cy="12" r="10"></circle>
                <line x1="15" y1="9" x2="9" y2="15"></line>
                <line x1="9" y1="9" x2="15" y2="15"></line>
            </svg>
        </div>

        <h1>فشل الدفع ❌</h1>
        <p class="subtitle">عذراً، لم نتمكن من إتمام عملية الدفع</p>

        <div class="payment-details">
            <div class="detail-row">
                <span class="detail-label">رقم الدفعة:</span>
                <span class="detail-value">#{{ $payment->id }}</span>
            </div>

            <div class="detail-row">
                <span class="detail-label">المبلغ:</span>
                <span class="detail-value amount-highlight">
                    {{ number_format($payment->amount, 2) }} {{ $payment->currency->code ?? 'SAR' }}
                </span>
            </div>

            <div class="detail-row">
                <span class="detail-label">الحالة:</span>
                <span class="status-badge">فشل</span>
            </div>

            @if($payment->payable)
                <div class="detail-row">
                    <span class="detail-label">متعلق بـ:</span>
                    <span class="detail-value">
                        @if($payment->payable_type === 'App\\Models\\CaseRecord')
                            قضية: {{ $payment->payable->subject ?? 'غير محدد' }}
                        @else
                            {{ class_basename($payment->payable_type) }}
                        @endif
                    </span>
                </div>
            @endif
        </div>

        <div class="error-reasons">
            <h3>الأسباب المحتملة لفشل الدفع:</h3>
            <ul>
                <li>رصيد غير كافٍ في البطاقة</li>
                <li>معلومات البطاقة غير صحيحة</li>
                <li>انتهاء صلاحية البطاقة</li>
                <li>رفض البنك للعملية</li>
                <li>مشكلة في الاتصال بالإنترنت</li>
            </ul>
        </div>

        <div class="support-box">
            <p>
                💡 <strong>هل تحتاج مساعدة؟</strong><br>
                يمكنك التواصل مع فريق الدعم الفني على:
                <a href="tel:+966500000000">+966 50 000 0000</a>
            </p>
        </div>

        <div class="btn-group">
            <a href="javascript:history.back()" class="btn btn-primary">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <polyline points="15 18 9 12 15 6"></polyline>
                </svg>
                المحاولة مرة أخرى
            </a>
            <a href="{{ url('/admin') }}" class="btn btn-secondary">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"></path>
                </svg>
                العودة للوحة التحكم
            </a>
        </div>
    </div>
</body>

</html>