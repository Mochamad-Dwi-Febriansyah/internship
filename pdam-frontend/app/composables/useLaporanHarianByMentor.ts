import {useCustomFetch} from "~~/plugins/fetch-interceptor";
import type {ApiResponse, ApiResponseSingle} from "~~/types/types";
import {jwtDecode} from "jwt-decode";

interface User {
    id: string
    nama: string
    nama_depan: string
    nama_belakang: string
    email: string
}

interface Laporan {
    id: string 
    title: string
    report: string
    result: string
    status: string
    rejection_note: string,
    tanggal_presensi : string,
    created_at: string
}

interface LaporanHarian {
    id: string  
    user_id: string
    presensi_id: string
    title: string
    report: string
    result: string
    status: string 
    rejected_note: string 
    created_at: string
    updated_at: string
    deleted_at: string | null 
    user: User | null 
    laporan: Laporan[] | null
    message : string

} 
interface ApiResponseSingleSimple {
    status: string
    message: string 
}
const getUserIdFromToken = (token: string | null): string | null => {
    if (token) {
        const decodedToken = jwtDecode<any>(token);
        // console.log(decodedToken)
        return decodedToken.sub;
    }
    return null;
};
export function useLaporanHarianByMentor(currentPage: Ref<number>) {
    const customFetch = useCustomFetch()
    const config = () => useRuntimeConfig();
    const token = computed(() => useCookie('access_token').value)
    const pendingCreate = ref(false)
    const pendingClearCache = ref(false)
    const errors = ref<Record<string, string[]>>({}) 

    const pendingUpdateValidasiLaporanByMentor = ref(false)

    const {data: laporanHarianByMentor, pending: pendingLaporanHarianByMentor, refresh: refreshlaporanHarianByMentor} = useAsyncData<ApiResponse<LaporanHarian>>(
          `laporanHarian-${currentPage.value}-${token.value}`,
          async () => {
              try {
                  const response = await customFetch<ApiResponse<LaporanHarian>>(`${config().public.apiBase}/v1/laporan_harian_mentor`, {
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
              getCachedData: (key) => useNuxtApp().payload.data[key] as ApiResponse<LaporanHarian> ?? null,
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

      const clearCachelaporanHarianByMentor = async () => {
        pendingClearCache.value = true;
        try {
            const response = await customFetch(`${config().public.apiBase}/v1/clear_cache_laporan_harian_mentor`, {
                method: 'GET',
                headers: {
                    Authorization: `Bearer ${token.value}`
                }
            });
            refreshlaporanHarianByMentor();
        } catch (error: any) {
            throw error
            // console.error("Gagal menghapus user:", error);
        } finally {
            pendingClearCache.value = false;
        }
    }

      const updateValidasiLaporanByMentor = async (ValidasiLaporanData: { status: any; ids: number[] }) => {
           pendingUpdateValidasiLaporanByMentor.value = true
           errors.value = {};
           try { 
                // const laporanId = ValidasiLaporanData.get("id") as string // Ambil ID dari FormData
                // const laporanStatus = ValidasiLaporanData.get("status") as string // Ambil ID dari FormData
                // console.log(laporanStatus)

                if (!ValidasiLaporanData.ids || ValidasiLaporanData.ids.length === 0) {
                    throw new Error("ID laporan tidak ditemukan");
                  }
            
               const response = await customFetch<LaporanHarian>(`${config().public.apiBase}/v1/validasi_laporan`, {
                   method: 'PUT',
                   headers: {Authorization: `Bearer ${token.value}`},
                   body: JSON.stringify(ValidasiLaporanData),
               }) 
            //    console.log("jj", response)
               return response
           } catch (error: any) {
                  console.error('Gagal update mentor:', error.data)
               if (error?.data.status === "error" && error?.data?.errors) { 
                   errors.value = error.data.errors
                } 
            // console.log("sa", error.data.message)
                throw error;
            } finally {
            //    console.log("jnn", errors.value)
               pendingUpdateValidasiLaporanByMentor.value = false
           }
       }

    return {laporanHarianByMentor, pendingLaporanHarianByMentor, refreshlaporanHarianByMentor, pendingClearCache,clearCachelaporanHarianByMentor, updateValidasiLaporanByMentor,errors, pendingUpdateValidasiLaporanByMentor}
}