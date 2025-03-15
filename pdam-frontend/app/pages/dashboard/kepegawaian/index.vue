<script setup lang="ts">
import SkeletonCard from '~/components/SkeletonCard.vue'

definePageMeta({
  layout: 'dashboard',
  middleware: 'auth',
})

const breadcrumb = [
  { label: "Dashboard", icon: "material-symbols:dashboard", to: "/dashboard/kepegawaian" }
]

const { 
  getTotalPengajuanBerkas, 
  getTotalUserMagang, 
  getTotalUserMentor, 
  getTotalVerifikasiLaporanAkhir, 
  pending, 
  errors 
} = useKepegawaianDashboard()

interface TotalPengajuanBerkas {
  total_belum_disetujui: number;
  total_belum_kirim_surat: number;
}

const totalMagang = ref<number | null>(null)
const totalPengajuanBerkas = ref<TotalPengajuanBerkas | null>(null)
const totalMentor = ref<number | null>(null)
const totalKepegawaian = ref<number | null>(null)

onMounted(async () => {
  try {
    const [pengajuanBerkas, magang, mentor, kepegawaian] = await Promise.all([
      getTotalPengajuanBerkas(),
      getTotalUserMagang(),
      getTotalUserMentor(),
      getTotalVerifikasiLaporanAkhir(),
    ])

    totalPengajuanBerkas.value = pengajuanBerkas.data
    totalMagang.value = magang.data
    totalMentor.value = mentor.data
    totalKepegawaian.value = kepegawaian.data
  } catch (error) {
    console.error(error)
  }
})
</script>

<template>
  <NuxtLayout>
    <BaseBreadcrumb :items="breadcrumb" />

    <section class="p-4">
      <div class="flex flex-wrap gap-3 justify-start">

        <!-- Card Pengajuan Berkas -->
        <NuxtLink to="/dashboard/kepegawaian/pengajuan-berkas" class="no-underline">
          <div class="h-full flex flex-col flex-1 min-w-[260px] max-w-[300px] p-4 bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-gray-200 dark:border-gray-700 hover:shadow-md transition-shadow duration-200">
            <SkeletonCard v-if="pending || totalPengajuanBerkas === null" />
            <div v-else>
              <h5 class="text-base font-semibold text-gray-900 dark:text-white mb-2">Total Pengajuan Berkas</h5>
              <p class="text-sm text-gray-700 dark:text-gray-300">
                Belum Disetujui: <span class="font-bold">{{ totalPengajuanBerkas?.total_belum_disetujui }}</span>
              </p>
              <p class="text-sm text-gray-700 dark:text-gray-300">
                Belum Kirim Surat Balasan: <span class="font-bold">{{ totalPengajuanBerkas?.total_belum_kirim_surat }}</span>
              </p>
            </div>
          </div>
        </NuxtLink>

        <!-- Card Total Magang -->
        <NuxtLink to="/dashboard/kepegawaian/magang" class="no-underline">
          <div class="h-full flex flex-col flex-1 min-w-[260px] max-w-[300px] p-4 bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-gray-200 dark:border-gray-700 hover:shadow-md transition-shadow duration-200">
            <SkeletonCard v-if="pending || totalMagang === null" />
            <div v-else>
              <h5 class="text-base font-semibold text-gray-900 dark:text-white mb-2">Total Magang</h5>
              <p class="text-xl font-bold text-gray-700 dark:text-gray-300">{{ totalMagang }}</p>
            </div>
          </div>
        </NuxtLink>

        <!-- Card Total Mentor -->
        <NuxtLink to="/dashboard/kepegawaian/mentor" class="no-underline">
          <div class="h-full flex flex-col flex-1 min-w-[260px] max-w-[300px] p-4 bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-gray-200 dark:border-gray-700 hover:shadow-md transition-shadow duration-200">
            <SkeletonCard v-if="pending || totalMentor === null" />
            <div v-else>
              <h5 class="text-base font-semibold text-gray-900 dark:text-white mb-2">Total Mentor</h5>
              <p class="text-xl font-bold text-gray-700 dark:text-gray-300">{{ totalMentor }}</p>
            </div>
          </div>
        </NuxtLink>

        <!-- Card Total Kepegawaian (Laporan Akhir) -->
        <NuxtLink to="/dashboard/kepegawaian/magang/laporan-akhir" class="no-underline">
          <div class="h-full flex flex-col flex-1 min-w-[260px] max-w-[300px] p-4 bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-gray-200 dark:border-gray-700 hover:shadow-md transition-shadow duration-200">
            <SkeletonCard v-if="pending || totalKepegawaian === null" />
            <div v-else>
              <h5 class="text-base font-semibold text-gray-900 dark:text-white mb-2">Total Kepegawaian</h5>
              <p class="text-xl font-bold text-gray-700 dark:text-gray-300">{{ totalKepegawaian }}</p>
            </div>
          </div>
        </NuxtLink>

      </div>
    </section>
  </NuxtLayout>
</template>
