@component('mail::message')
# Nomor Registrasi Anda

Halo,

Berikut adalah Nomor Registrasi Anda:

- **Nomor Registrasi:** {{ $nomor_registrasi }} 

Silakan gunakan kredensial di atas untuk memantau perkembangan berkas.

@component('mail::button', ['url' => 'http://localhost:3000/internship/cek-berkas'])
Cek Sekarang
@endcomponent

Terima kasih,<br>
{{ config('app.name') }}
@endcomponent
