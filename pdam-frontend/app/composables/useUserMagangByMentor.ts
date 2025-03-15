import {useCustomFetch} from "~~/plugins/fetch-interceptor"
import type {ApiResponse} from "~~/types/types"

const customFetch = useCustomFetch()

interface MasterSekolah {
    id: string
    nama_sekolah_universitas: string
    jurusan_sekolah: string
    fakultas_universitas: string | null
    program_studi_universitas: string | null
    email_sekolah_universitas: string | null
}

interface Berkas {
    id: string
    user_id: string
    master_sekolah_universitas_id: string
    master_sekolah: MasterSekolah
}

interface User {
    id: string
    nama_depan: string
    nama_belakang: string
    nisn_npm_nim_npp: string
    tanggal_lahir: string
    jenis_kelamin: string
    nomor_hp: string
    email: string
    alamat: string
    kabupaten_kota: string
    provinsi: string
    kode_pos: string
    role: string
    bagian: string
    status: string
    created_at: string
    updated_at: string
    deleted_at: string | null
    berkas?: Berkas
}

export function useUsersMagangByMentor(currentPage: Ref<number>) {
    const config = useRuntimeConfig()
    const token = computed(() => useCookie('access_token').value)
    const pendingClearCache = ref(false)

    const {data: usersMagangByMentor, refresh, pending} = useAsyncData<ApiResponse<User>>(
        `users-magang-by-mentor-${currentPage.value}-${token.value}`,
        async () => {
            try {
                const response = await customFetch<ApiResponse<User>>(`${config.public.apiBase}/v1/users-magang-by-mentor`, {
                    method: 'GET',
                    headers: {Authorization: `Bearer ${token.value}`}
                })
                return response
            } catch (error) {
                throw error
            }
        }, {
            server: false,
            lazy: true,
            getCachedData: (key) => useNuxtApp().payload.data[key] as ApiResponse<User> ?? null,
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

    const clearCacheUserMagangByMentor = async () => {
        pendingClearCache.value = true;
        try {
            const response = await customFetch(`${config.public.apiBase}/v1/clear-cache-users-magang-by-mentor`, {
                method: 'GET',
                headers: {
                    Authorization: `Bearer ${token.value}`
                }
            });
            refresh();
        } catch (error: any) {
            console.error("Gagal menghapus user:", error);
        } finally {
            pendingClearCache.value = false;
        }
    }

    return {usersMagangByMentor, pending, clearCacheUserMagangByMentor, pendingClearCache}
}