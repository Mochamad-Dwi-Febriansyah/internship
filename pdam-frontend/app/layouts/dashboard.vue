<script setup lang="ts">
import { ref, computed, onMounted, onUnmounted } from "vue";

const isSidebarLeftOpen = ref(false);
const windowWidth = ref(1024); // Default ukuran layar (desktop)

// Pastikan hanya dijalankan di client
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
    return isSidebarLeftOpen.value ? "13rem" : "0rem"; // Mobile: Full tertutup
  }
  return isSidebarLeftOpen.value ? "13rem" : "3.4rem"; // Desktop: Tetap 3.4rem
});

// Toggle sidebar
const toggleSidebar = () => {
  isSidebarLeftOpen.value = !isSidebarLeftOpen.value;
};
</script>

<template>
  <div class="flex flex-col min-h-screen">
    <!-- Header -->
    <BaseHeaderDashboard :toggleSidebar="toggleSidebar" />

    <div class="flex flex-1 relative">
      <!-- Sidebar -->
      <div
        class="bg-white border-r h-screen fixed transition-all duration-500 z-20"
        :style="{ width: sidebarWidth }"
      >
      <!-- <div
        class="bg-white border-r h-screen fixed transition-all duration-500 z-20"
        :style="{ width: sidebarWidth }"
      > -->
        <BaseSidebarDashboard :isSidebarLeftOpen="isSidebarLeftOpen" />
      </div>

      <!-- Overlay untuk mobile -->
      <div
        v-if="isMobile && isSidebarLeftOpen"
        class="fixed inset-0 bg-black bg-opacity-50 z-10"
        @click="toggleSidebar"
      ></div>

      <!-- Konten Utama -->
      <div
        class="flex-1 overflow-auto transition-all duration-500 bg-white"
        :style="{ marginLeft: isMobile ? '0rem' : sidebarWidth }"
      >
      <!-- <div
        class="flex-1 overflow-auto transition-all duration-500"
        :style="{ marginLeft: isMobile ? '0rem' : sidebarWidth }"
      > -->
        <slot />
        <Notification />
      </div>
    </div>
  </div>
</template>
