<?php

namespace App\Filament\Lawyer\Resources\CaseResource\Pages;

use App\Filament\Lawyer\Resources\CaseResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;
use Filament\Resources\Components\Tab;
use Illuminate\Database\Eloquent\Builder;

class ListCases extends ListRecords
{
    protected static string $resource = CaseResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }

    public function getTabs(): array
    {
        return [
            'all' => Tab::make(__('all')),
            'active' => Tab::make(__('active_cases'))
                ->badgeColor('success')
                ->modifyQueryUsing(fn (Builder $query) => $query->whereHas('status', fn($q) => $q->where('name->en', 'Active'))),
            'judgment' => Tab::make(__('judgment'))
                ->badgeColor('warning')
                ->modifyQueryUsing(fn (Builder $query) => $query->whereHas('status', fn($q) => $q->where('name->en', 'Judgment'))),
            'closed' => Tab::make(__('closed'))
                ->badgeColor('danger')
                ->modifyQueryUsing(fn (Builder $query) => $query->whereHas('status', fn($q) => $q->where('name->en', 'Closed'))),
        ];
    }
}
