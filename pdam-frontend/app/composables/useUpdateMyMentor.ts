import {jwtDecode} from 'jwt-decode';
import {useCustomFetch} from '~~/plugins/fetch-interceptor';
import type {ApiResponse} from "~~/types/types"

const customFetch = useCustomFetch()
const getUserIdFromToken = (token: string | null): string | null => {
    if (token) {
        const decodedToken = jwtDecode<any>(token);
        // console.log(decodedToken)
        return decodedToken.sub;
    }
    return null;
};

interface Berkas {
    id: string
    nomor_registrasi: string
    foto_identitas: string
    surat_permohonan: string
    tanggal_mulai: string
    tanggal_selesai: string
    status_berkas: string
    created_at: string
    user_id: string
    master_sekolah_universitas_id: string
}

export function useUpdateMyMentor() {
    const config = useRuntimeConfig()
    const token = computed(() => useCookie('access_token').value)
    const pendingUpdateMyMentor = ref(false)

    const updateMyMentor = async (mentorData: Partial<Berkas>) => {
        pendingUpdateMyMentor.value = true
        try {
            const userIdFromToken = getUserIdFromToken(token.value ?? null);
            if (!userIdFromToken) throw new Error('User ID not found')

            const response = await customFetch<ApiResponse<Berkas>>(`${config.public.apiBase}/v1/berkas_my_mentor/${userIdFromToken}`, {
                method: 'PUT',
                headers: {Authorization: `Bearer ${token.value}`},
                body: {...mentorData}
            })
            useCookie('is_blocked').value = null

            return response
        } catch (error) {
            console.error('Gagal update mentor:', error)
            throw error;
        } finally {
            pendingUpdateMyMentor.value = false
        }
    }

    return {updateMyMentor, pendingUpdateMyMentor}
}
