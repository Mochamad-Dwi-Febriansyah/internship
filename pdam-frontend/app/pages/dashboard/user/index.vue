<script setup lang="ts">
definePageMeta({
  layout: 'dashboard',
  middleware: 'auth',
})
import { ref, onMounted, watch } from "vue";

import * as yup from "yup";
import type { SubmissionHandler } from "vee-validate";
import BaseBreadcrumb from "~/components/BaseBreadcrumb.vue";
import BaseCKEditor from "~/components/BaseCKEditor.vue";
import { useTodayPresensi } from "../../../composables/useTodayPresensi";
import { FormatDate } from "~/utils/FormatDate"
import { getIconName, getStatusLabel } from "~/utils/FormatStatus"
import { useNotification } from '@/composables/useNotification'
import { usePresensi } from "../../../composables/usePresensi";
import { useLaporanHarian } from "../../../composables/useLaporanHarian";

const { addNotification } = useNotification();
const config = useRuntimeConfig()

const currentPage = ref(1);
const { getUserFullName, getUserNisnNpmNimNpp, getUserEmail, getFoto } = useAuth()
const { presensi, pending, refreshPresensi, createPresensi, pendingCreate, } = usePresensi(currentPage)
const { todayPresensi, pending: pendingGetTodayPresensi, refreshTodayPresensi } = useTodayPresensi(currentPage)

const breadcrumb = [
  {
    label: "Dashboard",
    icon: "material-symbols:dashboard",
    to: "/dashboard/user",
  },
];

const time = ref("");
const updateTime = () => {
  time.value = new Date().toLocaleDateString("id-ID", {
    weekday: "long", // Nama hari (Senin, Selasa, dst.)
    day: "2-digit", // Tanggal (01, 02, dst.)
    month: "long", // Nama bulan (Januari, Februari, dst.)
    year: "numeric", // Tahun (2025, dst.)
  });
};

onMounted(async () => {
  updateTime();
  setInterval(updateTime, 1000);
});

const videoElement = ref<HTMLVideoElement | null>(null)
const capturedImage = ref<string | null>(null)
const stream = ref<MediaStream | null>(null)
const step = ref(1)
const latitude = ref<number | null>(null)
const longitude = ref<number | null>(null)

const startCamera = async () => {
  try {
    stream.value = await navigator.mediaDevices.getUserMedia({ video: true })
    step.value = 2
    if (videoElement.value) {
      videoElement.value.srcObject = stream.value;
    }
  } catch (error) {
    console.error("Gagal mengakses kamera:", error)
  }
}

const stopCamera = () => {
  if (stream.value) {
    stream.value.getTracks().forEach(track => track.stop()); // Hentikan semua track video
    stream.value = null;
  }
};

const captureImage = async () => {
  if (!videoElement.value) return

  const video = videoElement.value
  const canvas = document.createElement("canvas")
  canvas.width = video.videoWidth
  canvas.height = video.videoHeight

  const ctx = canvas.getContext("2d")
  if (ctx) {
    ctx.drawImage(video, 0, 0, canvas.width, canvas.height)
    capturedImage.value = canvas.toDataURL("image/png")
  }
  stopCamera();
  step.value = 3
  const location = await getLocation() as { latitude: number, longitude: number };
  latitude.value = location.latitude
  longitude.value = location.longitude
  console.log("Lokasi pengguna:", location);
  // console.log(capturedImage.value)
}
const reset = () => {
  capturedImage.value = null;
  step.value = 2; // Kembali ke awal
};

// 🔹 Pantau perubahan step agar kamera hanya aktif di step 2
watch(step, async (newStep) => {
  if (newStep === 2) {
    await startCamera();
  }
});

const getLocation = () => {
  return new Promise((resolve, reject) => {
    if (!navigator.geolocation) {
      reject(new Error("Geolocation tidak didukung di browser ini"));
    } else {
      navigator.geolocation.getCurrentPosition(
        (position) => {
          resolve({
            latitude: position.coords.latitude,
            longitude: position.coords.longitude,
          });
        },
        (error) => {
          reject(error);
        }
      );
    }
  });
};
// console.log(todayPresensi)
const kirim = async () => {
  const formData = new FormData()
  formData.append('latitude', latitude.value?.toString() || '')
  formData.append('longitude', longitude.value?.toString() || '')
  formData.append("tanggal", getFormattedDate()); // Tambahkan tanggal
  formData.append("waktu", getFormattedTime()); // Tambahkan waktu
  if (capturedImage.value) {
    const blob = dataURLtoBlob(capturedImage.value);
    formData.append("foto", blob, "capture.png"); // Nama file opsional
  }
  // for (const pair of formData.entries()) {
  //   console.log(`${pair[0]}:`, pair[1]);
  // }
  // console.log(formData)
  try {
    const response = await createPresensi(formData);
    stopCamera();
    step.value = 1;
    refreshPresensi()
    refreshTodayPresensi()
    addNotification('success', response.message)
  } catch (error: any) {
    addNotification('error', error.data.message)
  }
  // Debug: Cek isi FormData
}
const dataURLtoBlob = (dataURL: string): Blob => {
  const arr = dataURL.split(",");
  const mimeMatch = arr[0]?.match(/:(.*?);/);
  const mime = mimeMatch && mimeMatch[1] ? mimeMatch[1] : "image/png";
  const bstr = atob(arr[1] || "");
  let n = bstr.length;
  const u8arr = new Uint8Array(n);
  while (n--) {
    u8arr[n] = bstr.charCodeAt(n);
  }
  return new Blob([u8arr], { type: mime });
};

const getFormattedDate = (): string => {
  const now = new Date();
  const year = now.getFullYear();
  const month = String(now.getMonth() + 1).padStart(2, "0"); // Tambah 1 karena bulan mulai dari 0
  const day = String(now.getDate()).padStart(2, "0");

  return `${year}-${month}-${day}`;
};

const getFormattedTime = (): string => {
  const now = new Date();
  const hours = String(now.getHours()).padStart(2, "0");
  const minutes = String(now.getMinutes()).padStart(2, "0");
  const seconds = String(now.getSeconds()).padStart(2, "0");

  return `${hours}:${minutes}:${seconds}`;
};

const tanggalPresensi = ref("");
const idPresensi = ref("");


const addLaporanHarian = async (id: string, tanggal: string) => {
  idPresensi.value = id
  tanggalPresensi.value = tanggal
}

const addLaporanSchema = yup.object({
  tanggal: yup.date().typeError('Tanggal presensi tidak valid').required('Tanggal presensi wajib diisi'),
  title: yup.string().required('Kegiatan wajib diisi'),
  report: yup.string().required('Deskripsi kegiatan wajib diisi'),
  result: yup.string().required('Hasil yang dicapai wajib diisi'),
})

const { createLaporanHarian, errors: errorsCreateLaporanHarian, pendingCreate: pendingCreateLaporanHarian } = useLaporanHarian(currentPage)

const handleCreateLaporan: SubmissionHandler<any> = async (value, { resetForm }: { resetForm: () => void }) => {
  const formData = new FormData()
  formData.append('presensi_id', idPresensi.value ?? '')
  formData.append('title', value.title ?? '')
  formData.append('report', value.report ?? '')
  // formData.append('report', report.value ?? '')
  formData.append('result', value.result ?? '')
  // for (const pair of formData.entries()) {
  //   console.log(`${pair[0]}:`, pair[1]);
  // }
  try {
    const response = await createLaporanHarian(formData);
    resetForm();
    tanggalPresensi.value = ""
    refreshPresensi()
    addNotification('success', response.message)
  } catch (error: any) {
    addNotification('error', error.data.message)
  }

} 
</script>

<template>
  <NuxtLayout>
    <BaseBreadcrumb :items="breadcrumb" />
    <div class="p-3 bg-white">
      <div class="flex flex-col md:flex-row  flex-wrap gap-3">
        <section class="mb-3 flex-[1] min-w-[300px] flex flex-col overflow-x-auto w-full">
          <h3 class="text-md text-gray-700 font-medium mb-2">Presensi</h3>
          <div class="flex flex-col  gap-3 mb-2   card-animation">
            <div v-if="step === 1"
              class="overflow-x-auto bg-white  p-6  rounded-lg shadow-sm dark:bg-gray-800 dark:border-gray-700">
            <!-- <div v-if="step === 1"
              class="overflow-x-auto bg-gradient-to-tr from-green-50 to-cyan-50  p-6  rounded-lg shadow-sm dark:bg-gray-800 dark:border-gray-700"> -->
              <!-- di tabel users tambahkan foto -->
              <div v-if="pendingGetTodayPresensi">
                <div class="flex flex-row mb-3">
                  <div class="w-20 h-20 bg-gray-300 rounded-md"></div>
                  <div class="ms-3">
                    <div class="w-40 h-4 bg-gray-300 rounded mt-2"></div>
                    <div class="w-32 h-4 bg-gray-300 rounded mt-2"></div>
                    <div class="w-48 h-4 bg-gray-300 rounded mt-2"></div>
                  </div>
                </div>
              </div>
              <div v-else>
                <div class="flex flex-row mb-3 space-x-4">
                  <!-- {{ todayPresensi }} -->
                  <NuxtImg :src="getFoto
                    ? `${config.public.storage}/storage/${getFoto}`
                    : 'https://rotendaokab.go.id/wp-content/uploads/2016/08/dummy-prod-1.jpg'" alt="Foto Identitas"
                    style="max-width: 80px; max-height: 80px; object-fit: cover;" format="webp" loading="lazy" />
                  <div class="overflow-hidden">
                    <p class="text-sm text-gray-700 dark:text-white line-clamp-1">{{ getUserFullName }} </p>
                    <p class="text-sm text-gray-700 dark:text-white line-clamp-1"> {{ getUserNisnNpmNimNpp }} </p>
                    <p class="text-sm text-gray-700 dark:text-white line-clamp-1">{{ getUserEmail }}</p>
                    <p class="text-sm text-gray-700 dark:text-white line-clamp-1">{{ FormatDate(new Date()) }}</p>
                  </div>
                </div>
                <div class="w-full h-[1px] bg-gradient-to-r from-green-400 to-green-600 mb-3"></div>
                <div class="flex flex-row flex-wrap mb-3 gap-2">
                  <div class="flex-[1] flex flex-col text-green-600 dark:text-white">
                    <div class="flex items-center">
                      <Icon name="material-symbols-light:export-notes-rounded" class="w-5 h-5 " />
                      <p class="ms-1 text-sm font-medium  ">Datang</p>
                    </div>
                    <p class="ms-1 text-sm">{{ todayPresensi?.data?.waktu_check_in ?? '--:--' }}</p>
                  </div>
                  <div class="flex-[1] flex flex-col text-red-600 dark:text-white">
                    <div class="flex items-center">
                      <Icon name="material-symbols-light:export-notes-rounded" class="w-5 h-5 " />
                      <p class="ms-1 text-sm font-medium  ">Pulang</p>
                    </div>
                    <p class="ms-1 text-sm ">{{ todayPresensi?.data?.waktu_check_out ?? '--:--' }}</p>
                  </div>
                </div>
                <button :disabled="!!(todayPresensi?.data?.waktu_check_in && todayPresensi?.data?.waktu_check_out)"
                  @click="startCamera" :class="{
                    'bg-gray-400 cursor-not-allowed': todayPresensi?.data?.waktu_check_in && todayPresensi?.data?.waktu_check_out,
                    'bg-blue-500 hover:bg-blue-600': !(todayPresensi?.data?.waktu_check_in && todayPresensi?.data?.waktu_check_out)
                  }"
                  class="text-white bg-blue-700  focus:ring-4 focus:outline-none focus:ring-blue-300 font-medium rounded-lg text-sm w-full  px-5 py-2.5 text-center dark:bg-blue-600 dark:hover:bg-blue-700 dark:focus:ring-blue-800">Presensi
                </button>
              </div>
            </div>
            <div v-if="step === 2"
              class="bg-gradient-to-tr from-green-50 to-cyan-50  w-full p-6 bg-white border border-gray-200 rounded-lg shadow-sm dark:bg-gray-800 dark:border-gray-700">
              <div class="w-full  bg-white mb-3">
                <video ref="videoElement" class="w-full h-full rounded" autoplay></video>
              </div>
              <button @click="captureImage"
                class="text-white bg-blue-700 hover:bg-blue-800 focus:ring-4 focus:outline-none focus:ring-blue-300 font-medium rounded-lg text-sm w-full  px-5 py-2.5 text-center dark:bg-blue-600 dark:hover:bg-blue-700 dark:focus:ring-blue-800">Ambil
                Gambar</button>
            </div>
            <div v-if="step === 3"
              class="bg-gradient-to-tr from-green-50 to-cyan-50  w-full p-6 bg-white border border-gray-200 rounded-lg shadow-sm dark:bg-gray-800 dark:border-gray-700">
              <div class="w-full bg-white mb-3">
                <img v-if="capturedImage" :src="capturedImage" alt="Captured" class=" object-contain rounded">
                <p v-else class="text-gray-500">tampilkan gambar disini</p>

              </div>
              <iframe :src="`https://www.google.com/maps?q=${latitude},${longitude}&output=embed`" class="mb-3"
                width="100%" height="300" style="border:0;"></iframe>

              <button @click="reset"
                class="w-full text-white bg-gray-500 hover:bg-gray-600 focus:ring-4 focus:outline-none focus:ring-gray-300 font-medium rounded-lg text-sm px-5 py-2.5 mb-2">
                Ambil Ulang
              </button>
              <button @click="kirim" :disabled="pendingCreate"
                class="text-white bg-blue-700 hover:bg-blue-800 focus:ring-4 focus:outline-none focus:ring-blue-300 font-medium rounded-lg text-sm w-full px-5 py-2.5 text-center flex items-center justify-center gap-2 dark:bg-blue-600 dark:hover:bg-blue-700 dark:focus:ring-blue-800">
                <Icon v-if="pendingCreate" name="codex:loader" class="animate-spin w-5 h-5" />
                <span v-else>Kirim</span>
              </button>

            </div>
          </div>
          <div
            class="bg-gradient-to-tr from-orange-50 to-cyan-50  p-6 bg-white border border-gray-200 rounded-lg shadow-sm dark:bg-gray-800 dark:border-gray-700 space-y-2">
            <div v-for="dataPresensi in presensi.data.data.slice(0, 3)" :key="dataPresensi.id"
              class=" border-b border-gray-300 last:border-0 pb-2 last:pb-0">

              <p class="text-right text-xs font-medium text-gray-700 dark:text-white whitespace-nowrap">
                {{ FormatDate(dataPresensi?.tanggal) }}
              </p>
              <div class="flex items-center space-x-2 w-full">
                <!-- Avatar Container -->

                <div class="relative w-14 h-10 flex shrink-0">
                  <NuxtImg :src="dataPresensi.foto_check_in
                    ? `${config.public.storage}/storage/${dataPresensi.foto_check_in}`
                    : 'https://rotendaokab.go.id/wp-content/uploads/2016/08/dummy-prod-1.jpg'"
                    class="rounded-full w-10 h-10 object-cover relative z-10 border-2 border-white" alt="Foto Identitas"
                    format="webp" loading="lazy" />
                  <NuxtImg :src="dataPresensi.foto_check_out
                    ? `${config.public.storage}/storage/${dataPresensi.foto_check_out}`
                    : 'https://rotendaokab.go.id/wp-content/uploads/2016/08/dummy-prod-1.jpg'"
                    class="rounded-full w-10 h-10 object-cover absolute top-0 left-5 border-2 border-white"
                    alt="Foto Identitas" format="webp" loading="lazy" />
                </div>


                <!-- Informasi Presensi -->
                <div class="flex flex-col items-start space-y-1">
                  <div class="flex flex-row items-center">
                    <p class="text-xs  text-gray-800">Datang </p>
                    <p class="ms-1 text-xs  text-gray-800">{{ dataPresensi?.waktu_check_in }}</p>
                  </div>
                  <div class="flex flex-row items-center">
                    <p class="text-xs  text-gray-800">Pulang</p>
                    <p class="ms-1 text-xs  text-gray-800">{{ dataPresensi?.waktu_check_out }}</p>
                  </div>
                </div>

                <!-- Status Laporan -->
                <div class="flex flex-row w-full  justify-end">
                  <div v-if="!dataPresensi?.laporan_harian" class="flex justify-end items-center space-x-1 ms-auto">
                    <Icon @click="addLaporanHarian(dataPresensi?.id, dataPresensi?.tanggal)"
                      name="material-symbols:add-to-photos-outline-rounded"
                      class="text-green-500 w-5 h-5 cursor-pointer" />
                    <!-- <span class="text-xs rounded-xl text-white bg-red-500 py-1 px-2 whitespace-nowrap"></span> -->
                  </div>

                  <div v-else>
                    <span class="text-xs rounded-xl py-1 px-2 whitespace-nowrap" :class="{
                      'bg-yellow-100 border border-yellow-200 text-yellow-600  ': dataPresensi.laporan_harian.status === 'pending',
                      'bg-green-100 border border-green-200 text-green-600  ': dataPresensi.laporan_harian.status === 'approved',
                      'bg-red-100 border border-red-200 text-red-600 ': dataPresensi.laporan_harian.status === 'reject',
                    }">
                      {{ getStatusLabel(dataPresensi.laporan_harian?.status) }}
                    </span>
                  </div>

                </div>
              </div>

            </div>

          </div>
        </section>

        <section class="mb-3 flex-[2]">
          <div class="flex flex-row justify-between">
            <h3 class="text-md text-gray-700 font-medium mb-2">Laporan Harian</h3>
            <!--              <Icon @click="closeFormLaporanHarian()" class="text-red-500 w-5 h-5 cursor-pointer" name="material-symbols:cancel-rounded"/>-->
          </div>
          <div
            class="w-full p-6 bg-white  border border-gray-200 rounded-lg shadow-sm dark:bg-gray-800 dark:border-gray-700">
            <Form @submit="handleCreateLaporan" :validation-schema="addLaporanSchema" :validate-on-mount="false">
              <div class="grid grid-cols-1 md:grid-cols-2  gap-4 mt-4">
                <div>
                  <BaseInput label="Tanggal" name="tanggal" type="date" :errors="errorsCreateLaporanHarian"
                    :disabled="true" v-model="tanggalPresensi" />
                  <p v-if="errorsCreateLaporanHarian.presensi_id" class="text-red-500 text-sm">Silahkan pilih tanggal
                  </p>
                </div>
                <BaseInput label="Nama Kegiatan" name="title" type="text" :disabled="!tanggalPresensi"
                  :errors="errorsCreateLaporanHarian" />
              </div>
              <div class="grid grid-cols-1 md:grid-cols-1 mt-4">
                <div>
                  <label for="report" class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">
                    Deskripsi Kegiatan
                  </label>
                  <Field :disabled="!tanggalPresensi" name="report" as="textarea" id="report" class="bg-white border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-blue-500 dark:focus:border-blue-500 h-32  disabled:bg-gray-100 disabled:text-gray-500 disabled:cursor-not-allowed
                   dark:disabled:bg-gray-800 dark:disabled:text-gray-500" />

                  <ErrorMessage name="report" class="text-red-500 text-sm" />

                  <p v-if="errorsCreateLaporanHarian.report" class="text-red-500 text-sm">
                    {{ errorsCreateLaporanHarian.report[0] }}
                  </p>
                </div>
              </div>
              <!-- <label for="deskripsi_kegiatan" class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">Deskripsi Kegiatan</label>
                <Field name="report" v-slot="{ field, errors }" class="m-3">
                  <client-only>
                    <BaseCKEditor v-bind="field" v-model="report" />
                  </client-only>
                  <span class="text-red-500 text-sm">{{ errors[0] }}</span>
                </Field>
                <p v-if="errorsCreateLaporanHarian.report" class="text-red-500 text-sm">{{
                  errorsCreateLaporanHarian.report[0] }}</p> -->

              <div class="grid grid-cols-1 md:grid-cols-1  mt-4">
                <div>
                  <label for="result" class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">
                    Hasil yang dicapai
                  </label>
                  <Field :disabled="!tanggalPresensi" name="result" as="textarea" id="result" class="bg-white border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-blue-500 dark:focus:border-blue-500 h-32  disabled:bg-gray-100 disabled:text-gray-500 disabled:cursor-not-allowed
                    dark:disabled:bg-gray-800 dark:disabled:text-gray-500" />

                  <ErrorMessage name="result" class="text-red-500 text-sm" />

                  <p v-if="errorsCreateLaporanHarian.result" class="text-red-500 text-sm">
                    {{ errorsCreateLaporanHarian.result[0] }}
                  </p>
                </div>
                <!-- <label for="hasil_yang_dicapai" class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">Hasil yang dicapai</label>
                <Field name="result" v-slot="{ field, errors }" class="m-3">
                  <client-only>
                    <BaseCKEditor v-bind="field" v-model="result" />
                  </client-only>
                  <span class="text-red-500 text-sm">{{ errors[0] }}</span>
                </Field>
                <p v-if="errorsCreateLaporanHarian.result" class="text-red-500 text-sm">{{
                  errorsCreateLaporanHarian.result[0] }}</p> -->
              </div>
              <div v-if="tanggalPresensi" class="mt-6">
                <div class=" flex flex-row justify-end gap-3">
                  <button  @click="tanggalPresensi = ''"
                    class=" text-white bg-red-700 hover:bg-red-800 focus:ring-4 focus:outline-none focus:ring-red-300 font-medium rounded-lg text-sm w-full sm:w-auto px-5 py-2.5 text-center dark:bg-red-600 dark:hover:bg-red-700 dark:focus:ring-red-800">
                    <span>Batal</span>
                  </button>
                  <button type="submit"
                    class=" text-white bg-blue-700 hover:bg-blue-800 focus:ring-4 focus:outline-none focus:ring-blue-300 font-medium rounded-lg text-sm w-full sm:w-auto px-5 py-2.5 text-center dark:bg-blue-600 dark:hover:bg-blue-700 dark:focus:ring-blue-800">
                    <Icon v-if="pendingCreateLaporanHarian" name="tabler:loader-2"
                      class="w-4 h-4 animate-spin text-white" />
                    <span v-else>Tambah</span>
                  </button>
                </div>
              </div>
            </Form>
          </div>
        </section>
      </div>
    </div>


  </NuxtLayout>
</template>
<style scoped>
.no-scrollbar {
  scrollbar-width: none;
  /* Untuk Firefox */
  -ms-overflow-style: none;
  /* Untuk Internet Explorer dan Edge lama */
}

.no-scrollbar::-webkit-scrollbar {
  display: none;
  /* Untuk Chrome, Safari, dan Edge baru */
}

.card-animation {
  position: relative;
  padding: 5px;
  z-index: 1;
}

@property --angle {
  syntax: "<angle>";
  initial-value: 0deg;
  inherits: false;
}

.card-animation::after,
.card-animation::before {
  content: '';
  position: absolute;
  height: 100%;
  width: 100%;
  background-image: conic-gradient(from var(--angle), #ff4545, #00ff99, #ff0095, #ff4545);
  /* background-image: conic-gradient(from var(--angle), transparent 20%, blue); */
  top: 50%;
  left: 50%;
  translate: -50% -50%;
  z-index: -1;
  padding: 5px;
  border-radius: 10px;
  animation: 4s spin infinite;
}

.card-animation::before {
  /* filter: blur(1rem); */
  opacity: 0.5;
}

@keyframes spin {

  from {
    --angle: 0deg;
  }

  to {
    --angle: 360deg;
  }
}
</style>