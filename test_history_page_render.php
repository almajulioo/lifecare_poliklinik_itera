<?php
/**
 * Test /app/history page with actual data
 */

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;

// Get app
$app = require __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(\Illuminate\Contracts\Http\Kernel::class);

// Create request
$req = Request::create('/app/history', 'GET');

// Add session for auth
$middleware = $app->make('Illuminate\View\Middleware\ShareErrorsFromSession');
$req = $middleware->handle($req, function() { return null; });

// Handle request
try {
    $response = $kernel->handle($req);
    
    if ($response->getStatusCode() === 200) {
        echo "✅ Page rendered successfully (HTTP 200)\n";
        
        // Get content
        $content = $response->getContent();
        $length = strlen($content);
        echo "✅ Response length: $length bytes\n";
        
        // Check for common errors
        if (strpos($content, 'error') !== false || strpos($content, 'Error') !== false) {
            echo "⚠️  Page contains 'error' text\n";
        }
        
        if (strpos($content, 'RiwayatMinum Obat') !== false || strpos($content,'history') !== false) {
            echo "✅ Page contains expected content\n";
        }
        
        // Check if it's HTML  
        if (strpos($content, '<html') !== false || strpos($content, '<div') !== false) {
            echo "✅ Response is HTML\n";
        }
    } else {
        echo "❌ Unexpected status: " . $response->getStatusCode() . "\n";
        echo substr($response->getContent(), 0, 500) . "\n";
    }
} catch (Exception $e) {
    echo "❌ ERROR: " . $e->getMessage() . "\n";
    echo "Line: " . $e->getLine() . " in " . $e->getFile() . "\n";
}

$kernel->terminate($req, $response ?? null);
