<script setup lang="ts">
definePageMeta({
    layout: 'dashboard',
    middleware: 'auth'
})

const route = useRoute()
const router = useRouter()
const currentPage = ref(Number(route.query.page) || 1);
const { usersMagangByMentor, pending, clearCacheUserMagangByMentor, pendingClearCache } = useUsersMagangByMentor(currentPage)

console.log(usersMagangByMentor)

const breadcrumb = [
    { label: "Magang", icon: "clarity:users-solid", to: "/dashboard/mentor/magang" }
]
function goToPage(page: number) {
    if (page < 1 || page > (usersMagangByMentor.value?.data.last_page ?? 1)) return;
    currentPage.value = page; 
    router.push({ query: { ...route.query, page } });
}

function getPageNumber(url: string | null | undefined): number {
  const match = url?.match(/page=(\d+)/);
  return match?.[1] ? parseInt(match[1], 10) : 1;
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
                        <!-- <h3 class="text-md text-gray-700 font-medium">Magang</h3> -->
                        <button @click="clearCacheUserMagangByMentor()" class="ms-auto">
                            <Icon name="material-symbols-light:directory-sync" 
                            :class="{'animate-spin' : pendingClearCache}"
                            class=" w-5 h-5 inline-block align-middle transition-transform duration-300 ease-in-out"/>
                        </button>
                    </div> 
                    <div v-if="!usersMagangByMentor?.data?.data || usersMagangByMentor.data.data.length === 0" class="text-center text-gray-500 p-4 border border-gray-200 rounded-lg">
                        Tidak ada data.
                    </div>
                    <div v-else>   
                        <div class="relative overflow-x-auto shadow-md sm:rounded-lg">
                            <table class="w-full text-sm text-left rtl:text-right text-gray-500 dark:text-gray-400">
                                <thead
                                    class="text-xs text-gray-700 uppercase bg-gray-50 dark:bg-gray-700 dark:text-gray-400">
                                    <tr>
                                        <th scope="col" class="px-6 py-3">
                                            Identitas
                                        </th>
                                        <th scope="col" class="px-6 py-3">
                                            Sekolah/Universitas
                                        </th> 
                                        <th scope="col" class="px-6 py-3 text-center">
                                            Status
                                        </th>
                                    </tr>
                                </thead>
                                <tbody>

                                    <tr v-for="user in usersMagangByMentor.data.data" :key="user.id"
                                        class="bg-white border-b dark:bg-gray-800 dark:border-gray-700 border-gray-200 hover:bg-gray-50 dark:hover:bg-gray-600">
                                        <td scope="row"
                                            class="px-6 py-2  text-gray-900 whitespace-nowrap dark:text-white">
                                            <div class="flex flex-col">
                                                <p class="   text-gray-900 dark:text-white">
                                                    {{ user.nama_depan }} {{ user.nama_belakang }}
                                                </p>
                                                <p class=" backdrop: text-gray-500 dark:text-gray-400">
                                                    {{ user.email }}
                                                </p>
                                            </div>
                                        </td>
                                        <td
                                            class="px-6 py-2  text-gray-900 whitespace-nowrap dark:text-white">
                                            <div class="flex flex-col">
                                                <p class=" text-gray-500 dark:text-white">
                                                    {{ user.berkas?.master_sekolah.nama_sekolah_universitas }}
                                                </p>
                                                <p class=" text-gray-500 dark:text-white">
                                                    {{ user.berkas?.master_sekolah.jurusan_sekolah }}
                                                </p>
                                                <p class=" text-gray-500 dark:text-gray-400">
                                                    {{ user.berkas?.master_sekolah.email_sekolah_universitas }}
                                                </p>
                                                <p class=" text-gray-500 dark:text-gray-400">
                                                    {{ user.berkas?.master_sekolah.fakultas_universitas }}
                                                </p>
                                                <p class=" text-gray-500 dark:text-gray-400">
                                                    {{ user.berkas?.master_sekolah.program_studi_universitas }}
                                                </p>
                                            </div>
                                        </td> 

                                        <td class="px-6 py-4">
                                            <div class="flex flex-row items-center justify-center gap-2">
                                                <NuxtLink :to="`./magang/${user.id}/detail`"
                                                    class="px-3 py-1 text-xs w-fit font-semibold rounded-full flex items-center gap-2 bg-blue-100 border border-blue-200 text-blue-800 dark:bg-blue-900 dark:text-blue-300">
                                                    <Icon name="material-symbols:person-search-outline" class="w-4 h-4 inline-block align-middle" />
                                                </NuxtLink> 
                                                <NuxtLink :to="`./magang/${user.id}`"
                                                    class="px-3 py-1 text-xs w-fit font-semibold rounded-full flex items-center gap-2 bg-blue-100 border border-blue-200 text-blue-800 dark:bg-blue-900 dark:text-blue-300">
                                                    <Icon name="material-symbols:visibility-outline" class="w-4 h-4 inline-block align-middle" />
                                                </NuxtLink> 
                                                <span class="px-3 py-1 text-xs font-semibold rounded-full flex items-center"
                                                    :class="{
                                                        'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-300': user.status === 'active',
                                                        'bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-300': user.status === 'inactive'
                                                    }">
                                                    <Icon
                                                        :name="user.status === 'active' ? 'mdi:check-circle' : 'mdi:close-circle'"
                                                        class="w-4 h-4 inline-block align-middle" />
                                                    {{ user.status === 'active' ? 'Aktif' : 'Nonaktif' }}
                                                </span>

                                                
                                            </div>
                                        </td>


                                    </tr>

                                </tbody>
                            </table>
                        </div>
                        
                        <nav class="flex items-center flex-column flex-wrap md:flex-row justify-between pt-4 px-6 py-3"
                        aria-label="Table navigation">
                        <!-- Informasi jumlah data -->
                        <span class="text-sm font-normal text-gray-500 dark:text-gray-400 mb-4 md:mb-0 block w-full md:inline md:w-auto">
                            Menampilkan 
                            <span class="font-semibold text-gray-900 dark:text-white">{{ usersMagangByMentor.data.from }}</span> - 
                            <span class="font-semibold text-gray-900 dark:text-white">{{ usersMagangByMentor.data.to }}</span> dari 
                            <span class="font-semibold text-gray-900 dark:text-white">{{ usersMagangByMentor.data.total }}</span>
                        </span>

                        <!-- Navigasi Pagination -->
                        <ul class="inline-flex -space-x-px rtl:space-x-reverse text-sm h-8">
                            <!-- Tombol Previous -->
                            <li>
                                <button 
                                    @click="goToPage(currentPage - 1)" 
                                    :disabled="currentPage <= 1"
                                    class="flex items-center justify-center px-3 h-8 ms-0 leading-tight border rounded-s-lg transition"
                                    :class="currentPage > 1 ? 
                                        'text-gray-500 bg-white border-gray-300 hover:bg-gray-100 hover:text-gray-700 dark:bg-gray-800 dark:border-gray-700 dark:text-gray-400 dark:hover:bg-gray-700 dark:hover:text-white' 
                                        : 'bg-gray-300 text-gray-500 cursor-not-allowed'"
                                >
                                    Previous
                                </button>
                            </li>

                            <!-- Nomor Halaman -->
                            <li v-for="link in usersMagangByMentor.data.links" :key="link.label">
                            <button 
                                v-if="link.url && !isNaN(parseInt(link.label))"   
                                @click="goToPage(getPageNumber(link.url))"
                                class="flex items-center justify-center px-3 h-8 leading-tight border transition"
                                :class="link.active ? 
                                    'text-white bg-blue-600' 
                                    : 'text-gray-500 bg-white border-gray-300 hover:bg-gray-100 hover:text-gray-700 dark:bg-gray-800 dark:border-gray-700 dark:text-gray-400 dark:hover:bg-gray-700 dark:hover:text-white'"
                            >
                                {{ link.label }}
                            </button>
                            </li> 
                            <!-- Tombol Next -->
                            <li>
                                <button 
                                    @click="goToPage(currentPage + 1)" 
                                    :disabled="currentPage >= usersMagangByMentor.data.last_page"
                                    class="flex items-center justify-center px-3 h-8 leading-tight border rounded-e-lg transition"
                                    :class="currentPage < usersMagangByMentor.data.last_page ? 
                                        'text-gray-500 bg-white border-gray-300 hover:bg-gray-100 hover:text-gray-700 dark:bg-gray-800 dark:border-gray-700 dark:text-gray-400 dark:hover:bg-gray-700 dark:hover:text-white' 
                                        : 'bg-gray-300 text-gray-500 cursor-not-allowed'"
                                >
                                    Next
                                </button>
                            </li>
                        </ul>
                        </nav> 
                    </div>
                </div>
            </section>
        </div>
    </NuxtLayout>
</template>

<style scoped>

</style>