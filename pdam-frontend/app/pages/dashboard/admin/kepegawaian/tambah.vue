<script setup lang="ts">
import * as yup from 'yup'
import type { SubmissionHandler } from 'vee-validate'

const currentPage = ref(1);
const { createUser, pendingCreate, errors, refresh } = useUsers(currentPage, 'kepegawaian');
const { addNotification }  = useNotification()
const router = useRouter()
definePageMeta({
    layout: 'dashboard',
    middleware: 'auth'
})

const breadcrumb = [
    {label: "User Kepegawaian" , icon: "clarity:users-solid",  to: "/dashboard/admin/kepegawaian", }, 
    {label: "Tambah" ,  }
]

const simpleSchema = yup.object({
    nisn_npm_nim_npp: yup.string().required('NPP / NIM wajib diisi(isikan strip jika belum mempunyai)'),
    bagian: yup.string().required('Bagian wajib diisi'),
    nama_depan: yup.string().required('Nama depan wajib diisi'),
    nama_belakang: yup.string().required('Nama belakang wajib diisi'), 
    jenis_kelamin: yup.string().oneOf(['male', 'female'], 'Pilih jenis kelamin yang valid').required('Jenis kelamin wajib diisi'),
    nomor_hp: yup.string().matches(/^[0-9]+$/, 'Nomor HP hanya boleh berisi angka').required('Nomor HP wajib diisi'),
    email: yup.string().email('Format email tidak valid').required('Email wajib diisi'),
    password: yup.string().required('Password wajib diisi').min(6, 'Password minimal 6 karakter'),
})

const handleCreate: SubmissionHandler<any> = async (value, { resetForm }: { resetForm: () => void }) => {
    try {
        const response = await createUser(value) 
        resetForm();
        addNotification('success', response.message)
        await refresh(); 
        router.push('/dashboard/admin/kepegawaian')
    } catch (error: any) { 
        addNotification('error', error.data.message) 
    }
}

</script>
<template>
     <NuxtLayout> 
        <BaseBreadcrumb :items="breadcrumb"/> 
        <div class="px-3 m-3">
            <section class="mb-3">
                <div class="w-full p-6 bg-white border border-gray-200 rounded-lg shadow-sm dark:bg-gray-800 dark:border-gray-700"> 
                    <Form @submit="handleCreate" :validation-schema="simpleSchema"  class=" mx-auto">
    
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mt-4">
                            <div>
                                <label for="nama_depan" class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">Nama Depan</label>
                                <Field name="nama_depan" type="text" id="nama_depan" class="bg-white border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-blue-500 dark:focus:border-blue-500" />
                                <ErrorMessage name="nama_depan"   class="text-red-500 text-sm"/>
                                <p v-if="errors.nama_depan" class="text-red-500 text-sm">{{ errors.nama_depan[0] }}</p>

                            </div>
                            <div>
                                <label for="nama_belakang" class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">Nama Belakang</label>
                                <Field name="nama_belakang" id="nama_belakang" class="bg-white border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-blue-500 dark:focus:border-blue-500" />
                                <ErrorMessage name="nama_belakang" class="text-red-500 text-sm"/>
                                <p v-if="errors.nama_belakang" class="text-red-500 text-sm">{{ errors.nama_belakang[0] }}</p>
                            </div> 
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mt-4">
                            <div>
                                <label for="nisn_npm_nim_npp" class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">NPP</label>
                                <Field name="nisn_npm_nim_npp" type="string" id="nisn_npm_nim_npp" class="bg-white border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-blue-500 dark:focus:border-blue-500" />
                                <ErrorMessage name="nisn_npm_nim_npp" class="text-red-500 text-sm"/>
                                <p v-if="errors.nisn_npm_nim_npp" class="text-red-500 text-sm">{{ errors.nisn_npm_nim_npp[0] }}</p>
                            </div>
                            <div>
                                <label for="bagian" class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">Bagian</label>
                                <Field name="bagian" type="text" id="bagian" class="bg-white border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-blue-500 dark:focus:border-blue-500" />
                                <ErrorMessage name="bagian" class="text-red-500 text-sm"/>
                                <p v-if="errors.bagian" class="text-red-500 text-sm">{{ errors.bagian[0] }}</p>
                            </div> 
                        </div>
                        
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mt-4">
                            <div>
                                <label for="jenis_kelamin" class="mb-2 block text-sm font-medium text-gray-700">
                                Jenis Kelamin <span class="text-red-500">*</span>
                                </label>
                                
                                <Field as="select" name="jenis_kelamin" id="jenis_kelamin"
                                class="bg-white border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-blue-500 dark:focus:border-blue-500" >
                                <option value="" disabled selected>Pilih jenis kelamin</option>
                                <option value="male">Laki-laki</option>
                                <option value="female">Perempuan</option>
                                </Field>

                                <ErrorMessage name="jenis_kelamin" class="text-red-500 text-sm" />
                                <p v-if="errors.jenis_kelamin" class="text-red-500 text-sm">{{ errors.jenis_kelamin[0] }}</p>
                            </div>
                            <div>
                                <label for="nomor_hp" class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">Nomor hp</label>
                                <Field name="nomor_hp" type="number" id="nomor_hp" class="bg-white border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-blue-500 dark:focus:border-blue-500" />
                                <ErrorMessage name="nomor_hp" class="text-red-500 text-sm"/>
                                <p v-if="errors.nomor_hp" class="text-red-500 text-sm">{{ errors.nomor_hp[0] }}</p>
                            </div>
                          
                        </div> 
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mt-4">
                            <div>
                                <label for="email" class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">Email</label>
                                <Field name="email" type="text" id="email" class="bg-white border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-blue-500 dark:focus:border-blue-500" />
                                <ErrorMessage name="email" class="text-red-500 text-sm"/>
                                <p v-if="errors.email" class="text-red-500 text-sm">{{ errors.email[0] }}</p>
                            </div> 
                            <div>
                                <label for="password" class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">Password</label>
                                <Field name="password" type="password" id="password" class="bg-white border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-blue-500 dark:focus:border-blue-500" />
                                <ErrorMessage name="password" class="text-red-500 text-sm"/>
                                <p v-if="errors.password" class="text-red-500 text-sm">{{ errors.password[0] }}</p>
                            </div> 
                        </div> 
    
                        <div class="flex mt-6 justify-start gap-4"> 
                            <button type="button"  @click="router.back()" class="text-white bg-red-700 hover:bg-red-800 focus:ring-4 focus:outline-none focus:ring-blue-300 font-medium rounded-lg text-sm w-full sm:w-auto px-5 py-2.5 text-center dark:bg-blue-600 dark:hover:bg-blue-700 dark:focus:ring-blue-800">
                                Kembali
                            </button>
                            <button type="submit" class="text-white bg-blue-700 hover:bg-blue-800 focus:ring-4 focus:outline-none focus:ring-blue-300 font-medium rounded-lg text-sm w-full sm:w-auto px-5 py-2.5 text-center dark:bg-blue-600 dark:hover:bg-blue-700 dark:focus:ring-blue-800">
                                {{ pendingCreate ? 'Membuat...' : 'Tambah' }}</button>
                        </div>

                    </Form>
                </div>
            </section> 
        </div>
        
    </NuxtLayout>
</template>



<style scoped>

</style>