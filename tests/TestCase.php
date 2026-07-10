<?php

namespace Tests;

use Illuminate\Foundation\Testing\TestCase as BaseTestCase;

abstract class TestCase extends BaseTestCase
{
    use CreatesApplication;

    protected function setUp(): void
    {
        parent::setUp();
        \Illuminate\Support\Facades\Cache::flush();
    }

    protected function tearDown(): void
    {
        if (class_exists('Mockery')) {
            \Mockery::close();
        }
        parent::tearDown();
    }
}
