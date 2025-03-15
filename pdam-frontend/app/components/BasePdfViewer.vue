<script setup lang="ts">
import { ref, onMounted } from 'vue'  

 
const props = defineProps<{
    items: { urlpdf: string }
}>()
 
const pdfUrl = ref<string>('')
 
onMounted(() => {
    pdfUrl.value = `http://localhost:8000/storage/${props.items.urlpdf}`
})
</script>

<template>
  <div> 
    <ClientOnly> 
        <embed :src="pdfUrl"/>
        <!-- <embed :src="pdfUrl" width="w-full" height="auto" /> -->
    </ClientOnly>
  </div>
</template>

<style scoped>
div{
    height: 100%;
}
embed {
  width: 100%;  /* Menyesuaikan lebar */
  height: 100%; /* Menyesuaikan tinggi */
  object-fit: contain; /* Menjaga proporsi dan menghindari pemotongan */
}

.bg-opacity-50 {
  background-color: rgba(0, 0, 0, 0.5);
}

.modal-body {
  display: flex;
  justify-content: center;
  align-items: center;
}

button {
  cursor: pointer;
}
</style>
