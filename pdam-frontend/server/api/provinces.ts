export default defineEventHandler(async (event) => {
  const config =   useRuntimeConfig()
    try {
      const response = await $fetch(`${config.public.apiBaseRegion}/api/provinces.json`, {
        method: 'GET',
        headers: { "Content-Type": "application/json" },
      });
      return response;
    } catch (error) {
      return { error: "Gagal mengambil data", details: error };
    }
  });
  