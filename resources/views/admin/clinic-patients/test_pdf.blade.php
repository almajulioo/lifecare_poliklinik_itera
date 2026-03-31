<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Test PDF</title>
</head>
<body style="font-family: Arial;">
    <h1>Test PDF Generation</h1>
    <p>Ini adalah test PDF sederhana</p>
    <p>Month: {{ $monthName }} {{ $year }}</p>
    <p>Total Data: {{ count($reportData) }}</p>
    
    @if(count($reportData) > 0)
        <h2>Data tersedia:</h2>
        <ul>
            @foreach($reportData as $row)
                <li>{{ $row['date']->format('d-m-Y') }} - Mahasiswa: {{ $row['mahasiswa'] }}, Pegawai: {{ $row['pegawai'] }}</li>
            @endforeach
        </ul>
    @else
        <p>Tidak ada data untuk bulan ini</p>
    @endif
    
    <p style="margin-top: 50px; color: #666; font-size: 12px;">Generated: {{ $generatedAt }}</p>
</body>
</html>
