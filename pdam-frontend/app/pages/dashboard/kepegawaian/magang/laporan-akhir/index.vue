<script setup lang="ts">
definePageMeta({
    layout: 'dashboard',
    middleware: 'auth'
})
import * as yup from "yup"
import type { SubmissionHandler } from 'vee-validate' 
import { FormatDate } from "~/utils/FormatDate"
import { getIconName, getStatusLabel  } from "~/utils/FormatStatus"
const { addNotification }  = useNotification()
const route = useRoute()
const router = useRouter()
const currentPage = ref(Number(route.query.page) || 1);
const { laporanAkhirByKepegawaian, pendingLaporanAkhirByKepegawaian, refreshlaporanAkhirByKepegawaian, pendingClearCache, clearCachelaporanAkhirByKepegawaian,updateValidasiLaporanByKepegawaian } = useLaporanAkhirByKepegawaian(currentPage)


const breadcrumb = [
    {label: "Dashboard" , icon: "material-symbols:dashboard",  to: "/dashboard" }, 
    { label: "Laporan Akhir", icon: "material-symbols:checkbook-outline-rounded" }
]
function goToPage(page: number) {
    if (page < 1 || page > (laporanAkhirByKepegawaian.value?.data.last_page ?? 1)) return;
    currentPage.value = page;
    router.push({ query: { ...route.query, page } });
}

function getPageNumber(url: string | null | undefined): number {
    const match = url?.match(/page=(\d+)/);
    return match?.[1] ? parseInt(match[1], 10) : 1;
}  
const pendingUpdateId = ref(null)
const validasiLaporanSchema = yup.object({ 
    status_verifikasi_kepegawaian: yup.string().required('Status  wajib diisi'), 
})
const updateValidasiLaporan: SubmissionHandler<any> = async (value, { resetForm }: { resetForm: () => void }) => {
    const formData = new FormData()  
    formData.append('_method', 'PUT') 
    formData.append('id', value.id)
    formData.append('status_verifikasi_kepegawaian', value.status_verifikasi_kepegawaian)  
    if (value.status_verifikasi_kepegawaian === 'rejected' && rejectionNote.value.trim()) {
        formData.append('rejection_note_kepegawaian', rejectionNote.value);
    }
    for (const pair of formData.entries()) {
        console.log(`${pair[0]}:`, pair[1]);
    }
    
    try {
        // console.log(value)
        pendingUpdateId.value = value.id  
        const response = await updateValidasiLaporanByKepegawaian(formData) 
        resetForm();
        addNotification('success', response.message) 
        await clearCachelaporanAkhirByKepegawaian() 
        showRejectionModal.value = false;
        pendingUpdateId.value = null 
    } catch (error: any) { 
        addNotification('error', error) 
        pendingUpdateId.value = null 
    }
}

const selectedStatus = ref('');
const rejectionNote = ref('');
const showRejectionModal = ref(false);

watch(selectedStatus, (newStatus) => {
    if (newStatus === 'rejected') {
        showRejectionModal.value = true;
    }else{
        showRejectionModal.value = false;
    }
});  
const cancelRejectionModal = () => {
    showRejectionModal.value = false;
    selectedStatus.value = ''; // Reset status jika batal
    rejectionNote.value = ''; // Kosongkan rejection note
}
</script>

<template>
    <NuxtLayout>
        <BaseBreadcrumb :items="breadcrumb" />
        <div>
            <section class="mb-3">
                <div v-if="pendingLaporanAkhirByKepegawaian" class="relative w-full h-[2px] bg-gray-200 overflow-hidden">
                    <div class="absolute h-full bg-gradient-to-r from-green-400 to-green-600 animate-loading"></div>
                </div>
                <div v-else class="px-3 m-3">
                    <div class="flex flex-row justify-between items-center mb-2">
                        <button @click="clearCachelaporanAkhirByKepegawaian()" class="ms-auto">
                            <Icon name="material-symbols-light:directory-sync"
                                :class="{ 'animate-spin': pendingClearCache }"
                                class=" w-5 h-5 inline-block align-middle transition-transform duration-300 ease-in-out" />
                        </button>
                    </div>
                    <div v-if="!laporanAkhirByKepegawaian?.data?.data || laporanAkhirByKepegawaian.data.data.length === 0"
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
                                            Tanggal Pengajuan
                                        </th>
                                        <th scope="col" class="px-6 py-3">
                                            Identitas
                                        </th>
                                        <th scope="col" class="px-6 py-3 ">
                                            Keterangan Pengajuan
                                        </th> 
                                        <th scope="col" class="px-6 py-3 text-center">
                                            Status Laporan
                                        </th>
                                    </tr>
                                </thead>
                                <tbody>

                                    <tr v-for="data in laporanAkhirByKepegawaian.data.data" :key="data.id"
                                        class="bg-white border-b dark:bg-gray-800 dark:border-gray-700 border-gray-200 hover:bg-gray-50 dark:hover:bg-gray-600">
                                        <td class="px-6 py-5">
                                            {{ FormatDate(data.created_at) }}
                                        </td>
                                        <td scope="row"
                                            class="px-6 py-2  text-gray-900 whitespace-nowrap dark:text-white">
                                            <div class="flex flex-col">
                                                <p class="   text-gray-900 dark:text-white">
                                                    {{ data.user?.nama_depan }} {{ data.user?.nama_belakang }}
                                                </p>
                                                <p class=" backdrop: text-gray-500 dark:text-gray-400">
                                                    {{ data.user?.email }}
                                                </p>
                                            </div>
                                        </td>
                                        <td class="px-6 py-5">
                                            {{ data.title }}
                                        </td>  
                                        <td class="px-6 py-5">
                                            <div class="flex flex-row flex-nowrap space-x-2 items-center">
                                                <NuxtLink :to="`./laporan-akhir/${data.id}`"
                                                    class="px-3 py-1 text-xs w-fit font-semibold rounded-full flex items-center gap-2 bg-yellow-100 border border-yellow-200 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-300">
                                                    <Icon name="material-symbols:visibility-outline" class="w-4 h-4 inline-block align-middle" />
                                                </NuxtLink> 
                                                <span
                                                    class="px-3 py-1 text-xs font-semibold rounded-full flex items-center"
                                                    :class="{
                                                        'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-300': data.status_verifikasi_kepegawaian === 'approved',
                                                        'bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-300': data.status_verifikasi_kepegawaian === 'pending',
                                                        'bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-300': data.status_verifikasi_kepegawaian === 'rejected',
                                                    }">
                                                    <Icon :name="getIconName(data.status_verifikasi_kepegawaian)"
                                                        class="w-4 h-4 inline-block align-middle" />
                                                    {{ getStatusLabel(data.status_verifikasi_kepegawaian) }}
                                                </span>
                                                <Form @submit="updateValidasiLaporan" :validation-schema="validasiLaporanSchema" class="flex flex-row flex-nowrap   border border-gray-200 py-1 px-2"> 
                                                <div>
                                                    <Field as="select" name="status_verifikasi_kepegawaian" id="status_verifikasi_kepegawaian" v-model="selectedStatus"
                                                        class="px-3 py-1 text-xs font-medium rounded-lg transition-all duration-300 bg-gray-50 dark:bg-gray-800 border border-gray-300 dark:border-gray-700 text-gray-900 dark:text-gray-200">
                                                        >
                                                        <option disabled value="">Pilih Status</option>
                                                        <option value="pending" :selected="data.status_verifikasi_kepegawaian === 'pending'">
                                                            Tunggu</option>
                                                        <option value="approved" :selected="data.status_verifikasi_kepegawaian === 'approved'">
                                                            Terima</option>
                                                        <option value="rejected" :selected="data.status_verifikasi_kepegawaian === 'rejected'">
                                                            Tolak</option>
                                                    </Field>
                                                    <ErrorMessage name="status" class="text-red-500 text-xs"  />
                                                </div>
                                                <Field name="rejection_note_kepegawaian" type="hidden" id="rejection_note_kepegawaian" :value="rejectionNote" v-if="selectedStatus === 'rejected'"/>

                                                          <!-- Modal untuk rejection_note -->
                                                    <div v-if="showRejectionModal" class="fixed inset-0 flex items-center justify-center bg-gray-900 bg-opacity-50 z-50 ">
                                                        <div class="bg-white dark:bg-gray-800 p-4 rounded-lg shadow-lg w-96">
                                                            <h3 class="text-lg font-semibold">Alasan Penolakan</h3>
                                                            <textarea v-model="rejectionNote" rows="3" name="rejection_note_kepegawaian" class="w-full mt-2 p-2 border border-gray-300 rounded-lg dark:bg-gray-700 dark:text-gray-200"></textarea>
                                                            <div class="flex justify-end mt-3 space-x-2">
                                                                <button @click="cancelRejectionModal" class="px-3 py-1 bg-gray-400 text-white rounded-lg">Batal</button>
                                                                <button type="submit" class="px-3 py-1 bg-blue-600 text-white rounded-lg">
                                                                    <Icon v-if="pendingUpdateId" name="codex:loader" class="text-xl align-middle"/>
                                                                    <span v-else>Simpan</span>
                                                                </button>
                                                                <!-- @click="confirmRejection"  -->
                                                            </div>
                                                        </div>
                                                    </div>
                                                <Field name="id" type="hidden" id="id" :value="data.id"/>
                                                <button  class="ms-2 text-white bg-blue-700 hover:bg-blue-800 focus:ring-4 focus:outline-none focus:ring-blue-300 font-medium rounded-lg text-xs w-full sm:w-auto px-3 py-1 text-center dark:bg-blue-600 dark:hover:bg-blue-700 dark:focus:ring-blue-800">
                                                    <Icon v-if="pendingUpdateId === data.id" name="codex:loader" class="text-xl align-middle"/>
                                                    <span v-else>Simpan</span>
                                                </button>
                                            </Form>
                                          
                                            </div>
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
                                    laporanAkhirByKepegawaian.data.from }}</span> -
                                <span class="font-semibold text-gray-900 dark:text-white">{{
                                    laporanAkhirByKepegawaian.data.to }}</span> dari
                                <span class="font-semibold text-gray-900 dark:text-white">{{
                                    laporanAkhirByKepegawaian.data.total }}</span>
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
                                <li v-for="link in laporanAkhirByKepegawaian.data.links" :key="link.label">
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
                                        :disabled="currentPage >= laporanAkhirByKepegawaian.data.last_page"
                                        class="flex items-center justify-center px-3 h-8 leading-tight border rounded-e-lg transition"
                                        :class="currentPage < laporanAkhirByKepegawaian.data.last_page ?
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