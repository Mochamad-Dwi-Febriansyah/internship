import { defineNuxtPlugin } from '#app'
import { configure, defineRule } from 'vee-validate'
import * as yup from 'yup'

// Konfigurasi Vee-Validate
export default defineNuxtPlugin((nuxtApp) => {
  configure({
    generateMessage: ({ field }) => `Field ${field} tidak valid.`,
    validateOnInput: true, // Validasi langsung saat mengetik
  });

  // Auto-import Yup jika perlu
  nuxtApp.vueApp.provide('yup', yup);
})
