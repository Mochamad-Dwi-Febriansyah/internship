<script setup lang="ts"> 



</script>

<template>
    <div class="flex flex-col min-h-screen">
        <BaseHeader />
        <div class="flex-1">
            <slot />
            <Notification /> 
        </div>
        <BaseFooter />
    </div>
</template>

