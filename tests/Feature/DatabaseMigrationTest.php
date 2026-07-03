<?php

namespace Tests\Feature;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Schema;

class DatabaseMigrationTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function it_can_run_migrations_successfully()
    {
        $this->assertTrue(Schema::hasTable('users'));
        $this->assertTrue(Schema::hasTable('languages'));
        $this->assertTrue(Schema::hasTable('uploads'));
        $this->assertTrue(Schema::hasTable('image_optimization_states'));
        $this->assertTrue(Schema::hasColumn('image_optimization_states', 'upload_id'));
    }
}
