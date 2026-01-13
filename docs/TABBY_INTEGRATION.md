# Tabby Payment Integration Guide

## Overview

This integration allows you to create Tabby payment sessions directly from case records in the Filament admin panel. The payment amount is automatically retrieved from the case's payment record.

## Setup Instructions

### 1. Configure Environment Variables

Add the following variables to your `.env` file:

```env
TABBY_API_BASE_URL=https://api.tabby.ai
TABBY_API_KEY=your_actual_api_key_here
TABBY_MERCHANT_CODE=your_actual_merchant_code_here
```

**Note:** For testing, use the sandbox URL:

```env
TABBY_API_BASE_URL=https://api.tabby.dev
```

### 2. Get Your Tabby Credentials

1. Sign up or log in to [Tabby Dashboard](https://dashboard.tabby.ai/)
2. Navigate to **Settings** → **API Keys**
3. Copy your:
    - API Key (Secret Key)
    - Merchant Code

### 3. Clear Configuration Cache

After adding the environment variables, run:

```bash
php artisan config:clear
php artisan cache:clear
```

## How to Use

### Creating a Payment Session

1. Navigate to **Cases** in the Filament admin panel
2. Open a case record
3. Go to the **Payment Sessions** tab
4. Click the **"Create Tabby Session"** button (green button with credit card icon)
5. Enter the buyer's phone number in international format (e.g., +966XXXXXXXXX)
6. Click **Submit**

### What Happens Next

1. The system automatically retrieves the payment amount from `case->payment->amount`
2. A Tabby payment session is created with:

    - Amount: From the case payment
    - Currency: SAR (Saudi Riyal)
    - Buyer Phone: The number you entered
    - Order Reference: Auto-generated as `CASE-{id}-{timestamp}`
    - Items: Legal services description with case subject

3. The payment session is saved to the database and linked to the case
4. An SMS with the payment link is automatically sent to the buyer's phone
5. You'll see a success notification with the Session ID

### Validation

The system validates:

-   ✅ Case has an associated payment record
-   ✅ Payment amount is greater than zero
-   ✅ Tabby credentials are configured
-   ✅ Buyer phone number is provided

### Error Handling

You'll receive clear error messages if:

-   **No Payment Found**: The case doesn't have an associated payment
-   **Invalid Payment Amount**: The payment amount is zero or negative
-   **Configuration Error**: Tabby credentials are missing from `.env`
-   **API Error**: Tabby API returns an error (shown in the notification)

## Database Structure

### PaymentSession Model

The `payment_sessions` table stores:

-   `session_id`: Tabby session identifier
-   `payment_id`: Tabby payment identifier
-   `provider`: 'tabby'
-   `status`: Payment status (created, link_sent, authorized, etc.)
-   `amount`: Payment amount
-   `currency`: Currency code (SAR)
-   `buyer_phone`: Customer phone number
-   `order_reference_id`: Unique order reference
-   `merchant_code`: Tabby merchant code
-   `web_url`: Payment page URL
-   `response_data`: Full API response (JSON)
-   `case_record_id`: Link to the case record

## Payment Statuses

| Status       | Description                       | Badge Color      |
| ------------ | --------------------------------- | ---------------- |
| `created`    | Session created, awaiting payment | Warning (Yellow) |
| `link_sent`  | Payment link sent to customer     | Warning (Yellow) |
| `authorized` | Payment authorized by customer    | Success (Green)  |
| `closed`     | Payment completed                 | Success (Green)  |
| `captured`   | Payment captured                  | Success (Green)  |
| `rejected`   | Payment rejected                  | Danger (Red)     |
| `expired`    | Session expired                   | Danger (Red)     |

## API Integration

### TabbyPaymentService Methods

#### `createSession()`

Creates a new Tabby payment session.

**Parameters:**

-   `amount` (float): Payment amount
-   `currency` (string): Currency code (default: 'SAR')
-   `buyerPhone` (string): Customer phone in international format
-   `orderReferenceId` (string): Unique order reference
-   `items` (array): Array of items being purchased
-   `merchantCode` (string|null): Optional merchant code override

**Returns:**

```php
[
    'success' => true,
    'status' => 'created',
    'session_id' => 'abc123...',
    'payment_id' => 'pay_xyz...',
    'web_url' => 'https://checkout.tabby.ai/...',
    'payment_session' => PaymentSession // Model instance
]
```

#### `sendPaymentLink()`

Sends the payment link via SMS to the customer.

**Parameters:**

-   `sessionId` (string): Tabby session ID

**Returns:** `bool` - Success status

#### `getPaymentStatus()`

Retrieves the current payment status.

**Parameters:**

-   `paymentId` (string): Tabby payment ID

**Returns:**

```php
[
    'success' => true,
    'status' => 'authorized',
    'data' => [...] // Full API response
]
```

## Testing

### Test Phone Numbers (Sandbox)

Use these phone numbers in the sandbox environment:

-   Approved: `+966500000001`
-   Rejected: `+966500000002`

### Test Flow

1. Create a case with a payment record
2. Use the "Create Tabby Session" action
3. Enter a test phone number
4. Check the payment session is created in the database
5. Verify the SMS is sent (in production) or check logs (in sandbox)

## Troubleshooting

### "Configuration Error" Message

**Problem:** Tabby credentials not configured

**Solution:**

1. Check `.env` file has all three variables set
2. Run `php artisan config:clear`
3. Restart your development server

### "No Payment Found" Message

**Problem:** Case doesn't have a payment record

**Solution:**

1. Navigate to the case
2. Create a payment record with a valid amount
3. Try creating the Tabby session again

### "Invalid Payment Amount" Message

**Problem:** Payment amount is zero or negative

**Solution:**

1. Edit the case's payment record
2. Set a valid amount greater than zero
3. Try again

## Security Notes

-   ✅ API keys are stored in `.env` (not in version control)
-   ✅ All API calls use HTTPS
-   ✅ Payment data is validated before sending to Tabby
-   ✅ Errors are logged for debugging
-   ✅ Sensitive data is not exposed in error messages

## Support

For Tabby-specific issues:

-   Documentation: https://docs.tabby.ai/
-   Support: support@tabby.ai
-   Dashboard: https://dashboard.tabby.ai/

For integration issues:

-   Check Laravel logs: `storage/logs/laravel.log`
-   Enable debug mode temporarily to see detailed errors
-   Review the `payment_sessions` table for stored responses
