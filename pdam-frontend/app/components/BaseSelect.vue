<script setup lang="ts">
defineProps({
  label: String,
  name: {
        type: String,
        default: ''
    },
  modelValue: String,
  required: {
    type: Boolean,
    default: false
  },
  disabled: {
        type: Boolean,
        default: false
    },
  options: {
    type: Array as () => { value: string; text: string }[],
    default: () => []
  }
});
const emit = defineEmits(['update:modelValue']);
</script>

<template>
  <div>
    <label :for="name" class="block text-sm font-medium text-gray-700">
      {{ label }} <span v-if="required" class="text-red-500">*</span>
    </label>
    <Field as="select" :name="name" :id="name"
      class="mt-1 p-2 w-full border rounded-md bg-white"
      :value="modelValue"
      :disabled="disabled"
      @change="$emit('update:modelValue', $event.target.value)">
      <option value="" disabled selected>Pilih {{ label }}</option>
      <option v-for="option in options" :key="option.value" :value="option.value">
        {{ option.text }}
      </option>
    </Field>
    <ErrorMessage :name="name" class="text-red-500 text-sm" />
  </div>
</template>
