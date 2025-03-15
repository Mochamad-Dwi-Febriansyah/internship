<script setup lang="ts">
definePageMeta({
    layout: 'dashboard',
    middleware: 'auth'
})
import * as yup from 'yup'
import type { SubmissionHandler } from "vee-validate"
const route = useRoute()
const router = useRouter()
const laporanHarianId = String(route.params.id)
const laporanHarianTanggal = route.query.tanggal
const { addNotification } = useNotification()

const breadcrumb = [ 
    { label: "Riwayat Presensi", icon: "material-symbols:checkbook-outline-rounded", to: "/dashboard/user/presensi" },
    { label: "Laporan Harian" },
]

const currentPage = ref(Number(route.query.page) || 1);
const { createLaporanHarian, errors: errorsCreateLaporanHarian, pendingCreate: pendingCreateLaporanHarian,getLaporanHarianById, pendingFetchById, updateLaporanHarian, pendingUpdate } = useLaporanHarian(currentPage)
const { refreshPresensi  }  = usePresensi(currentPage) 


// ✅ Ambil data dari API saat halaman dimuat
const isEditMode = ref(false)
onMounted(async () => { 
    const response = await getLaporanHarianById(laporanHarianId);  
    if (response) {
        isEditMode.value = true  
        setFieldValue("title", response.title); 
        setFieldValue("report", response.report); 
        setFieldValue("result", response.result); 

        // await nextTick();
        // report.value =  response.report;  
        // result.value =  response.result;  
        toggleUpdate()
      } else {
        isEditMode.value = false  
      }
});

const tanggalPresensi = ref(""); 
const report = ref(" ");
const result = ref(" ");

const addLaporanSchema = yup.object({
    title: yup.string().required('Judul wajib diisi'),
    report: yup.string().required('Deskripsi kegiatan wajib diisi'),
    result: yup.string().required('Hasil yang dicapai wajib diisi'),
    // laporan: yup.string().required('Laporan wajib diisi'),
})
const handleCreateOrUpdateLaporan: SubmissionHandler<any> = async (value, { resetForm }: { resetForm: () => void }) => {
    const formData = new FormData() 

    formData.append('presensi_id', String(laporanHarianId))
    formData.append('tanggal', String(laporanHarianTanggal))
    formData.append('title', value.title)
    formData.append('report', value.report)
    formData.append('result', value.result)
//     for (const pair of formData.entries()) {
//     console.log(`${pair[0]}:`, pair[1]);
//   } 
    
    try {
        let response
        if (isEditMode.value) {
            formData.append('_method', 'PUT')
         response = await updateLaporanHarian(laporanHarianId, formData)
        } else {
            response = await createLaporanHarian(formData)
        }

        // tanggalPresensi.value = ""
        // report.value = ""
        await refreshPresensi()
        addNotification('success', response.message)
        router.push('/dashboard/user/presensi')
        resetForm();
    } catch (error: any) {
        addNotification('error', error.data.message)
    }

}

const statusUpdate = ref(false)

const toggleUpdate = () => {
    statusUpdate.value = !statusUpdate.value 
    console.log("Status Update:", statusUpdate.value) // Cek perubahan nilai
}
 
const { setFieldValue, resetForm, values } = useForm({
  initialValues: {
    title: "",
    report: "",
    result: "", 
  },
});


</script>
<template>
    <NuxtLayout>
        <BaseBreadcrumb :items="breadcrumb" /> 
        <div class=" bg-white">
            <section class="mb-3">
                <div v-if="pendingFetchById" class="relative w-full h-[2px] bg-gray-200 overflow-hidden">
                    <div class="absolute h-full bg-gradient-to-r from-green-400 to-green-600 animate-loading"></div>
                </div>
                <div v-else class="p-3">
                    <div class="flex flex-row justify-between items-center mb-2 ">
                        <!-- <h3 class="text-md text-gray-700 font-medium">Laporan Harian</h3>  -->
                        <div class="flex flex-row space-x-2 ms-auto">
                            <button @click="router.back()" class="ms-auto px-3 py-1  text-xs w-fit rounded-full flex items-center gap-2 bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-300 border border-red-300 hover:bg-red-200">
                                <Icon name="material-symbols:arrow-left-alt-rounded" 
                                class="w-4 h-4 inline-block align-middle transition-transform duration-300 ease-in-out"/> Kembali
                             </button>
                             <button  v-if="statusUpdate" @click="toggleUpdate"  class="ms-auto px-3 py-1  text-xs w-fit rounded-full flex items-center gap-2 bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-300 border border-yellow-300 hover:bg-yellow-200">
                                <Icon name="material-symbols:edit-square-outline" 
                                class="w-4 h-4 inline-block align-middle transition-transform duration-300 ease-in-out"/>Update
                             </button>
                        </div>
                    </div>
                    <div
                        class="w-full p-6 bg-white  border border-gray-200 rounded-lg shadow-sm dark:bg-gray-800 dark:border-gray-700">
          
                        <Form @submit="handleCreateOrUpdateLaporan"   :validation-schema="addLaporanSchema">
                            <div class="grid grid-cols-1 md:grid-cols-2  gap-4 mt-4">
    
                                <div> 
                                    <label for="tanggal"
                                        class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">Tanggal</label>
                                    <Field name="tanggal" v-model="laporanHarianTanggal" disabled type="date" id="tanggal"
                                        class="bg-white border border-gray-300 text-gray-900 text-sm rounded-lg disabled:bg-gray-100 focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-blue-500 dark:focus:border-blue-500" />
                                    <ErrorMessage name="tanggal" class="text-red-500 text-sm" />
                                    <p v-if="errorsCreateLaporanHarian.tanggal" class="text-red-500 text-sm">{{
                                        errorsCreateLaporanHarian.tanggal[0] }}</p>
                                    <p v-if="errorsCreateLaporanHarian.presensi_id" class="text-red-500 text-sm">Silahkan
                                        pilih tanggal
                                    </p>
                                </div>
                                <div>
                                    <label for="title"
                                        class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">Kegiatan</label>
                                    <Field name="title" v-model="values.title" type="text" :disabled="statusUpdate" id="title"  
                                        class="bg-white border border-gray-300 text-gray-900 text-sm rounded-lg disabled:bg-gray-100 disabled:text-gray-500 disabled:cursor-not-allowed   focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-blue-500 dark:focus:border-blue-500" />
                                    <ErrorMessage name="title" class="text-red-500 text-sm" /> 
                                    <p v-if="errorsCreateLaporanHarian.title" class="text-red-500 text-sm">{{
                                        errorsCreateLaporanHarian.title[0] }}</p>
                                </div> 
                            </div>
                            <div class="grid grid-cols-1 md:grid-cols-1  mt-4">
                                <label for="report" class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">
                                    Deskripsi Kegiatan
                                </label> 
                                <Field :disabled="statusUpdate"  v-model="values.report"
                                    name="report" 
                                    as="textarea" 
                                    id="report" 
                                    :class="{
                                        'bg-gray-200   dark:bg-gray-600 cursor-not-allowed': statusUpdate,
                                        'bg-white dark:bg-gray-700': !statusUpdate
                                    }"
                                    class=" border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-blue-500 dark:focus:border-blue-500 h-32"
                                />
                                
                                <ErrorMessage name="report" class="text-red-500 text-sm"/>
                                
                                <p v-if="errorsCreateLaporanHarian.report" class="text-red-500 text-sm">
                                    {{ errorsCreateLaporanHarian.report[0] }}
                                </p> 
                                <!-- <label for="deskripsi_kegiatan"
                                    class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">Deskripsi Kegiatan</label>
                                <Field name="report" v-slot="{ field, errors }" class="m-3">
                                    <client-only>
                                        <BaseCKEditor v-bind="field" v-model="report" :statusUpdateCondition="statusUpdate" />
                                    </client-only>
                                    <span class="text-red-500 text-sm">{{ errors[0] }}</span>
                                </Field>
                                <p v-if="errorsCreateLaporanHarian.report" class="text-red-500 text-sm">{{
                                    errorsCreateLaporanHarian.report[0] }}</p> -->
                            </div>
                            <div class="grid grid-cols-1 md:grid-cols-1  mt-4">
                                <label for="result" class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">
                                    Deskripsi Kegiatan
                                </label> 
                                <Field :disabled="statusUpdate"   v-model="values.result"
                                    name="result" 
                                    as="textarea" 
                                    id="result" 
                                    :class="{
                                        'bg-gray-200 dark:bg-gray-600 cursor-not-allowed': statusUpdate,
                                        'bg-white dark:bg-gray-700': !statusUpdate
                                    }"
                                    class="border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-blue-500 dark:focus:border-blue-500 h-32"
                                />
                                
                                <ErrorMessage name="result" class="text-red-500 text-sm"/>
                                
                                <p v-if="errorsCreateLaporanHarian.result" class="text-red-500 text-sm">
                                    {{ errorsCreateLaporanHarian.result[0] }}
                                </p> 
                                <!-- <label for="hasil_yang_dicapai"
                                    class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">Hasil yang dicapai</label>
                                <Field name="result" v-slot="{ field, errors }" class="m-3">
                                    <client-only>
                                        <BaseCKEditor v-bind="field" v-model="result" :statusUpdateCondition="statusUpdate" />
                                    </client-only>
                                    <span class="text-red-500 text-sm">{{ errors[0] }}</span>
                                </Field>
                                <p v-if="errorsCreateLaporanHarian.result" class="text-red-500 text-sm">{{
                                    errorsCreateLaporanHarian.result[0] }}</p> -->
                            </div>
                            <div class="flex mt-6 justify-start gap-4">
                                <button type="submit" v-if="!statusUpdate" 
                                    class="ms-auto text-white bg-blue-700 hover:bg-blue-800 focus:ring-4 focus:outline-none focus:ring-blue-300 font-medium rounded-lg text-sm w-full sm:w-auto px-5 py-2.5 text-center dark:bg-blue-600 dark:hover:bg-blue-700 dark:focus:ring-blue-800">
                                    <Icon v-if="pendingCreateLaporanHarian || pendingUpdate" name="tabler:loader-2"
                                        class="w-5 h-5 animate-spin text-white" />
                                    <span v-else>{{isEditMode ? 'Update' : 'Tambah'}}</span>
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