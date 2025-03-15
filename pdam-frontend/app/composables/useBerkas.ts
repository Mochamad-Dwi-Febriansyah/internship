import {ref} from 'vue'
import {useCustomFetch} from '~~/plugins/fetch-interceptor'
import type {ApiResponse, ApiResponseAction} from '~~/types/types'
import {jwtDecode} from 'jwt-decode';

const customFetch = useCustomFetch()

interface Berkas {
    nomor_registrasi: string
    status_berkas: string
}

interface BerkasResponse {
    status: string
    message: string
    data?: Berkas
}

// Interface untuk master sekolah/universitas
interface MasterSekolah {
    id: string
    nama_sekolah_universitas: string
    jurusan_sekolah: string | null
    fakultas_universitas: string | null
    program_studi_universitas: string | null
    alamat_sekolah_universitas: string
    kabupaten_kota_sekolah_universitas: string
    provinsi_sekolah_universitas: string
    kode_pos_sekolah_universitas: string
    nomor_telp_sekolah_universitas: string
    email_sekolah_universitas: string
}

// Interface untuk data user
interface User {
    id: string
    nama_depan: string
    nama_belakang: string
    email: string
    nomor_hp: string
    nisn_npm_nim_npp: string
}

// Interface untuk data berkas
interface Berkas {
    id: string
    mentor_id: string
    nomor_registrasi: string
    foto_identitas: string
    surat_permohonan: string
    tanggal_mulai: string
    tanggal_selesai: string
    status_berkas: string 
    created_at: string
    user_id: string
    master_sekolah_universitas_id: string
    user: User | null
    master_sekolah: MasterSekolah | null
    selectedStatus: any
}
 

export function useBerkas(currentPage: Ref<number>) {
    const pending = ref(false)
    const response = ref<string | null>(null)
    const berkas = ref<Berkas | null>(null)
    const errors = ref<Record<string, string[]>>({})
    const config = useRuntimeConfig()
    const token = computed(() => useCookie('access_token').value)
    const pendingToggle = ref<string | null>(null)
    const pendingClearCache = ref(false)
    const pendingUpdateMyMentor = ref(false)
    const pendingUpdateSuratTerima = ref(false)

    const {
        data: daftarPengajuanBerkas,
        status: statusDaftarPengajuanBerkas,
        refresh: refreshDaftarPengajuanBerkas,
        error: errorDaftarPengajuanBerkas,
        pending: pendingDaftarPengajuanBerkas
    } = useAsyncData<ApiResponse<Berkas>>(
        `daftar-pengajuan-berkas--${currentPage.value}-${token.value}`,
        async () => {
            try {

                const response = await customFetch<ApiResponse<Berkas>>(`${config.public.apiBase}/v1/pengajuan-berkas?page=1`, {
                    method: 'GET',
                    headers: {Authorization: `bearer ${token.value}`}
                })
                return response
            } catch (error) {
                throw error
            }
        }, {
            server: false,
            lazy: true,
            // immediate: false,
            getCachedData: (key) => useNuxtApp().payload.data[key] as ApiResponse<Berkas>,
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
        })
    const updateMyMentor = async (mentorData: Partial<Berkas>) => {
        pendingUpdateMyMentor.value = true
        try {

            const response = await customFetch<ApiResponse<Berkas>>(`${config.public.apiBase}/v1/status-berkas-this-mentor/${mentorData.user_id}`, {
                method: 'PUT',
                headers: {Authorization: `Bearer ${token.value}`},
                body: {...mentorData}
            })
            return response
        } catch (error) {
            pendingUpdateMyMentor.value = false
            throw error
        }
    }
    const updateSuratTerima = async (mentorData: Partial<Berkas>) => {
        pendingUpdateSuratTerima.value = true
        errors.value = {}; 
        try {

            const response = await customFetch<ApiResponseAction>(`${config.public.apiBase}/v1/surat-terima/${mentorData.user_id}`, {
                method: 'PUT',
                headers: {Authorization: `Bearer ${token.value}`},
                body: {...mentorData}
            })
            return response
        } catch (error: any) {
            if (error?.data.status === "error" && error?.data?.errors) {
                errors.value = error.data.errors;
              }
            pendingUpdateSuratTerima.value = false
            throw error
        }
    }



    const toggleStatusBerkas = async (berkasId: string, currentStatus: string) => {
        pendingToggle.value = berkasId
        // console.log(currentStatus)
        try {
            // const newStatus = currentStatus

            const response = await customFetch(`${config.public.apiBase}/v1/toggle-status-berkas/${berkasId}`, {
                method: 'PUT',
                headers: {Authorization: `Bearer ${token.value}`},
                body: {status_berkas: currentStatus}
            })
            refreshDaftarPengajuanBerkas()
        } catch (error) {
            pendingToggle.value = null
            throw error
        }
    }

    const clearCachePengajuanBerkas = async () => {
        pendingClearCache.value = true;
        try {
            const response = await customFetch(`${config.public.apiBase}/v1/clear-cache-pengajuan-berkas`, {
                method: 'GET',
                headers: {
                    Authorization: `Bearer ${token.value}`
                }
            });
            refreshDaftarPengajuanBerkas();
        } catch (error: any) {
            // console.error("Gagal menghapus user:", error);
            throw error
        } finally {
            pendingClearCache.value = false;
        }
    }

    return {
        daftarPengajuanBerkas,
        statusDaftarPengajuanBerkas,
        refreshDaftarPengajuanBerkas,
        errorDaftarPengajuanBerkas,
        pendingDaftarPengajuanBerkas,
       
        pending,
        response,
        berkas,
        errors,
        toggleStatusBerkas,
        clearCachePengajuanBerkas,
        pendingClearCache,
        updateMyMentor,
        pendingUpdateMyMentor,

        updateSuratTerima,
        pendingUpdateSuratTerima
    }
}
