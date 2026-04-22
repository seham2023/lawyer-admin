<?php

namespace App\Filament\Lawyer\Resources\ClientResource\RelationManagers\VisitsRelationManager\Pages;

use App\Filament\Lawyer\Resources\ClientResource\RelationManagers\VisitsRelationManager;
use Filament\Resources\Pages\ViewRecord;

class ViewVisit extends ViewRecord
{
    protected static string $resource = VisitsRelationManager::class;

    public function getRelationManagers(): array
    {
        return [
            \App\Filament\Lawyer\Resources\ClientResource\RelationManagers\VisitsRelationManager\RelationManagers\PaymentDetailsRelationManager::class,
        ];
    }
}
