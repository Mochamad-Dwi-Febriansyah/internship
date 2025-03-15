<script setup lang="ts">
definePageMeta({
    layout: 'dashboard',
    middleware: 'auth'
})
import * as yup from 'yup'
import type { SubmissionHandler } from "vee-validate"
import type { LaporanAkhirForm } from '~~/types/types'
const route = useRoute()
const router = useRouter()
const config = useRuntimeConfig()
const { addNotification } = useNotification()
const id = String(route.params.id)
const isEdit = computed(() => id !== "new")

const breadcrumb = [ 
    { label: "Laporan Akhir", icon: "material-symbols:checkbook-outline-rounded", to: "/dashboard/user/laporan-akhir" },
    { label: "Tambah Laporan Harian" },
]
const currentPage = ref(Number(route.query.page) || 1)
// const { refreshLaporanAkhir, createLaporanAkhir, errors } = useLaporanAkhir(currentPage)
 

const handleFileUpload = (event: Event, handleChange: (value: File | null) => void): void => {
    const target = event.target as HTMLInputElement;
    const file = target.files?.[0] || null;
    handleChange(file);
};


const { setFieldValue, resetForm, values } = useForm({
  initialValues: {
    title: "",
    report: "",
    assessment_report_file: "", 
    final_report_file: "", 
    photo: "", 
    video: "", 
  },
});
// const report = ref(" ");
const { getLaporanAkhirById, pendingLaporanAkhirById , updateLaporanAkhir, pendingCreate, pendingUpdate, refreshLaporanAkhir, createLaporanAkhir, errors} = useLaporanAkhir(currentPage)
onMounted(async () => {
    if (isEdit.value) {
        const response = await getLaporanAkhirById(id)
        setFieldValue("title", response.data.title); 
        setFieldValue("report", response.data.report); 
        setFieldValue("assessment_report_file", response.data.assessment_report_file ?? ""); 
        setFieldValue("final_report_file", response.data.final_report_file ?? ""); 
        setFieldValue("photo", response.data.photo ?? ""); 
        setFieldValue("video", response.data.video ?? ""); 
        // report.value =  response.data.report;  
    }
});
// watch(report, (newValue) => {
//     setFieldValue("report", newValue);
// });

const addLaporanSchema = yup.object({
    title: yup.string().required('Nama laporan akhir wajib diisi'),
    report: yup.string().required('Deskipsi wajib diisi'),
    assessment_report_file: yup
        .mixed()
        .test("fileRequired", "File laporan penilaian wajib diunggah", (value) => {
            // Jika tambah data (isEdit = false), file wajib diisi
            if (!isEdit.value) return !!value;
            // Jika edit dan tidak ada file baru, tetap valid
            return true;
        })
        .test("fileFormat", "Format file harus JPG, PNG, atau PDF", (value) => {
            if (!value) return true; // Tidak perlu validasi jika tidak ada file baru
            return value instanceof File && ["application/pdf", "image/jpeg", "image/png"].includes(value.type);
        })
        .test("fileSize", "Ukuran file maksimal 2MB", (value) => {
            if (!value) return true; // Tidak perlu validasi jika tidak ada file baru
            return value instanceof File && value.size <= 2 * 1024 * 1024;
        }),

        final_report_file: yup
        .mixed()
        .test("fileRequired", "File Laporan akhir wajib diunggah", (value) => {
            // Jika tambah data (isEdit = false), file wajib diisi
            if (!isEdit.value) return !!value;
            // Jika edit dan tidak ada file baru, tetap valid
            return true;
        })
        .test("fileFormat", "Format file harus JPG, PNG, atau PDF", (value) => {
            if (!value) return true; // Tidak perlu validasi jika tidak ada file baru
            return value instanceof File && ["application/pdf", "image/jpeg", "image/png"].includes(value.type);
        })
        .test("fileSize", "Ukuran file maksimal 2MB", (value) => {
            if (!value) return true; // Tidak perlu validasi jika tidak ada file baru
            return value instanceof File && value.size <= 2 * 1024 * 1024;
        }),

        photo: yup
        .mixed()
        .nullable() // Memungkinkan nilai null
        .test("fileFormat", "Format file harus JPG atau PNG", (value) => {
            if (!value) return true; // Jika tidak ada file, lewati validasi
            return value instanceof File && ["image/jpeg", "image/png"].includes(value.type);
        })
        .test("fileSize", "Ukuran file maksimal 2MB", (value) => {
            if (!value) return true; // Jika tidak ada file, lewati validasi
            return value instanceof File && value.size <= 2 * 1024 * 1024;
        }),
    video: yup
        .string()
        .url("Harap masukkan URL video yang valid") // Hanya menerima URL
        .nullable(),
})
const handleCreateLaporan: SubmissionHandler<any> = async (values, { resetForm }: { resetForm: () => void }) => {
    // console.log(values)
    const formValues = values as LaporanAkhirForm; // Paksa tipe ke InternshipForm
    const formData = new FormData();
    // console.log(formData)

    // Konversi object ke FormData
    Object.entries(formValues).forEach(([key, value]) => {
        if (value !== undefined && value !== null) {
            formData.append(key, value as string | Blob); // Handle file upload jika ada
        }
    });

    try {
        let response
        if (isEdit.value) {
            formData.append('_method', 'PUT')
            response = await updateLaporanAkhir(id, formData)
        } else {
            response = await createLaporanAkhir(formData)
        }
        // const response = await createLaporanAkhir(formData); 
        await refreshLaporanAkhir()
        resetForm();
        addNotification('success', response.message)
        router.push('/dashboard/user/laporan-akhir')
    } catch (error: any) {
        addNotification('error', error.data.message)
    }

}
const currentYear = new Date().getFullYear();
const defaultTitleFinalReport = ref(`Laporan Akhir Magang PDAM Tirta Moedal Kota Semarang ${currentYear}`)

</script>

<template>
    <NuxtLayout>
        <BaseBreadcrumb :items="breadcrumb" />
        <div class=" bg-gray-50">
            <section class="mb-3">
                <div v-if="pendingLaporanAkhirById"
                    class="relative w-full h-[2px] bg-gray-200 overflow-hidden">
                    <div class="absolute h-full bg-gradient-to-r from-green-400 to-green-600 animate-loading"></div>
                </div>
                <div v-else class="p-3">
                    <div class="flex flex-row justify-between items-center mb-2 ">
                        <!-- <h3 class="text-md text-gray-700 font-medium">{{ isEdit ? "Edit Laporan Akhir" : "Tambah  Akhir" }}</h3> -->
                        <button @click="router.back()"
                            class="ms-auto px-3 py-1  text-xs w-fit font-semibold rounded-full flex items-center gap-2 bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-300 border border-red-300 hover:bg-red-200">
                            <Icon name="material-symbols:arrow-left-alt-rounded"
                                class="w-4 h-4 inline-block align-middle transition-transform duration-300 ease-in-out" />
                            Kembali
                        </button>
                    </div>
                    <div  
                        class="w-full p-6 bg-white  border border-gray-200 rounded-lg shadow-sm dark:bg-gray-800 dark:border-gray-700">
                        <Form @submit="handleCreateLaporan" :validation-schema="addLaporanSchema">
                            <div class="grid grid-cols-1 md:grid-cols-1  gap-4 mt-4">
                                <BaseInput label="Nama Laporan" name="title" type="text" :errors="errors" required v-model="defaultTitleFinalReport" disabled/>
                            </div>
                            <div class="grid grid-cols-1 md:grid-cols-1  gap-4 mt-4">
                                <label for="report"  class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">Deskripsi Laporan Kegiatan</label>
                                <Field name="report" v-model="values.report"  class="m-3">
                                    <Field name="report" as="textarea" id="report" class="bg-white border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-blue-500 dark:focus:border-blue-500 h-32  disabled:bg-gray-100 disabled:text-gray-500 disabled:cursor-not-allowed
                                    dark:disabled:bg-gray-800 dark:disabled:text-gray-500" />
                                    <!-- <client-only>
                                        <BaseCKEditor v-bind="field" v-model="values.report"  />
                                    </client-only> -->
                                    <span class="text-red-500 text-sm">{{ errors[0] }}</span>
                                </Field>
                                <p v-if="errors.report" class="text-red-500 text-sm">{{ errors.report[0] }}</p>
                            </div>
                            <div class="grid grid-cols-1 md:grid-cols-2  gap-4 mt-4">
                                <div>
                                    <label for="assessment_report_file"
                                        class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">File
                                        Penilaian Harian (PDF)
                                    </label>
                                    
                                        <Field name="assessment_report_file"
                                        class="bg-white border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-blue-500 dark:focus:border-blue-500"
                                        v-slot="{ handleChange }">
                                        <input type="file" id="assessment_report_file"
                                            @change="handleFileUpload($event, handleChange)"
                                            class="mt-1 p-2 w-full border rounded-md" />
                                    </Field>
                                    <ErrorMessage name="assessment_report_file" class="text-red-500 text-sm" />
                                     <!-- Jika ada file yang sudah tersimpan, tampilkan nama file -->
                                     <div v-if="values.assessment_report_file" class="mb-2">
                                            <p class="text-sm text-gray-700">File saat ini: 
                                                <a :href="`${config.public.storage}/storage/${values.assessment_report_file}`" target="_blank" class="text-blue-500 underline">
                                                {{ values.assessment_report_file }}
                                                </a>
                                            </p>
                                        </div> 
                                </div>
                                <div>
                                    <label for="final_report_file"
                                        class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">File
                                        Laporan Akhir (PDF)</label>
                                    <Field name="final_report_file"
                                        class="bg-white border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-blue-500 dark:focus:border-blue-500"
                                        v-slot="{ handleChange }">
                                        <input type="file" id="final_report_file"
                                            @change="handleFileUpload($event, handleChange)"
                                            class="mt-1 p-2 w-full border rounded-md" />
                                    </Field>
                                    <ErrorMessage name="final_report_file" class="text-red-500 text-sm" />
                                    <div v-if="values.final_report_file" class="mb-2">
                                            <p class="text-sm text-gray-700">File saat ini: 
                                                <a :href="`${config.public.storage}/storage/${values.final_report_file}`" target="_blank" class="text-blue-500 underline">
                                                {{ values.final_report_file }}
                                                </a>
                                            </p>
                                        </div> 
                                </div>
                            </div>
                            <div class="grid grid-cols-1 md:grid-cols-2  gap-4 mt-4">
                                <div>
                                    <label for="photo"
                                        class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">
                                        Upload Foto Bersama Mentor (JPG, PNG)
                                    </label>
                                    <Field name="photo" v-slot="{ handleChange }">
                                        <input type="file" id="photo" accept="image/jpeg,image/png"
                                            @change="handleFileUpload($event, handleChange)"
                                            class="bg-white border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2  dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-blue-500 dark:focus:border-blue-500" />
                                    </Field>
                                    <ErrorMessage name="photo" class="text-red-500 text-sm" />
                                    <div v-if="values.photo" class="mb-2">
                                            <p class="text-sm text-gray-700">File saat ini: 
                                                <a :href="`${config.public.storage}/storage/${values.photo}`" target="_blank" class="text-blue-500 underline">
                                                {{ values.photo }}
                                                </a>
                                            </p>
                                        </div> 
                                </div>

                                <!-- Input Video (URL atau Link) -->
                                <div> 
                                    <BaseInput label="Link Video (Opsional)" name="video" type="text" :errors="errors"  v-model="values.video" />  
                                </div>
                            </div>
                            <div class="flex mt-6 justify-start gap-4">
                                <button
                                    class="ms-auto text-white bg-blue-700 hover:bg-blue-800 focus:ring-4 focus:outline-none focus:ring-blue-300 font-medium rounded-lg text-sm w-full sm:w-auto px-5 py-2.5 text-center dark:bg-blue-600 dark:hover:bg-blue-700 dark:focus:ring-blue-800">
                                    <Icon v-if="pendingCreate || pendingUpdate" name="tabler:loader-2"
                                        class="w-5 h-5 animate-spin text-white" />
                                    <span v-else>{{isEdit ? 'Ubah' :'Simpan'}}</span>
                                </button>
                            </div>
                        </Form>
                    </div>
                </div>
            </section>
        </div>
    </NuxtLayout>
</template>


<style scoped></style>