<?php

namespace App\Support;

use App\Models\LawyerUser;
use App\Models\Qestass\User;
use Illuminate\Database\Eloquent\Builder;

class LawyerUserAccess
{
    public static function userIdsForLawyer(int $lawyerId, string $type = 'client'): array
    {
        return LawyerUser::query()
            ->where('lawyer_id', $lawyerId)
            ->where('user_type', $type)
            ->pluck('user_id')
            ->all();
    }

    public static function applyToUserQuery(Builder $query, int $lawyerId, string $type = 'client'): Builder
    {
        $userIds = self::userIdsForLawyer($lawyerId, $type);

        return $query
            ->where('type', $type === 'client' ? 'user' : 'admin')
            ->whereIn('id', $userIds);
    }

    public static function optionsForLawyer(int $lawyerId, string $type = 'client'): array
    {
        return self::applyToUserQuery(User::query(), $lawyerId, $type)
            ->orderBy('first_name')
            ->orderBy('last_name')
            ->get()
            ->mapWithKeys(fn (User $user) => [
                $user->id => trim($user->first_name . ' ' . $user->last_name) . ' - ' . $user->phone,
            ])
            ->all();
    }

    public static function attach(int $lawyerId, int $userId, string $type = 'client'): bool
    {
        $link = LawyerUser::query()->firstOrCreate([
            'lawyer_id' => $lawyerId,
            'user_id' => $userId,
            'user_type' => $type,
        ]);

        return $link->wasRecentlyCreated;
    }
}
