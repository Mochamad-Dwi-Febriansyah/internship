<script setup lang="ts">
definePageMeta({
    layout: 'dashboard',
    middleware: 'auth'
})
import { FormatDate } from "~/utils/FormatDate"
const route = useRoute()
const router = useRouter()
const config = useRuntimeConfig()
const laporanHarianId = String(route.params.id)
const breadcrumb = [
    { label: "Laporan Akhir", icon: "material-symbols:checkbook-outline-rounded", to: "/dashboard/user/laporan-akhir" },
    { label: "Detail Laporan Akhir" }
]
const report = ref({
    judul: 'Laporan Akhir Magang',
    status: 'pending',
})
const statusColor = (status: any) => {
    return status === 'approved' ? 'text-green-600' : status === 'pending' ? 'text-yellow-600' : 'text-red-600'
}

const currentPage = ref(Number(route.query.page) || 1);
const { getLaporanAkhirById, pendingLaporanAkhirById } = useLaporanAkhir(currentPage)
const laporanAkhirById = ref()
onMounted(async () => {
    const response = await getLaporanAkhirById(laporanHarianId);
    laporanAkhirById.value = response.data
});
</script>

<template>
    <NuxtLayout>
        <BaseBreadcrumb :items="breadcrumb" />
        <div class="bg-white">
            <section class="mb-3">
                <div v-if="pendingLaporanAkhirById && !laporanAkhirById"
                    class="relative w-full h-[2px] bg-gray-200 overflow-hidden">
                    <div class="absolute h-full bg-gradient-to-r from-green-400 to-green-600 animate-loading"></div>
                </div>
                <div v-else class="p-3">
                    <div class="flex flex-row justify-between items-center mb-2 ">
                        <!-- <h3 class="text-md text-gray-700 font-medium">Detail Laporan Akhir</h3> -->
                        <div class="flex flex-row space-x-3 ms-auto">
                            <button @click="router.back()"
                                class="ms-auto px-3 py-1  text-xs w-fit rounded-full flex items-center gap-2 bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-300 border border-red-300 hover:bg-red-200">
                                <Icon name="material-symbols:arrow-left-alt-rounded"
                                    class="w-4 h-4 inline-block align-middle transition-transform duration-300 ease-in-out" />
                                Kembali
                            </button>
                        </div>
                    </div>
                    <div class="flex lg:flex-row-reverse flex-col-reverse gap-3 flex-wrap lg:flex-nowrap"> 
                        <div v-if="laporanAkhirById"
                            class="flex  flex-row lg:flex-col items-start justify-start flex-wrap gap-3 mb-3 w-full lg:w-1/3 p-6 bg-white  border border-gray-200 rounded-lg shadow-sm dark:bg-gray-800 dark:border-gray-700">
     
                            <ol class="space-y-2 md:w-72 w-full">
                                <p class="font-medium text-sm mb-4 text-gray-700 dark:text-gray-200">Status Pengajuan</p>
    
                                <!-- Verifikasi Mentor -->
                                <li>
                                    <div class="w-full p-2 border rounded-lg dark:bg-gray-800 flex flex-row justify-between"
                                        :class="{
                                            'text-green-700 bg-green-50 border-green-300 dark:border-green-800 dark:text-green-400': laporanAkhirById.status_verifikasi_mentor === 'approved',
                                            'text-red-700 bg-red-50 border-red-300 dark:border-red-800 dark:text-red-400': laporanAkhirById.status_verifikasi_mentor === 'rejected',
                                            'text-yellow-900 bg-yellow-100 border-yellow-300 dark:border-yellow-700 dark:text-yellow-400': laporanAkhirById.status_verifikasi_mentor === 'pending'
                                        }" role="alert">
                                        <div class="flex items-center justify-between gap-2 w-full">
                                            <h3 class="font-medium text-sm">1. Verifikasi Mentor</h3>
                                            <Icon :name="laporanAkhirById.status_verifikasi_mentor === 'pending'
                                                        ? 'material-symbols-light:hourglass-bottom'
                                                        : laporanAkhirById.status_verifikasi_mentor === 'approved'
                                                            ? 'hugeicons:tick-double-03'
                                                            : laporanAkhirById.status_verifikasi_mentor === 'rejected'
                                                                ? 'material-symbols-light:cancel'
                                                                : 'material-symbols-light:help'"
                                                        class="w-4 h-4 inline-block align-middle font-bold ms-auto" /> 
                                        </div>
                                        <!-- <p class="text-sm">
                                            {{ laporanAkhirById.status_verifikasi_mentor === 'approved' ? 'Disetujui' :
                                                laporanAkhirById.status_verifikasi_mentor === 'rejected' ? 'Ditolak' :
                                                    'Pending' }}
                                        </p>  -->
                                    </div>
                                    <p v-if="laporanAkhirById.status_verifikasi_mentor === 'rejected'"
                                        class="text-sm text-red-500">
                                        <b>Alasan:</b> {{ laporanAkhirById.rejection_note_mentor }}
                                    </p>
                                </li>
    
                                <!-- Verifikasi Kepegawaian -->
                                <li>
                                    <div class="w-full p-2 border rounded-lg dark:bg-gray-800 flex flex-row justify-between"
                                        :class="{
                                            'text-green-700 bg-green-50 border-green-300 dark:border-green-800 dark:text-green-400': laporanAkhirById.status_verifikasi_kepegawaian === 'approved',
                                            'text-red-700 bg-red-50 border-red-300 dark:border-red-800 dark:text-red-400': laporanAkhirById.status_verifikasi_kepegawaian === 'rejected',
                                            'text-yellow-900 bg-yellow-100 border-yellow-300 dark:border-yellow-700 dark:text-yellow-400': laporanAkhirById.status_verifikasi_kepegawaian === 'pending'
                                        }" role="alert">
                                        <div class="flex items-center justify-between gap-2 w-full">
                                            <h3 class="font-medium text-sm">2. Verifikasi Kepegawaian</h3>
                                            <Icon :name="laporanAkhirById.status_verifikasi_kepegawaian === 'pending'
                                                        ? 'material-symbols-light:hourglass-bottom'
                                                        : laporanAkhirById.status_verifikasi_kepegawaian === 'approved'
                                                            ? 'hugeicons:tick-double-03'
                                                            : laporanAkhirById.status_verifikasi_kepegawaian === 'rejected'
                                                                ? 'material-symbols-light:cancel'
                                                                : 'material-symbols-light:help'"
                                                        class="w-4 h-4 inline-block align-middle font-bold ms-auto" />  
                                        </div>
                                        <!-- <p class="text-sm">
                                            {{ laporanAkhirById.status_verifikasi_kepegawaian === 'approved' ? 'Disetujui' :
                                                laporanAkhirById.status_verifikasi_kepegawaian === 'rejected' ? 'Ditolak' :
                                                    'Pending' }}
                                        </p>  -->
                                    </div>
                                    <p v-if="laporanAkhirById.status_verifikasi_kepegawaian === 'rejected'"
                                        class="text-sm text-red-500">
                                        <b>Alasan:</b> {{ laporanAkhirById.rejection_note_kepegawaian  }}
                                    </p>
                                </li>
    
                              
    
                            </ol>
    
                            <!-- Riwayat Pengajuan {{ laporanAkhirById.historis }} -->
                            <!-- Riwayat Pengajuan -->
                            <div class="  md:w-72 w-full">
                                <p class="font-medium text-sm mb-4 text-gray-700 dark:text-gray-200">Riwayat Pengajuan</p>
    
                                <!-- Wrapper utama -->
                                <div class="relative p-2 border rounded-lg dark:bg-gray-800">
                                    <!-- Scrollable area -->
                                    <div class="max-h-[200px] overflow-y-auto pl-2 space-y-6 relative">
    
                                        <!-- Jika ada data -->
                                        <div v-if="laporanAkhirById.historis && laporanAkhirById.historis.length > 0"
                                            class="border-l-2 border-blue-500 relative pl-2">
    
                                            <!-- Loop data -->
                                            <div v-for="(item, index) in laporanAkhirById.historis" :key="item.id"
                                                class="relative mb-2">
    
                                                <!-- Bullet -->
                                                <div
                                                    class="absolute w-3 h-3  bg-blue-500 rounded-full top-1 left-[-15px] border-2 border-white dark:border-gray-800">
                                                </div>
    
                                                <!-- Header -->
                                                <div class="flex justify-between items-center">
                                                    <p class="text-sm font-medium text-gray-900 dark:text-gray-100">
                                                        Versi ke-{{ item.version_number }}
                                                    </p>
                                                    <p class="text-xs text-gray-500 dark:text-gray-400">
                                                        {{ FormatDate(item.created_at) }}
                                                    </p>
                                                </div>
    
                                                <!-- Deskripsi -->
                                                <p class="text-sm text-gray-600 dark:text-gray-300">
                                                    Pengajuan versi ke-{{ item.version_number }} telah dikirim.
                                                </p>
    
                                                <!-- Catatan jika ada -->
                                                <p v-if="item.catatan" class="text-xs italic text-red-500 mt-1">
                                                    Catatan: {{ item.catatan }}
                                                </p>
                                            </div>
                                        </div>
    
                                        <!-- Jika kosong -->
                                        <p v-else class="text-gray-500 dark:text-gray-400 py-2">Belum ada riwayat pengajuan.
                                        </p>
                                    </div>
                                </div>
                            </div> 
                        </div>
    
                        <div v-if="laporanAkhirById"
                            class="w-full p-6 bg-white border border-gray-200 rounded-lg shadow-sm dark:bg-gray-800 dark:border-gray-700">
                            <!-- <h2 class="text-lg font-semibold text-gray-700 dark:text-gray-200">Detail Laporan</h2> -->
    
                            <div v-if="report" class="space-y-4">
    
                                <div class="flex flex-col md:flex-row gap-2 md:gap-4 text-sm">
                                    <div class="md:w-1/4 font-medium whitespace-nowrap text-gray-600">Nama</div>
                                    <div class="md:w-3/4 text-gray-500">
                                        <p>{{ laporanAkhirById.user.nama_depan }} {{ laporanAkhirById.user.nama_belakang }}
                                        </p>
                                        <p>{{ laporanAkhirById.user.email }}</p>
                                    </div>
                                </div>
    
                                <div class="flex flex-col md:flex-row gap-2 md:gap-4 text-sm">
                                    <div class="md:w-1/4 font-medium whitespace-nowrap text-gray-700">Universitas/Sekolah</div>
                                    <div class="md:w-3/4 text-gray-500">
                                        <p>{{ laporanAkhirById.master_sekolah.nama_sekolah_universitas }}</p>
                                        <p>{{ laporanAkhirById.master_sekolah.jurusan_sekolah }}</p>
                                        <p>{{ laporanAkhirById.master_sekolah.fakultas_universitas }}</p>
                                        <p>{{ laporanAkhirById.master_sekolah.program_studi_universitas }}</p>
                                    </div>
                                </div>
    
                                <div class="flex flex-col md:flex-row gap-2 md:gap-4 text-sm">
                                    <div class="md:w-1/4 font-medium whitespace-nowrap text-gray-700">Nama Laporan</div>
                                    <div class="md:w-3/4 text-gray-500">
                                        <p>{{ laporanAkhirById.title }}</p>
                                    </div>
                                </div>
    
                                <div class="flex flex-col md:flex-row gap-2 md:gap-4 text-sm">
                                    <div class="md:w-1/4 font-medium whitespace-nowrap text-gray-700">Deskripsi</div>
                                    <div class="md:w-3/4 text-gray-500" v-html="laporanAkhirById.report"></div>
                                </div>
    
                                <div class="flex flex-col md:flex-row gap-2 md:gap-4 text-sm  overflow-hidden">
                                    <div class="md:w-1/4 font-medium whitespace-nowrap text-gray-700">File Penilaian Mentor
                                    </div>
                                    <div v-if="laporanAkhirById.assessment_report_file"
                                        class="md:w-3/4 text-gray-500 line-clamp-1">
                                        <a :href="`${config.public.storage}/storage/${laporanAkhirById.assessment_report_file}`"
                                            target="_blank" class="text-blue-500 underline">
                                            File Penilaian Mentor
                                        </a>
                                    </div>
                                </div>
    
                                <div class="flex flex-col md:flex-row gap-2 md:gap-4 text-sm  overflow-hidden">
                                    <div class="md:w-1/4 font-medium whitespace-nowrap text-gray-700">File Laporan Akhir</div>
                                    <div v-if="laporanAkhirById.final_report_file"
                                        class="md:w-3/4 text-gray-500 line-clamp-1">
                                        <a :href="`${config.public.storage}/storage/${laporanAkhirById.final_report_file}`"
                                            target="_blank" class="text-blue-500 underline">
                                            File Laporan Akhir
                                        </a>
                                    </div>
                                </div>
    
                                <div class="flex flex-col md:flex-row gap-2 md:gap-4 text-sm">
                                    <div class="md:w-1/4 font-medium whitespace-nowrap text-gray-700">Photo</div>
                                    <div v-if="laporanAkhirById.photo" class="md:w-3/4 text-gray-500 line-clamp-1">
                                        <a :href="`${config.public.storage}/storage/${laporanAkhirById.photo}`"
                                            target="_blank" class="text-blue-500 underline">
                                            File Photo
                                        </a>
                                    </div>
                                </div>
    
                                <div class="flex flex-col md:flex-row gap-2 md:gap-4 text-sm">
                                    <div class="md:w-1/4 font-medium whitespace-nowrap text-gray-700">Video</div>
                                    <div class="md:w-3/4 text-gray-500"><a :href="`${laporanAkhirById.video}`"
                                            target="_blank" class="text-blue-500 underline">Link Video</a></div>
                                </div>
    
                                <div class="flex flex-col md:flex-row gap-2 md:gap-4 text-sm">
                                    <div class="md:w-1/4 font-medium whitespace-nowrap text-gray-700">Sertifikat</div>
                                    <div v-if="laporanAkhirById.certificate"
                                        class="md:w-3/4 text-gray-500 line-clamp-1">
                                        <a :href="`${config.public.storage}/storage/${laporanAkhirById.certificate}`"
                                            target="_blank" class="text-blue-500 underline">
                                            File Sertifikat
                                        </a>
                                    </div>
                                </div>
                            </div>
    
    
    
                            <div v-else class="text-gray-500 text-center py-4">Tidak ada data laporan.</div>
                        </div>
                    </div>
                </div>
            </section>
        </div>
    </NuxtLayout>
</template>