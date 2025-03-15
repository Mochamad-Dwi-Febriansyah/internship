<script setup>
import { ref } from 'vue'
import {
  Dialog,
  DialogPanel,
  Disclosure,
  DisclosureButton,
  DisclosurePanel,
  Popover,
  PopoverButton,
  PopoverGroup,
  PopoverPanel,
} from '@headlessui/vue'
import {
  Bars3Icon,
  ChartPieIcon,
  CursorArrowRaysIcon,
  FingerPrintIcon,
  XMarkIcon,
} from '@heroicons/vue/24/outline'
import { ChevronDownIcon, PhoneIcon, PlayCircleIcon } from '@heroicons/vue/20/solid'



const products = [
  { name: 'Analytics', description: 'Get a better understanding of your traffic', href: '#', icon: ChartPieIcon },
  { name: 'Engagement', description: 'Speak directly to your customers', href: '#', icon: CursorArrowRaysIcon },
  { name: 'Security', description: 'Your customers’ data will be safe and secure', href: '#', icon: FingerPrintIcon }
]
const callsToAction = [
  { name: 'Watch demo', href: '#', icon: PlayCircleIcon },
  { name: 'Contact sales', href: '#', icon: PhoneIcon },
]

const mobileMenuOpen = ref(false)

defineProps({
  toggleSidebar: Function
});

const { logout, getUserFullName, getUserRole, pendingLogout, getUserMentorName, getTanggalMulai, getTanggalSelesai } = useAuth()
const router = useRouter()

const handleLogout = async () => {
  await logout()
  router.push("/signin") // Redirect ke halaman login setelah logout
}

const time = ref("");
const updateTime = () => {
  time.value = new Date().toLocaleTimeString("id-ID", {
    hour: "2-digit",
    minute: "2-digit",
    second: "2-digit",
    hour12: false, // Format 24 jam
  });
};

onMounted(() => {
  updateTime();
  setInterval(updateTime, 1000);
});


// import { useSearch } from '../composables/useSearch'

const { search,loading, results, handleSelect } = useSearch()
</script>

<template>
  <header class="bg-white border-b sticky top-0 z-20">
    <nav class="container mx-auto flex items-center justify-between p-4 py-3" aria-label="Global">

      <div class="flex lg:flex-1">
        <button   @click="toggleSidebar" class="text-gray-800 mr-4 ">
          <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
          </svg>
        </button>
        <NuxtLink to="/" class="-m-1.5 p-1.5 flex items-center">
          <span class="sr-only">Your Company</span>
          <NuxtImg sizes="xs:40vw sm:45vw md:50px" src="/images/logo-web-pdam.png" format="webp" densities="x1 x2"
            alt="logo pdam" />
          <div class="flex flex-col items-start ml-4 justify-center">
            <h2
              class="-mx-3 block rounded-lg px-1 pt-2 pb-1 text-sm md:text-base font-semibold text-blue-900 hover:text-blue-800">
              Tirta Moedal
            </h2>
            <!-- <span class="text-gray-500 text-sm md:text-md">Kota Semarang</span> -->
          </div>
        </NuxtLink>
      </div>
      <div class="flex lg:hidden">


        <button type="button" class="-m-2.5 inline-flex items-center justify-center rounded-md p-2.5 text-gray-700"
          @click="mobileMenuOpen = true">
          <span class="sr-only">Open main menu</span>
          <Icon name="material-symbols:keyboard-arrow-down" class="w-6 h-6" />
        </button>
      </div>
      <PopoverGroup class="hidden lg:flex lg:gap-x-6">

        <!-- <p class="text-sm/6 font-normal text-gray-700">{{ time }}</p> -->

        <div class="relative w-full">
  <!-- Input Search -->
  <form @submit.prevent>
    <div class="relative">
      <div class="absolute inset-y-0 start-0 flex items-center ps-3 pointer-events-none">
        <svg class="w-4 h-4 text-gray-500 dark:text-gray-400" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 20 20">
          <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
            d="m19 19-4-4m0-7A7 7 0 1 1 1 8a7 7 0 0 1 14 0Z" />
        </svg>
      </div>
      <input
        v-model="search"
        type="search"
        class="block w-[200px] lg:w-[300px] focus:border-none focus:outline-none focus:shadow-md p-2 ps-9 text-sm text-gray-900 border border-gray-300 rounded-md bg-white focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-blue-500 dark:focus:border-blue-500"
        placeholder="Cari data..."
      />
    </div>
  </form>

  <!-- Hasil Pencarian -->
  <div v-if="search.length > 2" 
    class="absolute bg-white border rounded-md mt-1 shadow-lg z-50 w-[550px] max-h-60 overflow-y-auto 
         left-1/2 transform -translate-x-1/2">

    <!-- Loading Indicator -->
    <div v-if="loading" class="p-4  text-center text-gray-500">
       Memuat data...
    </div>

    <!-- Tidak Ada Data -->
    <div v-else-if="!results.length" class="p-4 text-center text-gray-500">
      Tidak ada data ditemukan.
    </div>

    <!-- List Data -->
    <ul v-else>
      <li v-for="(item, index) in results" :key="index" 
          class="p-2 hover:bg-gray-100 cursor-pointer border-b"
          @click="handleSelect(item)">
        <!-- Menampilkan berbagai jenis data -->
        <div v-if="item.nama_depan">
          👤 {{ item.nama_depan }} {{ item.nama_belakang }}
        </div>
        <div v-else-if="item.judul">
          📄 {{ item.judul }}
        </div>
        <div v-else-if="item.nama_sekolah">
          🏫 {{ item.nama_sekolah }}
        </div>
        <!-- <div v-else>
          📁 Data Lain
        </div> -->
      </li>
    </ul>
  </div>
</div>



      </PopoverGroup>
      <div class="hidden lg:flex lg:flex-1 lg:justify-end lg:items-center gap-4">
        <!-- Informasi User -->
        <div class="flex items-center gap-2">
          <div class="flex flex-row items-center text-gray-700 dark:text-gray-200">
            <div class="flex flex-col text-right">
              <div>
                <span class="ms-1 text-xs font-semibold">
                  {{ getUserFullName }}
                </span>
              </div>
              <span v-if="getUserRole === 'user'" class="text-xs font-medium text-gray-600">
                {{ getUserMentorName ? `Mentor ${getUserMentorName}` : 'Belum memiliki mentor' }}
              </span>
            </div> 
          </div>
        </div> 
      </div>

      <!-- <button @click="authStore.refreshAccessToken()">refresh</button> -->
    </nav>
    <Dialog class="lg:hidden" @close="mobileMenuOpen = false" :open="mobileMenuOpen">
      <div class="fixed inset-0 z-10" />

      <DialogPanel
        class="fixed inset-y-0 right-0 z-50 w-full overflow-y-auto bg-white px-6 py-6 sm:max-w-sm sm:ring-1 sm:ring-gray-900/10">
        <div class="flex items-center justify-between ">
          <a href="#" class="-m-1.5 p-1.5"> 
          </a>
          <button type="button" class="-m-2.5 rounded-md p-2.5 text-gray-700" @click="mobileMenuOpen = false">
            <span class="sr-only">Close menu</span>
            <XMarkIcon class="size-6" aria-hidden="true" />
          </button>
        </div>
        <div class="mt-6 flow-root">
          <div class="-my-6 divide-y divide-gray-500/10">
            <div class="space-y-2 py-6">
               
              <a href="#"
                class="-mx-3 block rounded-lg px-3 py-2 text-base/7 font-semibold text-gray-700 hover:bg-gray-50">Info
                Cabang</a>

                <p class="-mx-3 block rounded-lg px-3 py-2 text-base font-semibold text-gray-700 ">
  {{ getUserFullName }}
  <br>
  <span v-if="getUserRole === 'user'" class="text-sm font-medium text-gray-600">
    {{ getUserMentorName ? `Mentor ${getUserMentorName}` : 'Belum memiliki mentor' }}
  </span>
</p>



          
            </div>
            <!-- <div class="py-6">
              <Icon v-if="pendingLogout" name="tabler:loader-2" class="w-5 h-5 animate-spin text-gray-500" />
              <button v-else @click="handleLogout"
                class="text-sm/6 font-normal flex items-center gap-1 text-red-600 hover:text-red-700 dark:text-red-400 dark:hover:text-red-500  transition">
                <Icon name="tabler:logout" class="w-5 h-5" />
                Logout
              </button>
            </div> -->
          </div>
        </div>
      </DialogPanel>
    </Dialog>
  </header>
</template>
