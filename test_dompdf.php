<?php

// Simple DomPDF Test Script
require_once 'vendor/autoload.php';

$dompdf = new Dompdf\Dompdf();
$dompdf->loadHtml('<h1>Test PDF</h1><p>This is a simple test.</p>');
$dompdf->setPaper('A4', 'portrait');

try {
    $dompdf->render();
    echo "✓ PDF rendered successfully!\n";
    echo "PDF size: " . strlen($dompdf->output()) . " bytes\n";
} catch (\Exception $e) {
    echo "✗ PDF render error: " . $e->getMessage() . "\n";
    echo "File: " . $e->getFile() . "\n";
    echo "Line: " . $e->getLine() . "\n";
}
