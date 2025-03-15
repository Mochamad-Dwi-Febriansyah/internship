<script setup lang="ts">
defineProps<{
    items: Array<{ label: string, icon?: string, to?: string }>
}>()
import dayjs from 'dayjs'

const {  getUserRole, getTanggalSelesai } = useAuth() 
const tanggalSelesai = dayjs(getTanggalSelesai.value)
const today = dayjs()
const daysRemaining = tanggalSelesai.diff(today, 'day')  
 
</script> 
<template>
        <section class="w-full"> 
        <!-- <section class="mb-3">  -->
            <nav class="flex bg-gray-50 px-3 py-2 w-full" aria-label="Breadcrumb">
            <ol class="inline-flex items-center space-x-1 md:space-x-3 rtl:space-x-reverse">
                <li v-for="(item, index) in items" :key="index">
                    <div class="flex items-center"> 
                        <Icon v-if="item.to" :name="item.icon ?? ''" class="h-5 w-5 text-gray-700" /> 
                        <NuxtLink v-if="item.to" :to="item.to" class="ms-1 whitespace-nowrap text-xs font-medium text-gray-700 hover:text-blue-600 md:ms-2 dark:text-gray-400 dark:hover:text-white">
                            {{ item.label }}
                        </NuxtLink>
                        
                        <div v-else class="flex items-center">
                            <svg class="w-3 h-3 text-gray-400 mx-1 rtl:rotate-180" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 6 10">
                                <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="m1 9 4-4-4-4"/>
                            </svg>
                            <span  class="ms-1 whitespace-nowrap text-xs font-medium text-gray-500 md:ms-2 dark:text-gray-400">
                                {{ item.label  }}
                            </span>
                        </div> 
                    </div> 
                </li>
             </ol>
             <div v-if="getUserRole === 'user'" class="ms-auto flex items-center gap-1">
            <!-- Jika sudah berakhir -->
            <template v-if="daysRemaining < 0">
                <Icon name="material-symbols:warning-rounded" class="w-5 h-5 text-red-500" />
                <p class="text-xs text-red-600 font-semibold">
                Magang sudah berakhir
                </p>
            </template>

            <!-- Jika masih ada sisa hari -->
            <p v-else class="text-xs text-gray-700 font-semibold">
                Magang Berakhir {{ daysRemaining }} hari
            </p>
            </div>


            </nav>
        </section>
</template>

