import {ref} from 'vue'
import {useCustomFetch} from '~~/plugins/fetch-interceptor'
import type {ApiResponse} from '~~/types/types'
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
export function useAjukanBerkas(){
    const pending = ref(false)
    const errors = ref<Record<string, string[]>>({})
      const berkas = ref<Berkas | null>(null)
    const ajukanBerkas = async (payload: FormData) => {
        pending.value = true
        errors.value = {};
        try {
            const config = useRuntimeConfig()
            const data = await customFetch<BerkasResponse>(`${config.public.apiBase}/v1/berkas`, {
                method: 'POST',
                body: payload
            })
        } catch (error: any) {
            if (error?.data?.status === "error" && error?.data?.errors) {
                errors.value = error.data.errors;
            }
            throw error
        } finally {
            pending.value = false
        }
    }

    const cekBerkas = async (nomor_registrasi: string) => {
        pending.value = true
        berkas.value = null

        try {
            const config = useRuntimeConfig()
            const data = await customFetch<BerkasResponse>(
                `${config.public.apiBase}/v1/berkas-cek/${nomor_registrasi}`,
                {method: 'GET'}
            )

            if (!data || data.status !== 'success' || !data.data) {
                throw new Error(data?.message || 'Data berkas tidak ditemukan')
            }

            berkas.value = data.data
        } catch (error: any) {
            if (error?.data?.status === "error" && error?.data?.errors) {
                errors.value = error.data.errors;
            }
            throw error
        } finally {
            pending.value = false
        }
    }

    return {  
        ajukanBerkas,
        cekBerkas, 
        pending,
        errors,
        berkas,
    }
}