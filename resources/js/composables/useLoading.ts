import { ref } from 'vue';
import { router } from '@inertiajs/vue3';

const isNavigating = ref(false);
const navigationProgress = ref(0);

const isFormSubmitting = ref(false);
const formLoadingText = ref('Memproses...');

let progressInterval: ReturnType<typeof setInterval> | null = null;
let isInitialized = false;

function startProgressTimer() {
  if (progressInterval) clearInterval(progressInterval);
  navigationProgress.value = 15;

  progressInterval = setInterval(() => {
    if (navigationProgress.value < 90) {
      // Gradually increment progress, slowing down as it reaches 90%
      const step = Math.max(1, (90 - navigationProgress.value) / 10);
      navigationProgress.value = Math.min(90, navigationProgress.value + step);
    } else {
      if (progressInterval) clearInterval(progressInterval);
    }
  }, 120);
}

function stopProgressTimer(finish = true) {
  if (progressInterval) {
    clearInterval(progressInterval);
    progressInterval = null;
  }

  if (finish) {
    navigationProgress.value = 100;
    setTimeout(() => {
      isNavigating.value = false;
      setTimeout(() => {
        navigationProgress.value = 0;
      }, 200);
    }, 250);
  } else {
    isNavigating.value = false;
    navigationProgress.value = 0;
  }
}

export function initGlobalLoadingListeners() {
  if (isInitialized) return;
  isInitialized = true;

  router.on('start', (event) => {
    const visit = event.detail.visit;
    const method = (visit.method || 'get').toLowerCase();

    if (method === 'get') {
      // Page navigation link click
      isNavigating.value = true;
      startProgressTimer();
    } else {
      // Form submission (POST, PUT, PATCH, DELETE)
      const customText = (visit.data as any)?._loadingText || 'Memproses...';
      formLoadingText.value = customText;
      isFormSubmitting.value = true;
    }
  });

  router.on('progress', (event) => {
    if (event.detail.progress && event.detail.progress.percentage) {
      navigationProgress.value = Math.max(navigationProgress.value, event.detail.progress.percentage);
    }
  });

  router.on('finish', () => {
    if (isNavigating.value) {
      stopProgressTimer(true);
    }
    isFormSubmitting.value = false;
  });

  router.on('cancel', () => {
    if (isNavigating.value) {
      stopProgressTimer(false);
    }
    isFormSubmitting.value = false;
  });
}

export function useLoading() {
  // Return global reactive state and helper methods for manual control
  return {
    isNavigating,
    navigationProgress,
    isFormSubmitting,
    formLoadingText,

    startPageLoading: () => {
      isNavigating.value = true;
      startProgressTimer();
    },
    finishPageLoading: () => {
      stopProgressTimer(true);
    },
    showFormLoading: (text = 'Memproses...') => {
      formLoadingText.value = text;
      isFormSubmitting.value = true;
    },
    hideFormLoading: () => {
      isFormSubmitting.value = false;
    },
  };
}
