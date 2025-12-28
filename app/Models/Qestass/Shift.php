<?php

namespace App\Models\Qestass;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class Shift extends Model
{
    use HasFactory;

    protected $connection = 'qestass_app';

    protected $fillable = ['time_id', 'user_id', 'start_time', 'end_time'];

    public function time()
    {
        return $this->belongsTo(Time::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function intervals()
    {
        return $this->hasMany(Interval::class);
    }

    /**
     * Generate 30-minute intervals for this shift
     */
    public function generateIntervals(): void
    {
        // Delete existing intervals for this shift
        $this->intervals()->delete();

        $start = Carbon::parse($this->start_time);
        $end = Carbon::parse($this->end_time);
        $intervalDuration = 30; // minutes

        $current = $start->copy();

        while ($current->lt($end)) {
            $next = $current->copy()->addMinutes($intervalDuration);

            // Don't exceed end time
            if ($next->gt($end)) {
                break; // Stop if next interval would exceed end time
            }

            Interval::create([
                'from' => $current->format('H:i'),
                'to' => $next->format('H:i'),
                'shift_id' => $this->id,
                'time_id' => $this->time_id,
                'user_id' => $this->user_id,
            ]);

            $current = $next->copy();
        }
    }

    /**
     * Boot method to auto-generate intervals after creation
     */
    protected static function booted()
    {
        static::created(function ($shift) {
            $shift->generateIntervals();
        });

        static::updated(function ($shift) {
            // Regenerate intervals if times changed
            if ($shift->isDirty('start_time') || $shift->isDirty('end_time')) {
                $shift->generateIntervals();
            }
        });
    }
}
