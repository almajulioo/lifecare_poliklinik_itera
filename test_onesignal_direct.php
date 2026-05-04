<?php
/**
 * Direct OneSignal API Test
 * Test what OneSignal API actually returns
 */

require 'vendor/autoload.php';

$dotenv = \Dotenv\Dotenv::createImmutable(__DIR__);
$dotenv->load();

$appId = env('ONESIGNAL_APP_ID');
$apiKey = env('ONESIGNAL_REST_API_KEY');

echo "\n=== OneSignal Direct API Test ===\n";
echo "App ID: " . substr($appId, 0, 10) . "...\n";
echo "API Key: " . substr($apiKey, 0, 15) . "...\n\n";

// Test payload - simplest: just All segment (no filters)
$payload = [
    'app_id' => $appId,
    'included_segments' => ['All'],
    'target_channel' => 'push',
    'headings' => ['en' => '💊 Simple Test'],
    'contents' => ['en' => 'Testing with All segment only'],
    'send_after' => date('Y-m-d\TH:i:s\Z', strtotime('+1 day')),
    'data' => [
        'test' => true,
    ]
];

echo "Sending payload:\n";
echo json_encode($payload, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) . "\n\n";

// Make request
$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, 'https://onesignal.com/api/v1/notifications');
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_TIMEOUT, 30);
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    'Content-Type: application/json; charset=utf-8',
    'Authorization: ' . $apiKey,
]);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload));

// Disable SSL verification for localhost testing
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);

$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
$curlError = curl_error($ch);
curl_close($ch);

echo "HTTP Code: $httpCode\n";
echo "Curl Error: " . ($curlError ?: 'None') . "\n";
echo "API Key used: $apiKey\n\n";

// Try again without Basic prefix if 403
if ($httpCode === 403) {
    echo "Got 403 - retrying with just the API key (no Base64, no 'Basic')...\n\n";
    
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, 'https://onesignal.com/api/v1/notifications');
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 30);
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Content-Type: application/json; charset=utf-8',
        'Authorization: ' . $apiKey,  // Just the raw API key
    ]);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload));
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
    
    $response = curl_exec($ch);
    $httpCode2 = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    
    echo "HTTP Code (retry 1 - raw key): $httpCode2\n";
    if ($httpCode2 !== 403 && $response) {
        $data = json_decode($response, true);
        echo "Response:\n";
        echo json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) . "\n";
        exit;
    }
}

echo "\nResponse:\n";
if ($response) {
    $data = json_decode($response, true);
    echo json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) . "\n";
} else {
    echo "Empty response\n";
}
