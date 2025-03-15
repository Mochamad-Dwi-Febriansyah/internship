import {useCustomFetch} from '~~/plugins/fetch-interceptor';
import type {ApiResponseSingle} from "~~/types/types"

const customFetch = useCustomFetch() 

interface MasterSekolahUniversitas {
    id: number;
    nama_sekolah_universitas: string;
    jurusan_sekolah?: string;
    fakultas_universitas?: string;
    program_studi_universitas?: string;
  }
  
  interface Berkas {
    id: number;
    user_id: number;
    master_sekolah_universitas_id: number;
    masterSekolah?: MasterSekolahUniversitas;
  }
  
  interface LaporanHarian {
    id: number;
    status: string;
    presensi_id: number;
  }
  
  interface Presensi {
    id: number;
    user_id: number;
    tanggal: string;
    laporanHarian?: LaporanHarian[];
  }
  
  interface User {
    id: number;
    name: string;
    email: string;
    berkas?: Berkas[];
  }
  
  interface laporanAkhir {
    id: string;
    user_id: string;
    berkas_id: string;
    master_sekolah_universitas_id: string;
    title: string;  
    report: string;
    assessment_report_file: string | null;
    final_report_file: string | null;
    photo: string | null;
    video: string | null;
    certificate: string | null;
    verified_by_mentor_id: string | null;
    status_verifikasi_mentor: "approved" | "pending" | "rejected";
    rejection_note_mentor: string | null;
    verified_by_kepegawaian_id: string | null;
    status_verifikasi_kepegawaian: "approved" | "pending" | "rejected";
    rejection_note_kepegawaian: string | null;
    created_at: string;
    updated_at: string;
    user: User | null 
  }
  interface UserMagangResponse {
    user: User;
    presensi: Presensi[];
    laporanAkhir: laporanAkhir;
  }

  interface LaporanHarian {
    id: number 
    title: string
    report: string
    result: string
    status: string
    rejection_note: string
}

export function useDetailUserMagang() {
    const config = useRuntimeConfig()
    const token = computed(() => useCookie('access_token').value)
    const pendingDetailUsersMagang = ref(false)
    const pendingFetchById = ref(false)

    const detailUsersMagang = async (id: string) => {
        pendingDetailUsersMagang.value = true
        try { 

            const response = await customFetch<ApiResponseSingle<UserMagangResponse>>(`${config.public.apiBase}/v1/users-magang/${id}`, {
                method: 'GET',
                headers: {Authorization: `Bearer ${token.value}`}, 
            }) 

            return response
        } catch (error) {
            // console.error('Gagal update mentor:', error)
            throw error;
        } finally {
            pendingDetailUsersMagang.value = false
        }
    }

    const getLaporanHarianById = async (id: string) => {
        pendingFetchById.value = true; 
        try {
          const response = await customFetch<ApiResponseSingle<LaporanHarian>>(`${config.public.apiBase}/v1/laporan/${id}`, {
            method: "GET",
            headers: { Authorization: `Bearer ${token.value}` },
          });
    
          return response;
        } catch (error: any) { 
          throw error;
        } finally {
          pendingFetchById.value = false;
        }
      };

    return {detailUsersMagang, pendingDetailUsersMagang, getLaporanHarianById, pendingFetchById}
}
