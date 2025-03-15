import { ref, watch, computed } from 'vue'
import { useFetch } from '#app'

export const useSearch = () => {
  const search = ref<string>('')
  const results = ref<any[]>([])
  const loading = ref<boolean>(false) // Tambahkan loading
  const config = () => useRuntimeConfig()
  const token = computed(() => useCookie('access_token').value)

  let debounceTimeout: ReturnType<typeof setTimeout> | null = null

  watch(search, (newQuery) => {
    if (debounceTimeout) clearTimeout(debounceTimeout)
    if (newQuery.length > 2) {
      debounceTimeout = setTimeout(() => {
        fetchSearch(newQuery)
      }, 500)
    } else {
      results.value = []
    }
  })

  const fetchSearch = async (query: string) => {
    loading.value = true // Mulai loading
    const { data, error } = await useFetch<any>(`${config().public.apiBase}/v1/search?q=${query}`, {
      method: 'GET',
      headers: {
        Authorization: `Bearer ${token.value}`,
      },
    })
    if (!error.value && data.value) {
      const allResults = [
        ...(data.value.users || []),
        ...(data.value.presensi || []),
        ...(data.value.masterSekolahUniversitas || []),
        ...(data.value.laporanHarian || []),
        ...(data.value.laporanAkhir || []),
      ]
      results.value = allResults
    } else {
      results.value = []
    }
    loading.value = false // Selesai loading
  }

  const handleSelect = (item: any) => {
    console.log(item)
    // Tambah logic lain misal redirect atau fill form
  }

  return { search, results, loading, handleSelect }
}
