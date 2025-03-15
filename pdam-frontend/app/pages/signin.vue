<script setup lang="ts"> 
import { useRouter } from 'vue-router'
import * as yup from 'yup'
import type { SubmissionHandler } from 'vee-validate'
import { useNotification } from '~/composables/useNotification'
import BaseInput from '~/components/BaseInput.vue'
 
const  { login, forgotPassword, pendingForgotPassword,getUserRole, errors, pendingLogin,  } = useAuth()
const router = useRouter()
const { addNotification } = useNotification()

const showPassword = ref(false)

const togglePassword = () => {
  showPassword.value = !showPassword.value
}

const isForgotPassword = ref(false) // Toggle antara login dan forgot password

// Schema validasi login
const loginSchema = yup.object({
  email: yup.string().required('Email wajib diisi').email('Format email tidak valid'),
  password: yup.string().required('Password wajib diisi').min(6, 'Password minimal 6 karakter'),
})

// Schema validasi forgot password
const forgotPasswordSchema = yup.object({
  email: yup.string().required('Email wajib diisi').email('Format email tidak valid'),
})

// Fungsi untuk menangani login
const handleLogin: SubmissionHandler<any> = async (values) => {
  try {
    await login(values.email, values.password)
    addNotification('success', 'Login berhasil!')
    const role = getUserRole.value
    const roleRedirects: Record<string, string> = {
      admin: '/dashboard/admin',
      kepegawaian: '/dashboard/kepegawaian',
      mentor: '/dashboard/mentor',
      user: '/dashboard/user',
      guest: '/unauthorized'
    } 
    router.push(roleRedirects[role] || '/unauthorized')  
  } catch (err) {  
    addNotification('error', (err as Error).message)
  }
}

// Fungsi untuk menangani lupa password
const handleForgotPassword: SubmissionHandler<any> = async (values) => {
  try {
    console.log('Values:', values.email)
    await forgotPassword(values.email)
    addNotification('success', 'Email reset password telah dikirim!')
    isForgotPassword.value = false // Kembali ke login setelah sukses
  } catch (err) {
    addNotification('error', (err as Error).message) 
  }
}
</script>

<template>
  <NuxtLayout> 
    <section class="bg-gray-100">
      <section class="bg-[url('/images/Frame-p.png')] bg-bottom bg-cover">
        <div class="container">
          <div class="flex items-center justify-center py-20 md:py-10">
            <div class="bg-white p-8 rounded-lg border w-full max-w-md">
              <h2 class="text-2xl font-semibold text-center text-gray-700 mb-6">
                {{ isForgotPassword ? 'Lupa Password' : 'Login' }}
              </h2>
              
              <Form 
                v-if="!isForgotPassword"
                @submit="handleLogin" 
                :validation-schema="loginSchema" 
                :initial-values="{ email: '', password: '' }" 
                v-slot="{ meta }">
                <div class="mb-4">
                  <BaseInput label="Email" name="email" type="email" placeholder="Masukkan email anda" :errors="errors" required/>
                </div> 
                <div class="mb-6"> 
                  <BaseInput label="Password" name="password" type="password"  placeholder="Masukan password anda" :errors="errors" required :showToggle="true"/>
                </div>  
                <button type="submit" :disabled="!meta.valid"
                  class="w-full py-3 bg-blue-600 text-white rounded-md">
                  {{ pendingLogin ? "Logging in..." : "Login" }}
                </button>
                
                <p class="mt-4 text-center text-sm text-gray-600">
                  <a @click="isForgotPassword = true" class="text-blue-600 hover:underline cursor-pointer">Lupa password?</a>
                </p>
              </Form>
              
              <Form 
                v-else 
                @submit="handleForgotPassword" 
                :validation-schema="forgotPasswordSchema" >
                <div class="mb-4">
                  <BaseInput label="Email" name="email" type="email" placeholder="Masukkan email anda" :errors="errors" required/>
                  <!-- <p v-if="errors.nama_depan" class="text-red-500 text-sm mb-4">{{ errors.nama_depan[0] }}</p> -->
                </div>
                
                <button type="submit"  
                  class="w-full py-3 bg-blue-600 text-white rounded-md">
                  <Icon v-if="pendingForgotPassword" name="codex:loader" class="text-xl align-middle"/> 
                  <span v-else>Kirim Link Reset Password</span>
                </button>
                
                <p class="mt-4 text-center text-sm text-gray-600">
                  <a @click="isForgotPassword = false" class="text-blue-600 hover:underline cursor-pointer">Kembali ke login</a>
                </p>
              </Form>
            </div>
          </div>
        </div>
      </section>
    </section>
  </NuxtLayout>
</template>