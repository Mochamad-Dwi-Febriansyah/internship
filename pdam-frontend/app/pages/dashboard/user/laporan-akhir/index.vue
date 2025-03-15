<script setup lang="ts">
const config = useRuntimeConfig()
definePageMeta({
    layout: 'dashboard',
    middleware: 'auth'
})
import { FormatDate } from "~/utils/FormatDate" 
import { getIconName, getStatusLabel  } from "~/utils/FormatStatus"
import dayjs from 'dayjs'

const breadcrumb = [
    { label: "Laporan Akhir", icon: "material-symbols:checkbook-outline-rounded", to: "/dashboard/user/laporan-akhir" },  
]
const route = useRoute()
const currentPage = ref(Number(route.query.page) || 1)
const { laporanAkhir, pendingLaporanAkhir, refreshLaporanAkhir, clearCacheLaporanAkhir, pendingClearCache } = useLaporanAkhir(currentPage)

const { FetchBerkasByMagangId, pendingFetch: pendingFetchBerkasUserMagang } = useBerkasByUserMagang()

const berkasUserById = ref()
const canUploadLaporan = ref(false)
onMounted(async () => {
    const response = await FetchBerkasByMagangId()
    berkasUserById.value = response.data

    checkUploadLaporanEligibility()
})

// Fungsi cek apakah sudah H-14
const checkUploadLaporanEligibility = () => {
    if (berkasUserById.value?.tanggal_selesai) {
        const tanggalSelesai = dayjs(berkasUserById.value.tanggal_selesai, 'DD/MM/YYYY')
        const today = dayjs()
        const twoWeeksBefore = tanggalSelesai.subtract(14, 'day') // H-14

        // Jika hari ini >= H-14, tombol aktif
    //     console.log('Tanggal Selesai:', tanggalSelesai.format('YYYY-MM-DD'))
    // console.log('H-14:', twoWeeksBefore.format('YYYY-MM-DD'))
    // console.log('Hari ini:', today.format('YYYY-MM-DD'))

        canUploadLaporan.value = today.isAfter(twoWeeksBefore) || today.isSame(twoWeeksBefore, 'day')
    }
}
</script>

<template>
    <NuxtLayout>
        <BaseBreadcrumb :items="breadcrumb" />
        <div class="bg-white">
            <section class="mb-3">
                <div v-if="pendingLaporanAkhir" class="relative w-full h-[2px] bg-gray-200 overflow-hidden">
                    <div class="absolute h-full bg-gradient-to-r from-green-400 to-green-600 animate-loading"></div>
                </div>
                <div v-else class="p-3">
                    <div class="flex flex-row justify-between items-center mb-2">
                        <!-- <h3 class="text-md text-gray-700 font-medium">Laporan Akhir</h3> -->
                        <div class="flex flex-row space-x-2 items-center"> 
                            <!-- <button @click="clearCacheLaporanAkhir" class="ms-auto">
                                <Icon name="material-symbols-light:directory-sync" 
                                :class="{'animate-spin' : pendingClearCache}"
                                class=" w-5 h-5 inline-block align-middle transition-transform duration-300 ease-in-out"/>
                            </button> -->
                            <!-- {{ berkasUserById.tanggal_selesai  }} {{ canUploadLaporan }} -->
                            <NuxtLink  v-if="canUploadLaporan && laporanAkhir?.data?.data.length === 0" to="./laporan-akhir/form/new"
                                class="px-3 py-1  text-xs w-fit font-semibold rounded-full flex items-center gap-2 bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-300">
                                <Icon name="material-symbols:add-to-photos-outline-rounded"
                                    class="w-4 h-4 inline-block align-middle" />
                                Laporan Akhir
                            </NuxtLink> 
                        </div>
                    </div>
                    <div v-if="!laporanAkhir?.data?.data || laporanAkhir.data.data.length === 0"
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
                                            Keterangan Pengajuan
                                        </th> 
                                        <th scope="col" class="px-6 py-3">
                                            Status
                                        </th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr v-for="data in laporanAkhir.data.data" :key="data.id"
                                        class="bg-white border-b dark:bg-gray-800 dark:border-gray-700 border-gray-200 hover:bg-gray-50 dark:hover:bg-gray-600">

                                        <td class="px-6 py-4">
                                            {{ FormatDate(data.created_at) }}
                                        </td>
                                        <td class="px-6 py-4">
                                            {{ data.title }}
                                        </td> 
                                        <td class="px-6 py-4">
                                        <div class="flex flex-row space-x-2 items-center">
                                            <!-- Status Verifikasi Mentor -->
                                            <span class="text-xs rounded-xl py-1 px-2 whitespace-nowrap"
                                                :class="{
                                                    'bg-yellow-100 border border-yellow-200 text-yellow-600 hover:bg-yellow-200': data?.status_verifikasi_mentor === 'pending',
                                                    'bg-green-100 border border-green-200 text-green-600 hover:bg-green-200': data?.status_verifikasi_mentor === 'approved',
                                                    'bg-red-100 border border-red-200 text-red-600 hover:bg-red-200': data?.status_verifikasi_mentor === 'rejected',
                                                }">
                                                 <!-- {{ getStatusLabel(data?.status_verifikasi_mentor) }}   -->
                                                Mentor: <Icon :name="data.status_verifikasi_mentor === 'pending'
                                                        ? 'material-symbols-light:hourglass-bottom'
                                                        : data.status_verifikasi_mentor === 'approved'
                                                            ? 'hugeicons:tick-double-03'
                                                            : data.status_verifikasi_mentor === 'rejected'
                                                                ? 'material-symbols-light:cancel'
                                                                : 'material-symbols-light:help'"
                                                        class="w-4 h-4 inline-block align-middle font-bold" />
                                            </span>

                                            <!-- Status Verifikasi Kepegawaian -->
                                            <span class="text-xs rounded-xl py-1 px-2 whitespace-nowrap"
                                                :class="{
                                                    'bg-yellow-100 border border-yellow-200 text-yellow-600 hover:bg-yellow-200': data?.status_verifikasi_kepegawaian === 'pending',
                                                    'bg-green-100 border border-green-200 text-green-600 hover:bg-green-200': data?.status_verifikasi_kepegawaian === 'approved',
                                                    'bg-red-100 border border-red-200 text-red-600 hover:bg-red-200': data?.status_verifikasi_kepegawaian === 'rejected',
                                                }">
                                                <!-- Kepegawaian: {{ getStatusLabel(data?.status_verifikasi_kepegawaian) }}   -->
                                                Kepegawaian:  <Icon :name="data.status_verifikasi_kepegawaian === 'pending'
                                                        ? 'material-symbols-light:hourglass-bottom'
                                                        : data.status_verifikasi_kepegawaian === 'approved'
                                                            ? 'hugeicons:tick-double-03'
                                                            : data.status_verifikasi_kepegawaian === 'rejected'
                                                                ? 'material-symbols-light:cancel'
                                                                : 'material-symbols-light:help'"
                                                        class="w-4 h-4 inline-block align-middle font-bold" />

                                            </span>

                                            <!-- Link Detail -->
                                            <NuxtLink :to="`./laporan-akhir/${data.id}`"
                                                class="px-3 py-1 text-xs w-fit font-semibold rounded-full flex items-center gap-2 bg-yellow-100 border border-yellow-200 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-300">
                                                <Icon name="material-symbols:visibility-outline" class="w-4 h-4 inline-block align-middle" />
                                            </NuxtLink>
                                            <NuxtLink 
                                                v-if="!(data?.status_verifikasi_mentor === 'approved' && data?.status_verifikasi_kepegawaian === 'approved')" 
                                                :to="`./laporan-akhir/form/${data.id}`"
                                                class="px-3 py-1 text-xs w-fit font-semibold rounded-full flex items-center gap-2 bg-yellow-100 border border-yellow-200 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-300">
                                                <Icon name="material-symbols:edit-square-outline" class="w-4 h-4 inline-block align-middle" />
                                            </NuxtLink>

                                            <!-- v-if="data?.status_verifikasi_mentor != 'approved' && data?.status_verifikasi_kepegawaian != 'approved'" -->
                                        </div>
                                    </td>

                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

            </section>
        </div>
    </NuxtLayout>
</template>