<script setup lang="ts">
definePageMeta({
    layout: 'dashboard',
    middleware: 'auth'
})
import * as yup from "yup"
import type { SubmissionHandler } from 'vee-validate'
import { getIconName, getStatusLabel  } from "~/utils/FormatStatus" 
const { addNotification } = useNotification()
const route = useRoute()
const router = useRouter()
const currentPage = ref(Number(route.query.page) || 1);
const { laporanHarianByMentor, pendingLaporanHarianByMentor, refreshlaporanHarianByMentor, pendingClearCache, clearCachelaporanHarianByMentor, updateValidasiLaporanByMentor , errors} = useLaporanHarianByMentor(currentPage)
 


const breadcrumb = [ 
    { label: "Verifikasi Laporan Harian", icon: "mdi:file-document-alert-outline",to: "/dashboard/mentor/magang/laporan-harian"  }
]
function goToPage(page: number) {
    if (page < 1 || page > (laporanHarianByMentor.value?.data.last_page ?? 1)) return;
    currentPage.value = page;
    router.push({ query: { ...route.query, page } });
}

function getPageNumber(url: string | null | undefined): number {
    const match = url?.match(/page=(\d+)/);
    return match?.[1] ? parseInt(match[1], 10) : 1;
}
const formatTanggal = (tanggal: any) => {
    return new Date(tanggal).toLocaleDateString('id-ID', {
        weekday: 'long',  // Nama hari (Senin, Selasa, dst)
        day: '2-digit',   // Tanggal (01, 02, dst)
        month: 'short',   // Nama bulan singkat (Jan, Feb, dst)
        year: 'numeric'   // Tahun (2024, 2025, dst)
    });
};
 
const pendingUpdateId = ref(null)
const validasiLaporanSchema = yup.object({
    status: yup.string().required('Status  wajib diisi'),
})
const updateValidasiLaporan: SubmissionHandler<any> = async (value, { resetForm }: { resetForm: () => void }) => {
    const payload = {
        status: value.status, // Menggunakan status dari form
        ids: Array.isArray(value.id) ? value.id : [value.id], // Menggunakan ID laporan yang dipilih //tetap parsing ke ids karena regulasi backend
    };
    try {
        // console.log(value)
        pendingUpdateId.value = value.id
        const response = await updateValidasiLaporanByMentor(payload)
        resetForm();
        addNotification('success', response.message)
        await clearCachelaporanHarianByMentor()
        pendingUpdateId.value = null
    } catch (error: any) {
        addNotification('error', error.data.message)
        pendingUpdateId.value = null
    }
}

// State untuk menyimpan status terbuka/tutup setiap item accordion
const activeAccordion = ref(null);

// Fungsi untuk mengontrol buka/tutup accordion
const toggleAccordion = (index: any) => {
    activeAccordion.value = activeAccordion.value === index ? null : index;
};

const selectedReports = ref<number[]>([]);
const selectAll = ref<Record<string, boolean>>({});

const toggleSelectAll = ($userId: any) => {
    if (!$userId) return; // Hindari error jika user ID tidak ada

    if (!selectAll.value[$userId]) {
        selectAll.value[$userId] = false; // Inisialisasi jika belum ada
    }
    // console.log("IDsr:", $userId);
    // console.log("Data laporan sebelum filter:", laporanHarianByMentor.value.data.data);
    if (selectAll.value) {
        selectedReports.value = laporanHarianByMentor.value.data.data // Ambil array data laporan
            .filter((item: any) => item.user?.id === $userId) // Filter berdasarkan user ID
            .flatMap((item: any) => item.laporan?.map((lap: any) => lap.id) || []); // Ambil semua ID laporan
    } else {
        selectedReports.value = [];
    }
    console.log(selectedReports.value)
};
const validasiLaporanAllSchema = yup.object({
    status: yup.string().required('Status  wajib diisi'),
})
const pendingUpdateAll = ref(false)
const updateValidasiLaporanAll: SubmissionHandler<any> = async (value, { resetForm }: { resetForm: () => void }) => {
    if (selectedReports.value.length === 0) {
        addNotification('error', "Tidak ada laporan yang dipilih!") 
        return;
    }
    const payload = {
        status: value.status, // Menggunakan status dari form
        ids: selectedReports.value, // Menggunakan ID laporan yang dipilih
    };
 

    try {
        pendingUpdateAll.value = true
        const response = await updateValidasiLaporanByMentor(payload);
        // console.log("Response API:", response); 
        resetForm();
        addNotification('success', response.message)
        await clearCachelaporanHarianByMentor()
        selectedReports.value = []; 
    } catch (error: any) { 
        addNotification('error', error.data.message) 
    } finally {
        pendingUpdateAll.value = false
    }
}; 

</script>

<template>
    <NuxtLayout>
        <BaseBreadcrumb :items="breadcrumb" />
        <div class="bg-gray-50">
            <section class="mb-3">
                <div v-if="pendingLaporanHarianByMentor" class="relative w-full h-[2px] bg-gray-200 overflow-hidden">
                    <div class="absolute h-full bg-gradient-to-r from-green-400 to-green-600 animate-loading"></div>
                </div>
                <div v-else class="p-3">
                    <div class="flex flex-row justify-between items-center mb-2">
                        <!-- <h3 class="text-md text-gray-700 font-medium">Verifikasi Laporan Harian</h3> -->
                        <!-- <button @click="clearCachelaporanHarianByMentor()" class="ms-auto">
                            <Icon name="material-symbols-light:directory-sync"
                                :class="{ 'animate-spin': pendingClearCache }"
                                class=" w-5 h-5 inline-block align-middle transition-transform duration-300 ease-in-out" />
                        </button> -->
                    </div>
                    <div v-if="!laporanHarianByMentor?.data?.data || laporanHarianByMentor.data.data.length === 0"
                        class="text-center text-gray-500 p-4 border border-gray-200 rounded-lg">
                        Tidak ada data.
                    </div>
                    <div v-else>
                        <div class="relative overflow-x-auto shadow-md sm:rounded-lg">

                            <div id="accordion"><Icon name="mdi:hourglass" />
                                <div v-for="userData in laporanHarianByMentor.data.data" :key="userData.id">
                                    <!-- Button untuk membuka/menutup accordion -->
                                    <h2 :id="`accordion-heading-${userData.user?.id}`">
                                        <button type="button"
                                            class="flex items-center justify-between w-full p-5 font-medium text-gray-500 border border-gray-200 rounded-t-xl focus:ring-4 focus:ring-gray-200 dark:focus:ring-gray-800 dark:border-gray-700 dark:text-gray-400 hover:bg-gray-100 dark:hover:bg-gray-800 gap-3"
                                            @click="toggleAccordion(userData.user?.id)">
                                            <span>{{ userData.user?.nama }}</span>
                                            <svg :class="{ 'rotate-180': activeAccordion === userData.user?.id }"
                                                class="w-3 h-3 transition-transform" aria-hidden="true"
                                                xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 10 6">
                                                <path stroke="currentColor" stroke-linecap="round"
                                                    stroke-linejoin="round" stroke-width="2" d="M9 5 5 1 1 5" />
                                            </svg>
                                        </button>
                                    </h2>
                                    <!-- Konten accordion -->
                                    <div v-show="activeAccordion === userData.user?.id"
                                        class="p-5 border overflow-x-auto border-gray-200 dark:border-gray-700 dark:bg-gray-900">
                                        <div class="flex flex-row space-x-2 justify-end mb-2">
                                            <div class="flex flex-row border items-center border-gray-200 py-1 px-2 space-x-2"> 
                                                <input type="checkbox" v-model="selectAll[userData.user?.id || 'default']"
                                                    @change="toggleSelectAll(userData.user?.id)"
                                                    :disabled="!userData.user?.id" />
                                                <span class="text-xs font-medium whispace-nowrap">Pilih Semua</span>
                                                <Form @submit="updateValidasiLaporanAll"
                                                    :validation-schema="validasiLaporanAllSchema"
                                                    class="flex flex-row flex-nowrap space-x-2 ">
                                                    <div>
                                                        <Field as="select" name="status" id="status"
                                                            class="px-3 py-1 text-xs font-medium rounded-lg transition-all duration-300 bg-gray-50 dark:bg-gray-800 border border-gray-300 dark:border-gray-700 text-gray-900 dark:text-gray-200">
                                                            >
                                                            <option disabled value="">Pilih Status</option>
                                                            <option value="pending">Tunggu</option>
                                                            <option value="approved">Terima</option>
                                                            <option value="rejected">Tolak</option>
                                                        </Field>
                                                        <ErrorMessage name="status" class="text-red-500 text-xs" /> 
                                                        <p v-if="errors.status" class="text-red-500 text-sm">{{ errors.status[0] }}</p>
                                                    </div> 
                                                    <button
                                                        class="text-white bg-blue-700 hover:bg-blue-800 focus:ring-4 focus:outline-none focus:ring-blue-300 font-medium rounded-lg text-xs w-full sm:w-auto px-3 py-1 text-center dark:bg-blue-600 dark:hover:bg-blue-700 dark:focus:ring-blue-800">
                                                        <Icon v-if="pendingUpdateAll"
                                                            name="codex:loader" class="text-xl align-middle" />
                                                        <span v-else>Simpan</span>
                                                    </button>
                                                </Form>
                                            </div>
                                        </div>
                                        <table
                                            class="w-full text-sm text-left rtl:text-right text-gray-500 dark:text-gray-400">
                                            <thead
                                                class="text-xs text-gray-700 uppercase bg-gray-50 dark:bg-gray-700 dark:text-gray-400">
                                                <tr>
                                                    <th scope="col" class="px-6 py-3">
                                                        Tanggal Presensi
                                                    </th>
                                                    <th scope="col" class="px-6 py-3 ">
                                                        Nama Kegiatan
                                                    </th>
                                                    <th scope="col" class="px-6 py-3 ">
                                                        Deskripsi Kegiatan
                                                    </th>
                                                    <th scope="col" class="px-6 py-3 ">
                                                        Hasil yang dicapai
                                                    </th>
                                                    <th scope="col" class="px-6 py-3 text-center">
                                                        Status Laporan
                                                    </th>  
                                                </tr>
                                            </thead>
                                            <tbody>

                                                <tr v-for="data in userData.laporan" :key="data.id"
                                                    class="bg-white border-b dark:bg-gray-800 dark:border-gray-700 border-gray-200 hover:bg-gray-50 dark:hover:bg-gray-600">
                                                    <td class="px-6 py-5">
                                                        {{ formatTanggal(data.tanggal_presensi) }}
                                                    </td>
                                                    <td class="px-6 py-5">
                                                        {{ data.title }}
                                                    </td>
                                                    <td class="px-6 py-5" v-html="data.report">
                                                    </td>
                                                    <td class="px-6 py-5" v-html="data.result">
                                                    </td>
                                                    <td class="px-6 py-5">
                                                        <div class="flex flex-col flex-nowrap space-x-2 gap-3 items-center">
                                                            <span
                                                                class="px-3 py-1 text-xs font-semibold rounded-full flex items-center w-fit"
                                                                :class="{
                                                                    'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-300': data.status === 'approved',
                                                                    'bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-300': data.status === 'pending',
                                                                    'bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-300': data.status === 'rejected',
                                                                }">
                                                                <Icon :name="getIconName(data.status)"
                                                                    class="w-4 h-4 inline-block align-middle" />
                                                                {{ getStatusLabel(data.status) }}
                                                            </span>
                                                            <Form @submit="updateValidasiLaporan"
                                                                :validation-schema="validasiLaporanSchema"
                                                                class="flex flex-row flex-nowrap space-x-2 border border-gray-200 py-1 px-2">
                                                                <div>
                                                                    <Field as="select" name="status" id="status"
                                                                        class="px-3 py-1 text-xs font-medium rounded-lg transition-all duration-300 bg-gray-50 dark:bg-gray-800 border border-gray-300 dark:border-gray-700 text-gray-900 dark:text-gray-200">
                                                                        >
                                                                        <option disabled value="">Pilih Status</option>
                                                                        <option value="pending"
                                                                            :selected="data.status === 'pending'">
                                                                            Tunggu</option>
                                                                        <option value="approved"
                                                                            :selected="data.status === 'approved'">
                                                                            Terima</option>
                                                                        <option value="rejected"
                                                                            :selected="data.status === 'rejected'">
                                                                            Tolak</option>
                                                                    </Field>
                                                                    <ErrorMessage name="status"
                                                                        class="text-red-500 text-xs" />
                                                                        <p v-if="errors.status" class="text-red-500 text-sm">{{ errors.status[0] }}</p>
                                                                </div>
                                                                <Field name="id" type="hidden" id="id"
                                                                    :value="data.id" />
                                                                <button
                                                                    class="text-white bg-blue-700 hover:bg-blue-800 focus:ring-4 focus:outline-none focus:ring-blue-300 font-medium rounded-lg text-xs w-full sm:w-auto px-3 py-1 text-center dark:bg-blue-600 dark:hover:bg-blue-700 dark:focus:ring-blue-800">
                                                                    <Icon v-if="pendingUpdateId === data.id"
                                                                        name="codex:loader"
                                                                        class="text-xl align-middle" />
                                                                    <span v-else>Simpan</span>
                                                                </button>
                                                            </Form>

                                                            <!-- <Field  name="selectedReports" 
                                                                        type="checkbox" 
                                                                        :value="data.id" 
                                                                        v-model="selectedReports"
                                                                        @change="logSelectedReports"/>   -->
                                                        </div>
                                                    </td>



                                                </tr>

                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>

                        </div>

                        <nav class="flex items-center flex-column flex-wrap md:flex-row justify-between pt-4 px-6 py-3"
                            aria-label="Table navigation">
                            <span
                                class="text-sm font-normal text-gray-500 dark:text-gray-400 mb-4 md:mb-0 block w-full md:inline md:w-auto">
                                Menampilkan
                                <span class="font-semibold text-gray-900 dark:text-white">{{
                                    laporanHarianByMentor.data.from }}</span> -
                                <span class="font-semibold text-gray-900 dark:text-white">{{
                                    laporanHarianByMentor.data.to }}</span> dari
                                <span class="font-semibold text-gray-900 dark:text-white">{{
                                    laporanHarianByMentor.data.total }}</span>
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
                                <li v-for="link in laporanHarianByMentor.data.links" :key="link.label">
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
                                        :disabled="currentPage >= laporanHarianByMentor.data.last_page"
                                        class="flex items-center justify-center px-3 h-8 leading-tight border rounded-e-lg transition"
                                        :class="currentPage < laporanHarianByMentor.data.last_page ?
                                            'text-gray-500 bg-white border-gray-300 hover:bg-gray-100 hover:text-gray-700 dark:bg-gray-800 dark:border-gray-700 dark:text-gray-400 dark:hover:bg-gray-700 dark:hover:text-white'
                                            : 'bg-gray-300 text-gray-500 cursor-not-allowed'">
                                        Next
                                    </button>
                                </li>
                            </ul>
                        </nav>
                    </div>
                </div>
            </section>
        </div>
    </NuxtLayout>
</template>

<style scoped></style>