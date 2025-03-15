import {useCustomFetch} from "~~/plugins/fetch-interceptor";
import type {ApiResponse, ApiResponseSingle} from "~~/types/types";

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
}
interface Historis {
    version_number: number;
    created_at: string;
}
interface LaporanAkhir {
    id: string;
    user_id: string;
    berkas_id: string;
    master_sekolah_universitas_id: string;
    title: string;  
    report: string;
    assessment_report_file: string | null;
    final_report_file: string | null;
    photo: string | null;
    video: string;
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
    master_sekolah: MasterSekolah | null
    historis: Historis[]; // Relasi historis
} 

export function useLaporanAkhir(currentPage: Ref<number>) {
    const customFetch = useCustomFetch()
    const config = () => useRuntimeConfig();
    const token = computed(() => useCookie('access_token').value)
    const pendingCreate = ref(false)
    const pendingUpdate = ref(false)
    const errors = ref<Record<string, string[]>>({})
    const pendingClearCache = ref(false)
    const pendingLaporanAkhirById = ref(false)


        const {data: laporanAkhir, pending: pendingLaporanAkhir, refresh: refreshLaporanAkhir} = useAsyncData<ApiResponse<LaporanAkhir>>(
              `laporanAkhir-${currentPage.value}-${token.value}`,
              async () => {
                  try {
                      const response = await customFetch<ApiResponse<LaporanAkhir>>(`${config().public.apiBase}/v1/laporan-akhir`, {
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
                  getCachedData: (key) => useNuxtApp().payload.data[key] as ApiResponse<LaporanAkhir> ?? null,
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

    const createLaporanAkhir = async (laporanAkhirData: FormData | Partial<LaporanAkhir>) => {
        pendingCreate.value = true
        errors.value = {}
        try {
            // console.log(laporanAkhirData)
            const response = await customFetch<ApiResponse<LaporanAkhir>>(`${config().public.apiBase}/v1/laporan-akhir`, {
                method: 'POST',
                headers: {Authorization: `Bearer ${token.value}`},
                body: laporanAkhirData
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
    const updateLaporanAkhir = async (id: string,laporanAkhirData: FormData | Partial<LaporanAkhir>) => {
        pendingUpdate.value = true
        errors.value = {}
        try {
            // console.log(laporanAkhirData)
            const response = await customFetch<ApiResponse<LaporanAkhir>>(`${config().public.apiBase}/v1/laporan-akhir/${id}`, {
                method: 'POST',
                headers: {Authorization: `Bearer ${token.value}`},
                body: laporanAkhirData
            })

            return response
        } catch (error: any) {
            if (error?.data.status === "error" && error?.data?.errors) {
                errors.value = error.data.errors
            }
            throw error
        } finally {
            pendingUpdate.value = false
        } 
    }

    const clearCacheLaporanAkhir = async () => {
        pendingClearCache.value = true;
        try {
            const response = await customFetch(`${config().public.apiBase}/v1/clear-cache-laporan-akhir`, {
                method: 'GET',
                headers: {
                    Authorization: `Bearer ${token.value}`
                }
            });
            refreshLaporanAkhir();
        } catch (error: any) {
            throw error
            // console.error("Gagal menghapus user:", error);
        } finally {
            pendingClearCache.value = false;
        }
    }
     const getLaporanAkhirById = async (id: string) => {
            pendingLaporanAkhirById.value = true
            try {
                const response = await customFetch<ApiResponseSingle<LaporanAkhir>>(`${config().public.apiBase}/v1/laporan-akhir/${id}`, {
                    method: 'GET',
                    headers: {Authorization: `Bearer ${token.value}`}
                });
                return response;
            } catch (error) {
                // console.error("Gagal mengambil presensi hari ini:", error);
                throw error;
            } finally {
                pendingLaporanAkhirById.value = false
            }
        };
    return {laporanAkhir, pendingLaporanAkhir, refreshLaporanAkhir, createLaporanAkhir, pendingCreate, errors, clearCacheLaporanAkhir, pendingClearCache, getLaporanAkhirById, pendingLaporanAkhirById, updateLaporanAkhir, pendingUpdate}
}