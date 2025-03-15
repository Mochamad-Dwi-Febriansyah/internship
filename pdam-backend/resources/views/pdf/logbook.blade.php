 
    <!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Logbook PDF</title>
    <style>
        @page { 
            size: A4 portrait; 
            margin: 2cm 1.5cm 1.5cm 2cm; 
        }

        body {
            font-family: Arial, sans-serif; 
            font-size: 12px;
            margin: 0;
            padding: 0;
            position: relative;
        }

        h2 {
            text-align: center;
            margin-bottom: 20px;
        }

        table {
    width: 100%;
    border-collapse: collapse;
    margin-top: 20px;
    page-break-inside: auto;
}

tr {
    page-break-inside: avoid;
    page-break-after: auto;
}
        

        th, td {
            border: 1px solid black;
            padding: 6px;
            text-align: left;
        }

        th {
            background-color: #f2f2f2;
            font-weight: bold;
            text-align: center
        }

        td {
            font-size: 10px;
        }

        .info-table {
        width: 100%;
        border-collapse: collapse;
    }
    .info-table td {
        padding: 2px;
        border: none;
        /* border: 1px solid #ddd; */
        vertical-align: middle;
    }
    .info-table .label {
        font-weight: bold;
        width: 10%;
    }
    .info-table .separator {
        width: 1%;
        text-align: center;
    }
    .info-table .value {
        width: 25%;
    }
    .info-table .date-label {
        text-align: left;
        width: 10%;
        font-weight: bold;
    }
    .info-table .date-value {
        text-align: left;
        width: 20%;
    }
         

        .whitespacenowrap {
            white-space: nowrap;
        }

        .header {
            text-align: center;
            margin-bottom: 20px;
        }

        .header img {
            width: 50px;
        }

        .header .company-details {
            font-size: 12px;
            line-height: 1.5;
        }

        .company-details .company-name {
            font-weight: bold;
            font-size: 16px;
            margin: 0;
        }

        .company-details .address {
            font-style: italic;
            margin: 0;
        }

        .company-details .telepon {
            margin: 0;
        }

        .pending {
            padding: 4px 8px;
            border-radius: 5px;
        background-color: yellow;
        color: black;
        /* font-weight: bold; */
    }
    .approved {
        padding: 4px 8px;
        border-radius: 5px;
        background-color: green;
        color: white;
        /* font-weight: bold; */
    }
    .rejected {
        padding: 4px 8px;
        border-radius: 5px;
        background-color: red;
        color: white;
        /* font-weight: bold; */
    }

        /* Layout tanda tangan */
        .signature-container {
            display: block;
            page-break-inside: avoid;
            margin-top: 50px;
            width: 200px;
            text-align: center;
            position: absolute;
            right: 0;
            /* bottom: 50px; */
        }

       /* Box untuk tanda tangan */
.signature-container .sign-box {
    margin: 5px;
    width: 100%;
    height: 100px; /* Pastikan ukuran konsisten */
    display: flex;
    justify-content: center;
    align-items: center;
    overflow: hidden; /* Mencegah gambar keluar */
    /* border: 1px solid #ddd; Opsional: tambahkan border untuk estetika */
}

/* Gambar tanda tangan */
.signature-container .sign-box img {
    max-width: 100%; /* Pastikan tidak melebihi wadah */
    max-height: 100%;
    object-fit: contain; /* Menjaga proporsi gambar tanpa crop */
}

        .signature-container p {
            margin: 0;
        }
        
        .signature-container .notes{
            margin: 5px 0 ;
            font-style: italic; 
            font-size: .9em;
        }

    </style>
</head>
<body>

    <!-- Header Perusahaan -->
    <div class="header">
        <img src="{{ public_path('images/logo-web-pdam2.png') }}" alt="Logo">
        <div class="company-details">
            <p class="company-name">Perusahaan Umum Daerah Air Minum Tirta Moedal Kota Semarang</p>
            <p class="address">Jl. Kelud Raya No. 60 Semarang 50237, Indonesia</p>
            <p class="telepon">Telepon: +62 24 8315514 (Fax) 0800 1503 888 | Email: pdam@pdamkotasmg.co.id</p>
        </div>
    </div>

    <h2>Laporan Harian</h2>
    
    <table class="info-table">
        <tbody>
            <tr>
                <td class="label">Nama</td>
                <td class="separator">:</td>
                <td class="value">{{ $result['user']['nama'] }}</td> 
                <td class="date-label">Tanggal Mulai</td>
                <td class="separator">:</td>
                <td class="date-value">{{ $result['user']['tanggal_mulai'] }}</td>
            </tr>
            <tr>
                <td class="label">NISN/NPM/NIM</td>
                <td class="separator">:</td>
                <td class="value">{{ $result['user']['nisn_npm_nim_npp'] }}</td> 
                <td class="date-label">Tanggal Selesai</td>
                <td class="separator">:</td>
                <td class="date-value">{{ $result['user']['tanggal_selesai'] }}</td>
            </tr>
            <tr>
                <td class="label">Nama Sekolah/Universitas</td>
                <td class="separator">:</td>
                <td class="value" colspan="4">{{ $result['user']['nama_sekolah_universitas'] }}</td>
            </tr>
            <tr>
                <td class="label">Jurusan</td>
                <td class="separator">:</td>
                <td class="value" colspan="4">{{ $result['user']['jurusan_sekolah'] ?? '-' }}</td>
            </tr>
            <tr>
                <td class="label">Fakultas</td>
                <td class="separator">:</td>
                <td class="value" colspan="4">{{ $result['user']['fakultas_universitas'] }}</td>
            </tr>
            <tr>
                <td class="label">Program Studi</td>
                <td class="separator">:</td>
                <td class="value" colspan="4">{{ $result['user']['program_studi_universitas'] }}</td>
            </tr>
        </tbody>
    </table>
    
    <table>
        <thead>
            <tr>
                <th>No</th>
                <th>Presensi</th> 
                <th>Kegiatan</th>
                <th>Deskripsi</th>
                <th>Hasil Capaian</th>
                <th>Status Laporan</th>
            </tr>
        </thead>
        <tbody>
            {{-- @php
            dd($result['logBook']->laporanHarian);
        @endphp --}}
            @if (!empty($result['logBook']))
            @foreach ($result['logBook'] as $index => $log)
                @if (!empty($log->laporanHarian))
                    <tr> 
                        <td>{{ $index +1 }}</td>
                        <td class="whitespacenowrap">{{ \Carbon\Carbon::parse($log['tanggal'])->translatedFormat('l, d-m-Y') }}<br>Datang {{ $log['waktu_check_in'] ?? '--:--' }} <br>Pulang {{ $log['waktu_check_out'] ?? '--:--' }}</td>
                        <td>{{ optional($log->laporanHarian)->title ?? '-' }}</td> 
                        <td>{{ strip_tags(optional($log->laporanHarian)->report ?? '-') }}</td>
                        <td>{{ strip_tags(optional($log->laporanHarian)->result ?? '-') }}</td>
                        
                        
                        <td>
                            <span class="
                            @if(optional($log->laporanHarian)->status === 'pending') pending
                            @elseif(optional($log->laporanHarian)->status === 'approved') approved 
                            @elseif(optional($log->laporanHarian)->status === 'rejected') rejected 
                            @endif">
                            @if(optional($log->laporanHarian)->status === 'pending') Menunggu
                            @elseif(optional($log->laporanHarian)->status === 'approved') Disetujui
                            @elseif(optional($log->laporanHarian)->status === 'rejected') Ditolak
                            @else -
                            @endif
                            </span>
                        </td>
                    </tr> 
                @endif
            @endforeach
        @else
            <tr><td colspan="6" class="text-center">Logbook kosong</td></tr>
        @endif
        
        </tbody>
    </table>

    <!-- Tanda Tangan -->

    <div class="signature-container" >
        <p>Semarang, {{ \Carbon\Carbon::now()->translatedFormat('d F Y') }}</p>
        <p>Pembimbing Lapangan</p>
        <div class="sign-box">
            @if(!empty($result['user']['mentor_tanda_tangan']['signature']))
                <img src="{{ public_path('storage/' . $result['user']['mentor_tanda_tangan']['signature']) }}" alt="Tanda Tangan">
            @endif
        </div>
        <p><b>{{ $result['user']['mentor_nama'] ?? ' ' }}</b></p>
        <p>NPP. {{ $result['user']['mentor_nisn_npm_nim_npp'] ?? ' ' }}</p> 
        <p class="notes">{{ optional($result['user']['mentor_tanda_tangan'])->signature ? '' : '(Laporan harian belum lengkap)' }}</p>

    </div>

</body>
</html> 
