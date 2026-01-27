<template>
  <footer class="bg-gray-800 text-gray-300">
    <div class="container mx-auto px-4 py-8">
      <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
        <!-- 교회 정보 -->
        <div>
          <h3 class="text-white font-bold text-lg mb-4">에센 한인교회</h3>
          <p class="text-sm mb-2">Ev. Koreanische Gemeinde in Essen e.V.</p>
          <p class="text-sm mb-2">
            <strong>주소:</strong><br>
            Friedrichstr. 17 45128 Essen, Deutschland
          </p>
        </div>

        <!-- 예배 안내 -->
        <div>
          <h3 class="text-white font-bold text-lg mb-4">예배 시간</h3>
          <ul class="space-y-2 text-sm">
            <li>주일 대예배: 매주 일요일 오후 3시</li>
            <li>수요 예배: 매주 수요일 저녁 7시</li>
          </ul>
        </div>

        <!-- 연락처 -->
        <div>
          <h3 class="text-white font-bold text-lg mb-4">연락처</h3>
          <p class="text-sm mb-2">
            <strong>이메일:</strong><br>
            <a
              href="mailto:juheaum@hanmail.net"
              class="text-church-light-400 hover:text-church-light-300 transition-colors"
            >
              juheaum@hanmail.net
            </a>
          </p>
        </div>
      </div>

      <!-- Copyright -->
      <div class="border-t border-gray-700 mt-8 pt-6 text-center text-sm">
        <p>&copy; {{ currentYear }} 에센한인교회. All rights reserved.</p>
      </div>
    </div>
  </footer>
</template>

<script setup>
import { computed } from 'vue'

const currentYear = computed(() => new Date().getFullYear())
</script>
