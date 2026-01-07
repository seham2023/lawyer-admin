<?php

namespace App\Observers;

use App\Models\CaseRecord;
use App\Models\CaseRecordAudit;

class CaseRecordObserver
{
    /**
     * Handle the CaseRecord "created" event.
     */
    public function created(CaseRecord $caseRecord): void
    {
        CaseRecordAudit::create([
            'case_record_id' => $caseRecord->id,
            'user_id' => auth()->id(),
            'event_type' => 'created',
            'ip_address' => request()->ip(),
            'metadata' => [
                'user_agent' => request()->userAgent(),
                'url' => request()->fullUrl(),
            ],
        ]);
    }

    /**
     * Handle the CaseRecord "updated" event.
     */
    public function updated(CaseRecord $caseRecord): void
    {
        // Get all changed fields
        $changes = $caseRecord->getDirty();

        // Ignore updated_at timestamp
        unset($changes['updated_at']);

        // Create audit entry for each changed field
        foreach ($changes as $field => $newValue) {
            $oldValue = $caseRecord->getOriginal($field);

            // Skip if values are actually the same (type coercion)
            if ($oldValue == $newValue) {
                continue;
            }

            CaseRecordAudit::create([
                'case_record_id' => $caseRecord->id,
                'user_id' => auth()->id(),
                'action' => "Updated {$field}",
                'event_type' => 'updated',
                'field_name' => $field,
                'old_value' => $oldValue,
                'new_value' => $newValue,
                'ip_address' => request()->ip(),
                'metadata' => [
                    'user_agent' => request()->userAgent(),
                    'url' => request()->fullUrl(),
                ],
            ]);
        }
    }

    /**
     * Handle the CaseRecord "deleted" event.
     */
    public function deleted(CaseRecord $caseRecord): void
    {
        CaseRecordAudit::create([
            'case_record_id' => $caseRecord->id,
            'user_id' => auth()->id(),
            'action' => 'Case deleted',
            'event_type' => 'deleted',
            'ip_address' => request()->ip(),
            'metadata' => [
                'user_agent' => request()->userAgent(),
                'url' => request()->fullUrl(),
                'case_data' => $caseRecord->toArray(), // Store snapshot before deletion
            ],
        ]);
    }

    /**
     * Handle the CaseRecord "restored" event.
     */
    public function restored(CaseRecord $caseRecord): void
    {
        CaseRecordAudit::create([
            'case_record_id' => $caseRecord->id,
            'user_id' => auth()->id(),
            'action' => 'Case restored',
            'event_type' => 'restored',
            'ip_address' => request()->ip(),
            'metadata' => [
                'user_agent' => request()->userAgent(),
                'url' => request()->fullUrl(),
            ],
        ]);
    }
}
