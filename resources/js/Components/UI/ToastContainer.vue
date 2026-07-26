<script setup lang="ts">
import { TransitionGroup } from 'vue';
import { CheckCircleIcon, ExclamationTriangleIcon, XCircleIcon, InformationCircleIcon, XMarkIcon } from '@heroicons/vue/24/solid';
import { useToast, type Toast } from '@/composables/useToast';

const { toasts, dismiss } = useToast();

const icons = {
  success: CheckCircleIcon,
  error: XCircleIcon,
  warning: ExclamationTriangleIcon,
  info: InformationCircleIcon,
};

const styles = {
  success: {
    bg: 'bg-emerald-950/80 border-emerald-500/40',
    icon: 'text-emerald-400',
    text: 'text-emerald-100',
    bar: 'bg-emerald-500',
  },
  error: {
    bg: 'bg-rose-950/80 border-rose-500/40',
    icon: 'text-rose-400',
    text: 'text-rose-100',
    bar: 'bg-rose-500',
  },
  warning: {
    bg: 'bg-amber-950/80 border-amber-500/40',
    icon: 'text-amber-400',
    text: 'text-amber-100',
    bar: 'bg-amber-500',
  },
  info: {
    bg: 'bg-sky-950/80 border-sky-500/40',
    icon: 'text-sky-400',
    text: 'text-sky-100',
    bar: 'bg-sky-500',
  },
};
</script>

<template>
  <!-- Fixed container, top-right, above everything -->
  <div class="fixed top-20 right-4 z-[9999] flex flex-col gap-3 pointer-events-none w-full max-w-sm">
    <TransitionGroup
      name="toast"
      tag="div"
      class="flex flex-col gap-3"
    >
      <div
        v-for="toast in toasts"
        :key="toast.id"
        class="pointer-events-auto relative overflow-hidden rounded-2xl border backdrop-blur-xl shadow-2xl flex items-start gap-3 px-4 py-3.5 cursor-pointer"
        :class="styles[toast.type].bg"
        @click="dismiss(toast.id)"
      >
        <!-- Icon -->
        <component
          :is="icons[toast.type]"
          class="w-5 h-5 shrink-0 mt-0.5"
          :class="styles[toast.type].icon"
        />

        <!-- Message -->
        <p class="text-sm font-medium leading-snug flex-1" :class="styles[toast.type].text">
          {{ toast.message }}
        </p>

        <!-- Close -->
        <button
          @click.stop="dismiss(toast.id)"
          class="shrink-0 opacity-50 hover:opacity-100 transition"
          :class="styles[toast.type].icon"
        >
          <XMarkIcon class="w-4 h-4" />
        </button>
      </div>
    </TransitionGroup>
  </div>
</template>

<style scoped>
/* Slide down from top + fade in */
.toast-enter-active {
  transition: all 0.35s cubic-bezier(0.16, 1, 0.3, 1);
}
.toast-leave-active {
  transition: all 0.25s ease-in;
}
.toast-enter-from {
  opacity: 0;
  transform: translateY(-24px) scale(0.97);
}
.toast-leave-to {
  opacity: 0;
  transform: translateX(20px) scale(0.97);
}
.toast-move {
  transition: transform 0.3s ease;
}
</style>
