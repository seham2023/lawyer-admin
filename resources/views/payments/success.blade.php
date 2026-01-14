<!DOCTYPE html>
<html lang="ar" dir="rtl">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>تم الدفع بنجاح</title>
    <link href="https://fonts.googleapis.com/css2?family=Tajawal:wght@400;500;700&display=swap" rel="stylesheet">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Tajawal', 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #0f766e 0%, #14b8a6 50%, #2dd4bf 100%);
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

        .success-icon {
            width: 100px;
            height: 100px;
            background: linear-gradient(135deg, #10b981, #059669);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 30px;
            animation: scaleIn 0.5s ease-out 0.2s both;
            box-shadow: 0 10px 30px rgba(16, 185, 129, 0.3);
        }

        .success-icon svg {
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
                transform: scale(0) rotate(-180deg);
            }

            to {
                transform: scale(1) rotate(0deg);
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
            background: linear-gradient(135deg, #f8fafc 0%, #f1f5f9 100%);
            border-radius: 16px;
            padding: 30px;
            margin: 30px 0;
            text-align: right;
            border: 1px solid #e2e8f0;
        }

        .detail-row {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 16px 0;
            border-bottom: 1px solid #e2e8f0;
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
            background: linear-gradient(135deg, #10b981, #059669);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
            font-size: 24px;
        }

        .status-badge {
            display: inline-block;
            padding: 6px 16px;
            background: linear-gradient(135deg, #10b981, #059669);
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
            background: linear-gradient(135deg, #0f766e, #14b8a6);
            color: white;
            box-shadow: 0 4px 15px rgba(15, 118, 110, 0.3);
        }

        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(15, 118, 110, 0.4);
        }

        .btn-secondary {
            background: white;
            color: #0f766e;
            border: 2px solid #0f766e;
        }

        .btn-secondary:hover {
            background: #f0fdfa;
            transform: translateY(-2px);
        }

        .info-box {
            background: #fef3c7;
            border-left: 4px solid #f59e0b;
            padding: 16px;
            border-radius: 8px;
            margin-top: 20px;
            text-align: right;
        }

        .info-box p {
            color: #92400e;
            font-size: 14px;
            line-height: 1.6;
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
        <div class="success-icon">
            <svg viewBox="0 0 24 24">
                <polyline points="20 6 9 17 4 12"></polyline>
            </svg>
        </div>

        <h1>تم الدفع بنجاح! 🎉</h1>
        <p class="subtitle">شكراً لك، تم استلام دفعتك وتأكيدها بنجاح</p>

        <div class="payment-details">
            <div class="detail-row">
                <span class="detail-label">رقم الدفعة:</span>
                <span class="detail-value">#{{ $payment->id }}</span>
            </div>

            <div class="detail-row">
                <span class="detail-label">المبلغ المدفوع:</span>
                <span class="detail-value amount-highlight">
                    {{ number_format($payment->amount, 2) }} {{ $payment->currency->code ?? 'SAR' }}
                </span>
            </div>

            @if($payment->tax)
                <div class="detail-row">
                    <span class="detail-label">الضريبة:</span>
                    <span class="detail-value">{{ number_format($payment->tax, 2) }}
                        {{ $payment->currency->code ?? 'SAR' }}</span>
                </div>
            @endif

            <div class="detail-row">
                <span class="detail-label">تاريخ الدفع:</span>
                <span
                    class="detail-value">{{ $payment->payment_date?->format('Y-m-d') ?? now()->format('Y-m-d') }}</span>
            </div>

            <div class="detail-row">
                <span class="detail-label">طريقة الدفع:</span>
                <span class="detail-value">{{ $payment->payMethod->name ?? 'غير محدد' }}</span>
            </div>

            <div class="detail-row">
                <span class="detail-label">الحالة:</span>
                <span class="status-badge">{{ $payment->status->name ?? 'مدفوع' }}</span>
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

        <div class="info-box">
            <p>✓ سيتم إرسال إيصال الدفع إلى بريدك الإلكتروني قريباً</p>
        </div>

        <div class="btn-group">
            <a href="{{ url('/admin') }}" class="btn btn-primary">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"></path>
                </svg>
                العودة للوحة التحكم
            </a>
            <a href="#" onclick="window.print(); return false;" class="btn btn-secondary">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <polyline points="6 9 6 2 18 2 18 9"></polyline>
                    <path d="M6 18H4a2 2 0 0 1-2-2v-5a2 2 0 0 1 2-2h16a2 2 0 0 1 2 2v5a2 2 0 0 1-2 2h-2"></path>
                    <rect x="6" y="14" width="12" height="8"></rect>
                </svg>
                طباعة الإيصال
            </a>
        </div>
    </div>
</body>

</html>