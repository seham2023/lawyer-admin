<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CaseRecordAudit extends Model
{
    protected $fillable = [
        'case_record_id',
        'user_id',
        'action',
        'event_type',
        'field_name',
        'old_value',
        'new_value',
        'metadata',
        'ip_address',
    ];

    protected $casts = [
        'metadata' => 'array',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Get the case record that this audit belongs to.
     */
    public function caseRecord(): BelongsTo
    {
        return $this->belongsTo(CaseRecord::class);
    }

    /**
     * Get the user who made the change.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(\App\Models\Qestass\User::class, 'user_id');
    }

    /**
     * Get human-readable field label.
     */
    public function getFieldLabelAttribute(): string
    {
        $labels = [
            'status_id' => 'Status',
            'court_id' => 'Court',
            'category_id' => 'Category',
            'subject' => 'Subject',
            'subject_description' => 'Description',
            'start_date' => 'Start Date',
            'client_id' => 'Client',
            'opponent_id' => 'Opponent',
            'opponent_lawyer_id' => 'Opponent Lawyer',
            'judge_name' => 'Judge Name',
            'court_name' => 'Court Name',
            'court_number' => 'Court Number',
            'amount' => 'Amount',
            'currency_id' => 'Currency',
            'notes' => 'Notes',
        ];

        return $labels[$this->field_name] ?? ucfirst(str_replace('_', ' ', $this->field_name));
    }

    /**
     * Get formatted old value (resolves IDs to names).
     */
    public function getFormattedOldValueAttribute(): string
    {
        return $this->formatValue($this->old_value, $this->field_name);
    }

    /**
     * Get formatted new value (resolves IDs to names).
     */
    public function getFormattedNewValueAttribute(): string
    {
        return $this->formatValue($this->new_value, $this->field_name);
    }

    /**
     * Format value based on field type.
     */
    protected function formatValue($value, $fieldName): string
    {
        if (is_null($value)) {
            return '(empty)';
        }

        // Handle relationship IDs - resolve to names
        if (str_ends_with($fieldName, '_id')) {
            return $this->resolveRelationshipValue($value, $fieldName);
        }

        // Handle dates
        if (in_array($fieldName, ['start_date', 'created_at', 'updated_at'])) {
            return \Carbon\Carbon::parse($value)->format('Y-m-d');
        }

        return (string) $value;
    }

    /**
     * Resolve relationship ID to name.
     */
    protected function resolveRelationshipValue($id, $fieldName): string
    {
        try {
            $modelMap = [
                'status_id' => \App\Models\Status::class,
                'court_id' => \App\Models\Court::class,
                'category_id' => \App\Models\Category::class,
                'currency_id' => \App\Models\Currency::class,
                'client_id' => \App\Models\Qestass\User::class,
                'opponent_id' => \App\Models\Opponent::class,
                'opponent_lawyer_id' => \App\Models\OpponentLawyer::class,
            ];

            if (isset($modelMap[$fieldName])) {
                $model = $modelMap[$fieldName]::find($id);
                return $model ? $model->name : "ID: {$id}";
            }

            return "ID: {$id}";
        } catch (\Exception $e) {
            return "ID: {$id}";
        }
    }
}
