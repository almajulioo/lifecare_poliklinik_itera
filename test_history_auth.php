#!/usr/bin/env php
<?php
/**
 * Test script to simulate accessing /app/history with authentication
 */

// Bootstrap Laravel
define('LARAVEL_START', microtime(true));
require __DIR__ . '/vendor/autoload.php';
$app = require __DIR__ . '/bootstrap/app.php';

// Get the HTTP kernel
$kernel = $app->make(\Illuminate\Contracts\Http\Kernel::class);

// Create a request for the history page
$request = \Illuminate\Http\Request::create('/app/history', 'GET');

// Get a test user and authenticate
use App\Models\User;
use Illuminate\Support\Facades\Auth;

$user = User::where('email', 'user@lifecare.test')->first();
if (!$user) {
    $user = User::where('email', 'budi@example.com')->first();
}

if (!$user) {
    echo "❌ No test user found\n";
    exit(1);
}

// Set auth context
Auth::setUser($user);
$request->setUserResolver(function () use ($user) {
    return $user;
});

echo "User authenticated: " . $user->email . "\n\n";

// Handle the request
try {
    $response = $kernel->handle($request);
    echo "✅ Response status: " . $response->status() . "\n";
    
    if ($response->status() === 200) {
        echo "✅ Page rendered successfully\n";
    } else {
        echo "⚠️  Unexpected status code: " . $response->status() . "\n";
        echo "Response: " . substr($response->getContent(), 0, 500) . "\n";
    }
} catch (Exception $e) {
    echo "❌ ERROR: " . $e->getMessage() . "\n";
    echo "File: " . $e->getFile() . ":" . $e->getLine() . "\n\n";
    echo "Trace:\n";
    echo $e->getTraceAsString() . "\n";
}

$kernel->terminate($request, $response ?? null);
