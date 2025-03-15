import { useCustomFetch } from "~~/plugins/fetch-interceptor";
import type { ApiResponseSingle } from "~~/types/types";

const customFetch = useCustomFetch();

export function useMentorDashboard() {
  const config = useRuntimeConfig();
  const token = computed(() => useCookie("access_token").value);
  const pending = ref(false);

  // Reusable error state
  const errors = ref<string | null>(null);

  // Reusable function to fetch data
  const fetchData = async <T>(url: string) => {
    pending.value = true;
    errors.value = null;
    try {
      const response = await customFetch<ApiResponseSingle<T>>(`${config.public.apiBase}${url}`, {
        method: "GET",
        headers: { Authorization: `Bearer ${token.value}` },
      });
      return response;
    } catch (error: any) {
      errors.value = error?.data?.message || "Terjadi kesalahan saat memuat data.";
      throw error;
    } finally {
      pending.value = false;
    }
  };

  // API Calls
  const getTotalUserMagangByMentor = () => fetchData<number>("/v1/total-user-magang-by-mentor");
  const getTotalVerifikasiLaporanHarian = () => fetchData<number>("/v1/total-verifikasi-laporan-harian");
  const getTotalVerifikasiLaporanAkhir = () => fetchData<number>("/v1/total-verifikasi-laporan-akhir");

  return {
    getTotalUserMagangByMentor,
    getTotalVerifikasiLaporanHarian,
    getTotalVerifikasiLaporanAkhir,
    pending,
    errors,
  };
}
