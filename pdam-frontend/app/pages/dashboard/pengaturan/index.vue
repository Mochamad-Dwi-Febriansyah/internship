<script setup lang="ts">
definePageMeta({
    layout: 'dashboard',
    middleware: 'auth'
});
import * as yup from "yup"
import type { SubmissionHandler } from 'vee-validate'
import { useNotification } from '@/composables/useNotification'

const { addNotification } = useNotification()
const breadcrumb = [
    { label: "Pengaturan", icon: "material-symbols:settings", to: "/dashboard/pengaturan" },
];

const config = useRuntimeConfig();
const { getUserRole } = useAuth();

const { fetchTandaTangan, pendingFetch, errors, createTandaTangan, pendingCreate, deleteTandaTangan, pendingDelete } = useTandaTangan();
const isMentor = computed(() => getUserRole.value === "mentor");

const tandaTangan = ref()
onMounted(async () => {
    if (isMentor.value) {
        const response = await fetchTandaTangan();
        console.log(response)
        tandaTangan.value = response
    }
});


const handleFileUpload = (event: Event, handleChange: (value: File | null) => void): void => {
    const target = event.target as HTMLInputElement;
    const file = target.files?.[0] || null;
    handleChange(file);
};

const currentSchemaSignature = yup.object({
    signature: yup
        .mixed()
        .test('fileType', 'File harus berformat PNG', (value) => {
            const file = value as File;
            if (!file) return false;
            const allowedFormats = ['image/png'];
            return allowedFormats.includes(file.type);
        })
        .required('File tanda tangan wajib diunggah'),
});

const handleUploadSignature: SubmissionHandler<any> = async (values) => {
    try {
        const formValues = values
        const formData = new FormData();

        Object.entries(formValues).forEach(([key, value]) => {
            if (value !== undefined && value !== null) {
                formData.append(key, value as string | Blob);
            }
        });
        for (const pair of formData.entries()) {
            console.log(`${pair[0]}:`, pair[1]);
        }
        const response = await createTandaTangan(formData);
        const ttd = await fetchTandaTangan(); 
        tandaTangan.value = ttd
        addNotification('success', response.message);
    } catch (error: any) {
        addNotification('error', error.data.message);
        const errorFields = Object.keys(error.data.errors).join(', ')
        addNotification('error', `Validasi gagal pada: ${errorFields}`)
    }
}

const deleteTandaTanganClick = async (id: string) => {
    try {
        const response = await deleteTandaTangan(id)
        const ttd = await fetchTandaTangan(); 
        tandaTangan.value = ttd
        addNotification('success', response?.message || "Berhasil menghapus data");
    } catch (error: any) {
        addNotification('error', error.data.message);
    }
}

</script>

<template>
    <NuxtLayout>
        <BaseBreadcrumb :items="breadcrumb" />
        <div class="bg-white">
            <section class="mb-3">
                <div class="p-3">
                    <div class="flex flex-row justify-between items-center mb-2">
                        <!-- <h3 class="text-md text-gray-700 font-medium">Pengaturan</h3> -->
                    </div>
                    <!-- {{ tandaTangan }} -->
                    <div v-if="getUserRole === 'mentor'">
                        <div v-if="pendingFetch"
                            class="w-full p-6 flex gap-3 bg-white border border-gray-200 rounded-lg shadow-sm animate-pulse">
                            <div class="h-4 bg-gray-300 rounded w-1/4 mb-4"></div>
                            <div class="h-10 bg-gray-300 rounded w-full"></div>
                        </div>
                        <div v-else class="flex flex-col items-start gap-3">
                            <div class="w-full p-6 bg-white border border-gray-200 rounded-lg shadow-sm">
                                <div class="space-y-4">
                                    <div class="flex flex-row flex-wrap text-sm space-x-2">
                                        <div
                                            class="relative w-1/4 font-medium whitespace-nowrap text-gray-600 group flex items-center justify-center hover:bg-gray-100 cursor-pointer">
                                            <!-- Gambar Tanda Tangan (Ditengah) -->
                                            <NuxtImg v-if="tandaTangan?.data?.id" :src="tandaTangan?.data?.signature
                                                ? `${config.public.storage}/storage/${tandaTangan?.data?.signature}`
                                                : ''" class="max-w-full h-auto    rounded-md" alt="Tanda Tangan" format="webp"
                                                loading="lazy" />
                                            <p v-else>Belum unggah tanda tangan</p>
                                            <!-- Tombol Delete (Muncul Saat Hover) -->
                                            <button v-if="tandaTangan?.data?.id" @click="deleteTandaTanganClick(tandaTangan?.data?.id)"
                                                class="absolute top-1/2 left-1/2 -translate-x-1/2 -translate-y-1/2 cursor-pointer bg-red-500 text-white p-2 rounded-full opacity-0 group-hover:opacity-100 transition-opacity duration-300 shadow-md">
                                                <Icon name="material-symbols:delete-outline" class="w-5 h-5" />
                                            </button>
                                        </div>



                                        <div>
                                            <h3 class="text-lg font-semibold text-gray-700 mb-3">Langkah-langkah Mengunggah
                                                Tanda Tangan (PNG Transparan)</h3>
                                            <ul class="space-y-3 text-gray-600">
                                                <li>
                                                    <span class="font-medium text-gray-600">1. Tanda Tangani di Kertas
                                                        Putih</span>
                                                    <ul class="ml-5 list-disc text-sm text-gray-500">
                                                        <li>Gunakan pulpen atau spidol hitam agar lebih jelas.</li>
                                                        <li>Pastikan kertas dalam kondisi bersih.</li>
                                                    </ul>
                                                </li>

                                                <li>
                                                    <span class="font-medium text-gray-600">2. Ambil Gambar Tanda
                                                        Tangan</span>
                                                    <ul class="ml-5 list-disc text-sm text-gray-500">
                                                        <li>Gunakan kamera ponsel atau scanner.</li>
                                                        <li>Pastikan pencahayaan cukup agar terlihat jelas.</li>
                                                    </ul>
                                                </li>

                                                <li>
                                                    <span class="font-medium text-gray-600">3. Hapus Background Putih
                                                        (Buat Transparan)</span>
                                                    <ul class="ml-5 list-disc text-sm text-gray-500">
                                                        <li><span class="font-medium text-gray-500">Menggunakan
                                                                Website Online:</span></li>
                                                        <ul class="ml-5 list-disc text-gray-500">
                                                            <li>Kunjungi
                                                                <a href="https://www.remove.bg/" target="_blank"
                                                                    class="text-blue-500 hover:underline">remove.bg.</a> 
                                                            </li>
                                                            <li>Unggah gambar tanda tangan.</li>
                                                            <li>Unduh hasilnya dalam format PNG.</li>
                                                        </ul>
                                                    </ul>
                                                </li>

                                                <li>
                                                    <span class="font-medium text-gray-600">4. Unggah ke Sistem</span>
                                                    <ul class="ml-5 list-disc text-sm text-gray-500">
                                                        <li>Pastikan file dalam format PNG transparan.</li>
                                                        <li>Unggah melalui form yang tersedia di aplikasi.</li>
                                                    </ul>
                                                </li>
                                            </ul>
                                        </div>


                                    </div>


                                    <div class="flex flex-row flex-wrap text-sm space-x-2">
                                        <div class="w-1/4 font-medium whitespace-nowrap text-gray-600">Unggah Tanda Tangan
                                        </div>
                                        <div class="w-full sm:w-auto text-gray-500">
                                            <Form @submit="handleUploadSignature"
                                                :validation-schema="currentSchemaSignature"
                                                class="flex flex-row items-center gap-2">
                                                <Field name="signature" v-slot="{ handleChange }">
                                                    <input type="file" id="signature"
                                                        @change="handleFileUpload($event, handleChange)"
                                                        class="mt-1 p-2 w-full border rounded-md" />
                                                    <ErrorMessage name="signature" class="text-red-500 text-sm" />
                                                    <p v-if="errors.signature" class="text-red-500 text-sm">{{
                                                        errors.signature[0] }}</p>
                                                </Field>
                                                <button
                                                    class="ms-auto text-white bg-blue-700 hover:bg-blue-800 focus:ring-4 focus:outline-none focus:ring-blue-300 font-medium rounded-lg text-sm w-full sm:w-auto px-2 py-1 text-center dark:bg-blue-600 dark:hover:bg-blue-700 dark:focus:ring-blue-800">
                                                    <Icon v-if="pendingCreate" name="tabler:loader-2"
                                                        class="w-5 h-5 animate-spin text-white" />
                                                    <span v-else>Simpan</span>
                                                </button>
                                            </Form>
                                        </div> 
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>


                </div>
            </section>
        </div>
    </NuxtLayout>
</template>
