<script setup lang="ts">
definePageMeta({
    layout: 'dashboard',
    middleware: 'auth'
})
import * as yup from 'yup'
import type { SubmissionHandler } from "vee-validate"
import { useNotification } from '@/composables/useNotification'

const { addNotification } = useNotification()
const breadcrumb = [
    { label: "Profile", icon: "material-symbols:person", to: "/dashboard/profile" },
]
const config = useRuntimeConfig()

const { getUserMentorName, getUserMentorEmail, getUserRole } = useAuth()

const { users, status, error, refresh, pending, updateProfile, pendingUpdateProfile, errors } = useMe()

const { FetchMasterSekolahByMagangId, pendingFetch } = useMasterSekolahUniversitas()
const { FetchBerkasByMagangId, pendingFetch: pendingFetchBerkasUserMagang } = useBerkasByUserMagang()

// const tabs = ref([
//     { id: "about", label: "Data Pribadi" },
//     { id: "data_master_sekolah_universitas", label: "Data Magang" }
// ])
const tabs = computed(() => {
    if (getUserRole.value === 'admin') {
        return [
            { id: 'about', label: 'Data Pribadi' }, 
        ]
    } else if (getUserRole.value === 'mentor') {
        return [
            { id: 'about', label: 'Data Pribadi' }, 
        ]
    } else if (getUserRole.value === 'kepegawaian') {
        return [
            { id: 'about', label: 'Data Pribadi' }, 
        ]
    }  else {
        // Default untuk mahasiswa
        return [
            { id: 'about', label: 'Data Pribadi' },
            { id: 'data_master_sekolah_universitas', label: 'Data Master' },
            { id: 'data_berkas', label: 'Data Berkas' },
        ]
    }
})
// console.log(getUserRole)
// Tab aktif
const activeTab = ref("about")

const DataMasterSekolahByMagangId = ref()
const DataBerkasByMagangId = ref()
// Fungsi untuk mengganti tab
const setActiveTab = async (tabId: any) => {
    activeTab.value = tabId
    if (tabId === 'data_master_sekolah_universitas') {
        const response = await FetchMasterSekolahByMagangId()
        DataMasterSekolahByMagangId.value = response.data
    }
    if (tabId === 'data_berkas') {
        const response = await FetchBerkasByMagangId()
        DataBerkasByMagangId.value = response.data
    }
}

const statusUpdate = ref(false)
const toggleUpdate = () => {
    statusUpdate.value = !statusUpdate.value
}
const UpdateProfileSchema = yup.object({

})
const handleUpdateProfile: SubmissionHandler<any> = async (value, { resetForm }: { resetForm: () => void }) => {
    const formValues = value
    const formData = new FormData()
    Object.entries(formValues).forEach(([key, value]) => {
        if (value !== undefined && value !== null) {
            formData.append(key, value as string | Blob); // Handle file upload jika ada
        }
    });
//     for (const pair of formData.entries()) {
//     console.log(`${pair[0]}:`, pair[1]);
//   }
    formData.append("_method", "PUT")

    if (selectedProvince.value) {
        formData.set("provinsi", selectedProvince.value.name);
    }
    // console.log("sas")
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
    const response = await updateProfile(users.value.data.id, formData)
    // console.log(response)
    //       for (const pair of formData.entries()) {
    //     console.log('d',`${pair[0]}:`, pair[1]);
    //   }
}

const { province, getRegenciesByProvince, getDistrictByRegencies, getVillagesByDistrict } = useRegionIndonesia()
const selectedProvince = ref<{ code: string; name: string } | null>(null)
const selectedDistrict = ref<{ code: string; name: string } | null>(null)
const selectedSubdistrict = ref<{ code: string; name: string } | null>(null)

const districts = ref<{ code: string; name: string }[]>([]) // Kabupaten/Kota
const subdistricts = ref<{ code: string; name: string }[]>([]) // Kecamatan
const villages = ref<{ code: string; name: string, postal_code: string }[]>([]) // Desa/Kelurahan

const selectedSchoolProvince = ref<{ code: string; name: string } | null>(null)
const selectedSchoolDistrict = ref<{ code: string; name: string } | null>(null)
const selectedSchoolSubdistrict = ref<{ code: string; name: string } | null>(null)

const schoolDistricts = ref<{ code: string; name: string }[]>([]) // Kabupaten/Kota
const schoolSubdistricts = ref<{ code: string; name: string }[]>([]) // Kecamatan
const schoolVillages = ref<{ code: string; name: string, postal_code: string }[]>([]) // Desa/Kelurahan

const getDistrict = async (event: Event, type: 'identity' | 'school') => {
    const selectedId = (event.target as HTMLSelectElement).value
    const selectedData = Array.isArray(province.value.data) ? province.value.data.find((q: any) => q.code === selectedId) : null
    console.log(selectedId)
    console.log(province.value.data)
    console.log(province.value.data.find((q: any) => q.code === selectedId))
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

const selectedGender = computed({
    get: () => users.value.data.jenis_kelamin,
    set: (val) => {
        users.value.data.jenis_kelamin = val;
    }
});


const fileInput = ref<HTMLInputElement | null>(null)

const triggerFileInput = () => {
    if (fileInput.value) {
        fileInput.value.click()
    }
}

const pendingUpdateFotoProfile = ref(false)
const handleFileUpload = async (event: any) => {
    pendingUpdateFotoProfile.value = true
    const file = event.target.files[0]
    try {
        if (file) {
            const formData = new FormData()
            formData.append("_method", "PUT")
            formData.append('foto', file)
            const response = await updateProfile(users.value.data.id, formData)
            addNotification('success', response?.data?.value?.message || "Berhasil menghapus ");
            refresh()
        }
    } catch (error: any) {
        addNotification('success', error.data.message);
    } finally {
        pendingUpdateFotoProfile.value = false
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
                    <div class="flex flex-row justify-between items-center mb-2">
                        <!-- <h3 class="text-md text-gray-700 font-medium">Profile</h3> -->
                        <button v-if="!statusUpdate" @click="toggleUpdate"
                            class="ms-auto px-3 py-1  text-xs w-fit rounded-full flex items-center gap-2 bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-300 border border-yellow-300 hover:bg-yellow-200">
                            <Icon name="material-symbols:edit-square-outline"
                                class="w-4 h-4 inline-block align-middle transition-transform duration-300 ease-in-out" />
                            Update
                        </button>
                    </div>
                    <div class="flex flex-col items-start md:flex-row gap-3">
                        <div
                            class="w-full md:w-1/3  min-w-[300px]   bg-white border border-gray-200 rounded-lg shadow-sm dark:bg-gray-800 dark:border-gray-700">
                            <div class="flex justify-end px-4 pt-4">
                                <button id="dropdownButton" data-dropdown-toggle="dropdown"
                                    class="inline-block text-gray-500 dark:text-gray-400 hover:bg-gray-100 dark:hover:bg-gray-700 focus:ring-4 focus:outline-none focus:ring-gray-200 dark:focus:ring-gray-700 rounded-lg text-sm p-1.5"
                                    type="button">
                                    <span class="sr-only">Open dropdown</span>
                                    <svg class="w-5 h-5" aria-hidden="true" xmlns="http://www.w3.org/2000/svg"
                                        fill="currentColor" viewBox="0 0 16 3">
                                        <path
                                            d="M2 0a1.5 1.5 0 1 1 0 3 1.5 1.5 0 0 1 0-3Zm6.041 0a1.5 1.5 0 1 1 0 3 1.5 1.5 0 0 1 0-3ZM14 0a1.5 1.5 0 1 1 0 3 1.5 1.5 0 0 1 0-3Z" />
                                    </svg>
                                </button>
                                <!-- Dropdown menu -->
                                <div id="dropdown"
                                    class="z-10 hidden text-base list-none bg-white divide-y divide-gray-100 rounded-lg shadow-sm w-44 dark:bg-gray-700">
                                    <ul class="py-2" aria-labelledby="dropdownButton">
                                        <li>
                                            <a href="#"
                                                class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100 dark:hover:bg-gray-600 dark:text-gray-200 dark:hover:text-white">Edit</a>
                                        </li>
                                        <li>
                                            <a href="#"
                                                class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100 dark:hover:bg-gray-600 dark:text-gray-200 dark:hover:text-white">Export
                                                Data</a>
                                        </li>
                                        <li>
                                            <a href="#"
                                                class="block px-4 py-2 text-sm text-red-600 hover:bg-gray-100 dark:hover:bg-gray-600 dark:text-gray-200 dark:hover:text-white">Delete</a>
                                        </li>
                                    </ul>
                                </div>
                            </div>
                            <div v-if="users" class="flex flex-col items-center pb-10">
                                <div class="relative">
                                    <div v-if="pendingUpdateFotoProfile"
                                        class="absolute w-24 h-24 inset-0 flex items-center justify-center bg-white/50 backdrop-blur-md rounded-full z-10">
                                        <Icon name="codex:loader" class="w-4 h-4 " />
                                    </div>
                                    <NuxtImg :src="users.data.foto
                                        ? `${config.public.storage}/storage/${users.data.foto}`
                                        : 'https://rotendaokab.go.id/wp-content/uploads/2016/08/dummy-prod-1.jpg'"
                                        class="w-24 h-24 mb-3 rounded-full shadow-lg cursor-pointer hover:border-2"
                                        alt="Foto Identitas" format="webp" loading="lazy" @click="triggerFileInput" />
                                    <input type="file" ref="fileInput" class="hidden" @change="handleFileUpload" />
                                </div>

                                <h5 class="mb-1 text-xl font-medium text-gray-900 dark:text-white">{{
                                    users.data.nama_depan }} {{ users.data.nama_belakang }}</h5>
                                <span class="mb-2 text-sm text-gray-500 dark:text-gray-400">{{ users.data.email
                                    }}</span>
                                <span
                                    class="px-3 mb-2  text-xs w-fit font-semibold rounded-full  bg-yellow-100  border border-yellow-200 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-300">
                                    {{ users.data.role === 'user' ? 'Magang' : users.data.role }}
                                </span>
                                <span v-if="users.data.role === 'user'"
                                    class="px-3   text-xs w-fit font-semibold rounded-full  bg-yellow-100  border border-yellow-200 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-300">Mentor
                                    {{ getUserMentorName }}</span>
                                <!-- <div class="flex mt-4 md:mt-6">
                                    <a href="#"
                                        class="inline-flex items-center px-4 py-2 text-sm font-medium text-center text-white bg-blue-700 rounded-lg hover:bg-blue-800 focus:ring-4 focus:outline-none focus:ring-blue-300 dark:bg-blue-600 dark:hover:bg-blue-700 dark:focus:ring-blue-800">Add
                                        friend</a>
                                    <a href="#"
                                        class="py-2 px-4 ms-2 text-sm font-medium text-gray-900 focus:outline-none bg-white rounded-lg border border-gray-200 hover:bg-gray-100 hover:text-blue-700 focus:z-10 focus:ring-4 focus:ring-gray-100 dark:focus:ring-gray-700 dark:bg-gray-800 dark:text-gray-400 dark:border-gray-600 dark:hover:text-white dark:hover:bg-gray-700">Message</a>
                                </div> -->
                            </div>
                        </div>


                        <div
                            class="w-full bg-white border border-gray-200 rounded-lg shadow-sm dark:bg-gray-800 dark:border-gray-700">
                            <ul
                                class="flex flex-wrap  text-sm font-medium text-center text-gray-500 border-b border-gray-200 rounded-t-lg bg-gray-50 dark:border-gray-700 dark:text-gray-400 dark:bg-gray-800">
                                <li v-for="tab in tabs" :key="tab.id" class="me-2">
                                    <button :class="[
                                        'inline-block p-4 rounded-ss-lg hover:bg-gray-100 dark:bg-gray-800 dark:hover:bg-gray-700',
                                        activeTab === tab.id ? 'text-blue-600 dark:text-blue-500' : 'hover:text-gray-600 dark:hover:text-gray-300'
                                    ]" @click="setActiveTab(tab.id)">
                                        {{ tab.label }}
                                    </button>
                                </li>
                            </ul>

                            <div>
                                <div v-for="tab in tabs" :key="tab.id"
                                    :class="['p-4 bg-white rounded-lg md:p-8 dark:bg-gray-800', activeTab === tab.id ? '' : 'hidden']">
                                    <!-- <h2 v-if="tab.id === 'about'"
                                        class="mb-3 text-3xl font-extrabold tracking-tight text-gray-900 dark:text-white"> -->
                                    <div v-if="tab.id === 'about'">
                                        <Form @submit="handleUpdateProfile" :validation-schema="UpdateProfileSchema">
                                            <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mt-4">
                                                <BaseInput label="Nama Depan" v-model="users.data.nama_depan"
                                                    :disabled="!statusUpdate" name="nama_depan" type="text"
                                                    placeholder="Masukan nama depan" :errors="errors" />
                                                <BaseInput label="Nama Belakang" v-model="users.data.nama_belakang"
                                                    :disabled="!statusUpdate" name="nama_belakang" type="text"
                                                    placeholder="Masukan nama belakang" :errors="errors" />
                                                <BaseInput label="NISN/NPM/NIM" v-model="users.data.nisn_npm_nim_npp"
                                                    :disabled="!statusUpdate" name="nisn_npm_nim_npp" type="text"
                                                    placeholder="exp. A1123xxxx" :errors="errors" />
                                            </div>
                                            <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mt-4">
                                                <BaseInput label="Tanggal Lahir" v-model="users.data.tanggal_lahir"
                                                    :disabled="!statusUpdate" name="tanggal_lahir" type="date"
                                                    :errors="errors" />
                                                <BaseSelect label="Jenis Kelamin" name="jenis_kelamin"
                                                    v-model="selectedGender" :disabled="!statusUpdate" :options="[
                                                        { value: 'male', text: 'Laki-laki' },
                                                        { value: 'female', text: 'Perempuan' }
                                                    ]" />
                                                <BaseInput label="Nomor HP" v-model="users.data.nomor_hp"
                                                    :disabled="!statusUpdate" name="nomor_hp" type="number"
                                                    placeholder="85xxx" :errors="errors" />
                                            </div>

                                            <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mt-4">
                                                <BaseInput label="Alamat" v-model="users.data.alamat"
                                                    :disabled="!statusUpdate" name="alamat" type="text"
                                                    placeholder="Jl xxx no. xx" :errors="errors" />
                                                <!-- belum berhasil di component reusable       -->
                                                <div>
                                                    <label for="provinsi"
                                                        class="text-sm font-medium text-gray-700 flex justify-between">
                                                        <span>Provinsi </span>
                                                        <span class="text-right text-blue-500">{{ users.data.provinsi
                                                            }}</span>
                                                    </label>

                                                    <Field as="select" :disabled="!statusUpdate" name="provinsi"
                                                        id="provinsi"
                                                        @change="(event: any) => getDistrict(event, 'identity')"
                                                        class="mt-1 p-2 w-full border rounded-md">
                                                        <option value="" disabled>Pilih Provinsi</option>
                                                        <option v-for="data in province.data" :key="data.code"
                                                            :value="data.code">{{ data.name }}
                                                        </option>
                                                    </Field>

                                                    <ErrorMessage name="provinsi" class="text-red-500 text-sm" />
                                                    <p v-if="errors.provinsi" class="text-red-500 text-sm">{{
                                                        errors.provinsi[0] }}</p>
                                                </div>
                                                <div>
                                                    <label for="kabupaten_kota"
                                                        class="text-sm font-medium text-gray-700 flex justify-between"><span>Kabupaten/Kota
                                                        </span>
                                                        <span class="text-right text-blue-500">{{
                                                            users.data.kabupaten_kota
                                                            }}</span></label>
                                                    <Field as="select" name="kabupaten_kota" id="kabupaten_kota"
                                                        @change="(event: any) => getSubdistricts(event, 'identity')"
                                                        class="mt-1 p-2 w-full border rounded-md"
                                                        :disabled="!districts.length">
                                                        <option value="" disabled selected>Pilih kabupaten/Kota</option>
                                                        <option v-for="data in districts" :key="data.code"
                                                            :value="data.code">{{ data.name }}</option>
                                                    </Field>
                                                    <ErrorMessage name="kabupaten_kota" class="text-red-500 text-sm" />
                                                    <p v-if="errors.kabupaten_kota" class="text-red-500 text-sm">{{
                                                        errors.kabupaten_kota[0] }}</p>
                                                </div>
                                            </div>
                                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mt-4">
                                                <div>
                                                    <label for="kecamatan"
                                                        class="text-sm font-medium text-gray-700 flex justify-between"><span>Kecamatan
                                                        </span> <span class="text-right text-blue-500">{{
                                                            users.data.kecamatan }}</span></label>
                                                    <Field as="select" name="kecamatan" id="kecamatan"
                                                        class="mt-1 p-2 w-full border rounded-md"
                                                        :disabled="!subdistricts.length"
                                                        @change="(event: any) => getVillages(event, 'identity')">
                                                        <option value="" disabled selected>Pilih Kecamatan</option>
                                                        <option v-for="data in subdistricts" :key="data.code"
                                                            :value="data.code">
                                                            {{ data.name }}
                                                        </option>
                                                    </Field>
                                                    <ErrorMessage name="kecamatan" class="text-red-500 text-sm" />
                                                    <p v-if="errors.kecamatan" class="text-red-500 text-sm">{{
                                                        errors.kecamatan[0] }}</p>
                                                </div>
                                                <div>
                                                    <label for="kelurahan_desa"
                                                        class="text-sm font-medium text-gray-700 flex justify-between"><span>Kelurahan/Desa
                                                        </span> <span class="text-right text-blue-500">{{
                                                            users.data.kelurahan_desa
                                                            }}</span></label>
                                                    <Field as="select" name="kelurahan_desa" id="kelurahan_desa"
                                                        class="mt-1 p-2 w-full border rounded-md"
                                                        :disabled="!villages.length">
                                                        <option value="" disabled selected>Pilih Desa/Kelurahan</option>
                                                        <option v-for="data in villages" :key="data.code"
                                                            :value="data.code">
                                                            {{ data.name }}
                                                        </option>
                                                    </Field>
                                                    <ErrorMessage name="kelurahan_desa" class="text-red-500 text-sm" />
                                                    <p v-if="errors.kelurahan_desa" class="text-red-500 text-sm">{{
                                                        errors.kelurahan_desa[0] }}</p>
                                                </div>

                                            </div>

                                            <div class="flex  mt-4" v-if="statusUpdate">
                                                <div class="ms-auto flex gap-2">
                                                    <button @click="toggleUpdate"
                                                        class="ms-auto text-white bg-red-700 hover:bg-red-800 focus:ring-4 focus:outline-none focus:ring-red-300 font-medium rounded-lg text-sm w-full sm:w-auto px-5 py-2.5 text-center dark:bg-red-600 dark:hover:bg-red-700 dark:focus:ring-red-800">
                                                        <span>Cancel</span>
                                                    </button>
                                                    <button
                                                        class="ms-auto text-white bg-blue-700 hover:bg-blue-800 focus:ring-4 focus:outline-none focus:ring-blue-300 font-medium rounded-lg text-sm w-full sm:w-auto px-5 py-2.5 text-center dark:bg-blue-600 dark:hover:bg-blue-700 dark:focus:ring-blue-800">
                                                        <Icon v-if="pendingUpdateProfile" name="tabler:loader-2"
                                                            class="w-5 h-5 animate-spin text-white" />
                                                        <span v-else>Update</span>
                                                    </button>
                                                </div>
                                            </div>
                                        </Form>
                                    </div>

                                    <dl v-if="tab.id === 'data_master_sekolah_universitas' && users.data.role === 'user'"
                                        class="grid max-w-screen-xl grid-cols-2 gap-8 mx-auto text-gray-800 sm:grid-cols-3 xl:grid-cols-6 dark:text-white">
                                        <div class="flex flex-col">
                                            <dt class="mb-2 text-sm font-medium text-gray-700">Nama sekolah/universitas
                                            </dt>
                                            <dd class="text-gray-500 dark:text-gray-400">
                                                {{ DataMasterSekolahByMagangId?.nama_sekolah_universitas }}</dd>
                                        </div>
                                        <div class="flex flex-col">
                                            <dt class="mb-2 text-sm font-medium text-gray-700">Email</dt>
                                            <dd class="text-gray-500 dark:text-gray-400">
                                                {{ DataMasterSekolahByMagangId?.email_sekolah_universitas }}</dd>
                                        </div>
                                        <div class="flex flex-col">
                                            <dt class="mb-2 text-sm font-medium text-gray-700">Nomor Telepon</dt>
                                            <dd class="text-gray-500 dark:text-gray-400">
                                                {{ DataMasterSekolahByMagangId?.nomor_telp_sekolah_universitas }}</dd>
                                        </div>
                                        <div class="flex flex-col">
                                            <dt class="mb-2 text-sm font-medium text-gray-700">Jurusan</dt>
                                            <dd class="text-gray-500 dark:text-gray-400">
                                                {{ DataMasterSekolahByMagangId?.jurusan_sekolah }}</dd>
                                        </div>
                                        <div class="flex flex-col">
                                            <dt class="mb-2 text-sm font-medium text-gray-700">Fakultas</dt>
                                            <dd class="text-gray-500 dark:text-gray-400">
                                                {{ DataMasterSekolahByMagangId?.fakultas_universitas }}</dd>
                                        </div>
                                        <div class="flex flex-col">
                                            <dt class="mb-2 text-sm font-medium text-gray-700">Program Studi</dt>
                                            <dd class="text-gray-500 dark:text-gray-400">
                                                {{ DataMasterSekolahByMagangId?.program_studi_universitas }}</dd>
                                        </div>
                                        <div class="flex flex-col">
                                            <dt class="mb-2 text-sm font-medium text-gray-700">Alamat</dt>
                                            <dd class="text-gray-500 dark:text-gray-400">
                                                {{ DataMasterSekolahByMagangId?.alamat_sekolah_universitas }}</dd>
                                        </div>
                                        <div class="flex flex-col">
                                            <dt class="mb-2 text-sm font-medium text-gray-700">Kode pos</dt>
                                            <dd class="text-gray-500 dark:text-gray-400">
                                                {{ DataMasterSekolahByMagangId?.kode_pos_sekolah_universitas }}</dd>
                                        </div>
                                        <div class="flex flex-col">
                                            <dt class="mb-2 text-sm font-medium text-gray-700">Provinsi</dt>
                                            <dd class="text-gray-500 dark:text-gray-400">
                                                {{ DataMasterSekolahByMagangId?.provinsi_sekolah_universitas }}</dd>
                                        </div>
                                        <div class="flex flex-col">
                                            <dt class="mb-2 text-sm font-medium text-gray-700">Kabupaten/kota</dt>
                                            <dd class="text-gray-500 dark:text-gray-400">
                                                {{ DataMasterSekolahByMagangId?.kabupaten_kota_sekolah_universitas }}</dd>
                                        </div>
                                        <div class="flex flex-col">
                                            <dt class="mb-2 text-sm font-medium text-gray-700">Kecamatan</dt>
                                            <dd class="text-gray-500 dark:text-gray-400">
                                                {{ DataMasterSekolahByMagangId?.kecamatan_sekolah_universitas }}</dd>
                                        </div>
                                        <div class="flex flex-col">
                                            <dt class="mb-2 text-sm font-medium text-gray-700">Kelurahan/desa</dt>
                                            <dd class="text-gray-500 dark:text-gray-400">
                                                {{ DataMasterSekolahByMagangId?.kelurahan_desa_sekolah_universitas }}</dd>
                                        </div>
                                    </dl>
                                    <dl v-if="tab.id === 'data_berkas' && users.data.role === 'user'"
                                        class="grid max-w-screen-xl grid-cols-2 gap-8 mx-auto text-gray-800 sm:grid-cols-3 xl:grid-cols-3 dark:text-white">
                                        <div class="flex flex-col">
                                            <dt class="mb-2 text-sm font-medium text-gray-700">Foto Identitas (KTM/KTS)</dt>
                                            <dd class="text-gray-500 dark:text-gray-400">
                                                <a :href="`${config.public.storage}/storage/${DataBerkasByMagangId?.foto_identitas}`"
                                                    target="_blank" rel="noopener noreferrer"
                                                    class="px-3 py-1 text-xs w-fit font-semibold rounded-full flex items-center gap-2 bg-yellow-100 border border-yellow-200 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-300">
                                                    <Icon name="material-symbols:open-in-new"
                                                        class="w-4 h-4 inline-block align-middle" />
                                                </a>
                                            </dd>
                                        </div>
                                        <div class="flex flex-col">
                                            <dt class="mb-2 text-sm font-medium text-gray-700">Surat Permohonan</dt>

                                            <dd class="text-gray-500 dark:text-gray-400"> <a
                                                    :href="`${config.public.storage}/storage/${DataBerkasByMagangId?.surat_permohonan}`"
                                                    target="_blank" rel="noopener noreferrer"
                                                    class="px-3 py-1 text-xs w-fit font-semibold rounded-full flex items-center gap-2 bg-yellow-100 border border-yellow-200 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-300">
                                                    <Icon name="material-symbols:open-in-new"
                                                        class="w-4 h-4 inline-block align-middle" />
                                                </a></dd>
                                        </div>
                                        <div class="flex flex-col">
                                            <dt class="mb-2 text-sm font-medium text-gray-700">Surat Balasan</dt>
                                            <dd class="text-gray-500 dark:text-gray-400"> <a
                                                    :href="`${config.public.storage}/storage/${DataBerkasByMagangId?.surat_diterima}`"
                                                    target="_blank" rel="noopener noreferrer"
                                                    class="px-3 py-1 text-xs w-fit font-semibold rounded-full flex items-center gap-2 bg-yellow-100 border border-yellow-200 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-300">
                                                    <Icon name="material-symbols:open-in-new"
                                                        class="w-4 h-4 inline-block align-middle" />
                                                </a> </dd>
                                        </div>
                                        <div class="flex flex-col">
                                            <dt class="mb-2 text-sm font-medium text-gray-700">Sertifikat</dt>
                                            <dd class="text-gray-500 dark:text-gray-400"> 
                                                <!-- <a
                                                    :href="`${config.public.storage}/storage/${DataBerkasByMagangId?.surat_diterima}`"
                                                    target="_blank" rel="noopener noreferrer"
                                                    class="px-3 py-1 text-xs w-fit font-semibold rounded-full flex items-center gap-2 bg-yellow-100 border border-yellow-200 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-300">
                                                    <Icon name="material-symbols:open-in-new"
                                                        class="w-4 h-4 inline-block align-middle" />
                                                </a> -->
                                             </dd>
                                        </div>
                                        <div class="flex flex-col">
                                            <dt class="mb-2 text-sm font-medium text-gray-700">Surat Keterangan Magang</dt>
                                            <dd class="text-gray-500 dark:text-gray-400"> 
                                                <!-- <a
                                                    :href="`${config.public.storage}/storage/${DataBerkasByMagangId?.surat_diterima}`"
                                                    target="_blank" rel="noopener noreferrer"
                                                    class="px-3 py-1 text-xs w-fit font-semibold rounded-full flex items-center gap-2 bg-yellow-100 border border-yellow-200 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-300">
                                                    <Icon name="material-symbols:open-in-new"
                                                        class="w-4 h-4 inline-block align-middle" />
                                                </a> -->
                                             </dd>
                                        </div>
                                        <div class="flex flex-col">
                                            <dt class="mb-2 text-sm font-medium text-gray-700">Tanggal Mulai</dt>
                                            <dd class="text-gray-500 dark:text-gray-400">
                                                {{ FormatDate(DataBerkasByMagangId?.tanggal_mulai) }}</dd>
                                        </div>
                                        <div class="flex flex-col">
                                            <dt class="mb-2 text-sm font-medium text-gray-700">Tanggal Selesai</dt>
                                            <dd class="text-gray-500 dark:text-gray-400">
                                                {{ FormatDate(DataBerkasByMagangId?.tanggal_selesai) }}</dd>
                                        </div>
                                    </dl>
                                </div>
                            </div>
                        </div>
                    </div>

                </div>

            </section>
        </div>
    </NuxtLayout>
</template>