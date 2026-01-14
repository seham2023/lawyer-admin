<!DOCTYPE html>
<html lang="ar" dir="rtl">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>الدفع قيد المعالجة</title>
    <link href="https://fonts.googleapis.com/css2?family=Tajawal:wght@400;500;700&display=swap" rel="stylesheet">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Tajawal', 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #7c3aed 0%, #a78bfa 50%, #c4b5fd 100%);
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

        .pending-icon {
            width: 100px;
            height: 100px;
            background: linear-gradient(135deg, #8b5cf6, #7c3aed);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 30px;
            animation: pulse 2s ease-in-out infinite;
            box-shadow: 0 10px 30px rgba(139, 92, 246, 0.3);
        }

        .pending-icon svg {
            width: 60px;
            height: 60px;
            stroke: white;
            stroke-width: 3;
            fill: none;
            stroke-linecap: round;
            stroke-linejoin: round;
        }

        @keyframes pulse {

            0%,
            100% {
                transform: scale(1);
                box-shadow: 0 10px 30px rgba(139, 92, 246, 0.3);
            }

            50% {
                transform: scale(1.05);
                box-shadow: 0 15px 40px rgba(139, 92, 246, 0.5);
            }
        }

        .spinner {
            width: 50px;
            height: 50px;
            border: 4px solid rgba(255, 255, 255, 0.3);
            border-top-color: white;
            border-radius: 50%;
            animation: spin 1s linear infinite;
        }

        @keyframes spin {
            to {
                transform: rotate(360deg);
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
            background: linear-gradient(135deg, #faf5ff 0%, #f3e8ff 100%);
            border-radius: 16px;
            padding: 30px;
            margin: 30px 0;
            text-align: right;
            border: 1px solid #e9d5ff;
        }

        .detail-row {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 16px 0;
            border-bottom: 1px solid #e9d5ff;
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
            background: linear-gradient(135deg, #8b5cf6, #7c3aed);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
            font-size: 24px;
        }

        .status-badge {
            display: inline-block;
            padding: 6px 16px;
            background: linear-gradient(135deg, #8b5cf6, #7c3aed);
            color: white;
            border-radius: 20px;
            font-size: 14px;
            font-weight: 600;
            animation: pulse-badge 2s ease-in-out infinite;
        }

        @keyframes pulse-badge {

            0%,
            100% {
                opacity: 1;
            }

            50% {
                opacity: 0.7;
            }
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
            background: linear-gradient(135deg, #7c3aed, #8b5cf6);
            color: white;
            box-shadow: 0 4px 15px rgba(124, 58, 237, 0.3);
            margin-top: 30px;
        }

        .btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(124, 58, 237, 0.4);
        }

        .info-box {
            background: #dbeafe;
            border-left: 4px solid #3b82f6;
            padding: 16px;
            border-radius: 8px;
            margin-top: 20px;
            text-align: right;
        }

        .info-box p {
            color: #1e40af;
            font-size: 14px;
            line-height: 1.6;
            margin: 8px 0;
        }

        .refresh-notice {
            color: #64748b;
            font-size: 14px;
            margin-top: 20px;
        }

        @media (max-width: 640px) {
            .container {
                padding: 30px 20px;
            }

            h1 {
                font-size: 26px;
            }
        }
    </style>
    <script>
        // Auto-refresh every 10 seconds to check payment status
        let refreshCount = 0;
        const maxRefreshes = 30; // Stop after 5 minutes (30 * 10 seconds)

        function checkPaymentStatus() {
            refreshCount++;
            if (refreshCount >= maxRefreshes) {
                return; // Stop auto-refresh after max attempts
            }

            fetch('{{ route("payment.status", $payment->id) }}')
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        // Check if status changed to success or failed
                        const statusName = data.status.toLowerCase();
                        if (statusName.includes('paid') || statusName.includes('مدفوع') || statusName.includes('success')) {
                            window.location.href = '{{ route("payment.success", $payment->id) }}';
                        } else if (statusName.includes('failed') || statusName.includes('فشل') || statusName.includes('rejected')) {
                            window.location.href = '{{ route("payment.failed", $payment->id) }}';
                        }
                    }
                })
                .catch(error => console.error('Error checking payment status:', error));
        }

        // Check status every 10 seconds
        setInterval(checkPaymentStatus, 10000);
    </script>
</head>

<body>
    <div class="container">
        <div class="pending-icon">
            <div class="spinner"></div>
        </div>

        <h1>الدفع قيد المعالجة ⏳</h1>
        <p class="subtitle">نقوم حالياً بمعالجة دفعتك، يرجى الانتظار قليلاً</p>

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
                <span class="status-badge">قيد المعالجة</span>
            </div>
        </div>

        <div class="info-box">
            <p>⏱️ عادةً ما تستغرق عملية المعالجة من 1 إلى 3 دقائق</p>
            <p>🔄 سيتم تحديث الصفحة تلقائياً عند اكتمال الدفع</p>
            <p>📧 سنرسل لك إشعاراً فور تأكيد الدفع</p>
        </div>

        <p class="refresh-notice">يتم التحديث تلقائياً كل 10 ثوانٍ...</p>

        <a href="{{ url('/admin') }}" class="btn">
            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"></path>
            </svg>
            العودة للوحة التحكم
        </a>
    </div>
</body>

</html>