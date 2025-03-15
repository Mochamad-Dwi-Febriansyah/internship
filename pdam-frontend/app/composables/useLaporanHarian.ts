import {useCustomFetch} from "~~/plugins/fetch-interceptor";
import type {ApiResponse, ApiResponseSingle} from "~~/types/types";

interface LaporanHarian {
    id: string 
    title: string
    report: string
    result: string
    status: string
    rejection_note: string
}

export function useLaporanHarian(currentPage: Ref<number>) {
    const customFetch = useCustomFetch()
    const config = () => useRuntimeConfig();
    const token = computed(() => useCookie('access_token').value)
    const pendingCreate = ref(false)
    const pendingFetchById = ref(false)
    const pendingExportLogBook = ref(false)
    const errors = ref<Record<string, string[]>>({})
    const pendingUpdate = ref(false)
    const createLaporanHarian = async (laporanHarianData: FormData | Partial<LaporanHarian>) => {
        pendingCreate.value = true
        errors.value = {}
        try {
            const response = await customFetch<ApiResponse<LaporanHarian>>(`${config().public.apiBase}/v1/laporan`, {
                method: 'POST',
                headers: {Authorization: `Bearer ${token.value}`},
                body: laporanHarianData
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
      // Ambil laporan harian berdasarkan ID
  const getLaporanHarianById = async (id: string) => {
    pendingFetchById.value = true;
    errors.value = {};
    try {
      const response = await customFetch<ApiResponseSingle<LaporanHarian>>(`${config().public.apiBase}/v1/laporan/${id}`, {
        method: "GET",
        headers: { Authorization: `Bearer ${token.value}` },
      });

      return response.data;
    } catch (error: any) {
      if (error?.data.status === "error" && error?.data?.errors) {
        errors.value = error.data.errors;
      }
      throw error;
    } finally {
      pendingFetchById.value = false;
    }
  };
  


const updateLaporanHarian = async (id: string, laporanHarianData: FormData | Partial<LaporanHarian>) => {
    pendingUpdate.value = true
    errors.value = {}

    console.log(laporanHarianData)
    try {
        const response = await customFetch<ApiResponseSingle<LaporanHarian>>(`${config().public.apiBase}/v1/laporan/${id}`, {
            method: 'POST',
            headers: { Authorization: `Bearer ${token.value}` },
            body: laporanHarianData
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

const exportLogBook = async () => {
    pendingExportLogBook.value = true;
    try {
      const response = await fetch(`${config().public.apiBase}/v1/export-log-book`, {
        method: "GET",
        headers: {
          Authorization: `Bearer ${token.value}`,
          Accept: "application/pdf",
        },
      });
  
      if (!response.ok) throw new Error("Gagal mengunduh logbook");
  
      const blob = await response.blob();
      const url = window.URL.createObjectURL(blob);
      const a = document.createElement("a");
      a.href = url;
      a.download = "logbook.pdf";
      document.body.appendChild(a);
      a.click();
      window.URL.revokeObjectURL(url);
    } catch (error: any) {
      console.error("Error:", error);
    } finally {
      pendingExportLogBook.value = false;
    }
  };
  
    return {createLaporanHarian, pendingCreate, errors, getLaporanHarianById, pendingFetchById, updateLaporanHarian, pendingUpdate, exportLogBook,pendingExportLogBook }
}