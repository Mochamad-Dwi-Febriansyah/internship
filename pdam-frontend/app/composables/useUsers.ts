import {useCustomFetch} from "~~/plugins/fetch-interceptor"
import type {ApiResponse} from "~~/types/types"

const customFetch = useCustomFetch()

interface User {
    id: string
    nama_depan: string
    nama_belakang: string
    nisn_npm_nim_npp: string
    tanggal_lahir: string
    jenis_kelamin: string
    nomor_hp: string
    email: string
    alamat: string
    kabupaten_kota: string
    provinsi: string
    kode_pos: string
    role: string
    bagian: string
    status: string
    created_at: string
    updated_at: string
    deleted_at: string | null
}

export function useUsers(currentPage: Ref<number>, userType: 'admin' | 'magang' | 'mentor' | 'kepegawaian') {

    const config = useRuntimeConfig()
    const token = computed(() => useCookie('access_token').value)
    const pendingToggle = ref<string | null>(null)
    const pendingCreate = ref(false)
    const pendingDelete = ref<string | null>(null)
    const errors = ref<Record<string, string[]>>({})
    const pendingClearCache = ref(false)

    const {data: users, status, error, refresh, pending} = useAsyncData<ApiResponse<User>>(
        `users-${userType}-${currentPage.value}-${token.value}`,
        async () => {
            try {
                const response = await customFetch<ApiResponse<User>>(`${config.public.apiBase}/v1/users-${userType}?page=${currentPage.value}`, {
                    method: 'GET',
                    headers: {Authorization: `Bearer ${token.value}`}
                })
                return response
            } catch (err) {
                throw err
            }
        },
        {
            server: false,
            lazy: true,
            // immediate: false,
            getCachedData: (key) => useNuxtApp().payload.data[key] as ApiResponse<User> ?? null,
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

            watch: [token, currentPage],
        }
    )

    const createUser = async (userData: Partial<User>) => {
        pendingCreate.value = true;
        errors.value = {};

        try {
            const response = await customFetch<ApiResponse<User>>(`${config.public.apiBase}/v1/users`, {
                method: 'POST',
                headers: {
                    Authorization: `Bearer ${token.value}`
                },
                body: {...userData, role: userType}
            });

            // console.log("User berhasil dibuat:", response);
            // response.message
            // refresh()
            return response;
        } catch (error: any) {
            if (error?.data?.status === "error" && error?.data?.errors) {
                errors.value = error.data.errors;
            }
            throw error;
        } finally {
            pendingCreate.value = false;
        }
    }

    const toggleStatusUser = async (userId: string, currentStatus: string) => {
        pendingToggle.value = userId; // Tandai bahwa user ini sedang diproses

        try {
            const newStatus = currentStatus === 'active' ? 'inactive' : 'active';

            if (!token.value) {
                console.error("Token tidak ditemukan!");
                return;
            }

            const r = await customFetch(`${config.public.apiBase}/v1/toggle-user-status/${userId}`, {
                method: 'PUT',
                headers: {Authorization: `Bearer ${token.value}`},
                body: {status: newStatus}
            });

            if (!users.value?.data?.data || users.value.data.data.length === 0) {
                console.error("Data users tidak tersedia!");
                return;
            }

            refresh(); // Refresh data hanya jika berhasil
        } catch (error) {
            console.error("Gagal memperbarui status user:", error);
        } finally {
            pendingToggle.value = null; // Reset pending setelah selesai
        }
    }

    const deleteUser = async (userId: string) => {
        if (!confirm("Apakah Anda yakin ingin menghapus user ini?")) return;

        pendingDelete.value = userId; // Tandai bahwa user ini sedang dalam proses penghapusan

        try {
            const response = await customFetch(`${config.public.apiBase}/v1/users/${userId}`, {
                method: 'DELETE',
                headers: {
                    Authorization: `Bearer ${token.value}`
                }
            });

            // addNotification('success', response.message || 'User berhasil dihapus');
            refresh(); // Refresh data setelah penghapusan
        } catch (error: any) {
            console.error("Gagal menghapus user:", error);
            // addNotification('error', error?.data?.message || 'Terjadi kesalahan saat menghapus user');
        } finally {
            pendingDelete.value = null; // Reset status pending
        }
    }

    const clearCacheUser = async () => {
        pendingClearCache.value = true;
        try {
            const response = await customFetch(`${config.public.apiBase}/v1/clear-cache-users/${userType}`, {
                method: 'GET',
                headers: {
                    Authorization: `Bearer ${token.value}`
                }
            });
            refresh();
        } catch (error: any) {
            console.error("Gagal menghapus user:", error);
        } finally {
            pendingClearCache.value = false;
        }
    }

    return {
        users,
        status,
        error,
        refresh,
        pending,
        pendingToggle,
        toggleStatusUser,
        createUser,
        pendingCreate,
        pendingDelete,
        deleteUser,
        errors,
        clearCacheUser,
        pendingClearCache
    }
}
