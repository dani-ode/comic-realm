<script setup lang="ts">
import { watch, onUnmounted } from 'vue';
import { useLoading } from '@/composables/useLoading';

const { isFormSubmitting, formLoadingText } = useLoading();

// Prevent body scroll when fullpage loader is open
watch(
  isFormSubmitting,
  (active) => {
    if (typeof document !== 'undefined') {
      document.body.style.overflow = active ? 'hidden' : '';
    }
  },
  { immediate: true }
);

onUnmounted(() => {
  if (typeof document !== 'undefined') {
    document.body.style.overflow = '';
  }
});
</script>

<template>
  <Transition name="fade-popup">
    <div
      v-if="isFormSubmitting"
      class="fixed inset-0 z-[99999] flex items-center justify-center p-4 bg-slate-950/80 backdrop-blur-md select-none"
      role="dialog"
      aria-modal="true"
      aria-labelledby="loading-title"
    >
      <!-- Card Container -->
      <div
        class="relative w-full max-w-sm overflow-hidden rounded-3xl bg-slate-900/90 border border-slate-800 p-8 shadow-2xl shadow-sky-950/50 text-center transform transition-all"
      >
        <!-- Animated Background Radial Glow -->
        <div class="absolute -top-12 -left-12 w-32 h-32 bg-sky-500/20 rounded-full blur-2xl pointer-events-none"></div>
        <div class="absolute -bottom-12 -right-12 w-32 h-32 bg-indigo-500/20 rounded-full blur-2xl pointer-events-none"></div>

        <!-- Spinner & Brand Graphic -->
        <div class="relative flex items-center justify-center my-4">
          <!-- Outer Spinning Ring -->
          <div class="w-20 h-20 rounded-full border-4 border-sky-500/20 border-t-sky-400 border-r-indigo-500 animate-spin"></div>
          
          <!-- Inner Pulse Ring -->
          <div class="absolute w-14 h-14 rounded-full border-2 border-indigo-400/30 animate-ping"></div>

          <!-- Center Icon / Brand Logo -->
          <div class="absolute w-10 h-10 rounded-full bg-gradient-to-tr from-sky-500 to-indigo-600 flex items-center justify-center shadow-lg shadow-sky-500/30">
            <svg class="w-5 h-5 text-white animate-bounce" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z" />
            </svg>
          </div>
        </div>

        <!-- Dynamic Loading Title -->
        <h3 id="loading-title" class="text-xl font-bold text-white tracking-wide mt-4">
          {{ formLoadingText || 'Memproses...' }}
        </h3>

        <!-- Subtitle -->
        <p class="text-sm text-slate-400 mt-1">
          Mohon tunggu sebentar, permintaan Anda sedang diproses.
        </p>

        <!-- Dynamic Dots Animation -->
        <div class="flex items-center justify-center gap-1.5 mt-5">
          <span class="w-2 h-2 rounded-full bg-sky-400 animate-bounce [animation-delay:-0.3s]"></span>
          <span class="w-2 h-2 rounded-full bg-sky-400 animate-bounce [animation-delay:-0.15s]"></span>
          <span class="w-2 h-2 rounded-full bg-sky-400 animate-bounce"></span>
        </div>
      </div>
    </div>
  </Transition>
</template>

<style scoped>
.fade-popup-enter-active,
.fade-popup-leave-active {
  transition: opacity 0.3s cubic-bezier(0.4, 0, 0.2, 1), transform 0.3s cubic-bezier(0.4, 0, 0.2, 1);
}

.fade-popup-enter-from,
.fade-popup-leave-to {
  opacity: 0;
  transform: scale(0.95);
}
</style>
