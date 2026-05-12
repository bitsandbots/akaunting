<?php

namespace Modules\Nonprofit\Tests\Unit;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Modules\Nonprofit\Tests\TestCase;

class SchemaAuditTest extends TestCase
{
    /** @dataProvider expectedColumns */
    public function test_table_has_expected_columns(string $table, array $columns): void
    {
        foreach ($columns as $col) {
            $this->assertTrue(
                Schema::hasColumn($table, $col),
                "{$table} is missing column '{$col}' required by spec."
            );
        }
    }

    public static function expectedColumns(): array
    {
        return [
            'chart_of_accounts'      => ['chart_of_accounts',      ['company_id', 'code', 'name', 'type', 'parent_id', 'enabled']],
            'journal_entries'        => ['journal_entries',        ['entry_number', 'status', 'transaction_id', 'currency_code', 'currency_rate', 'reversed_by_entry_id', 'reverses_entry_id', 'posted_at']],
            'journal_entry_lines'    => ['journal_entry_lines',    ['debit_amount', 'credit_amount', 'debit_foreign', 'credit_foreign', 'currency_code', 'currency_rate', 'fund_id', 'program_id', 'functional_class_id']],
            'funds'                  => ['funds',                  ['type', 'restriction_detail', 'parent_id']],
            'functional_classes'     => ['functional_classes',     ['parent_class', 'is_system']],
            'fiscal_periods'         => ['fiscal_periods',         ['status', 'closed_at', 'closed_by']],
            'transaction_dimensions' => ['transaction_dimensions', ['allocation_percent', 'allocation_amount', 'sort_order']],
        ];
    }

    public function test_journal_entries_has_unique_company_entry_number(): void
    {
        if (DB::connection()->getDriverName() !== 'sqlite') {
            $this->markTestSkipped('Index audit only runs against sqlite.');
        }

        $found = false;

        foreach (DB::select('PRAGMA index_list(journal_entries)') as $idx) {
            if (! $idx->unique) {
                continue;
            }

            $cols = array_map(fn ($r) => $r->name, DB::select("PRAGMA index_info('{$idx->name}')"));
            sort($cols);

            if ($cols === ['company_id', 'entry_number']) {
                $found = true;
                break;
            }
        }

        $this->assertTrue($found, 'journal_entries must have UNIQUE(company_id, entry_number).');
    }

    public function test_journal_entry_lines_indexes_for_trial_balance(): void
    {
        if (DB::connection()->getDriverName() !== 'sqlite') {
            $this->markTestSkipped('Index audit only runs against sqlite.');
        }

        $expected = [
            ['company_id', 'chart_of_account_id', 'journal_entry_id'],  // trial balance
            ['company_id', 'fund_id', 'journal_entry_id'],              // fund subledger
        ];

        $haveSets = [];
        foreach (DB::select('PRAGMA index_list(journal_entry_lines)') as $idx) {
            $haveSets[] = array_map(fn ($r) => $r->name, DB::select("PRAGMA index_info('{$idx->name}')"));
        }

        foreach ($expected as $needle) {
            $this->assertContains($needle, $haveSets, 'Missing index ' . implode(',', $needle));
        }
    }
}
