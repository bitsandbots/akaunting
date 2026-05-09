<?php

namespace Modules\Nonprofit\Tests\Feature;

use Illuminate\Support\Facades\Schema;
use Modules\Nonprofit\Tests\TestCase;

class SmokeTest extends TestCase
{
    public function test_module_migrations_run_against_sqlite(): void
    {
        $this->assertTrue(Schema::hasTable('chart_of_accounts'));
        $this->assertTrue(Schema::hasTable('journal_entries'));
        $this->assertTrue(Schema::hasTable('journal_entry_lines'));
        $this->assertTrue(Schema::hasTable('funds'));
        $this->assertTrue(Schema::hasTable('programs'));
        $this->assertTrue(Schema::hasTable('functional_classes'));
        $this->assertTrue(Schema::hasTable('fiscal_periods'));
        $this->assertTrue(Schema::hasTable('transaction_dimensions'));
    }
}
