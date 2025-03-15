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

const getUserIdFromToken = (token: string | null): string | null => {
    if (token) {
        const decodedToken = jwtDecode<any>(token);
        return decodedToken.sub;
    }
    return null;
};

export function usePresensi(currentPage: Ref<number>) {
    const customFetch = useCustomFetch()

    const config = () => useRuntimeConfig();
    const token = computed(() => useCookie('access_token').value)
    const pendingCreate = ref(false)
    const pendingGetTodayPresensi = ref(false)
    const errors = ref<Record<string, string[]>>({})

    const {data: presensi, pending, refresh: refreshPresensi} = useAsyncData<ApiResponse<Presensi>>(
        `presensi-${currentPage.value}-${token.value}`,
        async () => {
            try {
                const response = await customFetch<ApiResponse<Presensi>>(`${config().public.apiBase}/v1/presensi`, {
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
            getCachedData: (key) => useNuxtApp().payload.data[key] as ApiResponse<Presensi> ?? null,
            default: () => ({
                status: "",
                message: "",
                data: {
                    current_page: 1,
                    data: [],
                    first_page_url: "",
                    from: 0,
                    last_page: 1,
                    last_page_url: "",
                    links: [],
                    next_page_url: null,
                    path: "",
                    per_page: 10,
                    prev_page_url: null,
                    to: 0,
                    total: 0
                }
            }),
            watch: [token, currentPage]
        }
    )
 

    const getTodayPresensi = async () => {
        pendingGetTodayPresensi.value = true
        try {
            const response = await customFetch<ApiResponseSingle<Presensi>>(`${config().public.apiBase}/v1/presensi-hari-ini`, {
                method: 'GET',
                headers: {Authorization: `Bearer ${token.value}`}
            });
            return response;
        } catch (error) {
            // console.error("Gagal mengambil presensi hari ini:", error);
            throw error;
        } finally {
            pendingGetTodayPresensi.value = false
        }
    };

    const createPresensi = async (presensiData: FormData | Partial<Presensi>) => {
        pendingCreate.value = true
        errors.value = {}
        try {
            const userIdFromToken = getUserIdFromToken(token.value ?? null);
            if (!userIdFromToken) throw new Error('User ID not found')
            if (presensiData instanceof FormData) {
                presensiData.append("user_id", userIdFromToken);
            } else {
                // 🔹 Jika bukan FormData, ubah ke format JSON dan tambahkan user_id
                presensiData = {...presensiData, user_id: userIdFromToken};
            }
            const response = await customFetch<ApiResponse<Presensi>>(`${config().public.apiBase}/v1/presensi`, {
                method: 'POST',
                headers: {Authorization: `Bearer ${token.value}`},
                body: presensiData instanceof FormData ? presensiData : JSON.stringify(presensiData)
            })

            return response
        } catch (error: any) {
            if (error?.data.status === "error" && error?.data?.errors) {
                errors.value = error.data.errors
            }
            throw error
        } finally {
            pendingCreate.value = false
        }

    }

    return {
        presensi,
        pending,
        refreshPresensi,
        createPresensi,
        pendingCreate,
        getTodayPresensi,
        pendingGetTodayPresensi
    }
}