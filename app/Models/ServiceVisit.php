<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\Pivot;

class ServiceVisit extends Pivot
{
    protected $connection = 'mysql';

    protected $table = 'service_visit';

    public $incrementing = true;

    protected static function booted(): void
    {
        static::saved(fn (ServiceVisit $serviceVisit) => $serviceVisit->syncVisitPayment());
        static::deleted(fn (ServiceVisit $serviceVisit) => $serviceVisit->syncVisitPayment());
    }

    private function syncVisitPayment(): void
    {
        Visit::find($this->visit_id)?->syncPaymentWithServices();
    }
}
