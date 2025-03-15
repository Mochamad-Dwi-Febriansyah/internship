<template>
  <div class="editor-container">
    <!-- Toolbar -->
    <div class="toolbar">
      <button
        v-for="button in buttons"
        :key="button.label"
        @click="button.action"
        :disabled="button.disabled"
        :class="{ 'is-active': button.isActive }"
      >
        {{ button.label }}
      </button>
    </div>

    <!-- Editor Content -->
    <div class="editor-wrapper">
      <EditorContent :editor="editor" class="editor-content" />
    </div>
  </div>
</template>

<script setup>
import { ref, computed, watch, onBeforeUnmount, defineProps, defineEmits } from "vue";
import { useEditor, EditorContent } from "@tiptap/vue-3";
import StarterKit from "@tiptap/starter-kit";
import BulletList from "@tiptap/extension-bullet-list";
import OrderedList from "@tiptap/extension-ordered-list";
import ListItem from "@tiptap/extension-list-item";
import Heading from "@tiptap/extension-heading";

// Props dan Emit untuk v-model
const props = defineProps({
  modelValue: String, // Data dari parent
});
const emit = defineEmits(["update:modelValue"]); // Emit ke parent

// Inisialisasi editor dengan konten awal dari modelValue
const editor = useEditor({
  content: props.modelValue || "",
  extensions: [
    StarterKit.configure({
      bulletList: false, 
      orderedList: false,
      listItem: false,
    }),
    BulletList, 
    OrderedList, 
    ListItem, 
    Heading.configure({ levels: [1, 2, 3, 4, 5, 6] }),
  ],
  editorProps: {
    attributes: {
      class: "tiptap ProseMirror",
    },
  },
});

// Watch perubahan editor untuk mengupdate v-model di parent
watch(
  () => editor.value?.getHTML(),
  (newValue) => {
    emit("update:modelValue", newValue);
  }
);

// Toolbar dengan status aktif
const buttons = computed(() => {
  if (!editor.value) return [];

  return [
    { label: "𝗕", action: () => editor.value.chain().focus().toggleBold().run(), isActive: editor.value.isActive("bold") },
    { label: "𝘐", action: () => editor.value.chain().focus().toggleItalic().run(), isActive: editor.value.isActive("italic") },
    { label: "S̶", action: () => editor.value.chain().focus().toggleStrike().run(), isActive: editor.value.isActive("strike") },
    { label: "↩️", action: () => editor.value.chain().focus().undo().run(), disabled: !editor.value.can().undo() },
    { label: "↪️", action: () => editor.value.chain().focus().redo().run(), disabled: !editor.value.can().redo() },
  ];
});

// Hapus editor saat komponen dihancurkan
onBeforeUnmount(() => {
  editor.value?.destroy();
});
</script>