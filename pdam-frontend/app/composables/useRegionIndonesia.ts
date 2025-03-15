interface ApiResponseRegion<T> { 
    data: T[]
}

interface Region {
    code: string,
    name: string, 
}

interface District{
    code: string,
    name: string, 
}
interface Subdistrict{
    code: string,
    name: string, 
}
interface Village{
    code: string,
    name: string, 
    postal_code: string
}

export function useRegionIndonesia(){
    const config = () => useRuntimeConfig()
    const pendingGetRegencies = ref(false)
    const pendingGetDistricts = ref(false)
    const pendingGetVillage = ref(false)
    const { data: province, pending: pendingProvince, refresh: refreshProvince } = useAsyncData<ApiResponseRegion<Region>>(
        `province-region`,
        async () => {
            try {
                const response = await $fetch<ApiResponseRegion<Region>>("/api/provinces")
            return response
            }catch(error: any) {
                throw error
            }
        },{
            server: false,
            lazy: true,
            getCachedData: (key) => useNuxtApp().payload.data[key] as ApiResponseRegion<Region> ?? null,
            default: () => ({ 
                data: []
            }),
            watch: []
        }
    )

    const getRegenciesByProvince = async (id_province: string) => {
        pendingGetRegencies.value = true
        try {
            const response = await $fetch<ApiResponseRegion<District>>(`/api/regencies`, {
                query: { province_id: id_province },
            })
            return response
        }catch(error: any) {
            throw error
        }finally{
            pendingGetRegencies.value = false
        }
    }

    const getDistrictByRegencies = async (id_kabupaten: string) => {
        pendingGetDistricts.value = true
        try {
            const response = await $fetch<ApiResponseRegion<Subdistrict>>(`/api/districts`, {
                query: { regencies_id: id_kabupaten },
            })
            return response
        }catch(error: any) {
            throw error
        }finally{
            pendingGetDistricts.value = false
        }
    }
    const getVillagesByDistrict = async (id_kecamatan: string) => {
        pendingGetVillage.value = true
        try {
            const response = await $fetch<ApiResponseRegion<Village>>(`/api/villages/`, {
                query: { districts_id: id_kecamatan },
            })
            return response
        }catch(error: any) {
            throw error
        }finally{
            pendingGetVillage.value = false
        }
    }

    return { province, pendingProvince, refreshProvince, getRegenciesByProvince, pendingGetRegencies, getDistrictByRegencies, pendingGetDistricts, getVillagesByDistrict, pendingGetVillage }
}