<script setup lang="ts"> 

definePageMeta({
    layout: 'dashboard',
    middleware: 'auth'
})
const route = useRoute()
const router = useRouter()
const config = useRuntimeConfig()
const userId = String(route.params.id)

const breadcrumb = [
    { label: "Magang", icon: "clarity:users-solid", to: "/dashboard/mentor/magang" },
    { label: "Detail Magang" }
]

const { detailUsersMagang, pendingDetailUsersMagang } = useDetailUserMagang()

const detailUser = ref()
onMounted(async () => {
    const response = await detailUsersMagang(userId);
    detailUser.value = response.data
})
// console.log('s',detailUser)

</script>

<template>
    <NuxtLayout>
        <BaseBreadcrumb :items="breadcrumb" />
        <div class="bg-white">
            <section class="mb-3">
                <div v-if="pendingDetailUsersMagang" class="relative w-full h-[2px] bg-gray-200 overflow-hidden">
                    <div class="absolute h-full bg-gradient-to-r from-green-400 to-green-600 animate-loading"></div>
                </div>
                <div v-else class="p-3">
                    <div class="flex flex-row justify-between items-center mb-2 ">
                <!-- <h3 class="text-md text-gray-700 font-medium">Detail Laporan Harian</h3> -->
                <div class="flex flex-row space-x-3 ms-auto">
                    <button @click="router.back()"
                        class="ms-auto px-3 py-1  text-xs w-fit rounded-full flex items-center gap-2 bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-300 border border-red-300 hover:bg-red-200">
                        <Icon name="material-symbols:arrow-left-alt-rounded"
                            class="w-4 h-4 inline-block align-middle transition-transform duration-300 ease-in-out" />
                        Kembali
                    </button>
                </div>
                </div>
                    <div class="flex flex-col items-start  gap-3">
                        <!-- <div class="flex flex-col items-start md:flex-row gap-3"> -->
                        <div class="w-full">
                            <div class="flex flex-row justify-between">
                                <h3 class="text-md text-gray-700 font-medium mb-2">Laporan Akhir</h3>
                            </div>
                            <div
                                class="w-full bg-white border border-gray-200 rounded-lg shadow-sm dark:bg-gray-800 dark:border-gray-700">
                                <div v-if="!detailUser?.laporanAkhir"
                                    class="text-center text-gray-500 p-4 border border-gray-200 rounded-lg">
                                    Tidak ada data.
                                </div>
                                <div v-else>
                                    <div class="relative overflow-x-auto shadow-md sm:rounded-lg">
                                        <table
                                            class="w-full text-sm text-left rtl:text-right text-gray-500 dark:text-gray-400">
                                            <thead
                                                class="text-xs text-gray-700 uppercase bg-gray-50 dark:bg-gray-700 dark:text-gray-400">
                                                <tr>
                                                    <th scope="col" class="px-6 py-3">
                                                        title
                                                    </th>
                                                    <th scope="col" class="px-6 py-3">
                                                        report
                                                    </th>
                                                    <th scope="col" class="px-6 py-3">
                                                        assessment report file
                                                    </th>
                                                    <th scope="col" class="px-6 py-3">
                                                        final report file
                                                    </th>
                                                    <th scope="col" class="px-6 py-3">
                                                        photo
                                                    </th>
                                                    <th scope="col" class="px-6 py-3">
                                                        video
                                                    </th>
                                                    <th scope="col" class="px-6 py-3">
                                                        certificate
                                                    </th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <tr> 
                                                <td class="px-6 py-2 min-w-[200px]">
                                                    {{ detailUser.laporanAkhir.title }}
                                                </td>
                                                <td class="px-6 py-2 min-w-[150px]"
                                                    v-html="detailUser.laporanAkhir.report">
                                                </td>
                                                <td class="px-6 py-2">
                                                    <a :href="`${config.public.storage}/storage/${detailUser.laporanAkhir.assessment_report_file}`"
                                                        target="_blank" rel="noopener noreferrer"
                                                        class="px-3 py-1 text-xs w-fit font-semibold rounded-full flex items-center gap-2 bg-yellow-100 border border-yellow-200 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-300">
                                                        <Icon name="material-symbols:open-in-new"
                                                            class="w-4 h-4 inline-block align-middle" />
                                                    </a>
                                                </td>
                                                <td class="px-6 py-2">
                                                    <a :href="`${config.public.storage}/storage/${detailUser.laporanAkhir.final_report_file}`"
                                                        target="_blank" rel="noopener noreferrer"
                                                        class="px-3 py-1 text-xs w-fit font-semibold rounded-full flex items-center gap-2 bg-yellow-100 border border-yellow-200 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-300">
                                                        <Icon name="material-symbols:open-in-new"
                                                            class="w-4 h-4 inline-block align-middle" />
                                                    </a>
                                                </td>
                                                <td class="px-6 py-2">
                                                    <a :href="`${config.public.storage}/storage/${detailUser.laporanAkhir.photo}`"
                                                        target="_blank" rel="noopener noreferrer"
                                                        class="px-3 py-1 text-xs w-fit font-semibold rounded-full flex items-center gap-2 bg-yellow-100 border border-yellow-200 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-300">
                                                        <Icon name="material-symbols:open-in-new"
                                                            class="w-4 h-4 inline-block align-middle" />
                                                    </a>
                                                </td>
                                                <td class="px-6 py-2">
                                                    {{ detailUser.laporanAkhir.video }}
                                                </td>
                                                <td class="px-6 py-2">
                                                    <a :href="`${config.public.storage}/storage/${detailUser.laporanAkhir.certificate}`" target="_blank" rel="noopener noreferrer"
                                                        class="px-3 py-1 text-xs w-fit font-semibold rounded-full flex items-center gap-2 bg-yellow-100 border border-yellow-200 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-300">
                                                        <Icon name="material-symbols:open-in-new"
                                                            class="w-4 h-4 inline-block align-middle" />
                                                    </a>
                                                </td>
                                            </tr>
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="w-full">
                            <div class="flex flex-row justify-between">
                                <h3 class="text-md text-gray-700 font-medium mb-2">Laporan Harian</h3>
                            </div>
                            <div
                                class="w-full bg-white border border-gray-200 rounded-lg shadow-sm dark:bg-gray-800 dark:border-gray-700">
                                <div v-if="!detailUser?.presensi || detailUser.presensi.length === 0"
                                    class="text-center text-gray-500 p-4 border border-gray-200 rounded-lg">
                                    Tidak ada data.
                                </div>
                                <div v-else>  
                                    <div class="relative overflow-x-auto shadow-md sm:rounded-lg">
                                        <table
                                            class="w-full text-sm text-left rtl:text-right text-gray-500 dark:text-gray-400">
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
                                                <tr v-for="data in detailUser?.presensi" :key="data.id"
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
                                                    <td>
                                                        <NuxtLink v-if="data.laporan_harian" :to="`./laporan-harian/${data.laporan_harian?.id}?tanggal=${data.tanggal}`"
                                                            class="px-3 py-1  text-xs w-fit  whitespace-nowrap rounded-full flex items-center gap-2 bg-green-100 border border-green-300 text-green-800 dark:bg-green-900 dark:text-green-300 hover:bg-green-200">
                                                            <Icon name="material-symbols:visibility-outline-rounded"
                                                                class="w-4 h-4 inline-block align-middle" />
                                                            Lihat Laporan
                                                        </NuxtLink> 
                                                    </td>
                                                </tr>
                                            </tbody>
                                        </table>
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