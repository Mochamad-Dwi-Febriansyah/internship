<script setup lang="ts">
// import { ref, computed, defineProps, onMounted, onUnmounted } from 'vue'
import { useRoute } from 'vue-router' 


// defineProps({
//   isSidebarLeftOpen: Boolean
// })
const props = defineProps<{
  isSidebarLeftOpen: boolean;
}>();

const route = useRoute()
const router = useRouter()
const { logout,pendingLogout, getUserRole } = useAuth()
const userRole = getUserRole.value

// State dropdown untuk setiap menu
const openDropdowns = ref<Record<string, boolean>>({})

// Fungsi untuk mengecek apakah path aktif
const isActive = (item: MenuItem) => {
  if (item.path) {
    return route.path === item.path // Cek menu utama dengan pencocokan penuh
  } else if (item.children) {
    return item.children.some(child => route.path === child.path) // Cek anak-anaknya dengan pencocokan penuh
  }
  return false
}



// Tipe data menu item
type MenuItem = {
  label: string
  path?: string
  icon?: string | null
  children?: MenuItem[]
}

// Menu berdasarkan role
const allMenus: Record<string, MenuItem[]> = {
  admin: [
    { label: 'Dashboard', path: '/dashboard/admin', icon: 'material-symbols:dashboard' },
    // { label: 'Users (Magang)', path: '/dashboard/user', icon: 'material-symbols:person' },
    // { label: 'Mentor', path: '/dashboard/mentor', icon: 'material-symbols:school' },
    // { label: 'Kepegawaian', path: '/dashboard/kepegawaian', icon: 'material-symbols:badge' },
    {
      label: 'Users',
      icon: 'clarity:users-solid',
      children: [
        { label: 'Magang', path: '/dashboard/admin/magang' },
        { label: 'Mentor', path: '/dashboard/admin/mentor' },
        { label: 'Kepegawaian', path: '/dashboard/admin/kepegawaian' },
      ]
    },
    { label: 'Profile', path: '/dashboard/profile', icon: 'material-symbols:person' },  
    { label: 'Pengaturan', path: '/dashboard/pengaturan', icon: 'material-symbols:settings' },  
    
  ],
  mentor: [
    { label: 'Dashboard', path: '/dashboard/mentor', icon: 'material-symbols:dashboard' },  
    { label: 'Magang', path: '/dashboard/mentor/magang', icon: 'clarity:users-solid' }, 
    { label: 'Verifikasi Laporan Harian', path: '/dashboard/mentor/magang/laporan-harian', icon: 'mdi:file-document-alert-outline' }, 
    { label: 'Verifikasi Laporan Akhir', path: '/dashboard/mentor/magang/laporan-akhir', icon: 'mdi:file-document-alert-outline' },
    { label: 'Profile', path: '/dashboard/profile', icon: 'material-symbols:person' },  
    { label: 'Pengaturan', path: '/dashboard/pengaturan', icon: 'material-symbols:settings' },  

  ],
  kepegawaian: [
    { label: 'Dashboard', path: '/dashboard/kepegawaian', icon: 'material-symbols:dashboard' },
    { label: 'Pengajuan Berkas', path: '/dashboard/kepegawaian/pengajuan-berkas', icon: 'mdi:file-document-alert-outline' },
    {
      label: 'Users',
      icon: 'clarity:users-solid',
      children: [
        { label: 'Magang', path: '/dashboard/kepegawaian/magang' },
        { label: 'Mentor', path: '/dashboard/kepegawaian/mentor' }, 
      ]
    },
    { label: 'Verifikasi Laporan Akhir', path: '/dashboard/kepegawaian/magang/laporan-akhir', icon: 'mdi:file-document-alert-outline' },
    { label: 'Profile', path: '/dashboard/profile', icon: 'material-symbols:person' },  
    { label: 'Pengaturan', path: '/dashboard/pengaturan', icon: 'material-symbols:settings' },  
  ],
  user: [
  { label: 'Dashboard', path: '/dashboard/user', icon: 'material-symbols:dashboard' }, 
  { label: 'Presensi', path: '/dashboard/user/presensi', icon: 'material-symbols:checkbook-outline-rounded' }, 
  { label: 'Laporan Akhir', path: '/dashboard/user/laporan-akhir', icon: 'material-symbols:checkbook-outline-rounded' }, 
  { label: 'Profile', path: '/dashboard/profile', icon: 'material-symbols:person' },  
  { label: 'Pengaturan', path: '/dashboard/pengaturan', icon: 'material-symbols:settings' },  
  ]
}

// Filter menu sesuai role user
const menuItems = computed(() => allMenus[userRole as keyof typeof allMenus] || [])

// Fungsi toggle dropdown
const toggleDropdown = (label: string) => {
  openDropdowns.value[label] = !openDropdowns.value[label]
}
 



const windowWidth = ref(1024); // Default ukuran layar (desktop)

// Pastikan hanya dijalankan di client-side
onMounted(() => {
  if (typeof window !== "undefined") {
    windowWidth.value = window.innerWidth;
    window.addEventListener("resize", updateWindowWidth);
  }
});
onUnmounted(() => {
  if (typeof window !== "undefined") {
    window.removeEventListener("resize", updateWindowWidth);
  }
});

// Update ukuran layar saat berubah
const updateWindowWidth = () => {
  windowWidth.value = window.innerWidth;
};

// Cek apakah mode mobile (<= 768px)
const isMobile = computed(() => windowWidth.value <= 768);

// Hitung lebar sidebar
const sidebarWidth = computed(() => {
  if (isMobile.value) {
    return props.isSidebarLeftOpen ? "13rem" : "0rem"; // Mobile: Tertutup full
  }
  return props.isSidebarLeftOpen ? "13rem" : "3.4rem"; // Desktop: Tetap 3.4rem
});
 
const handleLogout = async () => {
  await logout()
  router.push("/signin") // Redirect ke halaman login setelah logout
}
</script>

<template>
  <div
    class="bg-white border-r h-screen fixed transition-all duration-500 flex flex-col justify-between"
    :style="{ width: sidebarWidth }"
  >
    <ul :class="{ 'opacity-0 pointer-events-none': !isSidebarLeftOpen && isMobile }">
      <li
        v-for="item in menuItems"
        :key="item.label"
        class="py-2 px-4"
        :class="[
          isActive(item ?? '') ? 'bg-blue-100 text-blue-600' : 'text-gray-800 hover:bg-gray-100'
        ]"
      >
        <!-- Jika Menu Memiliki Children (Dropdown) -->
        <div
          v-if="item.children"
          class="cursor-pointer flex items-center justify-between"
          @click="toggleDropdown(item.label)"
        >
          <div class="flex items-center">
            <Icon :name="item.icon ?? 'material-symbols:dashboard'" class="h-5 w-5 mr-2" />
            <span v-if="isSidebarLeftOpen" class="text-sm whitespace-nowrap truncate">{{ item.label }}</span>
          </div>
          <Icon
           v-if="isSidebarLeftOpen"
            name="material-symbols:arrow-drop-down"
            class="h-5 w-5 transition-transform duration-200"
            :class="{ 'rotate-180': openDropdowns[item.label] }"
          />
        </div>

        <!-- Jika Menu Tidak Memiliki Children -->
        <NuxtLink
          v-else
          :to="item.path ?? '/unauthorized'"
          class="flex items-center"
        >
          <Icon :name="item.icon ?? 'material-symbols:dashboard'" class="h-5 w-5 min-h-5 min-w-5" :class="{'mr-2': isSidebarLeftOpen}" />
          <span v-if="isSidebarLeftOpen" class="text-sm whitespace-nowrap  truncate ">{{ item.label }}</span>
        </NuxtLink>

        <!-- Dropdown Menu -->
        <ul v-if="item.children && openDropdowns[item.label] && isSidebarLeftOpen" class="ml-6 mt-2  ">
          <li
            v-for="child in item.children"
            :key="child.label"
            class="py-2 px-4 text-gray-700 hover:bg-gray-200 rounded-lg"
          >
            <NuxtLink :to="child.path ?? '/unauthorized'" class="text-sm">{{ child.label }}</NuxtLink>
          </li>
        </ul>
      </li>
    </ul>
    <div class="border-t sticky bottom-0 left-0 right-0 bg-white ">
      <button
        @click="handleLogout"
        class="w-full flex items-center gap-2 py-2 px-4 transition-all text-red-500 hover:text-red-700"
      >
           <Icon v-if="pendingLogout" name="tabler:loader-2" class="h-5 w-5 min-h-5 min-w-5" />
          <Icon v-else name="material-symbols:power-settings-new-rounded" class="h-5 w-5 min-h-5 min-w-5" />
          <span v-if="isSidebarLeftOpen" class="text-sm font-semibold ">Logout</span>
      </button>
    </div>
  </div>
</template>

<style scoped>
/* Optional: Tambahan styling jika diperlukan */
</style>
