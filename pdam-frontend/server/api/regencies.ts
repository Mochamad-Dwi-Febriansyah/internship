import { H3Event } from 'h3';

interface ResponseType {
  data: {
    code: string;
    name: string;
  }[];
  meta: {
    administrative_area_level: number;
    updated_at: string;
  };
}
 
export default defineEventHandler(async (event: H3Event): Promise<ResponseType | { error: string, details?: any }> => {
  const config = useRuntimeConfig();
  const query = getQuery(event);
  const provinceId = query.province_id;
 

  if (!provinceId) {
    return { error: "province_id diperlukan!" };
  }


  try {
    const response: ResponseType = await $fetch(`${config.public.apiBaseRegion}/api/regencies/${provinceId}.json`, {
      method: 'GET',
      headers: { "Content-Type": "application/json" },
    });
    return response;
  } catch (error) {
    return { error: "Gagal mengambil data", details: error };
  }
});

  