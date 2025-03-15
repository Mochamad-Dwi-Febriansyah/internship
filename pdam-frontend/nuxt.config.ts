// https://nuxt.com/docs/api/configuration/nuxt-config
import { defineNuxtConfig } from 'nuxt/config';

export default defineNuxtConfig({
  compatibilityDate: '2024-11-01',
  devtools: { enabled: true },
  future: {
    compatibilityVersion: 4,
  },
  experimental: {
    scanPageMeta: 'after-resolve',
    sharedPrerenderData: false,
    compileTemplate: true,
    resetAsyncDataToUndefined: true,
    templateUtils: true,
    relativeWatchPaths: true,
    normalizeComponentNames: false,
    spaLoadingTemplateLocation: 'within',
    defaults: {
      useAsyncData: {
        deep: true
      }
    }
  },
  unhead: {
    renderSSRHeadOptions: {
      omitLineBreaks: false
    }
  },
  // modules: ['@nuxtjs/tailwindcss', "@nuxtjs/google-fonts", '@nuxt/image','@nuxt/icon','nuxt-swiper', '@vee-validate/nuxt', '@pinia/nuxt', '@i2d/nuxt-pdf-frame','nuxt-tiptap-editor'],
  modules: [
    '@nuxtjs/tailwindcss',
    ['@nuxtjs/google-fonts', {
      fontFamily: { 
        Satoshi: ['Satoshi', 'sans-serif'], // Ini bikin utility class `font-satoshi`
      },
    }],
    '@nuxt/image',
    '@nuxt/icon',
    'nuxt-swiper',
    '@vee-validate/nuxt',
    '@pinia/nuxt',
    '@i2d/nuxt-pdf-frame',
    'nuxt-tiptap-editor',
    ['@nuxtjs/tailwindcss', {
      exposeConfig: true,
      viewer: true,
      // and more...
    }]
  ],

  app: {
    head: {
      link: [
        { rel: 'icon', type: 'image/x-icon', href: '/favicon.ico' },
        {
          rel: 'stylesheet',
          href: 'https://api.fontshare.com/v2/css?f[]=satoshi@400,500,700,900&display=swap',
        },
      ]
    }, 
  },
  typescript: {
    typeCheck: true
  },
  runtimeConfig: {
    public: {
      apiBase: process.env.NUXT_PUBLIC_API_BASE,
      storage: process.env.NUXT_PUBLIC_STORAGE,
      apiBaseRegion: process.env.NUXT_PUBLIC_API_REGION,
      apiKeyBaseRegion: process.env.NUXT_PUBLIC_API_KEY_REGION,
    }
  },   
  css: [
    './assets/css/style.css'
  ],
  vue: {
    compilerOptions: {
      isCustomElement: (tag: any) => tag.startsWith('swiper-'),
    },
  },
})