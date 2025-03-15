
import { useCookie } from '#app'
import { navigateTo } from '#app' 

export function useCustomFetch() { 
  return new Proxy($fetch, {
      apply: async (target, thisArg, args) => {
        const token = useCookie('access_token') 
        const { refreshAccessToken } = useAuth()
        //aktifkan untuk skip page ngrok
             // Tambahkan headers secara otomatis ke setiap request
            const defaultOptions = args[1] || {} // Ambil opsi request
            args[1] = {
                ...defaultOptions,
                headers: {
                Authorization: `Bearer ${token.value}`,
                "ngrok-skip-browser-warning": "69420",
                ...defaultOptions.headers, // Pastikan tidak menimpa header lain
                },
            }

            
          try {
              console.log("🔍 Interceptor aktif - Cek request...")
              return await Reflect.apply(target, thisArg, args)
          } catch (error: any) {
              if (error?.status === 401 && error?.data?.message === "Token telah kedaluwarsa") {
                  console.warn("🔄 Token expired, attempting refresh...")

                  try {
                        await refreshAccessToken();
                      return await Reflect.apply(target, thisArg, args) // 🔄 Ulangi request setelah refresh sukses
                  } catch (refreshError) {
                      console.error("❌ Refresh token failed, logging out...")
                      token.value = null
                      return await navigateTo('/signin')
                  }
              } else if(error?.status === 403 && error?.data?.message === "Unauthorized"){
                // token.value = null
                // return await navigateTo('/signin')
                return navigateTo('/unauthorized')
              }
              throw error
          }
      }
  })
}