<script setup lang="ts">
import * as yup from "yup"
import type { SubmissionHandler } from 'vee-validate'
const { addNotification } = useNotification() 

const route = useRoute() 
const { cekBerkas, pending, berkas, errors } = useAjukanBerkas()

const cekBerkasSchema = yup.object({
  nomor_registrasi:  yup.string().required('Nomor Registrasi wajib diisi')
}) 

const handleCekBerkas: SubmissionHandler<any> = async (values) => { 
  try {
    const response: any = await cekBerkas(values.nomor_registrasi)
    
    if (berkas.value) {
      addNotification('success', response?.message || 'Data berkas berhasil ditemukan!')
      
      if (berkas.value.status_berkas === 'terima') {
        addNotification('info', 'Silahkan cek email untuk mendapatkan kredensial login!', 10000)
      }
    }
    
  } catch (error: any) {   
      addNotification('error', error.data.message) 
    }
}

const statusMapping: { [key: string]: string } = {
  pending: "Pending",
  approved: "Approved",
  reject: "Rejected"
};

</script>

<template>
  <NuxtLayout>
    <section class="py-12">
      <div class="container lg:px-16 mb-3 md:py-12 py-12">
        <h2 class="text-3xl font-bold text-gray-800 mb-6 text-center md:text-left">
          Cek Pengajuan Berkas
        </h2>

        <!-- Form Pengecekan -->
        <Form @submit="handleCekBerkas" :validation-schema="cekBerkasSchema" :initial-values="{ nomor_registrasi: '' }">
              
           
        <div class="w-full mx-auto bg-white ">
          <div class="grid md:grid-cols-1 gap-4">
            <!-- No Pengajuan --> 

            <BaseInput label="Nomor Pengajuan" name="nomor_registrasi" type="text" placeholder="Masukkan no pengajuan" :errors="errors" required/>
          </div>

          <!-- Tombol Cek -->
          <div class="mt-6 flex justify-center">
            <button type="submit" 
              :disabled="pending"
              class="px-6 py-2 bg-blue-600 text-white font-medium rounded-md hover:bg-blue-700 transition w-full"
            >
              {{ pending ? 'Mengecek...' : 'Cek' }}
            </button>

          </div>
          
        </div>
 </Form>
        <!-- Hasil Pengecekan -->
        <div  v-if="berkas" class="w-full mx-auto bg-green-100 border border-green-300 rounded-lg p-6 mt-6" 
        :class="{
        'bg-yellow-100 border-yellow-300': berkas.status_berkas === 'pending',
        'bg-green-100 border-green-300': berkas.status_berkas === 'terima',
        'bg-red-100 border-red-300': berkas.status_berkas === 'tolak'}">
          <p class="text-gray-700"><strong>No Registrasi:</strong> {{ berkas.nomor_registrasi }}</p>
          <p class="text-gray-700"><strong>Status:</strong> {{ statusMapping[berkas.status_berkas as keyof typeof statusMapping]   }}</p>

        </div>
      </div>
    </section>
  </NuxtLayout>
</template>

<style scoped></style>
