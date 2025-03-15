import { useCustomFetch } from "~~/plugins/fetch-interceptor";
import type { ApiResponseSingle } from "~~/types/types";

const customFetch = useCustomFetch();
interface TotalPengajuanBerkas {
  total_belum_disetujui: number;
  total_belum_kirim_surat: number;
}


export function useKepegawaianDashboard() {
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
  const getTotalPengajuanBerkas = () => fetchData<TotalPengajuanBerkas>("/v1/total-pengajuan-berkas");
  const getTotalUserMagang = () => fetchData<number>("/v1/total-user-magang-by-kepegawaian");
  const getTotalUserMentor = () => fetchData<number>("/v1/total-user-mentor-by-kepegawaian");
  const getTotalVerifikasiLaporanAkhir = () => fetchData<number>("/v1/total-verifikasi-laporan-akhir-by-kepegawaian");

  return {
    getTotalPengajuanBerkas,
    getTotalUserMagang,
    getTotalUserMentor,
    getTotalVerifikasiLaporanAkhir,
    pending,
    errors,
  };
}
