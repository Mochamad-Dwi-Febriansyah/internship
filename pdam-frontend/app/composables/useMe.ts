import type { ApiResponseSingle } from "~~/types/types"
import {useCustomFetch} from "~~/plugins/fetch-interceptor";
interface User {
    id: string
    nama_depan: string
    nama_belakang: string
    nisn_npm_nim_npp: string
    tanggal_lahir: string
    jenis_kelamin: string
    nomor_hp: string
    foto: string
    email: string
    alamat: string
    kabupaten_kota: string
    kecamatan: string
    kelurahan_desa: string
    provinsi: string
    kode_pos: string
    role: string
    bagian: string
    status: string
    created_at: string
    updated_at: string
    deleted_at: string | null
}
interface ApiResponseSimple {
  status: string,
  message: string
}
export function useMe(){
    const customFetch = useCustomFetch()
    const config = useRuntimeConfig()
    const token = computed(() => useCookie('access_token').value)
    const errors = ref<Record<string, string[]>>({}) 
    const pendingUpdateProfile = ref(false)  
    const {data: users, status, error, refresh, pending} = useAsyncData<ApiResponseSingle<User>>(
            `users-${token.value}sqsqs`,
            async () => {
                try {
                    const response = await customFetch<ApiResponseSingle<User>>(`${config.public.apiBase}/v1/auth/me`, {
                                     method: 'GET',
                                     headers: {Authorization: `Bearer ${token.value}`,}
                                 })
                                 return response
                } catch (err) {
                    throw err
                }
            },
            {
                server: false,
                lazy: true,
                getCachedData: (key) => useNuxtApp().payload.data[key] as ApiResponseSingle<User> ?? null,
                default: () => ({
                    status: "",
                    message: "",
                    data: {
                        id: "",
                        nama_depan: "",
                        nama_belakang: "",
                        nisn_npm_nim_npp: "",
                        tanggal_lahir: "",
                        jenis_kelamin: "",
                        nomor_hp: "",
                        foto: "",
                        email: "",
                        alamat: "",
                        kabupaten_kota: "",
                        kecamatan: "",
                        kelurahan_desa: "",
                        provinsi: "",
                        kode_pos: "",
                        role: "",
                        bagian: "",
                        status: "",
                        created_at: "",
                        updated_at: "",
                        deleted_at: "", 
                    }
                }),
    
                watch: [token],
            }
        )
        const updateProfile = async (id: string, profileData: FormData | Partial<User>) => {
            pendingUpdateProfile.value = true 
            errors.value = {}
            try {
              const response = await useFetch<ApiResponseSimple>(`${config.public.apiBase}/v1/auth/profile/${id}`, {
                method: 'POST',
                headers: { Authorization: `Bearer ${token.value}` },
                body:  profileData,
              }) 
              return response
            } catch (error: any) {
              if (error?.data?.status === "error" && error?.data?.errors) {
                errors.value = error.data.errors
              }
              throw error
            } finally { 
              pendingUpdateProfile.value = false
            }
          }
          
          
        return { users,status, error, refresh, pending, updateProfile, pendingUpdateProfile, errors}
}