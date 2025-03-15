
import { useCustomFetch } from "~~/plugins/fetch-interceptor"
import type { ApiResponse, ApiResponseSingle } from "~~/types/types"

const customFetch = useCustomFetch()
 

interface Berkas { 
    id: string,    
    nomor_registrasi: string,
    foto_identitas: string,
    surat_permohonan: string,
    surat_diterima: string,
    tanggal_mulai: string,
    tanggal_selesai: string,
    status_berkas: string,
    verified_by_id: string,
    created_at: string,
    updated_at: string, 
}

interface ApiResponseSimple {
    status: string,
    message: string
}
export function useBerkasByUserMagang() {
    const config = useRuntimeConfig()
    const token = computed(() => useCookie('access_token').value)
    const pendingFetch = ref(false)
    const pendingCreate = ref(false) 
    const pendingUpdate = ref(false)
    const pendingDelete = ref<string | null>(null)
    const errors = ref<Record<string, string[]>>({})

    const FetchBerkasByMagangId = async () => {
        pendingFetch.value = true;
        try {
            const response = await customFetch<ApiResponseSingle<Berkas>>(`${config.public.apiBase}/v1/berkas-by-magang`, {
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

    return { FetchBerkasByMagangId, pendingFetch }
}