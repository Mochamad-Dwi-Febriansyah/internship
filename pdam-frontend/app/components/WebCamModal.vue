<template>
    <div v-if="showModal"  class="w-full h-full rounded"> 
  
        <video ref="videoElement" class="w-full h-full rounded" autoplay></video>
  
        <div class="flex justify-between mt-4">
          <button @click="emit('capture')" class="px-4 py-2 bg-blue-500 text-white rounded">Ambil Foto</button>
          <button @click="closeModal" class="px-4 py-2 bg-red-500 text-white rounded">Tutup</button>
        </div>
      </div> 
  </template>
  
  <script setup>
  import { ref, onMounted, onUnmounted } from 'vue'
  
  const showModal = ref(false)
  const videoElement = ref(null)
  const emit = defineEmits(["capture", "closed"])
  
  const openModal = () => {
    showModal.value = true
    startCamera()
  }
  
  const closeModal = () => {
    stopCamera()
    showModal.value = false
    emit("closed")
  }
  
  // Mulai kamera
  const startCamera = async () => {
    try {
      const stream = await navigator.mediaDevices.getUserMedia({ video: true })
      videoElement.value.srcObject = stream
    } catch (error) {
      console.error("Gagal mengakses kamera:", error)
    }
  }
  
  // Hentikan kamera
  const stopCamera = () => {
    const stream = videoElement.value?.srcObject
    if (stream) {
      stream.getTracks().forEach(track => track.stop())
    }
  }
  
  defineExpose({ openModal })
  </script>
  