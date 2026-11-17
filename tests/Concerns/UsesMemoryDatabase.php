<?php

namespace Tests\Concerns;

use Illuminate\Support\Facades\DB;
use RuntimeException;

trait UsesMemoryDatabase
{
    public function setUpUsesMemoryDatabase(): void
    {
        if (DB::connection()->getDriverName() !== 'sqlite' || DB::connection()->getDatabaseName() !== ':memory:') {
            throw new RuntimeException('Pengujian hanya diizinkan pada SQLite :memory:.');
        }

        $this->artisan('migrate', ['--no-interaction' => true])->assertExitCode(0);
    }
}
