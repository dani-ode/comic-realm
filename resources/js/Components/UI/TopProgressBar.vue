<script setup lang="ts">
import { useLoading } from '@/composables/useLoading';

const { isNavigating, navigationProgress } = useLoading();
</script>

<template>
  <Transition name="fade-bar">
    <div
      v-if="isNavigating || navigationProgress > 0"
      class="fixed top-0 left-0 right-0 z-[99999] pointer-events-none h-1 overflow-hidden"
    >
      <!-- Background track glow -->
      <div class="absolute inset-0 bg-sky-500/20 backdrop-blur-xs"></div>

      <!-- Main progress bar -->
      <div
        class="h-full bg-gradient-to-r from-sky-500 via-indigo-500 to-amber-400 transition-all duration-200 ease-out shadow-[0_0_12px_rgba(56,189,248,0.9)] relative"
        :style="{ width: `${navigationProgress}%` }"
      >
        <!-- Shimmer & Glowing end flare -->
        <div class="absolute right-0 top-0 bottom-0 w-8 bg-gradient-to-r from-transparent to-white/80 animate-pulse"></div>
        <div class="absolute right-0 top-1/2 -translate-y-1/2 w-3 h-3 bg-sky-300 rounded-full blur-[2px] shadow-[0_0_10px_#38bdf8]"></div>
      </div>
    </div>
  </Transition>
</template>

<style scoped>
.fade-bar-enter-active,
.fade-bar-leave-active {
  transition: opacity 0.25s ease;
}

.fade-bar-enter-from,
.fade-bar-leave-to {
  opacity: 0;
}
</style>
