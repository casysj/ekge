<template>
  <div class="bg-gray-50 min-h-screen py-8">
    <div class="container mx-auto px-4 max-w-4xl">
      <!-- 교회 소개 -->
      <div class="bg-white rounded-lg shadow-md p-8 mb-6">
        <h1 class="text-3xl font-bold text-gray-800 mb-6">교회 소개</h1>

        <div class="prose max-w-none">
          <h2 class="text-2xl font-semibold text-church-green-500 mb-4">에센 한인교회</h2>
          <p class="text-gray-700 leading-relaxed mb-4">
            에센 한인교회는 독일 에센 지역에 거주하는 한인들을 위한 교회입니다.
            매주 40-50명의 성도들이 함께 예배를 드리며 교제하고 있습니다.
          </p>
          <p class="text-gray-700 leading-relaxed mb-6">
            Ex.Koreanische Gemeinde in Essen e.V.
          </p>

          <h3 class="text-xl font-semibold text-gray-800 mt-8 mb-3">예배 시간</h3>
          <ul class="list-disc list-inside space-y-2 text-gray-700 mb-6">
            <li><strong>주일 대예배:</strong> 매주 일요일 오후 3시</li>
            <li><strong>금요 기도회:</strong> 매주 금요일 저녁 7시</li>
          </ul>

          <h3 class="text-xl font-semibold text-gray-800 mt-8 mb-3">오시는 길</h3>
          <div class="bg-gray-50 p-4 rounded-lg mb-6">
            <p class="text-gray-700">
              <strong>주소:</strong><br>
              Friedrichstr. 17<br>
              45128 Essen, Deutschland
            </p>
          </div>

          <h3 class="text-xl font-semibold text-gray-800 mt-8 mb-3">연락처</h3>
          <div class="bg-gray-50 p-4 rounded-lg">
            <p class="text-gray-700">
              <strong>이메일:</strong>
              <a href="mailto:juheasum@hanmail.net" class="text-church-green-500 hover:underline">
                juheasum@hanmail.net
              </a>
            </p>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
// 교회 소개 페이지
</script>

<style scoped>
.prose h2 {
  margin-top: 0;
}

.prose ul {
  margin-left: 1.5rem;
}
</style>
