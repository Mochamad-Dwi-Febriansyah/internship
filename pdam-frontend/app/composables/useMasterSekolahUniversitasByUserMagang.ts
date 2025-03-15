
import { useCustomFetch } from "~~/plugins/fetch-interceptor"
import type { ApiResponse, ApiResponseSingle } from "~~/types/types"

const customFetch = useCustomFetch()
 

interface MasterSekolah { 
    id: string,
    nama_sekolah_universitas: string,
    jurusan_sekolah: string,
    fakultas_universitas: string,
    program_studi_universitas: string,
    alamat_sekolah_universitas: string,
    kode_pos_sekolah_universitas: string,
    provinsi_sekolah_universitas: string,
    kabupaten_kota_sekolah_universitas: string,
    kecamatan_sekolah_universitas: string,
    kelurahan_desa_sekolah_universitas: string,
    nomor_telp_sekolah_universitas: string,
    email_sekolah_universitas: string,
    created_at: string,
    updated_at: string, 
}

interface ApiResponseSimple {
    status: string,
    message: string
}
export function useMasterSekolahUniversitas() {
    const config = useRuntimeConfig()
    const token = computed(() => useCookie('access_token').value)
    const pendingFetch = ref(false)
    const pendingCreate = ref(false) 
    const pendingUpdate = ref(false)
    const pendingDelete = ref<string | null>(null)
    const errors = ref<Record<string, string[]>>({})

    const FetchMasterSekolahByMagangId = async () => {
        pendingFetch.value = true;
        try {
            const response = await customFetch<ApiResponseSingle<MasterSekolah>>(`${config.public.apiBase}/v1/master-sekolah-by-magang`, {
                method: 'GET',
                headers: { Authorization: `Bearer ${token.value}` }
            });
            return response;
        } catch (error) {
            throw error;
        } finally {
            pendingFetch.value = false
        }
    }

    return { FetchMasterSekolahByMagangId, pendingFetch }
}