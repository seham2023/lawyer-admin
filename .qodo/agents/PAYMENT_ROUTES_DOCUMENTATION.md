# Payment After-Pay Routes & Blade Views Documentation

## Overview

This document describes the complete payment flow implementation for the lawyer dashboard, including routes, controllers, and blade views for handling post-payment scenarios.

## 📋 Routes Created

### Payment Routes (`/payment/*`)

All routes are defined in `routes/web.php` under the `payment.*` namespace:

| Route             | URL                            | Controller Method               | Purpose                                 |
| ----------------- | ------------------------------ | ------------------------------- | --------------------------------------- |
| `payment.success` | `/payment/{paymentId}/success` | `PaymentController@success`     | Display successful payment confirmation |
| `payment.pending` | `/payment/{paymentId}/pending` | `PaymentController@pending`     | Show payment processing status          |
| `payment.failed`  | `/payment/{paymentId}/failed`  | `PaymentController@failed`      | Display payment failure page            |
| `payment.error`   | `/payment/error`               | `PaymentController@error`       | Show general payment errors             |
| `payment.status`  | `/payment/{paymentId}/status`  | `PaymentController@checkStatus` | AJAX endpoint for status checks         |

### Tabby Callback Routes (`/tabby/payment/*`)

These routes handle callbacks from the Tabby payment gateway:

| Route                   | URL                      | Controller Method                 | Purpose                     |
| ----------------------- | ------------------------ | --------------------------------- | --------------------------- |
| `tabby.payment.success` | `/tabby/payment/success` | `TabbyCallbackController@success` | Tabby success callback      |
| `tabby.payment.cancel`  | `/tabby/payment/cancel`  | `TabbyCallbackController@cancel`  | Tabby cancellation callback |
| `tabby.payment.failure` | `/tabby/payment/failure` | `TabbyCallbackController@failure` | Tabby failure callback      |

## 🎨 Blade Views

### 1. Success Page (`resources/views/payments/success.blade.php`)

**Features:**

-   ✅ Beautiful teal/green gradient design
-   ✅ Animated success icon with checkmark
-   ✅ Detailed payment information display
-   ✅ Shows payment ID, amount, tax, date, method, status
-   ✅ Links to related case if applicable
-   ✅ Print receipt functionality
-   ✅ Return to dashboard button
-   ✅ RTL support for Arabic

**Usage:**

```php
return redirect()->route('payment.success', ['paymentId' => $payment->id]);
```

### 2. Pending Page (`resources/views/payments/pending.blade.php`)

**Features:**

-   ⏳ Purple gradient design with pulsing animation
-   ⏳ Auto-refresh every 10 seconds to check status
-   ⏳ Automatically redirects when payment completes
-   ⏳ Loading spinner animation
-   ⏳ Informative messages about processing time
-   ⏳ RTL support for Arabic

**Usage:**

```php
return redirect()->route('payment.pending', ['paymentId' => $payment->id]);
```

**Auto-Refresh Logic:**

-   Checks payment status every 10 seconds via AJAX
-   Stops after 30 attempts (5 minutes)
-   Redirects to success/failed page when status changes

### 3. Failed Page (`resources/views/payments/failed.blade.php`)

**Features:**

-   ❌ Red gradient design with shake animation
-   ❌ Lists common failure reasons
-   ❌ Support contact information
-   ❌ Retry payment button
-   ❌ Return to dashboard button
-   ❌ RTL support for Arabic

**Common failure reasons displayed:**

-   Insufficient balance
-   Incorrect card information
-   Expired card
-   Bank rejection
-   Internet connection issues

**Usage:**

```php
return redirect()->route('payment.failed', ['paymentId' => $payment->id]);
```

### 4. Error Page (`resources/views/payments/error.blade.php`)

**Features:**

-   ⚠️ Gray gradient design
-   ⚠️ General error handling
-   ⚠️ Support contact information
-   ⚠️ Back to previous page button
-   ⚠️ Return to home button
-   ⚠️ RTL support for Arabic

**Usage:**

```php
return redirect()->route('payment.error')->with('error', 'Custom error message');
```

## 🎯 Controller Methods

### PaymentController (`app/Http/Controllers/PaymentController.php`)

#### `success($paymentId)`

-   Loads payment with all relationships
-   Displays success page
-   Logs access for tracking

#### `pending($paymentId)`

-   Shows pending status page
-   Enables auto-refresh functionality

#### `failed($paymentId)`

-   Displays failure page with reasons
-   Provides retry options

#### `error()`

-   Shows general error page
-   Displays custom error message from session

#### `checkStatus($paymentId)` [AJAX]

-   Returns JSON with current payment status
-   Used by pending page for auto-refresh

**Response format:**

```json
{
    "success": true,
    "status": "paid",
    "status_id": 1
}
```

## 🔧 TabbyPaymentService Updates

The `TabbyPaymentService` now uses named routes instead of hardcoded URLs:

```php
'merchant_urls' => [
    'success' => route('tabby.payment.success'),
    'cancel' => route('tabby.payment.cancel'),
    'failure' => route('tabby.payment.failure'),
]
```

**Benefits:**

-   ✅ Better maintainability
-   ✅ Automatic URL generation
-   ✅ Works with any APP_URL configuration
-   ✅ Follows Laravel best practices

## 📊 Filament Integration

### PaymentSessionsRelationManager Updates

The `web_url` column now displays as a clickable link:

```php
TextColumn::make('web_url')
    ->label('Payment Link')
    ->url(fn (PaymentSession $record): ?string => $record->web_url)
    ->openUrlInNewTab()
    ->toggleable()
    ->limit(50)
    ->tooltip(fn (PaymentSession $record): ?string => $record->web_url)
```

**Features:**

-   Opens in new tab
-   Shows truncated URL (50 chars)
-   Full URL visible on hover
-   Can be toggled on/off in table

## 🎨 Design Features

All blade views include:

1. **Modern Aesthetics:**

    - Gradient backgrounds
    - Smooth animations
    - Professional color schemes
    - Google Fonts (Tajawal for Arabic)

2. **Responsive Design:**

    - Mobile-friendly layouts
    - Flexible button groups
    - Adaptive padding and spacing

3. **Accessibility:**

    - RTL support for Arabic
    - Clear visual hierarchy
    - Semantic HTML
    - Proper ARIA labels (via SVG)

4. **User Experience:**
    - Clear status indicators
    - Helpful error messages
    - Action buttons (print, retry, home)
    - Auto-refresh for pending status

## 🚀 Usage Examples

### Redirect After Payment Processing

```php
// After successful payment
return redirect()->route('payment.success', ['paymentId' => $payment->id]);

// While payment is processing
return redirect()->route('payment.pending', ['paymentId' => $payment->id]);

// If payment failed
return redirect()->route('payment.failed', ['paymentId' => $payment->id]);

// For general errors
return redirect()->route('payment.error')
    ->with('error', 'لم نتمكن من معالجة الدفع');
```

### Check Payment Status (JavaScript)

```javascript
fetch(`/payment/${paymentId}/status`)
    .then((response) => response.json())
    .then((data) => {
        if (data.success) {
            console.log("Payment status:", data.status);
        }
    });
```

## 📝 Notes

-   All views are in Arabic (RTL)
-   All routes are registered and verified
-   Controller includes proper error handling and logging
-   Blade views are standalone (no layout dependencies)
-   Print functionality included in success page
-   Auto-refresh stops after 5 minutes to prevent infinite loops

## 🔍 Testing

To verify routes are working:

```bash
php artisan route:list --name=payment
```

Expected output: 5 payment routes + 3 Tabby routes = 8 total routes

## 📞 Support Information

Update support contact details in the blade views:

-   Email: `support@example.com`
-   Phone: `+966 50 000 0000`
-   Working hours: Sunday to Thursday, 9 AM - 5 PM

## ✅ Completion Checklist

-   [x] PaymentController created
-   [x] Routes registered in web.php
-   [x] Success blade view created
-   [x] Pending blade view created
-   [x] Failed blade view created
-   [x] Error blade view created
-   [x] TabbyPaymentService updated to use routes
-   [x] PaymentSessionsRelationManager web_url column fixed
-   [x] Routes verified and working
-   [x] Documentation created
