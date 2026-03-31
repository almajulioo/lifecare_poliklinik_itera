<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Laporan Kunjungan Poliklinik</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Times New Roman', Times, serif;
            font-size: 12pt;
            line-height: 1.5;
            color: #000;
            background: white;
        }
        
        .container {
            width: 100%;
            max-width: 21cm;
            margin: 0 auto;
            padding: 2cm 2cm;
        }
        
        /* Header Section */
        .header {
            text-align: center;
            padding-bottom: 20px;
            border-bottom: 2px solid #000;
            margin-bottom: 30px;
        }
        
        .header-top {
            font-size: 11pt;
            line-height: 1.6;
            margin-bottom: 15px;
        }
        
        .header-ministry {
            font-weight: bold;
            font-size: 11pt;
            margin-bottom: 3px;
        }
        
        .header-institution {
            font-weight: bold;
            font-size: 13pt;
            margin-bottom: 5px;
            letter-spacing: 0.5px;
        }
        
        .header-address {
            font-size: 10pt;
            margin-bottom: 3px;
        }
        
        .header-contact {
            font-size: 10pt;
        }
        
        /* Report Title */
        .report-title {
            text-align: center;
            margin: 30px 0 10px 0;
        }
        
        .report-title h2 {
            font-size: 14pt;
            font-weight: bold;
            margin-bottom: 5px;
            text-decoration: underline;
        }
        
        .report-subtitle {
            text-align: center;
            margin-bottom: 30px;
        }
        
        .report-subtitle p {
            font-size: 12pt;
            margin: 3px 0;
        }
        
        .month-name {
            font-weight: bold;
        }
        
        /* Table Styles */
        .table-wrapper {
            margin-top: 20px;
            margin-bottom: 20px;
        }
        
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 15px;
        }
        
        table thead {
            background-color: #f0f0f0;
        }
        
        table th {
            border: 1px solid #000;
            padding: 10px;
            text-align: center;
            font-weight: bold;
            font-size: 11pt;
            background-color: #f0f0f0;
        }
        
        table td {
            border: 1px solid #000;
            padding: 8px 10px;
            text-align: center;
            font-size: 11pt;
        }
        
        table tbody tr:nth-child(even) {
            background-color: #fafafa;
        }
        
        table tbody tr.total-row {
            background-color: #e8e8e8;
            font-weight: bold;
        }
        
        table tbody tr.total-row td {
            font-weight: bold;
            border-top: 2px solid #000;
        }
        
        /* Column alignment */
        .col-no {
            width: 8%;
        }
        
        .col-date {
            width: 25%;
            text-align: left;
        }
        
        .col-number {
            width: 22%;
            text-align: center;
        }
        
        /* Footer Info */
        .footer-info {
            margin-top: 30px;
            margin-bottom: 50px;
            font-size: 10pt;
            color: #666;
        }
        
        .footer-info p {
            margin: 2px 0;
        }
        
        /* Signature Section */
        .signature-section {
            margin-top: 50px;
            text-align: right;
        }
        
        .signature-item {
            display: inline-block;
            width: 45%;
            text-align: center;
        }
        
        .signature-label {
            font-size: 11pt;
            margin-bottom: 60px;
            font-style: italic;
        }
        
        .signature-name {
            font-size: 11pt;
            margin-bottom: 2px;
        }
        
        .signature-title {
            font-size: 10pt;
            font-style: italic;
        }
        
        /* Print styles */
        @media print {
            body {
                margin: 0;
                padding: 0;
            }
            
            .container {
                max-width: none;
                padding: 1.5cm 1.5cm;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <!-- Header -->
        <div class="header">
            <div class="header-top">
                <div class="header-ministry">KEMENTERIAN PENDIDIKAN, KEBUDAYAAN, RISET, DAN TEKNOLOGI</div>
                <div class="header-institution">INSTITUT TEKNOLOGI SUMATERA</div>
                <div class="header-address" style="margin-top: 5px;">Jalan Terusan Ryacudu, Way Huwi, Jati Agung, Lampung Selatan 35365</div>
                <div class="header-contact">Telepon: (0721) 311-4000</div>
            </div>
        </div>

        <!-- Report Title -->
        <div class="report-title">
            <h2>LAPORAN KUNJUNGAN POLIKLINIK</h2>
        </div>

        <!-- Report Subtitle -->
        <div class="report-subtitle">
            <p>Bulan <span class="month-name">{{ $monthName }} {{ $year }}</span></p>
        </div>

        <!-- Table -->
        <div class="table-wrapper">
            <table>
                <thead>
                    <tr>
                        <th class="col-no">No.</th>
                        <th class="col-date">Tanggal</th>
                        <th class="col-number">Mahasiswa</th>
                        <th class="col-number">Pegawai</th>
                        <th class="col-number">Jumlah</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($reportData as $row)
                        <tr>
                            <td class="col-no">{{ $row['no'] }}</td>
                            <td class="col-date">{{ $row['date']->format('d-m-Y') }}</td>
                            <td class="col-number">{{ $row['mahasiswa'] }}</td>
                            <td class="col-number">{{ $row['pegawai'] }}</td>
                            <td class="col-number">{{ $row['total'] }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" style="text-align: center; padding: 15px;">Tidak ada data kunjungan untuk bulan ini</td>
                        </tr>
                    @endforelse

                    @if(count($reportData) > 0)
                        <tr class="total-row">
                            <td colspan="2" style="text-align: center;">TOTAL</td>
                            <td class="col-number">{{ $grandTotal['mahasiswa'] }}</td>
                            <td class="col-number">{{ $grandTotal['pegawai'] }}</td>
                            <td class="col-number">{{ $grandTotal['total'] }}</td>
                        </tr>
                    @endif
                </tbody>
            </table>
        </div>

        <!-- Footer Info -->
        <div class="footer-info">
            <p>Sumber Data: Sistem Manajemen Pasien Poliklinik</p>
            <p>Tanggal Cetak: {{ $generatedAt }}</p>
        </div>

        <!-- Signature Section -->
        <div class="signature-section">
            <div class="signature-item">
                <div class="signature-label">Diketahui oleh,</div>
                <div class="signature-name">________________________</div>
                <div class="signature-title">Kepala Poliklinik</div>
            </div>
        </div>
    </div>
</body>
</html>
