<template>
  <div class="min-h-screen flex flex-col">
    <!-- Header -->
    <HeaderNav />

    <!-- Main Content -->
    <main class="flex-grow">
      <router-view />
    </main>

    <!-- Footer -->
    <FooterSection />
  </div>
</template>

<script setup>
import HeaderNav from '../components/HeaderNav.vue'
import FooterSection from '../components/FooterSection.vue'
</script>
