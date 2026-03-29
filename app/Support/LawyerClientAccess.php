<?php

namespace App\Support;

use App\Models\LawyerClient;
use App\Models\Qestass\User;
use Illuminate\Database\Eloquent\Builder;

class LawyerClientAccess
{
    public static function clientIdsForLawyer(int $lawyerId): array
    {
        return LawyerClient::query()
            ->where('lawyer_id', $lawyerId)
            ->pluck('client_id')
            ->all();
    }

    public static function applyToUserQuery(Builder $query, int $lawyerId): Builder
    {
        $clientIds = self::clientIdsForLawyer($lawyerId);

        return $query
            ->where('type', 'user')
            ->whereIn('id', $clientIds);
    }

    public static function optionsForLawyer(int $lawyerId): array
    {
        return self::applyToUserQuery(User::query(), $lawyerId)
            ->orderBy('first_name')
            ->orderBy('last_name')
            ->get()
            ->mapWithKeys(fn (User $user) => [
                $user->id => trim($user->first_name . ' ' . $user->last_name) . ' - ' . $user->phone,
            ])
            ->all();
    }

    public static function attach(int $lawyerId, int $clientId): bool
    {
        $link = LawyerClient::query()->firstOrCreate([
            'lawyer_id' => $lawyerId,
            'client_id' => $clientId,
        ]);

        return $link->wasRecentlyCreated;
    }
}
