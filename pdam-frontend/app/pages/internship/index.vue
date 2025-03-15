<script setup lang="ts"> 
import * as yup from 'yup'
import type { SubmissionHandler, FieldContext } from 'vee-validate'
import { type InternshipForm } from '../../../types/types'
import { useNotification } from '@/composables/useNotification'
import { useBerkas } from '@/composables/useBerkas'
import { useRegionIndonesia } from '@/composables/useRegionIndonesia'
import { useRouter, useRoute } from 'vue-router'
import { nextTick } from 'vue'
import { ref, computed } from 'vue'

const { addNotification } = useNotification()

const route = useRoute() 
const { ajukanBerkas, pending, errors } = useAjukanBerkas()


const steps = [
  { title: 'Informasi Pribadi' },
  { title: 'Informasi Sekolah' },
  { title: 'Berkas' },
];

const currentStep = ref(0);

// Daftar internship yang tersedia
const internships = ref([
  {
    title: "Magang Frontend Developer",
    company: "Tech Corp",
    description: "Kami mencari magang Frontend Developer yang mahir menggunakan Vue.js dan React.",
    location: "Jakarta, Indonesia",
    link: "/internship/1"
  },
  {
    title: "Magang Backend Developer",
    company: "Innovate Inc.",
    description: "Kami mencari magang Backend Developer yang memahami Node.js dan Express .",
    location: "Bandung, Indonesia",
    link: "/internship/2"
  },
  {
    title: "Magang UI/UX Designer",
    company: "DesignStudio",
    description: "Kami mencari magang UI/UX Designer untuk membantu mendesain antarmuka pengguna.",
    location: "Surabaya, Indonesia",
    link: "/internship/3"
  },
  {
    title: "Magang UI/UX Designer",
    company: "DesignStudio",
    description: "Kami mencari magang UI/UX Designer untuk membantu mendesain antarmuka pengguna.",
    location: "Surabaya, Indonesia",
    link: "/internship/3"
  }
]);

const files = ref({
  foto_identitas: null,
  surat_permohonan: null,
});

// Fungsi menangani upload file
const handleFileUpload = (event: Event, handleChange: (value: File | null) => void): void => {
  const target = event.target as HTMLInputElement;
  const file = target.files?.[0] || null;
  handleChange(file);
};

const validationSchema = [
  yup.object({
    nisn_npm_nim_npp: yup.string().required('NISN / NIM wajib diisi'),
    tanggal_lahir: yup.date()
        .typeError('Tanggal lahir tidak valid')
        .required('Tanggal lahir wajib diisi')
        .max(new Date(), 'Tanggal lahir tidak boleh di masa depan'),
    nama_depan: yup.string().required('Nama depan wajib diisi'),
    nama_belakang: yup.string(),
    jenis_kelamin: yup.string().oneOf(['male', 'female'], 'Pilih jenis kelamin yang valid').required('Jenis kelamin wajib diisi'),
    nomor_hp: yup.string().matches(/^[0-9]+$/, 'Nomor HP hanya boleh berisi angka').required('Nomor HP wajib diisi'),
    alamat: yup.string().required('Alamat wajib diisi'),
    provinsi: yup.string().required('Provinsi wajib diisi'),
    kabupaten_kota: yup.string().required('Kabupaten/Kota wajib diisi'),
    kecamatan: yup.string().required('Kecamatan wajib diisi'),
    kelurahan_desa: yup.string().required('Kelurahan/Desa wajib diisi'),
    // kode_pos: yup.string().matches(/^[0-9]+$/, 'Kode Pos hanya boleh berisi angka').required('Kode Pos wajib diisi'),
    email: yup.string().email('Format email tidak valid').required('Email wajib diisi'),
    //password: yup.string().required('Password wajib diisi').min(6, 'Password minimal 6 karakter'),
  }),
  yup.object({
    nama_sekolah_universitas: yup.string().required("Nama sekolah/universitas wajib diisi"),
    email_sekolah_universitas: yup.string().required("Email sekolah/universitas wajib diisi").email("Format email tidak valid"),
    jurusan_sekolah: yup.string(),
    fakultas_universitas: yup.string(),
    program_studi_universitas: yup.string(),
    alamat_sekolah_universitas: yup.string().required("Alamat sekolah/universitas wajib diisi"),
    // kode_pos_sekolah_universitas: yup.string().matches(/^\d{5}$/, "Kode pos harus 5 digit angka").required("Kode pos wajib diisi"),
    provinsi_sekolah_universitas: yup.string().required("Provinsi wajib diisi"),
    kabupaten_kota_sekolah_universitas: yup.string().required("Kabupaten/Kota wajib diisi"),
    kecamatan_sekolah_universitas: yup.string().required("Kecamatan wajib diisi"),
    kelurahan_desa_sekolah_universitas: yup.string().required("Kelurahan Desa wajib diisi"),
    nomor_telp_sekolah_universitas: yup.string().matches(/^\d+$/, "Nomor telepon hanya boleh mengandung angka").min(10, "Nomor telepon minimal 10 digit").max(15, "Nomor telepon maksimal 15 digit").required("Nomor telepon wajib diisi"),
  }),
  yup.object({
    foto_identitas: yup
      .mixed()
      .required("Foto identitas wajib diunggah")
      .test("fileFormat", "Format file harus JPG, PNG, atau PDF", (value) => {
        return value instanceof File && ["image/jpeg", "image/png", "application/pdf"].includes(value.type);
      })
      .test("fileSize", "Ukuran file maksimal 2MB", (value) => {
        return value instanceof File && value.size <= 2 * 1024 * 1024;
      }),

    surat_permohonan: yup
      .mixed()
      .required("Surat permohonan wajib diunggah")
      .test("fileFormat", "Format file harus JPG, PNG, atau PDF", (value) => {
        return value instanceof File && ["image/jpeg", "image/png", "application/pdf"].includes(value.type);
      })
      .test("fileSize", "Ukuran file maksimal 2MB", (value) => {
        return value instanceof File && value.size <= 2 * 1024 * 1024;
      }),
  }),
];

const currentSchema = computed(() => { 
  return validationSchema[currentStep.value];
});

const nextStep = async (values: unknown, { resetForm }: { resetForm: () => void }) => {
  try {
    const formValues = values as InternshipForm; // Paksa tipe ke InternshipForm
    const formData = new FormData();

    // Konversi object ke FormData
    Object.entries(formValues).forEach(([key, value]) => {
      if (value !== undefined && value !== null) {
        formData.append(key, value as string | Blob); // Handle file upload jika ada
      }
    });
    // 🚀 Override ID Wilayah dengan Nama Wilayah untuk Alamat Pribadi  
    if (selectedProvince.value) { 
      formData.set("provinsi", selectedProvince.value.name);
    }
    if (selectedDistrict.value) { 
      formData.set("kabupaten_kota", selectedDistrict.value.name);
    }
    if (selectedSubdistrict.value) { 
      formData.set("kecamatan", selectedSubdistrict.value.name);
    }
    if (villages.value.length > 0) { 
      const selectedVillage = villages.value.find(v => v.code === formValues.kelurahan_desa);
      if (selectedVillage) {
        formData.set("kelurahan_desa", selectedVillage.name);
        formData.set("kode_pos", selectedVillage.postal_code);
      }
    }

    // 🚀 Override ID Wilayah dengan Nama Wilayah untuk Alamat Sekolah/Universitas  
    if (selectedSchoolProvince.value) { 
      formData.set("provinsi_sekolah_universitas", selectedSchoolProvince.value.name);
    }
    if (selectedSchoolDistrict.value) { 
      formData.set("kabupaten_kota_sekolah_universitas", selectedSchoolDistrict.value.name);
    }
    if (selectedSchoolSubdistrict.value) { 
      formData.set("kecamatan_sekolah_universitas", selectedSchoolSubdistrict.value.name);
    }
    if (schoolVillages.value.length > 0) { 
      const selectedSchoolVillage = schoolVillages.value.find(v => v.code === formValues.kelurahan_desa_sekolah_universitas);
      if (selectedSchoolVillage) {
        formData.set("kelurahan_desa_sekolah_universitas", selectedSchoolVillage.name);
        formData.set("kode_pos_sekolah_universitas", selectedSchoolVillage.postal_code);
      }
    }
     for (const pair of formData.entries()) {
    console.log(`${pair[0]}:`, pair[1]);
  }
    if (currentStep.value === 2) {
      // console.log('Step:', currentStep.value);

      await ajukanBerkas(formData);

      // console.log('Done:', JSON.stringify(formValues, null, 2));
      resetForm();
      showForm.value = false
      currentStep.value = 0;
      addNotification('success', 'Pengajuan berkas berhasil dikirim!');
      addNotification('info', 'Silahkan cek email untuk mendapatkan nomor registrasi!', 10000);

      return;
    }

    currentStep.value++;
  } catch (error: any) {
    // console.error('Gagal mengajukan berkas:', (error as Error).message); 
    addNotification('error', error.data.message);
    const errorFields = Object.keys(error.data.errors).join(', ')
    addNotification('error', `Validasi gagal pada: ${errorFields}`)
  }
};



function prevStep() {
  if (currentStep.value <= 0) {
    return;
  }

  currentStep.value--;
}




const showForm = ref(false)
const toggleShowFormApply = () => {
  showForm.value = !showForm.value
  nextTick(() => {
        const targetElement = document.getElementById('apply-internship');
        if (targetElement) {
          targetElement.scrollIntoView({ behavior: 'smooth' });
        }
      });
  
}
const { province, getRegenciesByProvince, getDistrictByRegencies, getVillagesByDistrict } = useRegionIndonesia()
const selectedProvince = ref<{ code: string; name: string } | null>(null)
const selectedDistrict = ref<{ code: string; name: string } | null>(null)
const selectedSubdistrict = ref<{ code: string; name: string } | null>(null)

const districts = ref<{ code: string; name: string }[]>([]) // Kabupaten/Kota
const subdistricts = ref<{ code: string; name: string }[]>([]) // Kecamatan
const villages = ref<{ code: string; name: string, postal_code:string }[]>([]) // Desa/Kelurahan

const selectedSchoolProvince = ref<{ code: string; name: string } | null>(null)
const selectedSchoolDistrict = ref<{ code: string; name: string } | null>(null)
const selectedSchoolSubdistrict = ref<{ code: string; name: string } | null>(null)

const schoolDistricts = ref<{ code: string; name: string }[]>([]) // Kabupaten/Kota
const schoolSubdistricts = ref<{ code: string; name: string }[]>([]) // Kecamatan
const schoolVillages = ref<{ code: string; name: string, postal_code: string }[]>([]) // Desa/Kelurahan

const getDistrict = async (event: Event, type: 'identity' | 'school') => {
  const selectedId = (event.target as HTMLSelectElement).value
  const selectedData = Array.isArray(province.value.data) ? province.value.data.find((q: any) => q.code === selectedId) : null
  if (!selectedData) return

  if (type === 'identity') {
    selectedProvince.value = { code: selectedData.code, name: selectedData.name }
  } else {
    selectedSchoolProvince.value = { code: selectedData.code, name: selectedData.name }
  }

  try {
    const response = await getRegenciesByProvince(selectedId)
    if (type === 'identity') {
      districts.value = response.data
      subdistricts.value = [] // Reset kecamatan saat ganti kabupaten/kota
      villages.value = [] // Reset desa
    } else {
      schoolDistricts.value = response.data
      schoolSubdistricts.value = [] // Reset kecamatan saat ganti kabupaten/kota
      schoolVillages.value = [] // Reset desa
    }
  } catch (error) {
    console.error(`Gagal mengambil kota/kabupaten (${type}):`, error)
  }
}

const getSubdistricts = async (event: Event, type: 'identity' | 'school') => {
  const selectedId = (event.target as HTMLSelectElement).value
  const selectedData = (type === 'identity' ? districts.value : schoolDistricts.value).find(q => q.code === selectedId) || null
  if (!selectedData) return

  if (type === 'identity') {
    selectedDistrict.value = { code: selectedData.code, name: selectedData.name }
  } else {
    selectedSchoolDistrict.value = { code: selectedData.code, name: selectedData.name }
  }

  try {
    const response = await getDistrictByRegencies(selectedId)
    if (type === 'identity') {
      subdistricts.value = response.data
      villages.value = [] // Reset desa
    } else {
      schoolSubdistricts.value = response.data
      schoolVillages.value = [] // Reset desa
    }
  } catch (error) {
    console.error(`Gagal mengambil Kecamatan (${type}):`, error)
  }
}

const getVillages = async (event: Event, type: 'identity' | 'school') => {
  const selectedId = (event.target as HTMLSelectElement).value
  const selectedData = (type === 'identity' ? subdistricts.value : schoolSubdistricts.value).find(q => q.code === selectedId) || null
  if (!selectedData) return

  if (type === 'identity') {
    selectedSubdistrict.value = { code: selectedData.code, name: selectedData.name }
  } else {
    selectedSchoolSubdistrict.value = { code: selectedData.code, name: selectedData.name }
  }

  try {
    const response = await getVillagesByDistrict(selectedId)
    if (type === 'identity') {
      villages.value = response.data
    } else {
      schoolVillages.value = response.data
    }
  } catch (error) {
    console.error(`Gagal mengambil Desa/Kelurahan (${type}):`, error)
  }
}

const selectedGender = ref('');
</script>

<template>
  <NuxtLayout>
    <section>
      <div class="container lg:px-16 flex flex-col lg:flex-row items-center py-20 gap-10">
        <div class="flex-1 order-2 lg:order-1 text-center lg:text-left">
          <h1 class="text-4xl lg:text-5xl font-extrabold mb-6 text-balance text-gray-800">
            Jelajahi peluang magang dan program kerja
          </h1>
          <p class="text-xl lg:text-1xl mb-8 text-balance   text-gray-600">
            Dengan Budaya Kerja "Hebat" Satria Surya Sembada siap mewujudkan Air Sehat Untuk Masyarakat.
          </p>
          <div class="flex gap-2 flex-wrap justify-center md:justify-normal">
            <BaseBtn @click="toggleShowFormApply" label="Daftar Internship" />
            <NuxtLink to="/internship/cek-berkas"
              class="px-6 py-2  text-blue-600 self-start rounded-md font-medium text-sm md:text-base lg:text-lg cursor-pointer border border-blue-600 hover:bg-blue-50 inline-flex items-center">
              Cek Pengajuan Berkas
            </NuxtLink>
            <NuxtLink to="/signin"
              class="px-6 py-2  text-blue-600 self-start rounded-md font-medium text-sm md:text-base lg:text-lg cursor-pointer border border-blue-600 hover:bg-blue-50 inline-flex items-center">
              Login
            </NuxtLink>
          </div>
        </div>
        <div class="flex-1 order-1 lg:order-2">
          <NuxtImg sizes="xs:50vw sm:267px" src="/images/hero.png" format="webp" densities="x1" alt="" class="m-auto" />
        </div>
      </div>
    </section>

    <!-- Daftar Internship -->
    <section class="bg-back-gray py-12">
      <div id="internships" class="container lg:px-16 mb-3 md:py-12 py-12">
        <h2 class="text-3xl font-bold text-gray-800 mb-12">Kategori Berdasarkan Program Magang</h2>

        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
          <div v-for="internship in internships" :key="internship.title"
            class="border rounded-lg p-4 bg-white shadow-sm flex flex-col h-full">
            <div class="flex-1">
              <h2 class="text-xl font-semibold text-gray-800">{{ internship.title }}</h2>
              <p class="text-gray-600 text-sm">{{ internship.company }}</p>
              <p class="text-gray-500 mt-2">{{ internship.description }}</p>
              <p class="text-gray-400 text-sm mt-2">{{ internship.location }}</p>
            </div>

            <!-- Bagian Icon Tetap di Bawah -->
            <div class="flex justify-end items-end mt-4">
              <NuxtLink class="text-blue-600 hover:text-blue-500 hover:underline">
                <Icon name="material-symbols:arrow-forward-rounded" class="w-6 h-6" />
              </NuxtLink>
            </div>
          </div>
        </div>


      </div>
    </section>

    <!-- Alur Proses Magang -->
    <section class="py-12">
      <div id="process" class="container lg:px-16 mb-3 md:py-12 py-12">
        <h2 class="text-3xl font-bold text-gray-800 mb-12">Alur Proses Magang</h2>

        <div class="space-y-6">
          <div class="flex items-start">
            <div class="w-8 h-8 p-4 bg-blue-600 text-white rounded-full flex items-center justify-center mr-4">1</div>
            <div>
              <h3 class="text-xl font-semibold text-gray-800">Daftar Magang</h3>
              <p class="text-gray-600">Kirimkan aplikasi Anda melalui form pendaftaran yang ada di setiap detail magang
                yang tersedia.</p>
            </div>
          </div>

          <div class="flex items-start">
            <div class="w-8 h-8 p-4 bg-blue-600 text-white rounded-full flex items-center justify-center mr-4">2</div>
            <div>
              <h3 class="text-xl font-semibold text-gray-800">Seleksi Berkas</h3>
              <p class="text-gray-600">Tim kami akan meninjau berkas Anda dan memutuskan siapa yang akan lanjut ke tahap
                selanjutnya.</p>
            </div>
          </div>

          <div class="flex items-start">
            <div class="w-8 h-8 p-4 bg-blue-600 text-white rounded-full flex items-center justify-center mr-4">3</div>
            <div>
              <h3 class="text-xl font-semibold text-gray-800">Wawancara</h3>
              <p class="text-gray-600">Jika berkas Anda lolos seleksi, Anda akan diundang untuk wawancara baik secara
                online maupun tatap muka.</p>
            </div>
          </div>

          <div class="flex items-start">
            <div class="w-8 h-8 p-4 bg-blue-600 text-white rounded-full flex items-center justify-center mr-4">4</div>
            <div>
              <h3 class="text-xl font-semibold text-gray-800">Penawaran Magang</h3>
              <p class="text-gray-600">Setelah wawancara, kami akan memberikan penawaran magang jika Anda berhasil
                diterima.</p>
            </div>
          </div>

          <div class="flex items-start">
            <div class="w-8 h-8 p-4 bg-blue-600 text-white rounded-full flex items-center justify-center mr-4">5</div>
            <div>
              <h3 class="text-xl font-semibold text-gray-800">Mulai Magang</h3>
              <p class="text-gray-600">Setelah diterima, Anda akan mulai magang sesuai jadwal yang telah disepakati
                dengan perusahaan.</p>
            </div>
          </div>
        </div>
      </div>
    </section>

    <section   id="apply-internship" class="py-12 ">
    <!-- <section v-if="showForm" id="apply-internship" class="py-12 "> -->
      <div class="container lg:px-16 mb-3 md:py-12 py-12">
        <h2 class="text-3xl font-bold text-gray-800 mb-12">Daftar Magang</h2>

        <Form @submit="nextStep" :validation-schema="currentSchema" keep-values v-slot="{ values, resetForm }">
          <div class="space-y-6 max-w-7xl mx-auto flex flex-col">
            <!-- Left: Steps List -->
            <ol
              class="flex overflow-x-auto items-center w-full p-3 space-x-2 text-sm font-medium text-center text-gray-500 bg-white border border-gray-200 rounded-lg shadow-xs dark:text-gray-400 sm:text-base dark:bg-gray-800 dark:border-gray-700 sm:p-4 sm:space-x-4 rtl:space-x-reverse">
              <li v-for="(step, index) in steps" :key="step.title" class="flex items-center"
                :class="{ 'text-blue-600 dark:text-blue-500': currentStep >= index, 'text-gray-500 dark:text-gray-400': currentStep < index }">
                <span :class="{
                  'bg-blue-600 dark:bg-blue-500': currentStep >= index,
                  'bg-white dark:bg-gray-700 border-gray-500': currentStep < index
                }"
                  class="flex items-center justify-center w-5 h-5 me-2 text-xs border  rounded-full shrink-0 dark:border-blue-500">
                  {{ index + 1 }}
                </span>
                {{ step.title }}
                <svg v-if="currentStep > index" class="w-3 h-3 ms-2 sm:ms-4 rtl:rotate-180" aria-hidden="true"
                  xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 12 10">
                  <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="m7 9 4-4-4-4M1 9l4-4-4-4" />
                </svg>
              </li>
            </ol>


            <!-- Right: Form Section -->
            <div class=" ">
              <div class="mt-8">
                <template v-if="currentStep === 0">
                  <!-- Informasi Pribadi -->
                  <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mt-4">
                    <BaseInput label="Nama Depan" name="nama_depan" type="text" placeholder="Masukan nama depan" :errors="errors" required/>
                    <BaseInput label="Nama Belakang" name="nama_belakang" type="text" placeholder="Masukan nama belakang" :errors="errors"/>
                    <BaseInput label="NISN/NPM/NIM" name="nisn_npm_nim_npp" type="text" placeholder="exp. A1123xxxx" :errors="errors" required/>
                  </div>

                  <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mt-4"> 
                    <BaseInput label="Tanggal Lahir" name="tanggal_lahir" type="date"  :errors="errors" required/>
                    <BaseSelect label="Jenis Kelamin" name="jenis_kelamin" v-model="selectedGender"
                      :options="[
                        { value: 'male', text: 'Laki-laki' },
                        { value: 'female', text: 'Perempuan' }
                      ]" required />  
                    <BaseInput label="Nomor HP" name="nomor_hp" type="number" placeholder="85xxx" :errors="errors" required/>
                  </div>
 
                  <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mt-4">
                    <BaseInput label="Alamat" name="alamat" type="text" placeholder="Jl xxx no. xx" :errors="errors" required/>
<!-- belum berhasil di component reusable       -->
                    <div>
                      <label for="provinsi" class="block text-sm font-medium text-gray-700">Provinsi <span
                          class="text-red-500">*</span></label>
                      <Field as="select" name="provinsi" id="provinsi"
                        @change="(event: any) => getDistrict(event, 'identity')" class="mt-1 p-2 w-full border rounded-md">
                        <option value="" disabled selected>Pilih Provinsi</option>
                        <option v-for="data in province.data" :key="data.code" :value="data.code">{{ data.name }}</option>
                      </Field>
                      <ErrorMessage name="provinsi" class="text-red-500 text-sm" />
                      <p v-if="errors.provinsi" class="text-red-500 text-sm">{{ errors.provinsi[0] }}</p>
                    </div>
                    <div>
                      <label for="kabupaten_kota" class="block text-sm font-medium text-gray-700">Kabupaten/Kota <span
                          class="text-red-500">*</span></label>
                      <Field as="select" name="kabupaten_kota" id="kabupaten_kota"
                        @change="(event: any) => getSubdistricts(event, 'identity')"
                        class="mt-1 p-2 w-full border rounded-md" :disabled="!districts.length">
                        <option value="" disabled selected>Pilih kabupaten/Kota</option>
                        <option v-for="data in districts" :key="data.code" :value="data.code">{{ data.name }}</option>
                      </Field>
                      <ErrorMessage name="kabupaten_kota" class="text-red-500 text-sm" />
                      <p v-if="errors.kabupaten_kota" class="text-red-500 text-sm">{{ errors.kabupaten_kota[0] }}</p>
                    </div>
                  </div>
                  <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mt-4">
                    <div>
                      <label for="kecamatan" class="block text-sm font-medium text-gray-700">Kecamatan<span
                          class="text-red-500">*</span></label>
                      <Field as="select" name="kecamatan" id="kecamatan" class="mt-1 p-2 w-full border rounded-md"
                        :disabled="!subdistricts.length" @change="(event: any) => getVillages(event, 'identity')">
                        <option value="" disabled selected>Pilih Kecamatan</option>
                        <option v-for="data in subdistricts" :key="data.code" :value="data.code">
                          {{ data.name }}
                        </option>
                      </Field>
                      <ErrorMessage name="kecamatan" class="text-red-500 text-sm" />
                      <p v-if="errors.kecamatan" class="text-red-500 text-sm">{{ errors.kecamatan[0] }}</p>
                    </div>
                    <div>
                      <label for="kelurahan_desa" class="block text-sm font-medium text-gray-700">Kelurahan/Desa<span
                          class="text-red-500">*</span></label>
                      <Field as="select" name="kelurahan_desa" id="kelurahan_desa" class="mt-1 p-2 w-full border rounded-md"
                        :disabled="!villages.length">
                        <option value="" disabled selected>Pilih Desa/Kelurahan</option>
                        <option v-for="data in villages" :key="data.code" :value="data.code">
                          {{ data.name }}
                        </option>
                      </Field>
                      <ErrorMessage name="kelurahan_desa" class="text-red-500 text-sm" />
                      <p v-if="errors.kelurahan_desa" class="text-red-500 text-sm">{{ errors.kelurahan_desa[0] }}</p>
                    </div>

                  </div>

                  <div class="mt-4">
                    <h2 class="text-md md:text-lg font-semibold text-gray-800 border-b-2 border-gray-300 pb-2">
                      Kredensial Login
                    </h2>
                    <div class="grid grid-cols-1 md:grid-cols-1 gap-4 mt-4">
                      <BaseInput label="Email" name="email" type="email" placeholder="xx@xx.xx" :errors="errors" required/>
                      <!-- <div>
                        <label for="email" class="block text-sm font-medium text-gray-700">Email <span
                            class="text-red-500">*</span></label>
                        <Field name="email" type="email" id="email" class="mt-1 p-2 w-full border rounded-md" />
                        <ErrorMessage name="email" class="text-red-500 text-sm" />
                        <p v-if="errors.email" class="text-red-500 text-sm">{{ errors.email[0] }}</p>
                      </div> -->
                    </div>
                  </div>
                </template>

                <template v-if="currentStep === 1">
                  <!-- Account Info Form -->
                  <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mt-4">
                    <BaseInput label="Nama Sekolah/Universitas" name="nama_sekolah_universitas" type="text" placeholder="Masukan nama sekolah/universitas" :errors="errors" required/>
                    <BaseInput label="Email Sekolah/Universitas" name="email_sekolah_universitas" type="text" placeholder="Masukan email sekolah/universitas" :errors="errors" required/>
                    <BaseInput label="Nomor Telepon Sekolah/Universitas" name="nomor_telp_sekolah_universitas" type="text" placeholder="Masukan nomor telepon sekolah/universitas" :errors="errors" required/> 
                  </div>
                  <div class="mt-4">
                    <h2 class="text-md md:text-lg font-semibold text-gray-800 border-b-2 border-gray-300 pb-2">
                      Sekolah
                    </h2>
                    <p class="text-sm text-blue-600">Jika Anda masih bersekolah, silakan isi kolom di bawah ini.</p>
                    <div class="grid grid-cols-1  gap-4 mt-4">
                      <BaseInput label="Jurusan Sekolah" name="jurusan_sekolah" type="text" placeholder="Masukan jurusan sekolah" :errors="errors" /> 
                    </div>
                  </div>

                  <div class="mt-4">
                    <h2 class="text-md md:text-lg font-semibold text-gray-800 border-b-2 border-gray-300 pb-2">
                      Universitas
                    </h2>
                    <p class="text-sm text-blue-600">Jika Anda kuliah di universitas, silakan isi kolom di bawah ini.
                    </p>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mt-4">
                      <BaseInput label="Fakultas" name="fakultas_universitas" type="text" placeholder="Masukan fakultas" :errors="errors" /> 
                      <BaseInput label="Program Studi" name="program_studi_universitas" type="text" placeholder="Masukan program studi" :errors="errors" /> 
                    </div>
                  </div>
                  <div class="border-b-2 mt-4 border-gray-300 ">
                  </div>

                  <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mt-4">
                    <BaseInput label="Alamat sekolah universitas" name="alamat_sekolah_universitas" type="text" placeholder="Masukan alamat sekolah/universitas" :errors="errors" required/> 
                    <div>
                      <label for="provinsi_sekolah_universitas" class="block text-sm font-medium text-gray-700">Provinsi
                        <span class="text-red-500">*</span></label>
                      <Field as="select" name="provinsi_sekolah_universitas" id="provinsi_sekolah_universitas"
                        @change="(event: any) => getDistrict(event, 'school')" class="mt-1 p-2 w-full border rounded-md">
                        <option value="" disabled selected>Pilih Provinsi</option>
                        <option v-for="data in province.data" :key="data.code" :value="data.code">{{ data.name }}</option>
                      </Field>
                      <ErrorMessage name="provinsi_sekolah_universitas" class="text-red-500 text-sm" />
                      <p v-if="errors.provinsi_sekolah_universitas" class="text-red-500 text-sm">{{
                        errors.provinsi_sekolah_universitas[0] }}</p>
                    </div>
                    <div>
                      <label for="kabupaten_kota_sekolah_universitas" class="block text-sm font-medium text-gray-700">Kabupaten/Kota <span
                          class="text-red-500">*</span></label>
                      <Field as="select" name="kabupaten_kota_sekolah_universitas" id="kabupaten_kota_sekolah_universitas"
                        @change="(event: any) => getSubdistricts(event, 'school')"
                        class="mt-1 p-2 w-full border rounded-md" :disabled="!schoolDistricts.length">
                        <option value="" disabled selected>Pilih kabupaten/Kota</option>
                        <option v-for="data in schoolDistricts" :key="data.code" :value="data.code">{{ data.name }}</option>
                      </Field>
                      <ErrorMessage name="kabupaten_kota_sekolah_universitas" class="text-red-500 text-sm" />
                      <p v-if="errors.kabupaten_kota_sekolah_universitas" class="text-red-500 text-sm">{{ errors.kabupaten_kota_sekolah_universitas[0] }}</p>
                    </div>
                    </div>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mt-4">
                    <div>
                      <label for="kecamatan_sekolah_universitas" class="block text-sm font-medium text-gray-700">Kecamatan_sekolah_universitas<span
                          class="text-red-500">*</span></label>
                      <Field as="select" name="kecamatan_sekolah_universitas" id="kecamatan_sekolah_universitas" class="mt-1 p-2 w-full border rounded-md"
                        :disabled="!schoolSubdistricts.length" @change="(event: any) => getVillages(event, 'school')">
                        <option value="" disabled selected>Pilih Kecamatan_sekolah_universitas</option>
                        <option v-for="data in schoolSubdistricts" :key="data.code" :value="data.code">
                          {{ data.name }}
                        </option>
                      </Field>
                      <ErrorMessage name="kecamatan_sekolah_universitas" class="text-red-500 text-sm" />
                      <p v-if="errors.kecamatan_sekolah_universitas" class="text-red-500 text-sm">{{ errors.kecamatan_sekolah_universitas[0] }}</p>
                    </div>
                    <div>
                      <label for="kelurahan_desa_sekolah_universitas" class="block text-sm font-medium text-gray-700">Kelurahan/Desa<span
                          class="text-red-500">*</span></label>
                      <Field as="select" name="kelurahan_desa_sekolah_universitas" id="kelurahan_desa_sekolah_universitas" class="mt-1 p-2 w-full border rounded-md"
                        :disabled="!schoolVillages.length">
                        <option value="" disabled selected>Pilih Desa/Kelurahan</option>
                        <option v-for="data in schoolVillages" :key="data.code" :value="data.code">
                          {{ data.name }}
                        </option>
                      </Field>
                      <ErrorMessage name="kelurahan_desa_sekolah_universitas" class="text-red-500 text-sm" />
                      <p v-if="errors.kelurahan_desa_sekolah_universitas" class="text-red-500 text-sm">{{ errors.kelurahan_desa_sekolah_universitas[0] }}</p>
                    </div>

                  </div> 
                </template>

                <template v-if="currentStep === 2">
                  <!-- Upload Berkas -->
                  <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-2 gap-4 mt-4">
                    <div>
                      <label for="foto_identitas" class="block text-sm font-medium text-gray-700">
                        Foto Identitas (KTP/SIM/Kartu Pelajar)
                      </label>
                      <Field name="foto_identitas" v-slot="{ handleChange }">
                        <input type="file" id="foto_identitas" @change="handleFileUpload($event, handleChange)"
                          class="mt-1 p-2 w-full border rounded-md" />
                        <ErrorMessage name="foto_identitas" class="text-red-500 text-sm" />
                        <p v-if="errors.foto_identitas" class="text-red-500 text-sm">{{ errors.foto_identitas[0] }}</p>
                      </Field>
                    </div>


                    <div>
                      <label for="surat_permohonan" class="block text-sm font-medium text-gray-700">
                        Surat Permohonan Magang
                      </label>
                      <Field name="surat_permohonan" v-slot="{ handleChange }">
                        <input type="file" id="surat_permohonan" @change="handleFileUpload($event, handleChange)"
                          class="mt-1 p-2 w-full border rounded-md" />
                        <ErrorMessage name="surat_permohonan" class="text-red-500 text-sm" />
                        <p v-if="errors.surat_permohonan" class="text-red-500 text-sm">{{ errors.surat_permohonan[0] }}
                        </p>
                      </Field>

                    </div>
                  </div>

                  <!-- Input Tanggal Mulai & Selesai -->
                  <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mt-4">
                    <BaseInput label="Tanggal mulai" name="tanggal_mulai" type="date" :errors="errors" required/> 
                    <BaseInput label="Tanggal selesai" name="tanggal_selesai" type="date" :errors="errors" required/> 
                  </div>
                </template>


                <div class="flex mt-6 justify-between">
                  <button v-if="currentStep !== 0" type="button" @click="prevStep"
                    class="px-6 py-2 bg-gray-300 rounded-md text-sm font-medium">
                    Previous
                  </button>

                  <button v-if="currentStep !== 2" type="submit"
                    class="px-6 py-2 bg-blue-600 rounded-md text-white font-medium">Next</button>

                  <button v-if="currentStep === 2" type="submit" :disabled="pending"
                    class="px-6 py-2 bg-blue-600 rounded-md text-white font-medium">{{ pending ? 'Mengajukan...' :
                    'Ajukan Berkas'
                    }}</button> 
                </div>
              </div>
            </div>
          </div>
        </Form>
      </div>
    </section>
  </NuxtLayout>
</template>

<style scoped>
/* Custom Styling */
</style>
