import type {ApiResponse, ApiResponseSingle} from "~~/types/types";
import {useCustomFetch} from "~~/plugins/fetch-interceptor"
import {jwtDecode} from "jwt-decode";

interface User {
    id: string
    nama_depan: string
    nama_belakang: string
}

interface LaporanHarian {
    id: string
    judul: string
    status: string
}

interface Presensi {
    id: string
    user_id: string
    tanggal: string
    waktu_check_in: string
    waktu_check_out: string
    foto_check_in: string
    foto_check_out: string
    latitude_check_in: string
    latitude_check_out: string
    longitude_check_in: string
    longitude_check_out: string
    status: string
    created_at: string
    updated_at: string
    deleted_at: string | null
    user: User
    laporan_harian: LaporanHarian | null
}

export function useTodayPresensi(currentPage: Ref<number>) {
    const customFetch = useCustomFetch()

    const config = () => useRuntimeConfig();
    const token = computed(() => useCookie('access_token').value)  

    const {data: todayPresensi, pending, refresh: refreshTodayPresensi} = useAsyncData<ApiResponseSingle<Presensi>>(
        `today-presensi-${token.value}`,
        async () => {
            try {
                const response = await customFetch<ApiResponseSingle<Presensi>>(`${config().public.apiBase}/v1/presensi-hari-ini`, {
                    method: 'GET',
                    headers: {Authorization: `Bearer ${token.value}`,}
                })
                return response
            } catch (error) {
                throw error
            }
        }, {
            server: false,
            lazy: true,
            getCachedData: (key) => useNuxtApp().payload.data[key] as ApiResponseSingle<Presensi> ?? null,
            default: () => ({
                status: "",
                message: "",
                data: { 
                        id: "",
                        user_id: "",
                        tanggal: "",
                        waktu_check_in: "",
                        waktu_check_out: "",
                        foto_check_in: "",
                        foto_check_out: "",
                        latitude_check_in: "",
                        latitude_check_out: "",
                        longitude_check_in: "",
                        longitude_check_out: "",
                        status: "",
                        created_at: "",
                        updated_at: "",
                        deleted_at: null,
                        user: {
                            id: "",
                            nama_depan: "",
                            nama_belakang: ""
                        },
                        laporan_harian: null
            
                    
                }
            }),
            watch: [token, currentPage]
        }
    )
    return { todayPresensi, pending, refreshTodayPresensi }
}