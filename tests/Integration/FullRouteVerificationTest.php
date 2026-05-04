<?php

namespace Tests\Integration;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Route;
use Tests\TestCase;
use Tests\Traits\SeedsAppConfigs;

class FullRouteVerificationTest extends TestCase
{
    use RefreshDatabase, SeedsAppConfigs;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seedConfigs();
    }

    /** @test */
    public function all_named_routes_point_to_valid_controller_methods(): void
    {
        $routeCollection = Route::getRoutes();
        $errors = [];

        foreach ($routeCollection as $route) {
            $action = $route->getAction();
            
            if (isset($action['controller'])) {
                $controllerAction = $action['controller'];
                if (str_contains($controllerAction, '@')) {
                    list($controller, $method) = explode('@', $controllerAction);
                    
                    if (!class_exists($controller)) {
                        $errors[] = "Missing Controller: {$controller} (Route: " . ($route->getName() ?: $route->uri()) . ")";
                        continue;
                    }
                    
                    if (!method_exists($controller, $method)) {
                        $errors[] = "Missing Method: {$controller}@{$method} (Route: " . ($route->getName() ?: $route->uri()) . ")";
                    }
                }
            }
        }

        $this->assertEmpty($errors, "Found broken routes:\n" . implode("\n", $errors));
    }
}
