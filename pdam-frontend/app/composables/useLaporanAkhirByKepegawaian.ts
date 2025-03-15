import {useCustomFetch} from "~~/plugins/fetch-interceptor";
import type {ApiResponse, ApiResponseSingle} from "~~/types/types";
import {jwtDecode} from "jwt-decode";

interface User {
    id: string
    nama_depan: string
    nama_belakang: string
    email: string
}
 
interface LaporanAkhir {
    id: string  
    user_id: string
    presensi_id: string
    title: string;  
    report: string;
    assessment_report_file: string | null;
    final_report_file: string | null;
    photo: string | null;
    video: string | null;
    certificate: string | null;
    verified_by_mentor_id: string | null;
    status_verifikasi_kepegawaian: "approved" | "pending" | "rejected";
    rejection_note_kepegawaian: string | null;
    created_at: string
    updated_at: string
    deleted_at: string | null 
    user: User | null 
    message : string

} 
interface ApiResponseSingleSimple {
    status: string
    message: string 
} 
export function useLaporanAkhirByKepegawaian(currentPage: Ref<number>) {
    const customFetch = useCustomFetch()
    const config = () => useRuntimeConfig();
    const token = computed(() => useCookie('access_token').value)
    const pendingCreate = ref(false)
    const pendingClearCache = ref(false)
    const pendingLaporanAkhirById = ref(false)
    const errors = ref<Record<string, string[]>>({}) 
    const pendingUpdateValidasiLaporanByKepegawaian = ref(false)

    const {data: laporanAkhirByKepegawaian, pending: pendingLaporanAkhirByKepegawaian, refresh: refreshlaporanAkhirByKepegawaian} = useAsyncData<ApiResponse<LaporanAkhir>>(
          `laporanAkhir-${currentPage.value}-${token.value}`,
          async () => {
              try {
                  const response = await customFetch<ApiResponse<LaporanAkhir>>(`${config().public.apiBase}/v1/laporan-akhir-kepegawaian`, {
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

      const clearCachelaporanAkhirByKepegawaian = async () => {
        pendingClearCache.value = true;
        try {
            const response = await customFetch(`${config().public.apiBase}/v1/clear-cache-laporan-akhir-kepegawaian`, {
                method: 'GET',
                headers: {
                    Authorization: `Bearer ${token.value}`
                }
            });
            refreshlaporanAkhirByKepegawaian();
        } catch (error: any) {
            throw error
            // console.error("Gagal menghapus user:", error);
        } finally {
            pendingClearCache.value = false;
        }
    }

      const updateValidasiLaporanByKepegawaian = async (ValidasiLaporanData: FormData | Partial<LaporanAkhir>) => {
           pendingUpdateValidasiLaporanByKepegawaian.value = true
           try { 
                // const laporanId = ValidasiLaporanData.get("id") as string // Ambil ID dari FormData
                // const laporanStatus = ValidasiLaporanData.get("status") as string // Ambil ID dari FormData
                // console.log(laporanStatus)

                if ('id' in ValidasiLaporanData && !ValidasiLaporanData.id) {
                    throw new Error("ID laporan tidak ditemukan")
                } 
                // console.log(ValidasiLaporanData)
               const laporanId = ValidasiLaporanData instanceof FormData ? ValidasiLaporanData.get("id") as string : ValidasiLaporanData.id;
               const response = await customFetch<LaporanAkhir>(`${config().public.apiBase}/v1/validasi-laporan-akhir-kepegawaian/${laporanId}`, {
                   method: 'POST',
                   headers: {Authorization: `Bearer ${token.value}`},
                   body: ValidasiLaporanData
               }) 
   
               return response
           } catch (error) {
            //    console.error('Gagal update mentor:', error)
               throw error;
           } finally {
               pendingUpdateValidasiLaporanByKepegawaian.value = false
           }
       }

         const getLaporanAkhirById = async (id: string) => {
                   pendingLaporanAkhirById.value = true
                   try {
                       const response = await customFetch<ApiResponseSingle<LaporanAkhir>>(`${config().public.apiBase}/v1/laporan-akhir-kepegawaian/${id}`, {
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

    return {laporanAkhirByKepegawaian, pendingLaporanAkhirByKepegawaian, refreshlaporanAkhirByKepegawaian, pendingClearCache,clearCachelaporanAkhirByKepegawaian, updateValidasiLaporanByKepegawaian,errors, pendingUpdateValidasiLaporanByKepegawaian, getLaporanAkhirById, pendingLaporanAkhirById}
}