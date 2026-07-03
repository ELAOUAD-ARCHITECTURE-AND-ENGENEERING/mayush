<?php

namespace Tests;

use Illuminate\Foundation\Testing\TestCase as BaseTestCase;

abstract class TestCase extends BaseTestCase
{
    use CreatesApplication;

    protected function setUp(): void
    {
        parent::setUp();

        \Illuminate\Support\Facades\Gate::before(function ($user, $ability) {
            return $user->user_type === 'admin' ? true : null;
        });
    }

    protected function tearDown(): void
    {
        if (class_exists('Mockery')) {
            \Mockery::close();
        }
        parent::tearDown();
    }
}
