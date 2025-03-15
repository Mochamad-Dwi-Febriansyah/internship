<script setup lang="ts">
definePageMeta({
    layout: 'dashboard',
    middleware: 'auth'
})
const route = useRoute()
const router = useRouter()
const laporanHarianId = String(route.params.id)
const breadcrumb = [
    { label: "Laporan Akhir", icon: "mdi:file-document-alert-outline",to: "/dashboard/mentor/magang/laporan-akhir" },
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
const { getLaporanAkhirById, pendingLaporanAkhirById } = useLaporanAkhirByMentor(currentPage)
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
                        <h3 class="text-md text-gray-700 font-medium">Detail Laporan Akhir</h3>
                        <div class="flex flex-row space-x-3">
                            <button @click="router.back()"
                                class="ms-auto px-3 py-1  text-xs w-fit rounded-full flex items-center gap-2 bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-300 border border-red-300 hover:bg-red-200">
                                <Icon name="material-symbols:arrow-left-alt-rounded"
                                    class="w-4 h-4 inline-block align-middle transition-transform duration-300 ease-in-out" />
                                Kembali
                            </button>
                        </div>
                    </div> 

                    <div v-if="laporanAkhirById"
                        class="w-full p-6 bg-white border border-gray-200 rounded-lg shadow-sm dark:bg-gray-800 dark:border-gray-700">
                        <h2 class="text-lg font-semibold text-gray-700 dark:text-gray-200">Detail Laporan</h2>

                        <div v-if="report" class="mt-4 grid gap-y-4 sm:grid-cols-2 sm:gap-x-6">
                            <div class="flex flex-col">
                                <p class="font-medium text-gray-700 dark:text-gray-300">Nama</p>
                                <p class="text-gray-600 dark:text-gray-400">{{ laporanAkhirById.user.nama_depan }} {{
                                    laporanAkhirById.user.nama_belakang }}</p>
                                <p class="text-gray-600 dark:text-gray-400">{{ laporanAkhirById.user.email }} </p>
                            </div>

                            <div class="flex flex-col">
                                <p class="font-medium text-gray-700 dark:text-gray-300">Universitas/Sekolah</p>
                                <p class="text-gray-600 dark:text-gray-400">{{
                                    laporanAkhirById.master_sekolah.nama_sekolah_universitas }}</p>
                                <p class="text-gray-600 dark:text-gray-400">{{
                                    laporanAkhirById.master_sekolah.jurusan_sekolah }}</p>
                                <p class="text-gray-600 dark:text-gray-400">{{
                                    laporanAkhirById.master_sekolah.fakultas_universitas }}</p>
                                <p class="text-gray-600 dark:text-gray-400">{{
                                    laporanAkhirById.master_sekolah.program_studi_universitas }}</p>

                            </div>

                            <div class="flex flex-col">
                                <p class="font-medium text-gray-700 dark:text-gray-300">Nama Laporan</p>
                                <p class="text-gray-600 dark:text-gray-400">{{ laporanAkhirById.title }}</p>
                            </div>

                            <div class="flex flex-col">
                                <p class="font-medium text-gray-700 dark:text-gray-300">Deskripsi</p>
                                <p class="text-gray-600 dark:text-gray-400" v-html="laporanAkhirById.report"></p>
                            </div>

                            <div class="flex flex-col">
                                <p class="font-medium text-gray-700 dark:text-gray-300">Photo</p>
                                <p class="text-gray-600 dark:text-gray-400">{{ laporanAkhirById.photo }}</p>
                            </div>

                            <div class="flex flex-col">
                                <p class="font-medium text-gray-700 dark:text-gray-300">Video</p>
                                <p class="text-gray-600 dark:text-gray-400">{{ laporanAkhirById.video }}</p>
                            </div>

                            <div class="flex flex-col">
                                <p class="font-medium text-gray-700 dark:text-gray-300">Certificate</p>
                                <p class="text-gray-600 dark:text-gray-400">{{ laporanAkhirById.certificate }}</p>
                            </div>


                            <!-- <div class="flex flex-col">
        <p class="font-medium text-gray-700 dark:text-gray-300">Deskripsi</p>
        <p class="text-gray-600 dark:text-gray-400">
          <span :class="statusColor(report.status)">{{ report.status }}</span>
        </p>
      </div> -->

                            <div class="flex flex-col sm:col-span-2">
                                <p class="font-medium text-gray-700 dark:text-gray-300">Deskripsi</p> 
                            </div>
                        </div>

                        <div v-else class="text-gray-500 text-center py-4">Tidak ada data laporan.</div>
                    </div>
                </div>
            </section>
        </div>
    </NuxtLayout>
</template>