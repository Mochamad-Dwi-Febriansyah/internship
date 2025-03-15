import {useCustomFetch} from "~~/plugins/fetch-interceptor"
import type {ApiResponse, ApiResponseSingle} from "~~/types/types"

const customFetch = useCustomFetch()
 
interface TandaTangan {
    id: string  ,
    user_id: string, 
    signature: string, 
    created_at: string,
    updated_at: string, 
}

interface ApiResponseSimple {
    status: string,
    message: string
}
export function useTandaTangan() {
    const config = useRuntimeConfig()
    const token = computed(() => useCookie('access_token').value) 
    const pendingFetch= ref(false)
    const pendingCreate = ref(false)
    const pendingTandaTanganById = ref(false)
    const pendingUpdate = ref(false)
    const pendingDelete = ref<string | null>(null)
    const errors = ref<Record<string, string[]>>({}) 
    
    const fetchTandaTangan  = async () => {
        pendingFetch.value = true;
        try {
            const response = await customFetch<ApiResponseSingle<TandaTangan>>(`${config.public.apiBase}/v1/tanda-tangan`, {
                method: 'GET',
                headers: {Authorization: `Bearer ${token.value}`}
            });
            return response;
        } catch (error) { 
            throw error;
        } finally {
            pendingFetch.value = false
        }
    };

    const createTandaTangan = async (tandaTangan: FormData | Partial<TandaTangan>) => {
        pendingCreate.value = true
        errors.value = {}
        try { 
            const response = await customFetch<ApiResponseSimple>(`${config.public.apiBase}/v1/tanda-tangan`, {
                method: 'POST',
                headers: {Authorization: `Bearer ${token.value}`},
                body: tandaTangan
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
    const getTandaTanganById = async (id: string) => {
        pendingTandaTanganById.value = true
        try {
            const response = await customFetch<ApiResponseSingle<TandaTangan>>(`${config.public.apiBase}/v1/tanda-tangan/${id}`, {
                method: 'GET',
                headers: {Authorization: `Bearer ${token.value}`}
            });
            return response;
        } catch (error) { 
            throw error;
        } finally {
            pendingTandaTanganById.value = false
        }
    };

    const updateTandaTangan = async (id: string,tandaTangan: FormData | Partial<TandaTangan>) => {
        pendingUpdate.value = true
        errors.value = {}
        try { 
            const response = await customFetch<ApiResponseSimple>(`${config.public.apiBase}/v1/tanda-tangan/${id}`, {
                method: 'PUT',
                headers: {Authorization: `Bearer ${token.value}`},
                body: tandaTangan
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

    const deleteTandaTangan = async (id: string) => {
        if (!confirm("Apakah Anda yakin ingin menghapus user ini?")) return;

        pendingDelete.value = id; 

        try {
            const response = await customFetch<ApiResponseSimple>(`${config.public.apiBase}/v1/tanda-tangan/${id}`, {
                method: 'DELETE',
                headers: {
                    Authorization: `Bearer ${token.value}`
                }
            }); 
            return response 
        } catch (error: any) { 
            throw error
        } finally {
            pendingDelete.value = null;  
        }
    }

    return { fetchTandaTangan,errors, pendingFetch, getTandaTanganById, pendingTandaTanganById, updateTandaTangan, pendingUpdate, createTandaTangan, pendingCreate, deleteTandaTangan, pendingDelete }
}