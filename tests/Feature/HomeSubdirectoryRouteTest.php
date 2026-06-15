<?php

namespace Tests\Feature;

use App\Http\Controllers\HomeController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Tests\TestCase;

class HomeSubdirectoryRouteTest extends TestCase
{
    public function test_mayush_subdirectory_root_resolves_to_home_controller(): void
    {
        $route = Route::getRoutes()->match(Request::create('/mayush', 'GET'));

        $this->assertSame('home.local_subdirectory', $route->getName());
        $this->assertSame(HomeController::class . '@index', $route->getActionName());
    }
}
