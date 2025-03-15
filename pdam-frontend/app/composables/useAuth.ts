interface Mentor {
    id: string
    nama_depan: string
    nama_belakang: string
    email: string
}

interface User {
    id: string
    nama_depan: string
    nama_belakang: string
    email: string
    role: string
    foto: string
    is_blocked: string
    nisn_npm_nim_npp: string
    durasi_magang: any
    mentor: Mentor
}

interface AuthResponse {
    status: string
    message: string
    user:  User
    token: string
    error?: string
}
export function useAuth() {
    const config = useRuntimeConfig()
    const token = computed(() => useCookie('access_token').value)
    const pendingLogin = ref(false)
    const pendingLogout = ref(false)
    const pendingFetchUser = ref(false)
    const pendingRefreshAccessToken = ref(false)
    const pendingForgotPassword = ref(false)
    const pendingResetPassword = ref(false) 
    const errors = ref<Record<string, string[]>>({})

    const setAuthCookies = (user: User, token: string) => {
        useCookie('access_token', { maxAge: 5000 }).value = token
        useCookie('role', { maxAge: 5000 }).value = user.role
        useCookie('is_blocked', { maxAge: 5000 }).value = user.is_blocked
        useCookie('nama_depan', { maxAge: 5000 }).value = user.nama_depan
        useCookie('nama_belakang', { maxAge: 5000 }).value = user.nama_belakang
        useCookie('email', { maxAge: 5000 }).value = user.email
        useCookie('nisn_npm_nim_npp', { maxAge: 5000 }).value = user.nisn_npm_nim_npp
        useCookie('foto', { maxAge: 5000 }).value = user.foto
        useCookie('nama_mentor', { maxAge: 5000 }).value = user.mentor
            ? `${user.mentor.nama_depan} ${user.mentor.nama_belakang}`
            : null
        useCookie('email_mentor', { maxAge: 5000 }).value = user.mentor?.email || null
        if (user.role === 'user') {
          useCookie('magang_mulai', { maxAge: 5000 }).value = user.durasi_magang.tanggal_mulai
          useCookie('magang_selesai', { maxAge: 5000 }).value = user.durasi_magang.tanggal_selesai
      }
    }
    const getUserRole  = computed(() => useCookie('role').value || 'guest')
    const getUserFullName = computed(() => {
      const namaDepan = useCookie('nama_depan').value || ''
      const namaBelakang = useCookie('nama_belakang').value || ''
      return `${namaDepan} ${namaBelakang}`.trim()
    })
    const getUserEmail = computed(() => useCookie('email').value || '')
    const getUserNisnNpmNimNpp = computed(() => useCookie('nisn_npm_nim_npp').value || 'Tidak tersedia')
    const getFoto = computed(() => useCookie('foto').value || 'Tidak tersedia')
    const getUserMentorName = computed(() => useCookie('nama_mentor').value || 'Belum memiliki mentor')
    const getUserMentorEmail = computed(() => useCookie('email_mentor').value || '')
    const getTanggalMulai = computed(() => useCookie('magang_mulai').value || '')
    const getTanggalSelesai = computed(() => useCookie('magang_selesai').value || '')

    const login = async (email: string, password: string) => {
        pendingLogin.value = true
        errors.value = {};
        try {
            const response = await useFetch<AuthResponse>(`${config.public.apiBase}/v1/auth/login`, {
                method: 'POST',
                body: { email, password },
            }) 
            if (response.error.value) {
              throw new Error(response.error.value.data?.message || 'Terjadi kesalahan saat login.')
          }
            if (response.data?.value?.status === 'success') {
                setAuthCookies(response.data.value.user, response.data.value.token)
            }
            // console.log(response)
            return response
        } catch (error: any) {
            if (error?.data?.status === "error" && error?.data?.errors) {
                errors.value = error.data.errors;
            }
            throw error
        } finally {
            pendingLogin.value = false
        }
    }
    const logout = async () => {
        pendingLogout.value = true
        try {
            const response = await useFetch(`${config.public.apiBase}/v1/auth/logout`, {
                method: 'POST',
                credentials: 'include',// Kirim cookie refresh token agar backend bisa menghapusnya
                headers: { Authorization: `Bearer ${token}` }
            })
            return response
        } catch (error: any) {
            if (error?.data?.status === "error" && error?.data?.errors) {
                errors.value = error.data.errors;
            }
            throw error
        } finally {
            ['access_token', 'role', 'is_blocked', 'nama_depan', 'nama_belakang', 'email', 'nisn_npm_nim_npp', 'nama_mentor', 'email_mentor', 'magang_mulai', 'magang_selesai']
                .forEach(cookie => useCookie(cookie).value = null)

            pendingLogout.value = false
        }
    } 
    const fetchUser = async () => {
        pendingFetchUser.value = true
        try {
            const response = await useFetch<AuthResponse>(`${config.public.apiBase}/v1/auth/me`, {
                method: 'GET',
                headers: { Authorization: `Bearer ${token.value}` }
            });
            return response;
        } catch (error) {
            throw error;
        } finally {
            pendingFetchUser.value = false
        }
    }
    const refreshAccessToken = async () => {
        pendingRefreshAccessToken.value = true
      
        try {
          const response = await useFetch<AuthResponse>(`${config.public.apiBase}/v1/auth/refresh-token`, {
            method: 'POST',
            credentials: 'include', // Untuk mengirim cookie refresh token ke server
          })
      
          const responseData = response.data.value
      
          if (responseData?.token) {  
            useCookie('access_token', { maxAge: 900 }).value = responseData.token
          } else {
            throw new Error('Access Token tidak ditemukan dalam respons.')
          }
      
          return response
        } catch (error: any) {
          if (error?.data?.status === "error" && error?.data?.errors) {
            errors.value = error.data.errors
          }
          throw error
        } finally { 
          pendingRefreshAccessToken.value = false
        }
    }
    const forgotPassword = async (email: string) => {
      pendingForgotPassword.value = true
      errors.value = {};
      try {
        const response = await useFetch<AuthResponse>(`${config.public.apiBase}/v1/auth/forgot-password`, {
          method: 'POST',
          body:{ email }
          // credentials: 'include', // Untuk mengirim cookie refresh token ke server
        }) 
        // if(response)
       
        if (response.error.value) {
          const errorResponse = response.error.value as { data?: { status?: string, errors?: Record<string, string[]> } };
          if (errorResponse.data?.status === "error" && errorResponse.data?.errors) {
            errors.value = errorResponse.data?.errors
          } 
          // console.log("d", errors)
      }
        if (response.error.value) { 
          throw new Error(response.error.value.data?.message || 'Terjadi kesalahan saat lupa password.')
      }
        
        return response
      } catch (error: any) { 
        throw error
      } finally { 
        pendingForgotPassword.value = false
      }
    }
    const resetPassword = async (password: string, confirmPassword: string, token: string) => {
      pendingResetPassword.value = true
    
      try {
        const response = await useFetch(`${config.public.apiBase}/v1/auth/reset-password`, {
          method: 'POST',
          body: JSON.stringify({
            token,
            password,
            password_confirmation: confirmPassword
          }),
          headers: { 'Content-Type': 'application/json' }
        }) 
        return response
      } catch (error: any) {
        if (error?.data?.status === "error" && error?.data?.errors) {
          errors.value = error.data.errors
        }
        throw error
      } finally { 
        pendingResetPassword.value = false
      }
    }

      
    return { 
      login, pendingLogin, 
      errors, 
      logout, pendingLogout, 
      fetchUser, pendingFetchUser,
      forgotPassword,pendingForgotPassword,  
      refreshAccessToken, pendingRefreshAccessToken, 
      resetPassword, pendingResetPassword ,  
      getUserRole, 
      getUserFullName,
      getUserEmail,
      getUserNisnNpmNimNpp,
      getUserMentorName,
      getUserMentorEmail,
      getFoto,
      getTanggalMulai,
      getTanggalSelesai
    }
}