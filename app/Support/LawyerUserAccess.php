<?php

namespace App\Support;

use App\Models\LawyerUser;
use App\Models\Qestass\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;

class LawyerUserAccess
{
    protected static function lawyerUserTableExists(): bool
    {
        try {
            return Schema::hasTable((new LawyerUser())->getTable());
        } catch (\Throwable $exception) {
            Log::warning('LawyerUserAccess: failed to inspect lawyer_users table', [
                'error' => $exception->getMessage(),
            ]);

            return false;
        }
    }

    public static function userIdsForLawyer(int $lawyerId, string $type = 'client'): array
    {
        if (!self::lawyerUserTableExists()) {
            Log::warning('LawyerUserAccess: lawyer_users table is missing, returning no linked users', [
                'lawyer_id' => $lawyerId,
                'user_type' => $type,
            ]);

            return [];
        }

        try {
            return LawyerUser::query()
                ->where('lawyer_id', $lawyerId)
                ->where('user_type', $type)
                ->pluck('user_id')
                ->all();
        } catch (\Throwable $exception) {
            Log::error('LawyerUserAccess: failed to load linked users', [
                'lawyer_id' => $lawyerId,
                'user_type' => $type,
                'error' => $exception->getMessage(),
            ]);

            return [];
        }
    }

    public static function applyToUserQuery(Builder $query, int $lawyerId, string $type = 'client'): Builder
    {
        $userIds = self::userIdsForLawyer($lawyerId, $type);

        if ($userIds === []) {
            return $query->whereRaw('1 = 0');
        }

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
        if (!self::lawyerUserTableExists()) {
            Log::warning('LawyerUserAccess: attach skipped because lawyer_users table is missing', [
                'lawyer_id' => $lawyerId,
                'user_id' => $userId,
                'user_type' => $type,
            ]);

            return false;
        }

        $link = LawyerUser::query()->firstOrCreate([
            'lawyer_id' => $lawyerId,
            'user_id' => $userId,
            'user_type' => $type,
        ]);

        return $link->wasRecentlyCreated;
    }
}
