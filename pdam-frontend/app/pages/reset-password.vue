<script setup lang="ts">
import { useRoute, useRouter } from 'vue-router'
import * as yup from 'yup'
import { useNotification } from '~/composables/useNotification'

const route = useRoute()
const router = useRouter()
const { addNotification } = useNotification()  
const {  resetPassword, pendingResetPassword, errors } = useAuth()
const showPassword = ref(false)
const showPasswordConfirmed = ref(false)

const togglePassword = () => {
  showPassword.value = !showPassword.value
}
const togglePasswordConfirmed = () => {
  showPasswordConfirmed.value = !showPasswordConfirmed.value
}
// Skema validasi
const schema = yup.object({
  password: yup.string().required('Password wajib diisi').min(6, 'Password minimal 6 karakter'),
  confirm_password: yup.string()
    .oneOf([yup.ref('password')], 'Konfirmasi password tidak sesuai')
    .required('Konfirmasi password wajib diisi')
})

// Fungsi reset password
const handleResetPassword = async (values: any) => { 
  try { 
    await resetPassword(values.password, values.confirm_password, route.query.token as string)  
    addNotification('success', 'Password berhasil direset. Silakan login.')
    router.push('/signin')
  } catch (err) {
    addNotification('error', (err as Error).message)
  } 
}
</script>

<template>
    <NuxtLayout> 
      <section class="flex items-center justify-center min-h-screen bg-gray-100 py-20 md:py-10 p-8">
        <div class="bg-white p-8 rounded-lg border w-full max-w-md shadow-md">
          <h2 class="text-2xl font-semibold text-center text-gray-700 mb-6">
            Reset Password
          </h2>
  
          <Form @submit="handleResetPassword" :validation-schema="schema" v-slot="{ meta }">
            <div class="mb-4">
              <BaseInput
                label="Password Baru"
                name="password"
                type="password"
                :errors="errors"
                placeholder="Masukkan password baru"
                required
                :showToggle="true" />
          
            </div>
  
            <!-- Konfirmasi Password -->
            <div class="mb-6">
              <BaseInput
                label="Konfirmasi Password"
                name="confirm_password"
                type="password"
                :errors="errors"
                placeholder="Konfirmasi password baru"
                required
                :showToggle="true" />  
            </div>
  
            <!-- Tombol Submit -->
            <button  
              class="w-full py-3 bg-blue-600 text-white rounded-md font-semibold hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 disabled:bg-gray-400">
              {{ pendingResetPassword ? "Memproses..." : "Reset Password" }}
            </button>
          </Form>
        </div>
      </section>
    </NuxtLayout>
  </template>
  

  