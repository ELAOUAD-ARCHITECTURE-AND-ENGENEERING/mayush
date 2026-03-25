<?php

namespace App\Http\Middleware;

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(\Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Http\Request;

class TestSecurity
{
    public function test()
    {
        $middleware = new SecurityMonitoring();
        
        $test_phrases = [
            'Hello world',
            'We dropped by the table to see the items',
            'The union of these results is interesting',
            'The union select committee',
            'I will update the set of features',
            'Insert the key into the lock',
            'Delete the file from the disk',
            '<p>This is a normal paragraph</p>',
            '<script>alert(1)</script>',
        ];

        $output = "Testing patterns...\n";
        foreach ($test_phrases as $phrase) {
            $request = new Request([], ['content' => $phrase]);
            $reflect = new \ReflectionClass($middleware);
            $method = $reflect->getMethod('detectAttackPatterns');
            $method->setAccessible(true);
            
            $result = $method->invoke($middleware, $request);
            
            $output .= ($result ? "[BLOCKED]" : "[ALLOWED]") . " : " . $phrase . "\n";
        }
        file_put_contents('test_results.txt', $output);
        echo "Done.\n";
    }
}

(new TestSecurity())->test();
