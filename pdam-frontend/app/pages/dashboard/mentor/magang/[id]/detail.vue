<script setup lang="ts">

definePageMeta({
    layout: 'dashboard',
    middleware: 'auth'
})
const route = useRoute()
const router = useRouter()
const config = useRuntimeConfig()
const userId = String(route.params.id)

const breadcrumb = [
    { label: "Magang", icon: "clarity:users-solid", to: "/dashboard/mentor/magang" },
    { label: "Detail Magang" },
]

const { detailUsersMagang, pendingFetchById } = useDetailUserMagang()
const detailUser = ref()
onMounted(async () => {
    const response = await detailUsersMagang(userId);
    console.log(response)
    detailUser.value = response.data
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
                        <!-- <h3 class="text-md text-gray-700 font-medium">Detail Laporan Harian</h3> -->
                        <div class="flex flex-row space-x-3 ms-auto">
                            <button @click="router.back()"
                                class="ms-auto px-3 py-1  text-xs w-fit rounded-full flex items-center gap-2 bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-300 border border-red-300 hover:bg-red-200">
                                <Icon name="material-symbols:arrow-left-alt-rounded"
                                    class="w-4 h-4 inline-block align-middle transition-transform duration-300 ease-in-out" />
                                Kembali
                            </button>
                        </div>
                    </div>
                    <div v-if="detailUser"
                        class="mb-4 w-full p-6 bg-white border border-gray-200 rounded-2xl shadow-sm dark:bg-gray-800 dark:border-gray-700">
                        <div class="space-y-6">
                            <!-- Nama Lengkap -->
                            <div class="flex flex-col md:flex-row gap-2 md:gap-4 text-sm">
                                <div class="md:w-1/4 font-semibold text-gray-700 dark:text-gray-300">Nama Lengkap</div>
                                <div class="md:w-3/4 text-gray-600 dark:text-gray-400">
                                    <p class="leading-relaxed">
                                        {{ detailUser.user.nama_depan }} {{ detailUser.user.nama_belakang }}
                                    </p>
                                </div>
                            </div>

                            <!-- NISN/NPM/NIM/NPP -->
                            <div class="flex flex-col md:flex-row gap-2 md:gap-4 text-sm">
                                <div class="md:w-1/4 font-semibold text-gray-700 dark:text-gray-300">NISN/NPM/NIM/NPP
                                </div>
                                <div class="md:w-3/4 text-gray-600 dark:text-gray-400">
                                    <p class="leading-relaxed">{{ detailUser.user.nisn_npm_nim_npp }}</p>
                                </div>
                            </div>

                            <!-- Tanggal Lahir -->
                            <div class="flex flex-col md:flex-row gap-2 md:gap-4 text-sm">
                                <div class="md:w-1/4 font-semibold text-gray-700 dark:text-gray-300">Tanggal Lahir</div>
                                <div class="md:w-3/4 text-gray-600 dark:text-gray-400">
                                    <p class="leading-relaxed">{{ detailUser.user.tanggal_lahir }}</p>
                                </div>
                            </div>

                            <!-- Jenis Kelamin -->
                            <div class="flex flex-col md:flex-row gap-2 md:gap-4 text-sm">
                                <div class="md:w-1/4 font-semibold text-gray-700 dark:text-gray-300">Jenis Kelamin</div>
                                <div class="md:w-3/4 text-gray-600 dark:text-gray-400">
                                    <p class="leading-relaxed capitalize">
                                        {{ detailUser.user.jenis_kelamin === 'male' ? 'Laki-laki' : 'Perempuan' }}
                                    </p>
                                </div>
                            </div>

                            <!-- Nomor HP -->
                            <div class="flex flex-col md:flex-row gap-2 md:gap-4 text-sm">
                                <div class="md:w-1/4 font-semibold text-gray-700 dark:text-gray-300">Nomor HP</div>
                                <div class="md:w-3/4 text-gray-600 dark:text-gray-400">
                                    <p class="leading-relaxed">{{ detailUser.user.nomor_hp }}</p>
                                </div>
                            </div>

                            <!-- Email -->
                            <div class="flex flex-col md:flex-row gap-2 md:gap-4 text-sm">
                                <div class="md:w-1/4 font-semibold text-gray-700 dark:text-gray-300">Email</div>
                                <div class="md:w-3/4 text-gray-600 dark:text-gray-400">
                                    <p class="leading-relaxed">{{ detailUser.user.email }}</p>
                                </div>
                            </div>

                            <!-- Alamat -->
                            <div class="flex flex-col md:flex-row gap-2 md:gap-4 text-sm">
                                <div class="md:w-1/4 font-semibold text-gray-700 dark:text-gray-300">Alamat</div>
                                <div class="md:w-3/4 text-gray-600 dark:text-gray-400">
                                    <p class="leading-relaxed">
                                        {{ detailUser.user.alamat }}, Kec. {{ detailUser.user.kecamatan }},
                                        {{ detailUser.user.kelurahan_desa }}, {{ detailUser.user.provinsi }},
                                        {{ detailUser.user.kode_pos }}
                                    </p>
                                </div>
                            </div>

                            <!-- Foto -->
                            <div class="flex flex-col md:flex-row gap-2 md:gap-4 text-sm">
                                <div class="md:w-1/4 font-semibold text-gray-700 dark:text-gray-300">Foto</div>
                                <div class="md:w-3/4 text-gray-600 dark:text-gray-400"> 
                                        <a :href="`${config.public.storage}/storage/${detailUser.user.foto}`"
                                                target="_blank" rel="noopener noreferrer"
                                                class="px-3 py-1 text-xs w-fit font-semibold rounded-full flex items-center gap-2 bg-yellow-100 border border-yellow-200 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-300">
                                                <Icon name="material-symbols:open-in-new"
                                                    class="w-4 h-4 inline-block align-middle" />
                                            </a>
                                </div>
                            </div>

                            <!-- Role -->
                            <div class="flex flex-col md:flex-row gap-2 md:gap-4 text-sm">
                                <div class="md:w-1/4 font-semibold text-gray-700 dark:text-gray-300">Role</div>
                                <div class="md:w-3/4 text-gray-600 dark:text-gray-400">
                                    <p class="leading-relaxed capitalize">{{ detailUser.user.role }}</p>
                                </div>
                            </div>

                            <!-- Status -->
                            <div class="flex flex-col md:flex-row gap-2 md:gap-4 text-sm">
                                <div class="md:w-1/4 font-semibold text-gray-700 dark:text-gray-300">Status</div>
                                <div class="md:w-3/4 text-gray-600 dark:text-gray-400">
                                    <p class="leading-relaxed capitalize">{{ detailUser.user.status }}</p>
                                </div>
                            </div>
                        </div>
                    </div>



                </div>
            </section>
        </div>
    </NuxtLayout>
</template>