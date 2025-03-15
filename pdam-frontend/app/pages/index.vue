<script setup lang="ts">
import { NuxtLink } from '#components';
import { ref, onMounted } from 'vue';

const slides = ref([
  { image: '/images/slide-1.jpg', title: 'Slide 1', description: 'This is the description for Slide 1 the description for Slide 1' },
  { image: '/images/slide-2.jpg', title: 'Slide 2', description: 'This is the description for Slide 2' },
  { image: '/images/slide-3.png', title: 'Slide 3', description: 'This is the description for Slide 3' },
]);

const containerRef = ref(null);
const swiper = useSwiper(containerRef);

onMounted(() => {
  console.log(swiper.instance);
});
</script>

<template>
  <NuxtLayout>

    <section>
      <div class="md:pb-8 pb-12">
        <client-only>
          <div class="relative w-full">
            
            <swiper-container
              ref="containerRef"
              :slides-per-view="1"
              :loop="true"
              :autoplay-delay="5000"
              class="lg:h-[80vh] md:h-[50vh]"
            >
              <swiper-slide
                v-for="(slide, idx) in slides"
                :key="idx"
                class="flex justify-center items-center flex-col"
              >
                <img :src="slide.image" :alt="slide.title" class="w-full h-auto" />
                <div class="absolute bottom-0 left-0 text-white text-left p-4 bg-black bg-opacity-50 ">
                  <p class="text-sm lg:text-lg">{{ slide.description }}</p>
                </div>
    
              </swiper-slide>
            </swiper-container>
    
            <!-- Tombol Navigasi Tengah -->
            <button
              @click="swiper.prev()"
              class="absolute top-1/2 z-10 left-4 -translate-y-1/2 bg-black bg-opacity-0 text-white p-3 m-0 rounded-full hover:bg-opacity-20 transition flex flex-col items-center"
            >
            <Icon name="ooui:previous-ltr" class="w-6 h-6" />
            </button>
            <button
              @click="swiper.next()"
              class="absolute top-1/2 z-10 right-4 -translate-y-1/2 bg-black bg-opacity-0 text-white p-3 rounded-full  hover:bg-opacity-20 transition flex flex-col items-center"
            >
            <Icon name="ooui:previous-rtl" class="w-6 h-6" />
            </button>
          </div>
        </client-only>
      </div>
    </section>

    <section>
      <div class="container lg:px-16 mb-3 md:py-12 py-12">
        <div class="flex flex-col md:flex-row justify-between gap-4 "> 
          <div class="md:flex-[2]">
            <div class="flex flex-row justify-between mb-3">
              <h1 class="text-3xl text-gray-800">Berita</h1>
              <a href="" class="text-blue-600 hover:text-blue-500 hover:underline">Index Berita</a>
            </div>
  
              <!-- Grid Berita -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6 mt-4">
          <!-- Card Berita 1 -->
          <div class="bg-white border rounded-lg overflow-hidden hover:shadow-sm transition">
            <img src="/images/slide-1.jpg" alt="Berita 1" class="w-full h-40 object-cover">
            <div class="p-4">
              <h2 class="text-lg font-semibold text-gray-700">Judul Berita 1</h2>
              <p class="text-gray-600 text-sm mt-2 line-clamp-3">
                Ini adalah deskripsi singkat berita yang menarik dan memberikan gambaran isi berita...
              </p>
              <div class="mt-4 flex  justify-end">
                 <NuxtLink   class="text-blue-600 hover:text-blue-500 hover:underline flex items-center gap-2 cursor-pointer">
                  Selengkapnya 
                  <Icon name="material-symbols:arrow-forward-rounded" class="w-6 h-6" />
                 </NuxtLink>
               </div>
            </div>
          </div>
  
          <!-- Card Berita 2 -->
          <div class="bg-white border rounded-lg overflow-hidden hover:shadow-sm transition">
            <img src="/images/slide-2.jpg" alt="Berita 2" class="w-full h-40 object-cover">
            <div class="p-4">
              <h2 class="text-lg font-semibold text-gray-700">Judul Berita 2</h2>
              <p class="text-gray-600 text-sm mt-2 line-clamp-3">
                Berita terbaru yang sedang viral dan banyak dibicarakan, baca selengkapnya di sini...
              </p>
              <div class="mt-4 flex justify-end">
                 <NuxtLink   class="text-blue-600 hover:text-blue-500 hover:underline">
                  <Icon name="material-symbols:arrow-forward-rounded" class="w-6 h-6" />
                 </NuxtLink>
               </div>
            </div>
          </div>
          
          <!-- Card Berita 3 -->
          <div class="bg-white border rounded-lg overflow-hidden hover:shadow-sm transition">
            <img src="/images/slide-3.png" alt="Berita 3" class="w-full h-40 object-cover">
            <div class="p-4">
              <h2 class="text-lg font-semibold text-gray-700">Judul Berita 3</h2>
              <p class="text-gray-600 text-sm mt-2 line-clamp-3">
                Simak berita terbaru yang sedang menjadi perbincangan hangat di media sosial...
              </p>
              <div class="mt-4 flex justify-end">
                 <NuxtLink   class="text-blue-600 hover:text-blue-500 hover:underline">
                  <Icon name="material-symbols:arrow-forward-rounded" class="w-6 h-6" />
                 </NuxtLink>
               </div>
            </div>
          </div>
          <!-- Card Berita 3 -->
          <div class="bg-white border rounded-lg overflow-hidden hover:shadow-sm transition">
            <img src="/images/slide-3.png" alt="Berita 3" class="w-full h-40 object-cover">
            <div class="p-4">
              <h2 class="text-lg font-semibold text-gray-700">Judul Berita 3</h2>
              <p class="text-gray-600 text-sm mt-2 line-clamp-3">
                Simak berita terbaru yang sedang menjadi perbincangan hangat di media sosial ihunitbgnyu Lorem ipsum dolor sit amet consectetur adipisicing elit. Vel, quos.
              </p>
              <div class="mt-4 flex justify-end">
                 <NuxtLink   class="text-blue-600 hover:text-blue-500 hover:underline">
                  <Icon name="material-symbols:arrow-forward-rounded" class="w-6 h-6" />
                 </NuxtLink>
               </div>
            </div>
          </div>
          </div>
  
          </div>
          <div class="md:w-1/4">
        <div class=""> 
  
          <!-- List Menu Action -->
          <div class="flex flex-col gap-3">
            <a href="#" class="flex items-center gap-2 bg-blue-500 text-white text- px-8 py-4 rounded-lg  hover:bg-blue-600 transition">
              <Icon name="mdi:credit-card-outline" class="w-5 h-5" />
              Info Tagihan Pelanggan
            </a>
  
            <NuxtLink to="/internship" class="flex items-center gap-2 bg-green-500 text-white text- px-8 py-4 rounded-lg  hover:bg-green-600 transition">
              <Icon name="mdi:school-outline" class="w-5 h-5" />
              Internship Magang 
            </NuxtLink> 
  
            <a href="#" class="flex items-center gap-2 bg-yellow-500 text-white text- px-8 py-4 rounded-lg  hover:bg-yellow-600 transition">
              <Icon name="mdi:headset" class="w-5 h-5" />
              Call Center
            </a>
  
            <a href="#" class="flex items-center gap-2 bg-red-500 text-white text- px-8 py-4 rounded-lg  hover:bg-red-600 transition">
              <Icon name="mdi:storefront-outline" class="w-5 h-5" />
              Loket Pembayaran
            </a>
  
            <a href="#" class="flex items-center gap-2 bg-purple-500 text-white text- px-8 py-4 rounded-lg  hover:bg-purple-600 transition">
              <Icon name="mdi:download-circle-outline" class="w-5 h-5" />
              Dapatkan Aplikasinya
            </a>
          </div>
        </div>
      </div>
        </div>
      </div>
    </section>


  </NuxtLayout>
</template>

<style scoped>
swiper-container {
  width: 100%;
}

/* Menggunakan Flexbox untuk menempatkan tombol navigasi di tengah */
swiper-slide img {
  object-fit: cover;
}
</style>
