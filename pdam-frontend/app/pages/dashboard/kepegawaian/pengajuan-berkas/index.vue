<script setup lang="ts">
import * as yup from 'yup'
import type { SubmissionHandler } from 'vee-validate'
const { addNotification } = useNotification()
const config = useRuntimeConfig()
definePageMeta({
    layout: 'dashboard',
    middleware: ['auth']
})
import { getIconName, getStatusLabel } from "~/utils/FormatStatus"
import { FormatDate } from "~/utils/FormatDate"
const route = useRoute()
const router = useRouter()
const currentPage = ref(Number(route.query.page) || 1)

const breadcrumb = [
    { label: "Daftar Pengajuan Berkas", icon: "mdi:file-document-alert-outline", to: "/dashboard/kepegawaian/pengajuan-berkas" }
]


const { daftarPengajuanBerkas, statusDaftarPengajuanBerkas, refreshDaftarPengajuanBerkas, errorDaftarPengajuanBerkas, pendingDaftarPengajuanBerkas, toggleStatusBerkas, clearCachePengajuanBerkas, pendingClearCache, updateSuratTerima, pendingUpdateSuratTerima, errors } = useBerkas(currentPage)
const { users: mentors, pending } = useUsers(currentPage, "mentor");

const { updateMyMentor, pendingUpdateMyMentor } = useBerkas(currentPage);

function goToPage(page: number) {
    if (page < 1 || page > (daftarPengajuanBerkas.value?.data.last_page ?? 1)) return;
    currentPage.value = page;

    router.push({ query: { ...route.query, page } });
}

function getPageNumber(url: string | null | undefined): number {
    const match = url?.match(/page=(\d+)/)
    return match?.[1] ? parseInt(match[1], 10) : 1
}
const pendingUpdateId = ref(null)
const updateBerkasSchema = yup.object({
    // mentor_id: yup.string().required('Silahkan Pilih Mentor'),
    status_berkas: yup.string().required('Status Berkas wajib diisi'),
})

const updatePengajuanBerkasMagang: SubmissionHandler<any> = async (value, { resetForm }: { resetForm: () => void }) => {
    try {
        // console.log(value)
        pendingUpdateId.value = value.user_id
        const response = await updateMyMentor(value)
        resetForm();
        addNotification('success', response.message)
        // await clearCachePengajuanBerkas()
        await refreshDaftarPengajuanBerkas();
        pendingUpdateId.value = null
    } catch (error: any) {
        addNotification('error', error)
        pendingUpdateId.value = null
    }
}
const updateSuratTerimaSchema = yup.object({
    // status_berkas: yup.string().required('Status Berkas wajib diisi'), 
    nama: yup.string().required('Nama wajib diisi'),
    nisn_npm_nim_npp: yup.string().required('nisn_npm_nim_npp wajib diisi'),
    nomor_surat: yup.string().required('Nomor Surat wajib diisi'),
    kepada: yup.string().required('Kepada wajib diisi'),
})

const updateSuratTerimaToggle: SubmissionHandler<any> = async (value, { resetForm }: { resetForm: () => void }) => {
    try {
        pendingUpdateId.value = value.user_id
        const response = await updateSuratTerima(value)
        resetForm();
        resetFormSuratTerima();
        addNotification('success', response.message)
        // await clearCachePengajuanBerkas()
        showModal.value = false
        await refreshDaftarPengajuanBerkas();
        pendingUpdateId.value = null
    } catch (error: any) {
        addNotification('error', error)
        pendingUpdateId.value = null
    }
}

const suratData = ref({
    nomorSurat: '123/PDAM-SMG/III/2024',
    sifat: 'Penting',
    lampiran: '1 (Satu) Berkas',
    hal: 'Surat Keterangan Penerimaan',
    tanggal: 'Semarang, 11 Maret 2024',
    mahasiswa: [
        { nama: 'John Doex', nim: '123456789', prodi: 'Teknik Informatika' },
        { nama: 'Jane Smith', nim: '987654321', prodi: 'Manajemen' },
    ],
});


const surat_terima = computed(() => `
  <div style="font-family: Times New Roman,Times, serif; line-height: 1.6;">
    <!-- KOP SURAT -->
    <div style="text-align: center; border-bottom: 3px solid black; padding-bottom: 10px; margin-bottom: 20px;">
      <img src='https://upload.wikimedia.org/wikipedia/commons/f/f2/Lambang_Kota_Semarang.png' alt='Logo' style='float: left; width: 80px; height: auto; margin-right: 10px;'>
      <div style="display: inline-block;">
      <p style="font-size: 16px; margin: 0; font-weight: bold;">PEMERINTAH KOTA SEMARANG</p>
      <p style="font-size: 18px; margin: 0; font-weight: bold;">PERUSAHAAN UMUM DAERAH AIR MINUM</p>
      <p style="font-size: 18px; margin: 0; font-weight: bold;">"TIRTA MOEDAL"</p>
      <p style="font-size: 14px; margin: 0;">Alamat: Jl. Kelud Raya Semarang, Kode Pos : 50237</p>
      <p style="font-size: 14px; margin: 0;">Telp. (024) 8315514 Fax. 8314078 Email: pdam@pdamkotasmg.co.id</p>
      </div>
      <img src='http://localhost:3000/_ipx/w_100&f_webp/images/logo-web-pdam.png' alt='Logo' style='float: right; width: 80px; height: auto; margin-right: 10px;'>
    </div>

    <!-- JUDUL SURAT -->
    <p style="text-align: center; font-weight: bold; font-size: 18px; text-decoration: underline; margin-bottom: 20px;">
      SURAT KETERANGAN PENERIMAAN
    </p> 

    <div style="display: flex; justify-content: space-between; align-items:start ;margin-bottom: 20px;">
    <!-- Identitas Surat -->
    <table style="width: 60%;">
        <tr><td style="width: 25%;">Nomor</td><td style="width: 2%;">:</td><td>${suratData.value.nomorSurat}</td></tr>
        <tr><td>Sifat</td><td>:</td><td>${suratData.value.sifat}</td></tr>
        <tr><td>Lampiran</td><td>:</td><td>${suratData.value.lampiran}</td></tr>
        <tr><td>Hal</td><td>:</td><td>${suratData.value.hal}</td></tr>
    </table>

    <!-- Tanggal dan Tujuan Surat -->
    <div style="width: 35%; text-align: left;">
        <p>${suratData.value.tanggal}</p>
        <p style="margin-top: 30px">Kepada Yth,</p>
        <p style="margin: 0; font-weight: bold;">Bapak/Ibu Penerima</p>
        <p style="margin: 0;">Di Tempat</p>
    </div>
    </div>

    <!-- ISI SURAT -->
    <div style="margin-bottom: 20px">
      <p>Dengan hormat,</p>
      <p>Sehubungan dengan:</p>
      <ol style="padding-left: 20px; list-style: decimal; text-align: justify;">
        <li style="margin-bottom: 10px;">
          Memperhatikan Surat dari <strong>Ketua Lembaga Pengembangan Profesi Pusat Karir, PPL, dan Pemagangan Universitas PGRI Semarang</strong> Nomor: <strong>579/PKP2/UPGRIS/X/2024</strong> tanggal <strong>18 Oktober 2024</strong> perihal permohonan izin magang.
        </li>
        <li style="margin-bottom: 10px;">
          Sehubungan dengan hal tersebut di atas, bersama ini dapat kami sampaikan bahwa pada prinsipnya <strong>PERUMDA Air Minum Tirta Moedal Kota Semarang</strong> dapat menerima mahasiswa Saudara untuk melakukan magang dengan alokasi waktu mulai tanggal <strong>23 Januari 2025</strong> sampai <strong>16 April 2025</strong>, dengan rincian data mahasiswa sebagai berikut:
          <table border="1" cellspacing="0" cellpadding="5" style="width: 100%; border-collapse: collapse; margin: 10px 0;">
            <thead>
              <tr style="text-align: center;">
                <th style="width: 5%; border: 1px solid black;">No</th>
                <th style="border: 1px solid black;">Nama</th>
                <th style="border: 1px solid black;">NIM</th>
                <th style="border: 1px solid black;">Program Studi</th>
              </tr>
            </thead>
            <tbody>
              ${suratData.value.mahasiswa.map((m, index) => `
                <tr>
                  <td style="text-align: center; border: 1px solid black;">${index + 1}</td>
                  <td style="border: 1px solid black;">${m.nama}</td>
                  <td style="border: 1px solid black;">${m.nim}</td>
                  <td style="border: 1px solid black;">${m.prodi}</td>
                </tr>`).join('')}
            </tbody>
          </table>
        </li>
      </ol>
      <p>Demikian informasi ini kami sampaikan. Atas perhatian dan kerjasamanya, kami ucapkan terima kasih.</p>
    </div>

    <!-- PENUTUP -->
    <div style="width: 50%; text-align: center; float: right;">
      <p style="margin-bottom: 0;">An. Direksi Perusahaan Umum Daerah Air Minum</p>
      <p style="margin: 0;">Tirta Moedal Kota Semarang</p>
      <p style="margin-bottom: 0; font-weight: bold;">Direktur Umum</p>
      <p style="margin-bottom: 0;">Ub.</p>
      <p style="margin-bottom: 60px;">Kepala Bagian Kepegawaian</p>
      <p style="margin: 0; font-weight: bold; text-decoration: underline;">Sundariyah, S.E.</p>
      <p style="margin: 0;">Staf Madya I</p>
      <p style="margin: 0;">NPP. 6908384</p>
    </div>
    <div style="clear: both;"></div>
  </div>
`);
const showModal = ref(false)
const form = reactive({
    user_id: '',
    nama: '',
    jurusan: '',
    program_studi_universitas: '',
    nisn_npm_nim_npp: '',
    tanggalSurat: '',
    nomor_surat: '',
    sifat: '',
    lampiran: '',
    kepada: '',
    alamat_kepada: '',
    tanggal_kepada: '',
    tanggalMulai: '',
    tanggalSelesai: '',
})

const isiForm = (data: any) => {
    form.user_id = data.user?.id
    form.nama = `${data.user?.nama_depan || ''} ${data.user?.nama_belakang || ''}`
    form.jurusan = data.master_sekolah?.jurusan || ''
    form.program_studi_universitas = data.master_sekolah?.program_studi_universitas || ''
    form.nisn_npm_nim_npp = data.user?.nisn_npm_nim_npp || ''
    form.tanggalSurat = FormatDate(new Date())
    form.tanggalMulai = FormatDate(data.tanggal_mulai)
    form.tanggalSelesai = FormatDate(data.tanggal_selesai)
    showModal.value = true
}
const resetFormSuratTerima = () => {
    form.user_id = ''
    form.nama = ''
    form.jurusan = ''
    form.program_studi_universitas = ''
    form.nisn_npm_nim_npp = ''
    form.tanggalSurat = ''
    form.nomor_surat = ''
    form.sifat = ''
    form.lampiran = ''
    form.kepada = ''
    form.alamat_kepada = ''
    form.tanggal_kepada = ''
    form.tanggalMulai = ''
    form.tanggalSelesai = ''
}
const formattedTanggalKepada = computed(() => {
  if (!form.tanggal_kepada) return '-'
  const date = new Date(form.tanggal_kepada)
  return new Intl.DateTimeFormat('id-ID', { day: 'numeric', month: 'long', year: 'numeric' }).format(date)
})
</script>

<template>
    <NuxtLayout>
        <BaseBreadcrumb :items="breadcrumb" />
        <div>
            <section class="mb-3">
                <div v-if="pendingDaftarPengajuanBerkas" class="relative w-full h-[2px] bg-gray-200 overflow-hidden">
                    <div class="absolute h-full bg-gradient-to-r from-green-400 to-green-600 animate-loading"></div>
                </div>
                <div v-else class="px-3 m-3">
                    <div class="flex flex-row justify-between items-center mb-2">
                        <!-- <button @click="clearCachePengajuanBerkas()" class="ms-auto">
                                <Icon name="material-symbols-light:directory-sync" 
                                :class="{'animate-spin' : pendingClearCache}"
                                class=" w-5 h-5 inline-block align-middle transition-transform duration-300 ease-in-out"/>
                            </button> -->
                    </div>
                    <div v-if="!daftarPengajuanBerkas?.data?.data || daftarPengajuanBerkas.data.data.length === 0"
                        class="text-center text-gray-500 p-4 border border-gray-200 rounded-lg">
                        Tidak ada data.
                    </div>
                    <div v-else>
                        <div class="relative overflow-x-auto shadow-md sm:rounded-lg">
                            <table class="w-full text-sm text-left rtl:text-right text-gray-500 dark:text-gray-400">
                                <thead
                                    class="text-xs text-gray-700 uppercase bg-gray-50 dark:bg-gray-700 dark:text-gray-400">
                                    <tr>
                                        <th scope="col" class="px-6 py-3">
                                            Kirim surat balasan
                                        </th>
                                        <th scope="col" class="px-6 py-3">
                                            Identitas
                                        </th>
                                        <th scope="col" class="px-6 py-3">
                                            Sekolah/Universitas
                                        </th>
                                        <th scope="col" class="px-6 py-3">
                                            Foto Identitas
                                        </th>
                                        <th scope="col" class="px-6 py-3">
                                            Surat Permohonan
                                        </th>
                                        <th scope="col" class="px-6 py-3">
                                            Tanggal Mulai sampai Selesai
                                        </th>
                                        <th scope="col" class="px-6 py-3 text-center">
                                            Status Berkas
                                        </th>
                                    </tr>
                                </thead>
                                <tbody>

                                    <tr v-for="daftarPengajuanBerkas in daftarPengajuanBerkas.data.data"
                                        :key="daftarPengajuanBerkas.id"
                                        class="bg-white border-b dark:bg-gray-800 dark:border-gray-700 border-gray-200 hover:bg-gray-50 dark:hover:bg-gray-600">
                                        <td class="px-6 py-4">
                                            <button
                                                v-if="daftarPengajuanBerkas.status_berkas === 'approved' || daftarPengajuanBerkas.status_berkas === 'terima'"
                                                @click="isiForm(daftarPengajuanBerkas)"
                                                class="px-3 py-1 text-xs w-fit font-semibold rounded-full flex items-center gap-2 bg-blue-100 border border-blue-200 text-blue-800 dark:bg-blue-900 dark:text-blue-300">
                                                Kirim
                                            </button>
                                            <button v-else disabled
                                                class="whitespace-nowrap px-3 py-1 text-xs w-fit font-semibold rounded-full flex items-center gap-2 bg-gray-100 border border-gray-200 text-gray-800 dark:bg-gray-900 dark:text-gray-300">
                                                Berkas belum disetujui
                                            </button>

                                        </td>
                                        <th scope="row"
                                            class="px-6 py-2 font-medium text-gray-900 whitespace-nowrap dark:text-white">
                                            <div class="flex flex-col">
                                                <p class="text-base  text-gray-500 dark:text-white">
                                                    {{ daftarPengajuanBerkas.user?.nama_depan }} {{
                                                    daftarPengajuanBerkas.user?.nama_belakang }}
                                                </p>
                                                <p class="text-sm text-gray-500 dark:text-gray-400">
                                                    {{ daftarPengajuanBerkas.user?.email }}
                                                </p>
                                            </div>
                                        </th>
                                        <td
                                            class="px-6 py-2 font-medium text-gray-900 whitespace-nowrap dark:text-white">
                                            <div class="flex flex-col">
                                                <p class="text-base  text-gray-500 dark:text-white">
                                                    {{ daftarPengajuanBerkas.master_sekolah?.nama_sekolah_universitas }}
                                                </p>
                                                <p class="text-sm text-gray-500 dark:text-gray-400">
                                                    {{ daftarPengajuanBerkas.master_sekolah?.email_sekolah_universitas
                                                    }}
                                                </p>
                                            </div>
                                        </td>
                                        <td class="px-6 py-4">
                                            <a :href="`${config.public.storage}/storage/${daftarPengajuanBerkas.foto_identitas}`"
                                                target="_blank" rel="noopener noreferrer"
                                                class="px-3 py-1 text-xs w-fit font-semibold rounded-full flex items-center gap-2 bg-yellow-100 border border-yellow-200 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-300">
                                                <Icon name="material-symbols:open-in-new"
                                                    class="w-4 h-4 inline-block align-middle" />
                                            </a>
                                        </td>
                                        <td class="px-6 py-4">
                                            <a :href="`${config.public.storage}/storage/${daftarPengajuanBerkas.surat_permohonan}`"
                                                target="_blank" rel="noopener noreferrer"
                                                class="px-3 py-1 text-xs w-fit font-semibold rounded-full flex items-center gap-2 bg-yellow-100 border border-yellow-200 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-300">
                                                <Icon name="material-symbols:open-in-new"
                                                    class="w-4 h-4 inline-block align-middle" />
                                            </a>

                                        </td>
                                        <td class="px-6 py-4">
                                            <p class="whitespace-nowrap">{{
                                                FormatDate(daftarPengajuanBerkas.tanggal_mulai) }} <br>
                                                {{ FormatDate(daftarPengajuanBerkas.tanggal_selesai) }}</p>
                                        </td>
                                        <td class="px-6 py-4">
                                            <!-- <div>
                                                tampilkan kirim surat jika status terima dan sudah mengisi
                                            </div> -->
                                            <!-- {{  daftarPengajuanBerkas.status_berkas != 'terima'  }} {{ daftarPengajuanBerkas.mentor_id  }} -->
                                            <div class="flex flex-col flex-nowrap space-x-2 items-center gap-2">
                                                <div class="flex flex-row flex-nowrap space-x-2">
                                                    <span
                                                        class="w-fit px-3 py-1 text-xs font-semibold rounded-full flex items-center"
                                                        :class="{
                                                            'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-300': daftarPengajuanBerkas.status_berkas === 'terima',
                                                            'bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-300': daftarPengajuanBerkas.status_berkas === 'pending',
                                                            'bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-300': daftarPengajuanBerkas.status_berkas === 'tolak',
                                                        }">
                                                        <Icon :name="getIconName(daftarPengajuanBerkas.status_berkas)"
                                                            class="w-4 h-4 inline-block align-middle" />
                                                        {{ getStatusLabel(daftarPengajuanBerkas.status_berkas) }}
                                                    </span>
                                                    <span v-if="!daftarPengajuanBerkas.mentor_id">Belum Memilih
                                                        Mentor</span>
                                                </div>
                                                <Form @submit="updatePengajuanBerkasMagang"
                                                    :validation-schema="updateBerkasSchema"
                                                    class="flex flex-row flex-nowrap space-x-2 border border-gray-200 py-1 px-2">

                                                    <div>
                                                        <Field as="select" name="status_berkas" id="status_berkas"
                                                            class="px-3 py-1 text-xs font-medium rounded-lg transition-all duration-300 bg-gray-50 dark:bg-gray-800 border border-gray-300 dark:border-gray-700 text-gray-900 dark:text-gray-200"
                                                            :value="daftarPengajuanBerkas.status_berkas || ''">
                                                            >
                                                            <option disabled value="">Pilih Status</option>
                                                            <option value="pending"
                                                                :selected="daftarPengajuanBerkas.status_berkas === 'pending'">
                                                                Menunggu</option>
                                                            <option value="terima"
                                                                :selected="daftarPengajuanBerkas.status_berkas === 'terima'">
                                                                Terima</option>
                                                            <option value="tolak"
                                                                :selected="daftarPengajuanBerkas.status_berkas === 'tolak'">
                                                                Tolak</option>
                                                        </Field>
                                                        <!-- <ErrorMessage name="status_berkas" class="text-red-500 text-xs"  /> -->

                                                    </div>


                                                    <div>

                                                        <Field as="select" name="mentor_id" id="mentor_id"
                                                            v-model="daftarPengajuanBerkas.mentor_id"
                                                            class="px-3 py-1 text-xs font-medium rounded-lg transition-all duration-300 bg-gray-50 dark:bg-gray-800 border border-gray-300 dark:border-gray-700 text-gray-900 dark:text-gray-200">
                                                            >
                                                            <option disabled value="">Pilih Mentor</option>
                                                            <option v-if="pending">Memuat...</option>
                                                            <option v-for="mentor in mentors.data.data" :key="mentor.id"
                                                                :id="mentor.id" :value="mentor.id"
                                                                :selected="daftarPengajuanBerkas.mentor_id === mentor.id">
                                                                {{ mentor.nama_depan }} {{ mentor.nama_belakang }} -
                                                                {{ mentor.bagian }}
                                                            </option>
                                                            <option value="">Tidak Ada</option>
                                                        </Field>
                                                        <!-- <ErrorMessage name="mentor_id" class="text-red-500 text-xs"  />  -->
                                                    </div>
                                                    <Field name="user_id" type="hidden" id="user_id"
                                                        :value="daftarPengajuanBerkas.user_id" />
                                                    <button
                                                        class="text-white bg-blue-700 hover:bg-blue-800 focus:ring-4 focus:outline-none focus:ring-blue-300 font-medium rounded-lg text-xs w-full sm:w-auto px-3 py-1 text-center dark:bg-blue-600 dark:hover:bg-blue-700 dark:focus:ring-blue-800">
                                                        <Icon v-if="pendingUpdateId === daftarPengajuanBerkas.user_id"
                                                            name="codex:loader" class="text-xl align-middle" />
                                                        <span v-else>Simpan</span>
                                                    </button>
                                                </Form>
                                            </div>

                                            <ErrorMessage name="status_berkas" class="text-red-500 text-xs" />
                                            <ErrorMessage name="mentor_id" class="text-red-500 text-xs" />
                                        </td>


                                    </tr>

                                </tbody>
                            </table>
                        </div>

                        <nav class="flex items-center flex-column flex-wrap md:flex-row justify-between pt-4 px-6 py-3"
                            aria-label="Table navigation">
                            <!-- Informasi jumlah data -->
                            <span
                                class="text-sm font-normal text-gray-500 dark:text-gray-400 mb-4 md:mb-0 block w-full md:inline md:w-auto">
                                Menampilkan
                                <span class="font-semibold text-gray-900 dark:text-white">{{
                                    daftarPengajuanBerkas.data.from }}</span> -
                                <span class="font-semibold text-gray-900 dark:text-white">{{
                                    daftarPengajuanBerkas.data.to }}</span> dari
                                <span class="font-semibold text-gray-900 dark:text-white">{{
                                    daftarPengajuanBerkas.data.total }}</span>
                            </span>

                            <!-- Navigasi Pagination -->
                            <ul class="inline-flex -space-x-px rtl:space-x-reverse text-sm h-8">
                                <!-- Tombol Previous -->
                                <li>
                                    <button @click="goToPage(currentPage - 1)" :disabled="currentPage <= 1"
                                        class="flex items-center justify-center px-3 h-8 ms-0 leading-tight border rounded-s-lg transition"
                                        :class="currentPage > 1 ?
                                            'text-gray-500 bg-white border-gray-300 hover:bg-gray-100 hover:text-gray-700 dark:bg-gray-800 dark:border-gray-700 dark:text-gray-400 dark:hover:bg-gray-700 dark:hover:text-white'
                                            : 'bg-gray-300 text-gray-500 cursor-not-allowed'">
                                        Previous
                                    </button>
                                </li>

                                <!-- Nomor Halaman -->
                                <li v-for="link in daftarPengajuanBerkas.data.links" :key="link.label">
                                    <button v-if="link.url && !isNaN(parseInt(link.label))"
                                        @click="goToPage(getPageNumber(link.url))"
                                        class="flex items-center justify-center px-3 h-8 leading-tight border transition"
                                        :class="link.active ?
                                            'text-white bg-blue-600'
                                            : 'text-gray-500 bg-white border-gray-300 hover:bg-gray-100 hover:text-gray-700 dark:bg-gray-800 dark:border-gray-700 dark:text-gray-400 dark:hover:bg-gray-700 dark:hover:text-white'">
                                        {{ link.label }}
                                    </button>
                                </li>
                                <!-- Tombol Next -->
                                <li>
                                    <button @click="goToPage(currentPage + 1)"
                                        :disabled="currentPage >= daftarPengajuanBerkas.data.last_page"
                                        class="flex items-center justify-center px-3 h-8 leading-tight border rounded-e-lg transition"
                                        :class="currentPage < daftarPengajuanBerkas.data.last_page ?
                                            'text-gray-500 bg-white border-gray-300 hover:bg-gray-100 hover:text-gray-700 dark:bg-gray-800 dark:border-gray-700 dark:text-gray-400 dark:hover:bg-gray-700 dark:hover:text-white'
                                            : 'bg-gray-300 text-gray-500 cursor-not-allowed'">
                                        Next
                                    </button>
                                </li>
                            </ul>
                        </nav>

                        <div v-if="showModal"
                            class="fixed inset-0 flex items-center justify-center bg-black bg-opacity-50 z-20">
                            <div class="bg-white w-[90%] max-h-[90vh] p-4 rounded-lg shadow-lg relative overflow-auto">
                                <button @click="showModal = false"
                                    class="absolute top-2 right-2 text-gray-500 hover:text-gray-700">
                                    ✖
                                </button>

                                <div class="flex flex-col md:flex-row gap-10">
                                    <!-- Form Input -->
                                    <div class="w-full md:w-1/3  space-y-4">
                                        <Form @submit="updateSuratTerimaToggle"
                                            :validation-schema="updateSuratTerimaSchema">
                                            <h2 class="font-bold text-lg">Formulir Data Mahasiswa</h2>
                                            <Field name="user_id" type="hidden" id="user_id" :value="form.user_id" />
                                            <Field name="nama" type="hidden" id="nama" :value="form.nama" />
                                            <Field name="jurusan" type="hidden" id="jurusan" :value="form.jurusan" />
                                            <Field name="program_studi_universitas" type="hidden"
                                                id="program_studi_universitas"
                                                :value="form.program_studi_universitas" />
                                            <Field name="nisn_npm_nim_npp" type="hidden" id="nisn_npm_nim_npp"
                                                :value="form.nisn_npm_nim_npp" />
                                            <Field name="tanggalSurat" type="hidden" id="tanggalSurat"
                                                :value="form.tanggalSurat" />
                                            <Field name="tanggalMulai" type="hidden" id="tanggalMulai"
                                                :value="form.tanggalMulai" />
                                            <Field name="tanggalSelesai" type="hidden" id="tanggalSelesai"
                                                :value="form.tanggalSelesai" />
                                            <div>
                                                <BaseInput label="Nomor Surat" v-model="form.nomor_surat"
                                                    name="nomor_surat" type="text" :errors="errors" />
                                            </div>
                                            <div>
                                                <BaseSelect label="Sifat" name="sifat" v-model="form.sifat"
                                                :options="[
                                                    { value: 'Penting', text: 'Penting' },
                                                    { value: 'Biasa', text: 'Biasa' },
                                                    { value: 'Rahasia', text: 'Rahasia' },
                                                    { value: '', text: 'Tidak ada' }
                                                ]" required />   
                                            </div>

                                            <div>
                                                <BaseInput label="Lampiran" v-model="form.lampiran" name="lampiran"
                                                    type="text" :errors="errors" />
                                            </div>
                                            <div>
                                                <BaseInput label="Kepada" v-model="form.kepada" name="kepada"
                                                    type="text" :errors="errors" />
                                            </div>
                                            <div>
                                                <BaseInput label="Alamat Kepada" v-model="form.alamat_kepada"
                                                    name="alamat_kepada" type="text" :errors="errors" />
                                            </div>
                                            <div>
                                                <BaseInput label="Tanggal Kepada" v-model="form.tanggal_kepada"
                                                    name="tanggal_kepada" type="date" :errors="errors" />
                                            </div>

                                            <div class="flex mt-4">
                                                <div class="ms-auto flex gap-2">
                                                    <button
                                                        class="text-white bg-blue-700 hover:bg-blue-800 focus:ring-4 focus:outline-none focus:ring-blue-300 font-medium rounded-lg text-xs w-full sm:w-auto px-3 py-1 text-center dark:bg-blue-600 dark:hover:bg-blue-700 dark:focus:ring-blue-800">
                                                        <Icon v-if="pendingUpdateId" name="codex:loader"
                                                            class="text-xl align-middle" />
                                                        <span v-else>Simpan</span>
                                                    </button>
                                                </div>
                                            </div>
                                        </Form>
                                    </div>


                                    <div class="w-full overflow-x-auto">
                                        <div class="border p-4 rounded shadow-md overflow-y-auto max-h-[80vh] min-w-[800px] max-w-full mx-auto"
                                            style="font-family: Times New Roman, Times, serif; line-height: 1.6;">
                                            <!-- KOP SURAT -->
                                            <div class="text-center border-b-4 border-black pb-4 mb-4 relative">
                                                <img src="https://upload.wikimedia.org/wikipedia/commons/f/f2/Lambang_Kota_Semarang.png"
                                                    alt="Logo" class="absolute left-0 top-5 w-20" />
                                                <div>
                                                    <p class="text-md font-bold tracking-widest">PEMERINTAH KOTA
                                                        SEMARANG</p>
                                                    <p class="text-2xl font-bold">PERUSAHAAN UMUM DAERAH AIR MINUM</p>
                                                    <p class="text-2xl font-bold">"TIRTA MOEDAL"</p>
                                                    <p class="text-md font-bold leading-tight">Alamat: Jl. Kelud Raya
                                                        Semarang, Kode Pos : 50237
                                                    </p>
                                                    <p class="text-md font-bold leading-tight">Telp. (024) 8315514 Fax.
                                                        8314078 Email:
                                                        pdam@pdamkotasmg.co.id</p>
                                                </div>
                                                <img src="http://localhost:3000/_ipx/w_100&f_webp/images/logo-web-pdam.png"
                                                    alt="Logo" class="absolute right-0 top-5 w-20" />
                                            </div>

                                            <div class="mx-8">


                                                <!-- JUDUL -->
                                                <p class="text-center font-bold underline text-lg mb-4">SURAT KETERANGAN
                                                    PENERIMAAN MAGANG
                                                </p>

                                                <!-- Nomor & Tujuan -->
                                                <div class="flex justify-between mb-8 text-md">
                                                    <!-- Kolom Kiri -->
                                                    <div class="flex flex-col space-y-1 w-1/2 pr-4">
                                                        <div class=" h-[20px]"> </div>
                                                        <div class="flex leading-none">
                                                            <span class="w-24">Nomor</span>
                                                            <span class="w-2">:</span>
                                                            <span class="flex-1">{{ form.nomor_surat || '.......'
                                                                }}</span>
                                                        </div>
                                                        <div class="flex leading-none">
                                                            <span class="w-24">Sifat</span>
                                                            <span class="w-2">:</span>
                                                            <span class="flex-1">{{ form.sifat || '.......' }}</span>
                                                        </div>
                                                        <div class="flex leading-none">
                                                            <span class="w-24">Lampiran</span>
                                                            <span class="w-2">:</span>
                                                            <span class="flex-1">{{ form.lampiran || '.......' }}</span>
                                                        </div>
                                                        <div class="flex leading-none">
                                                            <span class="w-24">Hal</span>
                                                            <span class="w-2">:</span>
                                                            <span class="flex-1">Surat Keterangan Penerimaan</span>
                                                        </div>
                                                    </div>

                                                    <!-- Kolom Kanan -->
                                                    <div
                                                        class="flex flex-col  font-bold w-1/2 pl-4 space-y-1 text-left">
                                                        <p>Semarang, {{ form.tanggalSurat || '.......' }}</p>
                                                        <div class="h-[65px]"></div> <!-- Spasi antar baris -->
                                                        <p>K e p a d a :</p>
                                                        <p>{{ form.kepada || '......' }}</p>
                                                        <p>{{ form.alamat_kepada || '......' }}</p>
                                                        <p>di -</p>
                                                        <p>&nbsp; &nbsp; &nbsp; &nbsp;<span class="underline">S E M A R
                                                                A N G</span></p>

                                                    </div>
                                                </div>


                                                <!-- ISI SURAT -->
                                                <div class="mb-4 text-justify ml-20">
                                                    <!-- <p>Dengan hormat,</p> -->
                                                    <ol class="list-decimal pl-5 space-y-2">
                                                        <li> &nbsp; &nbsp; &nbsp;
                                                            Memperhatikan Surat {{ form.kepada || '......' }}
                                                            Nomor:
                                                            {{ form.nomor_surat || '.......' }} tanggal {{
                                                            formattedTanggalKepada || '.......' }} perihal permohonan
                                                            izin magang.
                                                        </li>
                                                        <li> &nbsp; &nbsp; &nbsp; &nbsp;
                                                            Sehubungan dengan hal tersebut di atas, bersama ini dapat
                                                            kami
                                                            sampaikan bahwa pada prinsipnya
                                                            PERUMDA Air Minum Tirta Moedal Kota
                                                            Semarang
                                                            dapat menerima mahasiswa Saudara untuk melakukan magang
                                                            dengan
                                                            alokasi waktu mulai tanggal
                                                            {{ form.tanggalMulai || '.......' }} sampai {{
                                                            form.tanggalSelesai || '.......' }}, dengan rincian data
                                                            mahasiswa sebagai
                                                            berikut:
                                                            <!-- Tabel Data Mahasiswa -->
                                                            <table
                                                                class="border-collapse  w-full my-4 text-sm border border-black"
                                                                border="1">
                                                                <thead>
                                                                    <tr class="text-center ">
                                                                        <th class="border border-black p-2 uppercase">No
                                                                        </th>
                                                                        <th class="border border-black p-2 uppercase">
                                                                            Nama</th>
                                                                        <th class="border border-black p-2 uppercase">
                                                                            NIM</th>
                                                                        <th class="border border-black p-2 uppercase">
                                                                            Program Studi</th>
                                                                    </tr>
                                                                </thead>
                                                                <tbody>
                                                                    <tr>
                                                                        <td class="border border-black p-2 text-center">
                                                                            1</td>
                                                                        <td class="border border-black p-2">{{ form.nama
                                                                            ||
                                                                            '...........'
                                                                            }}</td>
                                                                        <td class="border border-black p-2 text-center">
                                                                            {{ form.nisn_npm_nim_npp
                                                                            ||
                                                                            '...........' }}</td>
                                                                        <td
                                                                            class="border border-black p-2  text-center">
                                                                            {{
                                                                            form.program_studi_universitas ||
                                                                            '...........'
                                                                            }}</td>
                                                                    </tr>
                                                                </tbody>
                                                            </table>
                                                            Untuk keterangan lebih lanjut dapat menghubungi <span
                                                                class="text-bold underline">Bagian Kepegawaian.</span>
                                                        </li>
                                                        <li> &nbsp; &nbsp; &nbsp;
                                                            Demikian informasi ini kami sampaikan. Atas perhatian dan
                                                            kerjasama
                                                            Bapak/Ibu, kami ucapkan terima kasih.
                                                        </li>
                                                    </ol>

                                                </div>
                                            </div>



                                            <!-- TTD -->
                                            <div class="w-fit text-center float-right mx-8">
                                                <p>An. Direksi Perusahaan Umum Daerah Air Minum</p>
                                                <p class="-mt-2">Tirta Moedal Kota Semarang</p>
                                                <p class="-mt-2">Direktur Umum</p>
                                                <p class="-mt-2">Ub</p>
                                                <p class="mb-12 -mt-2">Kepala Bagian Kepegawaian</p>
                                                <p class="font-bold underline">Sundariyah, S.E.</p>
                                                <p class="-mt-2">Staf Madya I</p>
                                                <p class="-mt-2">NPP. 6908384</p>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </section>
        </div>
    </NuxtLayout>
</template>

<style scoped></style>