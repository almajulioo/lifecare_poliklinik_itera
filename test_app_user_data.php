<?php

require_once 'vendor/autoload.php';

$app = require_once 'bootstrap/app.php';

$kernel = $app->make(\Illuminate\Contracts\Console\Kernel::class);

$exit_code = $kernel->call('tinker', [
    'command' => <<<'EOD'
// Test user data retrieval
$user = App\Models\User::first();
if ($user) {
    echo "✅ User Found:\n";
    echo "  ID: {$user->id}\n";
    echo "  Name: {$user->name}\n";
    echo "  Email: {$user->email}\n";
    echo "  Medical Conditions: " . json_encode($user->medical_conditions) . "\n";
    echo "  Notes: {$user->notes}\n";
} else {
    echo "❌ No users found\n";
}
exit;
EOD
]);
