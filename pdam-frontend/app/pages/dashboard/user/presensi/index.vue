<script setup lang="ts">

definePageMeta({
    layout: 'dashboard',
    middleware: 'auth'
})

const breadcrumb = [
    { label: "Riwayat Presensi", icon: "material-symbols:checkbook-outline-rounded", to: "/dashboard/user/presensi" },
]
import { FormatDate } from "~/utils/FormatDate"
import { getIconName, getStatusLabel } from "~/utils/FormatStatus"
const { addNotification } = useNotification()
const route = useRoute()
const router = useRouter()
const config = useRuntimeConfig()
const currentPage = ref(Number(route.query.page) || 1);
const { presensi, pending, } = usePresensi(currentPage)
const { exportLogBook, pendingExportLogBook } = useLaporanHarian(currentPage)
const viewMode = ref("table")


const exportLogBookBtn = async () => {
    try {
        const response = await exportLogBook()
        addNotification('success', 'Berhasil di download')
    } catch (error: any) {
        addNotification('error', error.data.message)
    }
}
</script>

<template>
    <NuxtLayout>
        <BaseBreadcrumb :items="breadcrumb" />
        <div class="bg-white">
            <section class="mb-3">
                <div v-if="pending" class="relative w-full h-[2px] bg-gray-200 overflow-hidden">
                    <div class="absolute h-full bg-gradient-to-r from-green-400 to-green-600 animate-loading"></div>
                </div>
                <div v-else class="p-3">
                    <div v-if="!presensi?.data?.data || presensi.data.data.length === 0"
                        class="text-center text-gray-500 p-4 border border-gray-200 rounded-lg">
                        Tidak ada data.
                    </div>
                    <div v-else>
                        <div class="flex flex-row justify-start items-center mb-2 gap-2">
                            <!-- <h3 class="text-md text-gray-700 font-medium">Riwayat Presensi</h3> -->
                            <div class="ms-auto flex flex-row gap-2">
                                <div
                                    class="flex bg-gray-100 dark:bg-gray-700 rounded-full items-center border ">
                                    <button @click="viewMode = 'grid'"
                                        :class="viewMode === 'grid' ? 'bg-green-100   text-green-800' : 'text-gray-600 dark:text-gray-300'"
                                        class="px-3 py-1 text-xs font-semibold rounded-l-full transition-all flex items-center">
                                        <Icon name="material-symbols:grid-view" class="w-4 h-4 inline-block" />
                                    </button>

                                    <div class="h-6 w-[1px] bg-green-300 dark:bg-gray-500"></div>

                                    <button @click="viewMode = 'table'"
                                        :class="viewMode === 'table' ? 'bg-green-100   text-green-800' : 'text-gray-600 dark:text-gray-300'"
                                        class="px-3 py-1 text-xs font-semibold rounded-r-full transition-all flex items-center">
                                        <Icon name="material-symbols:table-rows" class="w-4 h-4 inline-block" />
                                    </button>
                                </div>

                                <button @click="exportLogBookBtn"
                                    class="px-3 py-1   text-xs w-fit font-medium rounded-full flex items-center gap-2 bg-green-100 border border-green-300 text-green-800 dark:bg-green-900 dark:text-green-300">
                                    <Icon
                                        :name="pendingExportLogBook ? 'codex:loader' : 'material-symbols:file-export-sharp'"
                                        class="w-4 h-4 inline-block align-middle" />
                                    Cetak buku catatan
                                </button>

                            </div>
                        </div>

                        <div v-if="viewMode === 'grid'"
                            class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-3">
                            <div v-for="data in presensi.data.data" :key="data.id"
                                class="rounded-lg shadow-md p-4 bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 transition-all hover:shadow-lg">
                                <h4 class="text-sm text-center font-semibold text-gray-700 dark:text-gray-300 mb-2">
                                    {{ FormatDate(data.tanggal) }}
                                </h4>

                                <div class="flex flex-col gap-3"> 
                                    <div class="flex flex-row gap-3 justify-center">
                                        <!-- Check-in -->
                                        <div class="flex flex-col items-center">
                                            <NuxtImg
                                                :src="data.foto_check_in ? `${config.public.storage}/storage/${data.foto_check_in}` : 'https://rotendaokab.go.id/wp-content/uploads/2016/08/dummy-prod-1.jpg'"
                                                class="w-24 h-24 object-cover rounded-lg border mb-1"
                                                alt="Foto Check-in" format="webp" loading="lazy" />
                                            <div class="text-center">
                                                <p class="text-gray-500 dark:text-gray-300 text-sm">Masuk</p>
                                                <p class="font-medium text-sm text-gray-700 dark:text-white">{{
                                                    data.waktu_check_in ? data.waktu_check_in : '--:--:--' }}</p>
                                            </div>
                                        </div>

                                        <!-- Check-out -->
                                        <div class="flex flex-col  items-center">
                                            <NuxtImg
                                                :src="data.foto_check_out ? `${config.public.storage}/storage/${data.foto_check_out}` : 'https://rotendaokab.go.id/wp-content/uploads/2016/08/dummy-prod-1.jpg'"
                                                class="w-24 h-24 object-cover rounded-lg border  mb-1"
                                                alt="Foto Check-out" format="webp" loading="lazy" />
                                            <div class="text-center">
                                                <p class="text-gray-500 dark:text-gray-300 text-sm">Keluar</p>
                                                <p class="font-medium text-sm text-gray-700 dark:text-white">{{
                                                    data.waktu_check_out ? data.waktu_check_out : '--:--:--' }}</p>
                                            </div>
                                        </div>
                                    </div>


                                    <div class="flex flex-row space-x-2 items-center justify-center">

                                        <NuxtLink v-if="!data.laporan_harian"
                                                    :to="`./presensi/laporan-harian/${data.id}?tanggal=${data.tanggal}`"
                                                    class="px-3 py-1  text-xs w-fit  whitespace-nowrap rounded-full flex items-center gap-2 bg-blue-100 border border-blue-300 text-blue-800 dark:bg-blue-900 dark:text-blue-300 hover:bg-blue-200">
                                                    <Icon name="material-symbols:add-to-photos-outline-rounded"
                                                        class="w-4 h-4 inline-block align-middle" />
                                                    <!-- Buat Laporan -->
                                                </NuxtLink>
                                                <!-- jika sudah ada laporan harian yang dikirim adalah laporan harian id -->
                                                <NuxtLink v-if="data.laporan_harian"
                                                    :to="`./presensi/laporan-harian/${data.laporan_harian?.id}?tanggal=${data.tanggal}`"
                                                    class="px-3 py-1  text-xs w-fit  whitespace-nowrap rounded-full flex items-center gap-2 bg-green-100 border border-green-300 text-green-800 dark:bg-green-900 dark:text-green-300 hover:bg-green-200">
                                                    <Icon name="material-symbols:visibility-outline-rounded"
                                                        class="w-4 h-4 inline-block align-middle" />
                                                    <!-- Lihat Laporan -->
                                                </NuxtLink>

                                                <span v-if="data.laporan_harian"
                                                    class="text-xs rounded-xl py-1 px-2 whitespace-nowrap relative group "
                                                    :class="{
                                                        'bg-yellow-100 border border-yellow-200 text-yellow-600   ': data.laporan_harian?.status === 'pending',
                                                        ' bg-green-100 border border-green-200 text-green-600   ': data.laporan_harian?.status === 'approved',
                                                        ' bg-red-100 border border-red-200 text-red-600   ': data.laporan_harian?.status === 'reject',
                                                    }">
                                                    <Icon :name="data.laporan_harian?.status === 'pending'
                                                        ? 'material-symbols-light:hourglass-bottom'
                                                        : data.laporan_harian?.status === 'approved'
                                                            ? 'hugeicons:tick-double-03'
                                                            : data.laporan_harian?.status === 'reject'
                                                                ? 'material-symbols-light:cancel'
                                                                : 'material-symbols-light:help'"
                                                        class="w-4 h-4 inline-block align-middle font-bold" />

                                                    <!-- {{ data.laporan_harian?.status ===  }} -->
                                                    <span
                                                        class="absolute bottom-full left-1/2 -translate-x-1/2 mb-2 px-2 py-1 text-[10px] text-white bg-gray-700 rounded-md opacity-0 group-hover:opacity-100 transition-opacity z-10">
                                                        {{
                                                            data.laporan_harian?.status === 'pending'
                                                                ? 'Menunggu Verifikasi'
                                                                : data.laporan_harian?.status === 'approved'
                                                                    ? 'Diterima'
                                                                    : data.laporan_harian?.status === 'reject'
                                                                        ? 'Ditolak'
                                                                        : 'Status Tidak Diketahui'
                                                        }}
                                                    </span>
                                                </span>
                                                <span v-if="!data.laporan_harian"
                                                    class="text-xs rounded-xl py-1 px-2 whitespace-nowrap bg-red-100 border border-red-200 text-red-600 flex items-center gap-1 relative group">
                                                    <Icon name="material-symbols:question-mark-rounded" class="w-4 h-4" />
                                              

                                                    <!-- Tooltip saat hover -->
                                                    <span
                                                        class="absolute bottom-full left-1/2 -translate-x-1/2 mb-2 px-2 py-1 text-[10px] text-white bg-gray-700 rounded-md opacity-0 group-hover:opacity-100 transition-opacity z-10">
                                                        Belum mengisi laporan harian
                                                    </span>
                                                </span>
 
                                     
                                    </div>
                                </div>
                            </div>
                        </div>


                        <div v-else class="relative overflow-x-auto shadow-md sm:rounded-lg">
                            <table class="w-full text-sm text-left rtl:text-right text-gray-500 dark:text-gray-400">
                                <thead
                                    class="text-xs text-gray-700 uppercase bg-gray-50 dark:bg-gray-700 dark:text-gray-400">
                                    <tr>
                                        <th scope="col" class="px-6 py-3">
                                            Tanggal
                                        </th>
                                        <th scope="col" class="px-6 py-3">
                                            Presensi Datang
                                        </th>
                                        <th scope="col" class="px-6 py-3">
                                            Presensi Pulang
                                        </th>
                                        <th scope="col" class="px-6 py-3">
                                            Laporan Harian
                                        </th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr v-for="data in presensi.data.data" :key="data.id"
                                        class="bg-white border-b dark:bg-gray-800 dark:border-gray-700 border-gray-200 hover:bg-gray-50 dark:hover:bg-gray-600">

                                        <td class="px-6 py-4">
                                            {{ FormatDate(data.tanggal) }}
                                        </td>
                                        <td class="px-6 py-4">
                                            <div class=" flex flex-row space-x-2 items-center">
                                                <NuxtImg
                                                    :src="data.foto_check_in
                                                        ? `${config.public.storage}/storage/${data.foto_check_in}`
                                                        : 'https://rotendaokab.go.id/wp-content/uploads/2016/08/dummy-prod-1.jpg'"
                                                    class="rounded-full w-10 h-10 object-cover border-2 border-white"
                                                    alt="Foto Identitas" format="webp" loading="lazy" />
                                                <div class="flex flex-col text-xs">
                                                    <p>{{ data.waktu_check_in }}</p>
                                                </div>
                                            </div>
                                        </td>
                                        <td class="px-6 py-4">
                                            <div class=" flex flex-row space-x-2 items-center">
                                                <NuxtImg
                                                    :src="data.foto_check_out
                                                        ? `${config.public.storage}/storage/${data.foto_check_out}`
                                                        : 'https://rotendaokab.go.id/wp-content/uploads/2016/08/dummy-prod-1.jpg'"
                                                    class="rounded-full w-10 h-10 object-cover border-2 border-white"
                                                    alt="Foto Identitas" format="webp" loading="lazy" />
                                                <div class="flex flex-col text-xs">
                                                    <p>{{ data.waktu_check_out }}</p>
                                                </div>
                                            </div>
                                        </td>
                                        <td class="px-6 py-4  ">
                                            <div class="flex flex-row space-x-2 items-center">

                                                <!-- jika belum ada laporan harian yang dikirim adalah presensi id -->
                                                <NuxtLink v-if="!data.laporan_harian"
                                                    :to="`./presensi/laporan-harian/${data.id}?tanggal=${data.tanggal}`"
                                                    class="px-3 py-1  text-xs w-fit  whitespace-nowrap rounded-full flex items-center gap-2 bg-blue-100 border border-blue-300 text-blue-800 dark:bg-blue-900 dark:text-blue-300 hover:bg-blue-200">
                                                    <Icon name="material-symbols:add-to-photos-outline-rounded"
                                                        class="w-4 h-4 inline-block align-middle" />
                                                    <!-- Buat Laporan -->
                                                </NuxtLink>
                                                <!-- jika sudah ada laporan harian yang dikirim adalah laporan harian id -->
                                                <NuxtLink v-if="data.laporan_harian"
                                                    :to="`./presensi/laporan-harian/${data.laporan_harian?.id}?tanggal=${data.tanggal}`"
                                                    class="px-3 py-1  text-xs w-fit  whitespace-nowrap rounded-full flex items-center gap-2 bg-green-100 border border-green-300 text-green-800 dark:bg-green-900 dark:text-green-300 hover:bg-green-200">
                                                    <Icon name="material-symbols:visibility-outline-rounded"
                                                        class="w-4 h-4 inline-block align-middle" />
                                                    <!-- Lihat Laporan -->
                                                </NuxtLink>
                                                <span v-if="data.laporan_harian"
                                                    class="text-xs rounded-xl py-1 px-2 whitespace-nowrap relative group "
                                                    :class="{
                                                        'bg-yellow-100 border border-yellow-200 text-yellow-600   ': data.laporan_harian?.status === 'pending',
                                                        ' bg-green-100 border border-green-200 text-green-600   ': data.laporan_harian?.status === 'approved',
                                                        ' bg-red-100 border border-red-200 text-red-600   ': data.laporan_harian?.status === 'reject',
                                                    }">
                                                    <Icon :name="data.laporan_harian?.status === 'pending'
                                                        ? 'material-symbols-light:hourglass-bottom'
                                                        : data.laporan_harian?.status === 'approved'
                                                            ? 'hugeicons:tick-double-03'
                                                            : data.laporan_harian?.status === 'reject'
                                                                ? 'material-symbols-light:cancel'
                                                                : 'material-symbols-light:help'"
                                                        class="w-4 h-4 inline-block align-middle font-bold" />

                                                    <!-- {{ data.laporan_harian?.status ===  }} -->
                                                    <span
                                                        class="absolute bottom-full left-1/2 -translate-x-1/2 mb-2 px-2 py-1 text-[10px] text-white bg-gray-700 rounded-md opacity-0 group-hover:opacity-100 transition-opacity z-10">
                                                        {{
                                                            data.laporan_harian?.status === 'pending'
                                                                ? 'Menunggu Verifikasi'
                                                                : data.laporan_harian?.status === 'approved'
                                                                    ? 'Diterima'
                                                                    : data.laporan_harian?.status === 'reject'
                                                                        ? 'Ditolak'
                                                                        : 'Status Tidak Diketahui'
                                                        }}
                                                    </span>
                                                </span>
                                                <span v-if="!data.laporan_harian"
                                                    class="text-xs rounded-xl py-1 px-2 whitespace-nowrap bg-red-100 border border-red-200 text-red-600 flex items-center gap-1 relative group">
                                                    <Icon name="material-symbols:question-mark-rounded" class="w-4 h-4" />
                                              

                                                    <!-- Tooltip saat hover -->
                                                    <span
                                                        class="absolute bottom-full left-1/2 -translate-x-1/2 mb-2 px-2 py-1 text-[10px] text-white bg-gray-700 rounded-md opacity-0 group-hover:opacity-100 transition-opacity z-10">
                                                        Belum mengisi laporan harian
                                                    </span>
                                                </span>


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