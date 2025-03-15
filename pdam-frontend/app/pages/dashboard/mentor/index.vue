<script setup lang="ts">
import SkeletonCard from '~/components/SkeletonCard.vue'

definePageMeta({
  layout: 'dashboard',
  middleware: 'auth',
})

const breadcrumb = [
  { label: "Dashboard", icon: "material-symbols:dashboard", to: "/dashboard/mentor" }
]

// Composable untuk ambil data (nanti disesuaikan endpointnya)
const { 
getTotalUserMagangByMentor,
getTotalVerifikasiLaporanHarian,
getTotalVerifikasiLaporanAkhir, 
  pending, 
  errors 
} = useMentorDashboard()

const totalMahasiswaMagang = ref<number | null>(null)
const totalLaporanHarian = ref<number | null>(null)
const totalLaporanAkhir = ref<number | null>(null)

onMounted(async () => {
  try {
    const [magang, laporanHarian, laporanAkhir] = await Promise.all([
      getTotalUserMagangByMentor(),
      getTotalVerifikasiLaporanHarian(),
      getTotalVerifikasiLaporanAkhir(),
    ])

    totalMahasiswaMagang.value = magang.data
    totalLaporanHarian.value = laporanHarian.data
    totalLaporanAkhir.value = laporanAkhir.data
  } catch (error) {
    console.error(error)
  }
})
</script>

<template>
  <NuxtLayout>
    <BaseBreadcrumb :items="breadcrumb" />

    <section class="p-4">
      <div class="flex flex-wrap gap-3 ">

        <!-- Card Mahasiswa Magang -->
        <NuxtLink to="/dashboard/mentor/magang" class="no-underline">
          <div class="h-full flex flex-col flex-1 min-w-[260px] max-w-[300px] p-4 bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-gray-200 dark:border-gray-700 hover:shadow-md transition-shadow duration-200">
            <SkeletonCard v-if="pending || totalMahasiswaMagang === null" />
            <div v-else>
              <h5 class="text-base font-semibold text-gray-900 dark:text-white mb-2">Mahasiswa Magang</h5> 
              <p class="text-xl font-bold text-gray-700 dark:text-gray-300">{{ totalMahasiswaMagang }}</p>
            </div>
          </div>
        </NuxtLink>

        <!-- Card Verifikasi Laporan Harian -->
        <NuxtLink to="/dashboard/mentor/magang/laporan-harian" class="no-underline">
          <div class="h-full flex flex-col flex-1 min-w-[260px] max-w-[300px] p-4 bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-gray-200 dark:border-gray-700 hover:shadow-md transition-shadow duration-200">
            <SkeletonCard v-if="pending || totalLaporanHarian === null" />
            <div v-else>
              <h5 class="text-base font-semibold text-gray-900 dark:text-white mb-2">Laporan Harian</h5>
              <p class="text-xl font-bold text-gray-700 dark:text-gray-300">{{ totalLaporanHarian }}</p>
            </div>
          </div>
        </NuxtLink>

        <!-- Card Verifikasi Laporan Akhir -->
        <NuxtLink to="/dashboard/mentor/magang/laporan-akhir" class="no-underline">
          <div class="h-full flex flex-col flex-1 min-w-[260px] max-w-[300px] p-4 bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-gray-200 dark:border-gray-700 hover:shadow-md transition-shadow duration-200">
            <SkeletonCard v-if="pending || totalLaporanAkhir === null" />
            <div v-else>
              <h5 class="text-base font-semibold text-gray-900 dark:text-white mb-2">Laporan Akhir</h5>
              <p class="text-xl font-bold text-gray-700 dark:text-gray-300">{{ totalLaporanAkhir }}</p>
            </div>
          </div>
        </NuxtLink> 

      </div>
    </section>
  </NuxtLayout>
</template>
