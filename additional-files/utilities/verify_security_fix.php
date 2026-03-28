<?php

namespace App\Http\Middleware;

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(\Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\User;

class VerifySecurityFix
{
    public function run()
    {
        $middleware = new SecurityMonitoring();
        $reflect = new \ReflectionClass($middleware);
        $method = $reflect->getMethod('detectAttackPatterns');
        $method->setAccessible(true);

        echo "--- STARTING SECURITY VERIFICATION ---\n\n";

        // 1. Test False Positives (Should be ALLOWED)
        $false_positives = [
            'We dropped by the table to see the items',
            'The union of two sets is a concept in math',
            'Please update the set of colors available',
            'Insert the key into the lock',
            'They deleted the file from the trash'
        ];

        echo "Testing False Positives (EXPECT [ALLOWED]):\n";
        foreach ($false_positives as $phrase) {
            $request = new Request([], ['content' => $phrase]);
            $result = $method->invoke($middleware, $request);
            echo ($result ? "[BLOCKED] ❌" : "[ALLOWED] ✅") . " : " . $phrase . "\n";
        }

        echo "\n";

        // 2. Test Real Attacks (Should be BLOCKED)
        $real_attacks = [
            'DROP TABLE users',
            'UNION SELECT password FROM users',
            'UPDATE users SET password = 123',
            'INSERT INTO users (name) VALUES ("hacker")',
            'DELETE FROM orders'
        ];

        echo "Testing Real Attacks (EXPECT [BLOCKED]):\n";
        foreach ($real_attacks as $phrase) {
            $request = new Request([], ['content' => $phrase]);
            $result = $method->invoke($middleware, $request);
            echo ($result ? "[BLOCKED] ✅" : "[ALLOWED] ❌") . " : " . $phrase . "\n";
        }

        echo "\n";

        // 3. Test Admin Bypass
        echo "Testing Admin Bypass (EXPECT [ALLOWED] for attacking pattern):\n";
        $admin = User::where('user_type', 'admin')->first();
        if ($admin) {
            Auth::login($admin);
            $phrase = 'DROP TABLE blocks_nothing';
            $request = Request::create('admin/dashboard', 'POST', ['content' => $phrase]);
            
            $result = $method->invoke($middleware, $request);
            echo ($result ? "[BLOCKED] ❌" : "[ALLOWED] ✅") . " : Admin submitting: " . $phrase . "\n";
        } else {
            echo "Skipping Admin Bypass test (No admin user found in DB).\n";
        }

        echo "\n--- VERIFICATION COMPLETE ---\n";
    }
}

(new VerifySecurityFix())->run();
