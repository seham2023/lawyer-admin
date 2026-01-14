# Tabby Payment Integration Guide

## Overview

The Tabby payment integration has been configured for legal case payments in the lawyer management system. The integration follows the structure from the backend TabbyService but is simplified for case-based payments (no shipping or discounts).

## Structure

### Case → Payment → Amount

-   **CaseRecord**: The legal case being paid for
-   **Payment**: The payment record with the amount
-   **Client**: The user/client making the payment

## Files Modified

### 1. Service Layer

-   **`app/Services/TabbyPaymentService.php`**: Main service for creating Tabby payment sessions
-   **`app/Services/PaymentServiceInterface.php`**: Interface updated for case payment structure

### 2. Controller

-   **`app/Http/Controllers/TabbyCallbackController.php`**: Handles payment callbacks (success, cancel, failure)

### 3. Routes

-   **`routes/web.php`**: Routes for payment callbacks already configured:
    -   `/tabby/payment/success`
    -   `/tabby/payment/cancel`
    -   `/tabby/payment/failure`

### 4. Views

-   **`resources/views/tabby/success.blade.php`**: Success page with payment details
-   **`resources/views/tabby/cancel.blade.php`**: Cancellation page
-   **`resources/views/tabby/failure.blade.php`**: Failure page with error information

## Configuration

Add these to your `.env` file:

```env
TABBY_API_BASE_URL=https://api.tabby.ai
TABBY_API_KEY=your_tabby_api_key_here
TABBY_MERCHANT_CODE=your_merchant_code_SAR
```

## Usage

### Method 1: Using CaseRecord and Payment Models (Recommended)

```php
use App\Services\TabbyPaymentService;

$tabbyService = new TabbyPaymentService();

// Assuming you have a CaseRecord and its Payment
$caseRecord = CaseRecord::find($caseId);
$payment = $caseRecord->payment;

// Create a Tabby session
$result = $tabbyService->createSessionFromCase($caseRecord, $payment);

if ($result['success']) {
    // Redirect user to Tabby payment page
    return redirect($result['web_url']);
} else {
    // Handle error
    return back()->with('error', $result['error']);
}
```

### Method 2: Manual Parameters

```php
use App\Services\TabbyPaymentService;

$tabbyService = new TabbyPaymentService();

$result = $tabbyService->createSession(
    amount: 1000.00,
    currency: 'SAR',
    buyerPhone: '+966501234567',
    buyerName: 'Ahmed Ali',
    buyerEmail: 'ahmed@example.com',
    caseReferenceId: '12345',
    userId: 1,
    userCreatedAt: '2024-01-01T00:00:00+03:00',
    loyaltyLevel: 5
);
```

## Response Structure

### Success Response

```php
[
    'success' => true,
    'status' => 'created',
    'session_id' => 'tabby_session_id',
    'payment_id' => 'tabby_payment_id',
    'web_url' => 'https://checkout.tabby.ai/...',
    'payment_session' => PaymentSession // Model instance
]
```

### Error Response

```php
[
    'success' => false,
    'error' => 'Error message',
    'status' => null,
    'session_id' => null,
    'payment_id' => null,
]
```

## Payment Flow

1. **Create Session**: Call `createSession()` or `createSessionFromCase()`
2. **Redirect User**: Send user to the `web_url` from the response
3. **User Pays**: User completes payment on Tabby's platform
4. **Callback**: Tabby redirects to one of the merchant URLs:
    - Success: `/tabby/payment/success?payment_id=xxx`
    - Cancel: `/tabby/payment/cancel?payment_id=xxx`
    - Failure: `/tabby/payment/failure?payment_id=xxx`
5. **Update Status**: The callback controller automatically updates the payment session status

## Payload Structure

The service sends the following to Tabby API:

```php
[
    'payment' => [
        'amount' => '1000.00',
        'currency' => 'SAR',
        'description' => 'Legal case payment #12345',
        'buyer' => [
            'phone' => '+966501234567',
            'name' => 'Ahmed Ali',
            'email' => 'ahmed@example.com',
        ],
        'order' => [
            'tax_amount' => '0.00',
            'shipping_amount' => '0.00',
            'discount_amount' => '0.00',
            'updated_at' => '2026-01-14T21:30:00+02:00',
            'reference_id' => '12345',
            'items' => [],
        ],
        'buyer_history' => [
            'registered_since' => '2024-01-01T00:00:00+03:00',
            'loyalty_level' => 5,
            'wishlist_count' => 0,
            'is_social_networks_connected' => true,
            'is_phone_number_verified' => true,
            'is_email_verified' => true
        ],
        'meta' => [
            'order_id' => '1234',
            'customer' => '1'
        ],
    ],
    'lang' => 'ar',
    'merchant_code' => 'your_merchant_code_SAR',
    'merchant_urls' => [
        'success' => 'https://yourapp.com/tabby/payment/success',
        'cancel' => 'https://yourapp.com/tabby/payment/cancel',
        'failure' => 'https://yourapp.com/tabby/payment/failure',
    ],
    'token' => null
]
```

## Example: Filament Action

```php
use App\Services\TabbyPaymentService;

Tables\Actions\Action::make('pay_with_tabby')
    ->label('Pay with Tabby')
    ->icon('heroicon-o-credit-card')
    ->action(function (CaseRecord $record) {
        $payment = $record->payment;

        if (!$payment) {
            Notification::make()
                ->title('No payment found')
                ->danger()
                ->send();
            return;
        }

        $tabbyService = new TabbyPaymentService();
        $result = $tabbyService->createSessionFromCase($record, $payment);

        if ($result['success']) {
            // Redirect to Tabby payment page
            return redirect($result['web_url']);
        } else {
            Notification::make()
                ->title('Payment Error')
                ->body($result['error'])
                ->danger()
                ->send();
        }
    })
```

## Database Tables

### payment_sessions

Stores Tabby payment session data:

-   `session_id`: Tabby session ID
-   `payment_id`: Tabby payment ID
-   `provider`: 'tabby'
-   `status`: Session status (created, authorized, cancelled, rejected, etc.)
-   `amount`: Payment amount
-   `currency`: Currency code
-   `buyer_phone`: Client phone number
-   `order_reference_id`: Case ID
-   `merchant_code`: Tabby merchant code
-   `web_url`: Tabby checkout URL
-   `response_data`: Full API response (JSON)
-   `case_record_id`: Foreign key to case_records table

## Testing

1. Use Tabby's test environment for development
2. Test phone numbers and amounts according to Tabby's documentation
3. Check logs in `storage/logs/laravel.log` for detailed API requests/responses

## Notes

-   Phone numbers must be in international format (+966XXXXXXXXX)
-   Minimum amount is 1.00 SAR
-   All amounts are formatted to 2 decimal places
-   Tax, shipping, and discount are set to 0.00 for legal case payments
-   Language is set to Arabic ('ar') by default
-   Loyalty level is calculated from completed payments for the client
