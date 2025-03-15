<script setup>
import { ref } from 'vue'
import {
  Dialog,
  DialogPanel,
  Disclosure,
  DisclosureButton,
  DisclosurePanel,
  Popover,
  PopoverButton,
  PopoverGroup,
  PopoverPanel,
} from '@headlessui/vue'
import {
  Bars3Icon,
  ChartPieIcon,
  CursorArrowRaysIcon,
  FingerPrintIcon,
  XMarkIcon,
} from '@heroicons/vue/24/outline'
import { ChevronDownIcon, PhoneIcon, PlayCircleIcon } from '@heroicons/vue/20/solid'

const products = [
  { name: 'Analytics', description: 'Get a better understanding of your traffic', href: '#', icon: ChartPieIcon },
  { name: 'Engagement', description: 'Speak directly to your customers', href: '#', icon: CursorArrowRaysIcon },
  { name: 'Security', description: 'Your customers’ data will be safe and secure', href: '#', icon: FingerPrintIcon }
]
const callsToAction = [
  { name: 'Watch demo', href: '#', icon: PlayCircleIcon },
  { name: 'Contact sales', href: '#', icon: PhoneIcon },
]

const mobileMenuOpen = ref(false)
</script>

<template>
    <header class="bg-white border-b sticky top-0 z-20">
      <nav class="container mx-auto flex items-center justify-between p-6 py-3 lg:px-8" aria-label="Global">
        <div class="flex lg:flex-1">
          <NuxtLink to="/" class="-m-1.5 p-1.5 flex items-center">
            <span class="sr-only">Your Company</span>
                <NuxtImg 
                sizes="xs:100vw sm:667px md:367px"
                src="/images/logo-web-pdam.png" 
                format="webp"
                densities="x1"
                alt="logo pdam"/> 
                <div class="flex flex-col items-start ml-4 ">
                    <h2 class="-mx-3 block rounded-lg px-3 pt-2 pb-1 text-sm md:text-base font-medium text-gray-700 hover:text-gray-800">
                            Perumda Air Minum <br/>Tirta Moedal
                    </h2>
                    <!-- <span class="text-gray-500 text-sm md:text-md">Kota Semarang</span> -->
                </div> 
          </NuxtLink> 
        </div>
        <div class="flex lg:hidden">
          <button type="button" class="-m-2.5 inline-flex items-center justify-center rounded-md p-2.5 text-gray-700" @click="mobileMenuOpen = true">
            <span class="sr-only">Open main menu</span>
            <Bars3Icon class="size-6" aria-hidden="true" />
          </button>
        </div>
        <PopoverGroup class="hidden lg:flex lg:gap-x-6">
          <Popover class="relative">
            <PopoverButton class="flex items-center gap-x-1 text-sm/6 font-normal text-gray-700">
              Profil
              <ChevronDownIcon class="size-5 flex-none text-gray-400" aria-hidden="true" />
            </PopoverButton>
  
            <transition enter-active-class="transition ease-out duration-200" enter-from-class="opacity-0 translate-y-1" enter-to-class="opacity-100 translate-y-0" leave-active-class="transition ease-in duration-150" leave-from-class="opacity-100 translate-y-0" leave-to-class="opacity-0 translate-y-1">
              <PopoverPanel class="absolute top-full -left-8 z-10 mt-3 w-screen max-w-md overflow-hidden rounded-3xl bg-white ring-1 shadow-lg ring-gray-900/5">
                <div class="p-4">
                  <div v-for="item in products" :key="item.name" class="group relative flex items-center gap-x-6 rounded-lg p-4 text-sm/6 hover:bg-gray-50">
                    <div class="flex size-11 flex-none items-center justify-center rounded-lg bg-gray-50 group-hover:bg-white">
                      <component :is="item.icon" class="size-6 text-gray-600 group-hover:text-indigo-600" aria-hidden="true" />
                    </div>
                    <div class="flex-auto">
                      <a :href="item.href" class="block font-semibold text-gray-900">
                        {{ item.name }}
                        <span class="absolute inset-0" />
                      </a>
                      <p class="mt-1 text-gray-600">{{ item.description }}</p>
                    </div>
                  </div>
                </div>
                <div class="grid grid-cols-2 divide-x divide-gray-900/5 bg-gray-50">
                  <a v-for="item in callsToAction" :key="item.name" :href="item.href" class="flex items-center justify-center gap-x-2.5 p-3 text-sm/6 font-semibold text-gray-900 hover:bg-gray-100">
                    <component :is="item.icon" class="size-5 flex-none text-gray-400" aria-hidden="true" />
                    {{ item.name }}
                  </a>
                </div>
              </PopoverPanel>
            </transition>
          </Popover>
  
          <a href="#" class="text-sm/6 font-normal text-gray-700">Info Cabang</a>
          <a href="#" class="text-sm/6 font-normal text-gray-700">Informasi Pelanggan</a>
          <a href="#" class="text-sm/6 font-normal text-gray-700">Infrastuktur</a>
          <a href="#" class="text-sm/6 font-normal text-gray-700">PPID</a>
          <a href="#" class="text-sm/6 font-normal text-gray-700">Layanan Online</a>
        </PopoverGroup>
        <div class="hidden lg:flex lg:flex-1 lg:justify-end">
          <NuxtLink to="/signin" class="text-sm/6 font-normal text-gray-700">Log in <span aria-hidden="true">&rarr;</span></NuxtLink> 
        </div>
      </nav>
      <Dialog class="lg:hidden" @close="mobileMenuOpen = false" :open="mobileMenuOpen">
        <div class="fixed inset-0 z-10" />
        <DialogPanel class="fixed inset-y-0 right-0 z-10 w-full overflow-y-auto bg-white px-6 py-6 sm:max-w-sm sm:ring-1 sm:ring-gray-900/10">
          <div class="flex items-center justify-between">
            <a href="#" class="-m-1.5 p-1.5">
              <span class="sr-only">Your Company</span>
              <NuxtImg 
                    sizes="xs:100vw sm:667px"
                    src="/images/logo-web-pdam.png" 
                    format="webp"
                    densities="x1"
                    alt="logo pdam"/> 
            </a>
            <button type="button" class="-m-2.5 rounded-md p-2.5 text-gray-700" @click="mobileMenuOpen = false">
              <span class="sr-only">Close menu</span>
              <XMarkIcon class="size-6" aria-hidden="true" />
            </button>
          </div>
          <div class="mt-6 flow-root">
            <div class="-my-6 divide-y divide-gray-500/10">
              <div class="space-y-2 py-6">
                <Disclosure as="div" class="-mx-3" v-slot="{ open }">
                  <DisclosureButton class="flex w-full items-center justify-between rounded-lg py-2 pr-3.5 pl-3 text-base/7 font-semibold text-gray-900 hover:bg-gray-50">
                    Product
                    <ChevronDownIcon :class="[open ? 'rotate-180' : '', 'size-5 flex-none']" aria-hidden="true" />
                  </DisclosureButton>
                  <DisclosurePanel class="mt-2 space-y-2">
                    <DisclosureButton v-for="item in [...products, ...callsToAction]" :key="item.name" as="a" :href="item.href" class="block rounded-lg py-2 pr-3 pl-6 text-sm/7 font-semibold text-gray-900 hover:bg-gray-50">{{ item.name }}</DisclosureButton>
                  </DisclosurePanel>
                </Disclosure>
                <a href="#" class="-mx-3 block rounded-lg px-3 py-2 text-base/7 font-semibold text-gray-900 hover:bg-gray-50">Info Cabang</a>
                <a href="#" class="-mx-3 block rounded-lg px-3 py-2 text-base/7 font-semibold text-gray-900 hover:bg-gray-50">Informasi Pelanggan</a>
                <a href="#" class="-mx-3 block rounded-lg px-3 py-2 text-base/7 font-semibold text-gray-900 hover:bg-gray-50">Infrastuktur</a>
                <a href="#" class="-mx-3 block rounded-lg px-3 py-2 text-base/7 font-semibold text-gray-900 hover:bg-gray-50">PPID</a>
                <a href="#" class="-mx-3 block rounded-lg px-3 py-2 text-base/7 font-semibold text-gray-900 hover:bg-gray-50">Layanan Online</a>
              </div>
              <div class="py-6">
                <NuxtLink to="/signin" class="-mx-3 block rounded-lg px-3 py-2.5 text-base/7 font-semibold text-gray-900 hover:bg-gray-50">
                  Log in
                </NuxtLink>
              </div>
            </div>
          </div>
        </DialogPanel>
      </Dialog>
    </header>
  </template>
  
