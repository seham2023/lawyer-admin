<?php

namespace App\Support;

use App\Models\Currency;

class Money
{
    /**
     * Get the default currency code (SAR).
     */
    public static function getCurrencyCode(): string
    {
        return 'SAR';
    }

    /**
     * Get the default currency symbol.
     */
    public static function getCurrencySymbol(): string
    {
        return 'SAR';
    }

    /**
     * Get the default currency ID (from the database).
     */
    public static function getCurrencyId(): int
    {
        return once(fn() => Currency::where('code', 'SAR')->first()->id ?? 3);
    }
}
