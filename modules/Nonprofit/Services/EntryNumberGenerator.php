<?php

namespace Modules\Nonprofit\Services;

use Illuminate\Support\Facades\DB;

/**
 * Generates per-company sequential journal entry numbers.
 *
 * Format: JE-{YYYY}-{NNNNN}  (e.g., "JE-2026-00042")
 *
 * Uses SELECT ... FOR UPDATE on the settings table to prevent
 * duplicate numbers under concurrent writes.
 */
class EntryNumberGenerator
{
    /**
     * Settings key for the per-company sequence counter.
     */
    protected const SETTING_KEY_PREFIX = 'nonprofit.journal_entry_sequence';

    /**
     * Generate the next entry number for the given company.
     *
     * This method executes inside a database transaction and locks
     * the sequence record with FOR UPDATE to guarantee uniqueness
     * even under concurrent requests.
     *
     * @param int $companyId
     * @return string e.g. "JE-2026-00042"
     */
    public function next(int $companyId): string
    {
        return DB::transaction(function () use ($companyId) {
            $key = $this->settingKey($companyId);
            $year = date('Y');

            // Atomically read-and-increment using a locked row.
            $setting = DB::table('settings')
                ->where('company_id', $companyId)
                ->where('key', $key)
                ->lockForUpdate()
                ->first();

            if ($setting) {
                $nextValue = (int) $setting->value + 1;
                DB::table('settings')
                    ->where('id', $setting->id)
                    ->update(['value' => (string) $nextValue]);
            } else {
                $nextValue = 1;
                DB::table('settings')->insert([
                    'company_id' => $companyId,
                    'key'        => $key,
                    'value'      => '1',
                ]);
            }

            return sprintf('JE-%s-%05d', $year, $nextValue);
        });
    }

    /**
     * Get the settings key for a company.
     */
    protected function settingKey(int $companyId): string
    {
        return static::SETTING_KEY_PREFIX . '.' . $companyId;
    }
}
