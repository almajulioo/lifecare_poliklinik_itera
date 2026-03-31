<?php

// Laravel DomPDF Facade Test
require_once 'vendor/autoload.php';
require_once 'bootstrap/app.php';

use Barryvdh\DomPDF\Facade\Pdf;

$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);

try {
    $pdf = Pdf::loadHtml('<h1>Test PDF with Facade</h1><p>Using Laravel DomPDF Facade</p>');
    $pdf->setPaper('A4', 'portrait');
    
    // Render the PDF
    $output = $pdf->output();
    
    echo "✓ PDF generated with Facade!\n";
    echo "PDF size: " . strlen($output) . " bytes\n";
    
    // Try to save for inspection
    file_put_contents(storage_path('logs/test_pdf_facade.pdf'), $output);
    echo "✓ Saved to storage/logs/test_pdf_facade.pdf\n";
    
} catch (\Exception $e) {
    echo "✗ Error: " . $e->getMessage() . "\n";
    echo "File: " . $e->getFile() . ":" . $e->getLine() . "\n";
    if (method_exists($e, 'getTraceAsString')) {
        echo "Trace: " . $e->getTraceAsString() . "\n";
    }
}
