<?php

namespace Modules\Nonprofit\Tests;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Feature\FeatureTestCase;

abstract class TestCase extends FeatureTestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        // Run module migrations explicitly because the module is not registered
        // in the global Migrator path during testing.
        $this->artisan('migrate', [
            '--path' => 'modules/Nonprofit/Database/Migrations',
            '--realpath' => false,
        ])->run();
    }
}
