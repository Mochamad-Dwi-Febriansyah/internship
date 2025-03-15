<script setup lang="ts">
defineProps<{
  label?: string;
  to?: string; 
}>();
</script>

<template>
  <NuxtLink
    :to="to"
    class="px-6 py-2  text-white self-start rounded-md font-medium text-sm md:text-base lg:text-lg cursor-pointer bg-blue-600  hover:bg-blue-700 inline-flex items-center" 
  >
    <slot>{{ label }}</slot>
  </NuxtLink>
</template>
