import { defineConfig } from 'vite'
import vue from '@vitejs/plugin-vue'

// https://vite.dev/config/
export default defineConfig({
  plugins: [vue()],
  server: {
    host: true, // 모든 네트워크 인터페이스에서 접속 허용
    port: 5173,
    allowedHosts: [
      'dev.local',
      'localhost',
      '.local' // *.local 도메인 모두 허용
    ],
    proxy: {
      '/api': {
        target: 'http://nginx',
        changeOrigin: true
      }
    }
  }
})
