<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Surat Keterangan Penerimaan Magang</title>
    <style>
        @page {
            size: 21cm 33cm portrait;
            /* size: Letter portrait; */
            margin: .8cm .6cm;
        }

        body {
            font-family: 'Times New Roman', Times, serif;
            line-height: 1.6;
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        th, td {
            border: 1px solid black;
            padding: 5px;
        }

        .no-border {
            border: none;
        }

        .text-center {
            text-align: center;
        }

        .text-right {
            text-align: right;
        }

        .text-justify {
            text-align: justify;
        }

        .font-bold {
            font-weight: bold;
            font-weight: 800
        }

        .underline {
            text-decoration: underline;
        }

        .mb-4 {
            margin-bottom: 1rem;
        }

        .mb-12 {
            margin-bottom: 3rem;
        }

        .p-2 {
            padding: 0.5rem;
        }

        p {
            margin: 0; 
        }
    </style>
</head>

<body>

    <!-- KOP SURAT -->
    <table class="no-border " style="width: 100%;">
        <tr>
            <td class="no-border" style="width: 15%; text-align: center;">
                <img src="{{ public_path('images/Lambang_Kota_Semarang.png') }}" alt="Logo Kiri" width="80">
            </td>
            <td class="no-border text-center" style="line-height: 1.2;">
                <strong>
                <p class="font-bold" style="font-size: 18px; margin: 0; letter-spacing: 3px;">PEMERINTAH KOTA SEMARANG</p>
                <p class="font-bold" style="font-size: 24px; margin: 0;">PERUSAHAAN UMUM DAERAH AIR MINUM</p>
                <p class="font-bold" style="font-size: 24px; margin: 0;">"TIRTA MOEDAL"</p>
                <p style="font-bold font-size: 8px; margin: 0; letter-spacing: .5px;">Alamat: Jl. Kelud Raya Semarang, Kode Pos : 50237</p>
                <p style="font-bold font-size: 8px; margin: 0; line-height : 1; letter-spacing: .5px;">Telp. (024) 8315514 Fax. 8314078 Email: pdam@pdamkotasmg.co.id</p>
                </strong> 
            </td>
            <td class="no-border" style="width: 15%; text-align: center;">
                <img src="{{ public_path('images/logo-web-pdam2.png') }}" alt="Logo Kanan" width="80">
            </td>
        </tr>
    </table>
    
    <hr style="border: 2px solid black; margin-top: 0;">

    <!-- JUDUL -->
    <p class="text-center font-bold underline mb-4" style="margin-top: 10px;">SURAT KETERANGAN PENERIMAAN MAGANG</p>

    <!-- Nomor dan Tujuan -->
    <table class="no-border mb-4" style="width: 100%; padding: 0 60px">
        <tr>
            <!-- Kolom Kiri -->
            <td class="no-border" style="width: 50%; vertical-align: top;  ">
                <table class=" " style="line-height: 1.2; width: 100%;  ">
                    <tr style="">
                        <td class="no-border" style="padding:0;padding-top: 10px; width: 25%;">Nomor</td>
                        <td class="no-border" style="padding:0;padding-top: 10px; width: 5%;">:</td>
                        <td class="no-border"  style="padding:0;padding-top: 10px;">{{ $result['nomor_surat'] ?? '.......' }}</td>
                    </tr>
                    <tr>
                        <td class="no-border " style="padding:0;">Sifat</td>
                        <td class="no-border " style="padding:0;">:</td>
                        <td class="no-border " style="padding:0;">{{ $result['sifat'] ?? '.......' }}</td>
                    </tr>
                    <tr>
                        <td class="no-border " style="padding:0;">Lampiran</td>
                        <td class="no-border " style="padding:0;">:</td>
                        <td class="no-border " style="padding:0;">{{ $result['lampiran'] ?? '.......' }}</td>
                    </tr>
                    <tr>
                        <td class="no-border " style="padding:0;">Hal</td>
                        <td class="no-border " style="padding:0;">:</td>
                        <td class="no-border" style="padding:0;"><u><i>Kegiatan Magang</i></u></td>
                    </tr>
                </table>
            </td>

    
            <!-- Spacer Kolom Tengah -->
            <td class="no-border" style="width: 6%; vertical-align: top; padding: 0;">
                <table class="no-border" style="line-height: .8; width: 100%; ">
                    <tr>
                        <td class="no-border" style="padding:0;"> </td> 
                    </tr>
                    <tr>
                        <td class="no-border" style="padding:0; height: 75px"></td> 
                    </tr>
                    <tr>
                        <td class="no-border" style="padding:0;"></td> 
                    </tr>
                    <tr>
                        <td class="no-border" style="padding:0;" >
                            <strong> 
                                <p style="letter-spacing: 3px;"> </p>
                                <p style="; line-height: 1.2;">Yth. </p>
                                <p style=""> </p>
                                <p style="letter-spacing: 3px; text-decoration: underline; "> </p>
                            </strong> 
                        </td>
                    </tr>
                </table> 
            </td>
    
                <!-- Spacer Kolom Tengah -->
                <td class="no-border" style="  vertical-align: top; padding: 0;"> 
                    <table class="no-border" style="line-height: 1.2; width: 100%; ">
                        <tr>
                            <td class="no-border" style="padding:0;"><p>Semarang, {{ $result['tanggalSurat'] ?? '.......' }}</p></td> 
                        </tr>
                        <tr>
                            <td class="no-border" style="padding:0; height: 38px"></p></td> 
                        </tr>
                        <tr>
                            <td class="no-border" style="padding:0;"></td> 
                        </tr>
                        <tr>
                            <td class="no-border" style="padding:0;" >
                                <strong> 
                                    <p style="letter-spacing: 3px; font-weigth: bold">Kepada :</p>
                                    <p style=" ">{{ $result['kepada'] ?? '.......' }}</p>
                                    <p style=" ">{{ $result['alamat_kepada'] ?? '.......' }}</p>
                                    <p style="  ">di - </p>
                                    <p>&nbsp; &nbsp; &nbsp;
                                        <span style="letter-spacing: 3px; text-decoration: underline; ">SEMARANG</span>
                                    </p>
                                </strong>
                            </td>
                        </tr>
                    </table>  
                </td> 
        </tr>
    </table>
    
    
    
    <!-- Isi Surat -->
    <div class="text-justify mb-4" style="padding: 0 60px 0  115px"> 
        <ol style=" ">
            <li style="margin-bottom: 10px; text-indent: 40px;">
                Memperhatikan Surat dari  {{ $result['kepada'] ?? '.......' }}  Nomor:  {{ $result['nomor_surat'] ?? '.......' }}  tanggal  {{ $result['tanggal_kepada'] ?? '.......' }} perihal permohonan izin magang.
            </li>
            <li style="margin-bottom: 10px; text-indent: 40px;">
                Sehubungan dengan hal tersebut, kami sampaikan bahwa  PERUMDA Air Minum Tirta Moedal Kota Semarang  dapat menerima mahasiswa Saudara untuk magang mulai tanggal  {{ $result['tanggalMulai'] ?? '.......' }}  sampai dengan {{ $result['tanggalSelesai'] ?? '.......' }} , dengan data sebagai berikut:
                <br><br>
                <table>
                    <thead>
                        <tr class="text-center">
                            <th>No</th>
                            <th>Nama</th>
                            <th>{{ $result['status_magang'] == 'mahasiswa' ? 'NIM' : 'NISN' }}</th>
                            <th>Program Studi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td class="text-center">1</td>
                            <td>{{ $result['nama'] ?? '.......' }}</td>
                            <td class="text-center">{{ $result['nisn_npm_nim_npp'] ?? '.......' }}</td>
                            <td class="text-center">{{ $result['program_studi_universitas'] ?? '.......' }}</td>
                        </tr>
                    </tbody>
                </table>
                <br>
                Untuk keterangan lebih lanjut dapat menghubungi <span class="font-bold underline">Bagian Kepegawaian</span>.
            </li>
            <li style="text-indent: 40px;">
                Demikian surat ini kami sampaikan. Atas perhatian dan kerjasama Bapak/Ibu, diucapkan terima kasih.
            </li>
        </ol>
    </div>
 
    <div style="padding: 0 60px 0  115px; width: 350px; float: right; text-align: center;line-height: 1.2">
        <p>An. Direksi Perusahaan Umum Daerah Air Minum</p>
        <p>Tirta Moedal Kota Semarang</p>
        <p>Direktur Umum</p>
        <p>Ub</p>
        <p class="mb-12">Kepala Bagian Kepegawaian</p>
        <p class="font-bold underline">Sundariyah, S.E.</p>
        <p>Staf Madya I</p>
        <p>NPP. 6908384</p>
    </div>

</body>

</html>
