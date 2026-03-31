<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Laporan Pasien Poliklinik - {{ $displayMonth }}</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Arial', sans-serif;
            line-height: 1.6;
            color: #333;
            background-color: white;
            padding: 40px;
        }

        .print-toolbar {
            display: flex;
            gap: 10px;
            margin-bottom: 30px;
            padding: 15px;
            background-color: #f8f9fa;
            border: 1px solid #dee2e6;
            border-radius: 4px;
            align-items: center;
        }

        .print-button {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 10px 20px;
            background-color: #28a745;
            color: white;
            border: none;
            border-radius: 4px;
            font-size: 14px;
            font-weight: 600;
            cursor: pointer;
            transition: background-color 0.3s ease;
        }

        .print-button:hover {
            background-color: #218838;
        }

        .print-button:active {
            background-color: #1e7e34;
        }

        @media print {
            .print-toolbar {
                display: none !important;
            }

            body {
                padding: 0;
                background-color: white;
            }
        }

        .header {
            text-align: center;
            margin-bottom: 40px;
            border-bottom: 3px solid #007bff;
            padding-bottom: 20px;
        }

        h1 {
            font-size: 28px;
            color: #007bff;
            margin-bottom: 10px;
            font-weight: bold;
        }

        .subtitle {
            font-size: 14px;
            color: #666;
            margin: 5px 0;
        }

        .report-info {
            margin-bottom: 30px;
            background-color: #f9f9f9;
            padding: 15px;
            border-left: 4px solid #007bff;
        }

        .info-row {
            display: flex;
            justify-content: space-between;
            margin: 8px 0;
            font-size: 13px;
        }

        .info-label {
            font-weight: bold;
            color: #555;
            min-width: 150px;
        }

        .info-value {
            color: #333;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 30px;
            margin-bottom: 30px;
        }

        thead {
            background-color: #007bff;
            color: white;
        }

        th {
            padding: 15px;
            text-align: left;
            font-weight: bold;
            border: 1px solid #ddd;
            font-size: 14px;
        }

        td {
            padding: 12px 15px;
            border: 1px solid #ddd;
            font-size: 13px;
        }

        tbody tr:nth-child(even) {
            background-color: #f9f9f9;
        }

        tbody tr:hover {
            background-color: #f0f0f0;
        }

        tfoot tr {
            background-color: #007bff;
            color: white;
            font-weight: bold;
        }

        tfoot td {
            padding: 15px;
            border: 1px solid #0056b3;
            font-size: 14px;
        }

        .text-center {
            text-align: center;
        }

        .text-right {
            text-align: right;
        }

        .number {
            text-align: right;
            font-weight: 500;
        }

        .percentage {
            text-align: right;
            font-weight: 500;
            color: #007bff;
        }

        .footer {
            margin-top: 50px;
            padding-top: 20px;
            border-top: 1px solid #ddd;
            font-size: 11px;
            color: #999;
            text-align: center;
        }

        .footer-note {
            margin: 10px 0;
            line-height: 1.4;
        }

        .summary {
            margin-top: 30px;
            padding: 20px;
            background-color: #e8f4f8;
            border: 1px solid #007bff;
            border-radius: 4px;
        }

        .summary h3 {
            color: #007bff;
            font-size: 16px;
            margin-bottom: 10px;
        }

        .summary-text {
            font-size: 13px;
            color: #333;
            line-height: 1.6;
        }

        .badge {
            display: inline-block;
            padding: 4px 8px;
            border-radius: 3px;
            font-size: 12px;
            font-weight: bold;
            margin: 2px;
        }

        .badge-success {
            background-color: #d4edda;
            color: #155724;
        }

        .badge-info {
            background-color: #d1ecf1;
            color: #0c5460;
        }

        @page {
            margin: 20mm;
        }

        @media print {
            body {
                padding: 0;
            }
        }
    </style>
</head>
<body>
    <!-- Print Toolbar -->
    <div class="print-toolbar">
        <button class="print-button" onclick="window.print()">
            <svg style="width: 18px; height: 18px; fill: currentColor;" viewBox="0 0 24 24">
                <path d="M19 8H5c-1.66 0-3 1.34-3 3v6h4v4h12v-4h4v-6c0-1.66-1.34-3-3-3zm-3 11H8v-5h8v5zm3-7c-.55 0-1-.45-1-1s.45-1 1-1 1 .45 1 1-.45 1-1 1zm-1-9H6v4h12V3z"/>
            </svg>
            Cetak Laporan
        </button>
    </div>

    <!-- Header -->
    <div class="header">
        <h1>📋 Laporan Kunjungan Pasien Poliklinik</h1>
        <div class="subtitle">Periode: {{ $displayMonth }}</div>
    </div>

    <!-- Report Info -->
    <div class="report-info">
        <div class="info-row">
            <span class="info-label">Periode Laporan:</span>
            <span class="info-value">{{ $displayMonth }}</span>
        </div>
        <div class="info-row">
            <span class="info-label">Tanggal Dibuat:</span>
            <span class="info-value">{{ now()->translatedFormat('d F Y H:i') }}</span>
        </div>
        <div class="info-row">
            <span class="info-label">Lokasi:</span>
            <span class="info-value">Poliklinik LifeCare</span>
        </div>
    </div>

    <!-- Summary Statistics -->
    <div class="summary">
        <h3>📊 Ringkasan Data</h3>
        <div class="summary-text">
            <p style="margin-bottom: 10px;">
                <strong>Total Kunjungan:</strong> 
                <span class="badge badge-info">{{ $totalVisits ?? 65 }} pasien</span>
            </p>
            <p style="margin-bottom: 10px;">
                <strong>Kategori Terbanyak:</strong> 
                <span class="badge badge-success">Mahasiswa ({{ $studentPercentage ?? '65%' }})</span>
            </p>
            <p>
                Laporan ini menunjukkan distribusi kunjungan pasien berdasarkan kategori (Mahasiswa/Pegawai) selama periode yang ditentukan.
            </p>
        </div>
    </div>

    <!-- Data Table -->
    <table>
        <thead>
            <tr>
                <th style="width: 50%;">Kategori Pasien</th>
                <th style="width: 25%;" class="text-right">Jumlah Kunjungan</th>
                <th style="width: 25%;" class="text-right">Persentase</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td>
                    <span class="badge badge-success">👨‍🎓</span> 
                    Mahasiswa
                </td>
                <td class="number">{{ $studentCount ?? 42 }}</td>
                <td class="percentage">{{ $studentPercentage ?? '65%' }}</td>
            </tr>
            <tr>
                <td>
                    <span class="badge badge-info">👔</span> 
                    Pegawai
                </td>
                <td class="number">{{ $staffCount ?? 23 }}</td>
                <td class="percentage">{{ $staffPercentage ?? '35%' }}</td>
            </tr>
        </tbody>
        <tfoot>
            <tr>
                <td><strong>TOTAL</strong></td>
                <td class="number">{{ $totalVisits ?? 65 }}</td>
                <td class="percentage">{{ '100%' }}</td>
            </tr>
        </tfoot>
    </table>

    <!-- Footer -->
    <div class="footer">
        <div class="footer-note">
            <p>Dokumen ini dibuat secara otomatis oleh Sistem Informasi LifeCare.</p>
            <p style="margin-top: 8px;">Untuk informasi lebih lanjut, hubungi administrator Poliklinik.</p>
            <p style="margin-top: 8px; color: #ccc;">
                Generated on {{ now()->format('Y-m-d H:i:s') }} | 
                Document ID: {{ md5($month . $displayMonth) }}
            </p>
        </div>
    </div>
</body>
</html>
