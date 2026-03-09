<?php

namespace Tests;

use Illuminate\Foundation\Testing\TestCase as BaseTestCase;
use Illuminate\Contracts\Console\Kernel;

abstract class TestCase extends BaseTestCase
{
    // We run migrations explicitly in setUp() to support sqlite :memory: and avoid
    // VACUUM-inside-transaction issues that can occur when RefreshDatabase
    // attempts to wrap migrations in transactions for in-memory databases.

    /**
     * Creates the application / boots the framework for tests.
     */
    public function createApplication()
    {
        $app = require __DIR__ . '/../bootstrap/app.php';

        $app->make(Kernel::class)->bootstrap();

        return $app;
    }

    /**
     * Ensure migrations are applied for the in-memory sqlite used by tests.
     */
    protected function setUp(): void
    {
        parent::setUp();

        // Run migrations fresh to ensure tables exist for tests using :memory:
        $this->artisan('migrate:fresh --seed')->run();
    }
}
