<script setup lang="ts">

definePageMeta({
    layout: 'dashboard',
    middleware: 'auth'
})
const route = useRoute()
const router = useRouter()
const config = useRuntimeConfig()
const presensiId = String(route.params.id)

const breadcrumb = [
    { label: "Magang", icon: "clarity:users-solid", to: "/dashboard/mentor/magang" },
    { label: "Detail Magang" },
    { label: "Detail Laporan Harian" }
]

const { getLaporanHarianById, pendingFetchById } = useDetailUserMagang()
const detailLaporanHarian = ref()
onMounted(async () => {
    const response = await getLaporanHarianById(presensiId);
    console.log(response)
    detailLaporanHarian.value = response.data
})

</script>
<template>
    <NuxtLayout>
        <BaseBreadcrumb :items="breadcrumb" />
        <div class="bg-white">
            <section class="mb-3">
                <div v-if="pendingFetchById" class="relative w-full h-[2px] bg-gray-200 overflow-hidden">
                    <div class="absolute h-full bg-gradient-to-r from-green-400 to-green-600 animate-loading"></div>
                </div>
                <div v-else class="p-3">
                    <div class="flex flex-row justify-between items-center mb-2 ">
                        <h3 class="text-md text-gray-700 font-medium">Detail Laporan Harian</h3>
                        <div class="flex flex-row space-x-3">
                            <button @click="router.back()"
                                class="ms-auto px-3 py-1  text-xs w-fit rounded-full flex items-center gap-2 bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-300 border border-red-300 hover:bg-red-200">
                                <Icon name="material-symbols:arrow-left-alt-rounded"
                                    class="w-4 h-4 inline-block align-middle transition-transform duration-300 ease-in-out" />
                                Kembali
                            </button>
                        </div>
                    </div>
                    <div
  v-if="detailLaporanHarian"
  class="mb-4 w-full p-6 bg-white border border-gray-200 rounded-2xl shadow-sm dark:bg-gray-800 dark:border-gray-700"
>
  <div class="space-y-6">
    <!-- Kegiatan -->
    <div class="flex flex-col md:flex-row gap-2 md:gap-4 text-sm">
      <div class="md:w-1/4 font-semibold text-gray-700 dark:text-gray-300">Kegiatan</div>
      <div class="md:w-3/4 text-gray-600 dark:text-gray-400">
        <p v-html="detailLaporanHarian.title" class="leading-relaxed"></p>
      </div>
    </div>

    <!-- Deskripsi Kegiatan -->
    <div class="flex flex-col md:flex-row gap-2 md:gap-4 text-sm">
      <div class="md:w-1/4 font-semibold text-gray-700 dark:text-gray-300">Deskripsi Kegiatan</div>
      <div class="md:w-3/4 text-gray-600 dark:text-gray-400">
        <p v-html="detailLaporanHarian.report" class="leading-relaxed"></p>
      </div>
    </div>

    <!-- Hasil yang Dicapai -->
    <div class="flex flex-col md:flex-row gap-2 md:gap-4 text-sm">
      <div class="md:w-1/4 font-semibold text-gray-700 dark:text-gray-300">Hasil yang Dicapai</div>
      <div class="md:w-3/4 text-gray-600 dark:text-gray-400">
        <p v-html="detailLaporanHarian.result" class="leading-relaxed"></p>
      </div>
    </div>
  </div>
</div>


                </div>
            </section>
        </div>
    </NuxtLayout>
</template>