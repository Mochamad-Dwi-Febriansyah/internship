import { defineNuxtRouteMiddleware, navigateTo } from '#app'

export default defineNuxtRouteMiddleware(async (to) => {
  const accessToken = useCookie('access_token'); // Cek token di cookie

  if (!accessToken.value) {
    // console.log("dsds")
    return navigateTo('/signin', { replace: true }); // Redirect ke login jika tidak ada token
  }

  // const authStore = useAuthStore()

  // Cek apakah user sudah login
  // if (!authStore.isAuthenticated) {
  //   return navigateTo('/signin', { replace: true }) // Redirect ke login jika tidak ada token
  // }

  // console.log(authStore.isBlocked)
  // if (authStore.isBlocked) { 
  //   // console.log(authStore.isBlocked)
  //   if (to.path !== '/dashboard/user') {
  //     return navigateTo('/dashboard/user') // Redirect ke dashboard user jika mengakses halaman selain dashboard/user
  //   }
  // }

  // try {
  //   await authStore.fetchUser() // Pastikan ini melakukan request ke backend untuk validasi token
  // } catch (error: any) {
  //   if (error.response?.data?.message === "Token telah kedaluwarsa") {
  //     authStore.logout() // Hapus token dari state dan cookie
  //     return navigateTo('/signin') // Redirect ke halaman login
  //   }
  // }

  // Ambil role dari state atau cookie
  
  const { getUserRole } = useAuth()
  const userRole = getUserRole.value

  const roleAccess: Record<string, string[]> = {
    admin: ['/dashboard/admin', '/dashboard/profile', '/dashboard/pengaturan'],
    kepegawaian: ['/dashboard/kepegawaian', '/dashboard/profile', '/dashboard/pengaturan'],
    mentor: ['/dashboard/mentor', '/dashboard/profile', '/dashboard/pengaturan'],
    user: ['/dashboard/user', '/dashboard/profile', '/dashboard/pengaturan'],
    guest: []
  }

  // Periksa apakah role memiliki akses ke halaman tujuan
  const allowedPaths = roleAccess[userRole] || []
  // console.log(allowedPaths)
  if (!allowedPaths.some(path => to.path.startsWith(path))) {
    return navigateTo('/unauthorized', { replace: true }) // Redirect jika akses ditolak
  }
})
