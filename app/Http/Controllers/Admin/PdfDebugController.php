<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;

class PdfDebugController extends Controller
{
    public function testBasic()
    {
        try {
            $html = '<html>
                <head><title>Test PDF</title></head>
                <body>
                    <h1>Test PDF</h1>
                    <p>DomPDF Test - Basic HTML</p>
                    <p>Timestamp: ' . now() . '</p>
                </body>
            </html>';
            
            $pdf = Pdf::loadHtml($html);
            $pdf->setPaper('A4', 'portrait');
            return $pdf->stream('test-basic.pdf');
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString()
            ], 500);
        }
    }
    
    public function testDebug()
    {
        try {
            // Test 1: Raw Dompdf
            $dompdf = new \Dompdf\Dompdf();
            $dompdf->loadHtml('<h1>Raw DomPDF</h1>');
            $dompdf->render();
            $rawPdfSize = strlen($dompdf->output());
            
            // Test 2: Laravel Facade
            $pdf = Pdf::loadHtml('<h1>Laravel Facade DomPDF</h1>');
            $pdf->setPaper('A4', 'portrait');
            $facadePdfSize = strlen($pdf->output());
            
            return response()->json([
                'success' => true,
                'raw_dompdf_size' => $rawPdfSize,
                'facade_pdf_size' => $facadePdfSize,
                'fonts_dir' => storage_path('fonts'),
                'fonts_dir_exists' => is_dir(storage_path('fonts')),
                'fonts_dir_writable' => is_writable(storage_path('fonts')),
                'config_font_dir' => config('dompdf.options.font_dir')
            ]);
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine()
            ], 500);
        }
    }
    
    public function testReportPdf()
    {
        try {
            // Prepare dummy data similar to reportPdf
            $data = [
                'year' => 2026,
                'month' => 3,
                'monthName' => 'Maret',
                'monthParam' => '2026-03',
                'reportData' => [
                    ['no' => 1, 'date' => now(), 'mahasiswa' => 5, 'pegawai' => 3, 'total' => 8],
                    ['no' => 2, 'date' => now()->addDay(), 'mahasiswa' => 4, 'pegawai' => 2, 'total' => 6],
                    ['no' => 3, 'date' => now()->addDays(2), 'mahasiswa' => 6, 'pegawai' => 4, 'total' => 10],
                ],
                'grandTotal' => [
                    'mahasiswa' => 15,
                    'pegawai' => 9,
                    'total' => 24,
                ],
                'generatedAt' => now()->format('d F Y H:i'),
            ];

            // Generate PDF using the actual report view
            $pdf = Pdf::loadView('admin.clinic-patients.report_pdf', $data);
            $pdf->setPaper('A4', 'portrait');
            
            return $pdf->stream('test-report.pdf');
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString()
            ], 500);
        }
    }
    
    public function testReportPdfDownload()
    {
        try {
            // Prepare dummy data
            $data = [
                'year' => 2026,
                'month' => 3,
                'monthName' => 'Maret',
                'monthParam' => '2026-03',
                'reportData' => [
                    ['no' => 1, 'date' => now(), 'mahasiswa' => 5, 'pegawai' => 3, 'total' => 8],
                    ['no' => 2, 'date' => now()->addDay(), 'mahasiswa' => 4, 'pegawai' => 2, 'total' => 6],
                    ['no' => 3, 'date' => now()->addDays(2), 'mahasiswa' => 6, 'pegawai' => 4, 'total' => 10],
                ],
                'grandTotal' => [
                    'mahasiswa' => 15,
                    'pegawai' => 9,
                    'total' => 24,
                ],
                'generatedAt' => now()->format('d F Y H:i'),
            ];

            // Generate PDF using the actual report view
            $pdf = Pdf::loadView('admin.clinic-patients.report_pdf', $data);
            $pdf->setPaper('A4', 'portrait');
            
            // Save to file for inspection
            $filename = 'laporan-test-' . now()->timestamp . '.pdf';
            $filepath = storage_path('app/public/' . $filename);
            
            // Ensure directory exists
            if (!is_dir(dirname($filepath))) {
                mkdir(dirname($filepath), 0755, true);
            }
            
            // Save file
            file_put_contents($filepath, $pdf->output());
            
            return response()->json([
                'success' => true,
                'file_saved' => $filename,
                'file_path' => $filepath,
                'file_size' => filesize($filepath),
                'download_url' => asset('storage/' . $filename)
            ]);
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine()
            ], 500);
        }
    }
    
    public function testSimpleHtmlReport()
    {
        try {
            // Build simple HTML without using Blade engine
            $html = '
            <html>
            <head>
                <style>
                    body { font-family: Arial, sans-serif; margin: 20px; }
                    h1 { text-align: center; color: #333; }
                    table { width: 100%; border-collapse: collapse; margin-top: 20px; }
                    th, td { 
                        border: 1px solid #ddd; 
                        padding: 10px; 
                        text-align: center;
                    }
                    th { background-color: #f2f2f2; font-weight: bold; }
                    .total-row { background-color: #e8f4f8; font-weight: bold; }
                </style>
            </head>
            <body>
                <h1>LAPORAN KUNJUNGAN POLIKLINIK</h1>
                <h2 style="text-align: center;">Maret 2026</h2>
                
                <table>
                    <thead>
                        <tr>
                            <th>No</th>
                            <th>Tanggal</th>
                            <th>Mahasiswa</th>
                            <th>Pegawai</th>
                            <th>Jumlah</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td>1</td>
                            <td>2026-03-01</td>
                            <td>5</td>
                            <td>3</td>
                            <td>8</td>
                        </tr>
                        <tr>
                            <td>2</td>
                            <td>2026-03-02</td>
                            <td>4</td>
                            <td>2</td>
                            <td>6</td>
                        </tr>
                        <tr>
                            <td>3</td>
                            <td>2026-03-03</td>
                            <td>6</td>
                            <td>4</td>
                            <td>10</td>
                        </tr>
                        <tr class="total-row">
                            <td colspan="2">JUMLAH</td>
                            <td>15</td>
                            <td>9</td>
                            <td>24</td>
                        </tr>
                    </tbody>
                </table>
                
                <p style="margin-top: 30px; text-align: right;">
                    Laporan Dihasilkan: ' . now()->format('d F Y H:i') . '
                </p>
            </body>
            </html>';

            $pdf = Pdf::loadHtml($html);
            $pdf->setPaper('A4', 'portrait');
            
            return $pdf->stream('laporan-simple.pdf');
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString()
            ], 500);
        }
    }
}
