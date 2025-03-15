<script setup>
import { ref, shallowRef, defineAsyncComponent, onMounted, defineProps, defineEmits, watch } from 'vue'

// Lazy load CKEditor
const CKEditor = defineAsyncComponent(() =>
    import('@ckeditor/ckeditor5-vue').then(m => m.component)
)

// ClassicEditor di-load saat mounted agar tidak menimbulkan error SSR
const ClassicEditor = shallowRef(null)
const editorInstance = ref(null) // Simpan instance editor

onMounted(async () => {
  ClassicEditor.value = (await import('@ckeditor/ckeditor5-build-classic')).default
})

// Props & Emit untuk v-model
const props = defineProps({
  modelValue: String,
  statusUpdateCondition: Boolean // Tambahkan prop untuk status readonly
})
const emit = defineEmits(['update:modelValue'])

// Fungsi yang dipanggil setelah CKEditor siap
const onReady = (editor) => {
  editorInstance.value = editor

  // Set nilai awal jika ada
  if (props.modelValue) {
    editor.setData(props.modelValue)
    emit('update:modelValue', props.modelValue) // Emit nilai awal agar form tahu ada data
  }

  toggleReadonly(props.statusUpdateCondition) // Atur readonly sesuai status awal
}


// Fungsi untuk mengaktifkan/menonaktifkan readonly
const toggleReadonly = (isReadonly) => {
  if (editorInstance.value) {
    if (isReadonly) {
      editorInstance.value.enableReadOnlyMode('readonly-mode')
    } else {
      editorInstance.value.disableReadOnlyMode('readonly-mode')
    }
  }
}

// Watch perubahan statusUpdateCondition dan update readonly mode
watch(() => props.statusUpdateCondition, (newValue) => {
  toggleReadonly(newValue)
})
watch(() => props.modelValue, (newValue) => {
  if (editorInstance.value) {
    const currentData = editorInstance.value.getData()
    if (currentData !== newValue) {
      editorInstance.value.setData(newValue)
    }
  }
})

</script>

<template>
  <client-only>
    <CKEditor
        v-if="ClassicEditor"
        :editor="ClassicEditor"
        :model-value="modelValue"
        @update:model-value="emit('update:modelValue', $event)" 
        :config="{ readOnly: statusUpdateCondition }" 
        @ready="onReady" 
        class="w-full"
    />
  </client-only>
</template>
