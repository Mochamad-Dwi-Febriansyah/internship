<script setup lang="ts">
const props = defineProps({
    label: String,
    name: {
        type: String,
        default: ''
    },
    type: {
        type: String,
        default: 'text'
    },
    required: {
        type: Boolean,
        default: false
    },
    modelValue: [String, Number], // Pastikan mendukung berbagai tipe data
    errors: Object,
    placeholder: String,
    showToggle: {
        type: Boolean,
        default: false
    },
    disabled: {
        type: Boolean,
        default: false
    }
});

const emit = defineEmits(['update:modelValue']);

const showPassword = ref(false);
const togglePassword = () => {
    showPassword.value = !showPassword.value;
};
</script>

<template>
    <div>
        <label :for="name" class="block text-sm font-medium text-gray-700">
            {{ label }}
            <span v-if="required" class="text-red-500">*</span>
        </label>

        <div v-if="showToggle" class="relative">
            <Field 
                :type="showPassword ? 'text' : 'password'" 
                :name="name" 
                :id="name" 
                :placeholder="placeholder"
                class="w-full p-2 mt-1 border border-gray-300 rounded-md pr-10"
                :model-value="modelValue"
                @input="emit('update:modelValue', ($event.target as HTMLInputElement).value)"
                :disabled="disabled"
            />
            <button type="button" @click="togglePassword"
                class="absolute inset-y-0 right-4 top-1 flex items-center text-gray-500">
                <Icon :name="showPassword ? 'mdi:eye-off' : 'mdi:eye'" class="w-6 h-6" />
            </button>
        </div>

        <Field 
            v-else 
            :type="type" 
            :name="name" 
            :id="name" 
            :placeholder="placeholder"
            class="w-full p-2 mt-1 border border-gray-300 rounded-md"
            :model-value="modelValue"
            @input="emit('update:modelValue', ($event.target as HTMLInputElement).value)"
            :disabled="disabled"
        />

        <ErrorMessage :name="name" class="text-red-500 text-sm" />
        <p v-if="errors && errors[name]" class="text-red-500 text-sm">{{ errors[name][0] }}</p>
    </div>
</template>
