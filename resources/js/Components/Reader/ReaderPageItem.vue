<script setup lang="ts">
import { ref, onMounted } from 'vue';

interface Page {
  id: number;
  page_number: number;
  image_url?: string;
  image_path: string;
}

const props = defineProps<{
  page: Page;
  isFirstPage?: boolean;
}>();

const isVisible = ref(props.isFirstPage || false);
const isLoading = ref(true);
const isError = ref(false);
const pageElement = ref<HTMLElement | null>(null);

onMounted(() => {
  if (props.isFirstPage) {
    return;
  }

  // Lazy loading via IntersectionObserver
  if ('IntersectionObserver' in window && pageElement.value) {
    const observer = new IntersectionObserver((entries) => {
      entries.forEach((entry) => {
        if (entry.isIntersecting) {
          isVisible.value = true;
          observer.unobserve(entry.target);
        }
      });
    }, { rootMargin: '300px 0px' });

    observer.observe(pageElement.value);
  } else {
    isVisible.value = true;
  }
});

const onImageLoad = () => {
  isLoading.value = false;
};

const onImageError = () => {
  isLoading.value = false;
  isError.value = true;
};

const getImageUrl = () => {
  if (props.page.image_url) return props.page.image_url;
  return `/storage/${props.page.image_path}`;
};
</script>

<template>
  <div
    ref="pageElement"
    :data-page="page.page_number"
    class="relative w-full max-w-3xl mx-auto min-h-[400px] bg-slate-950 flex items-center justify-center border-b border-slate-900/40"
  >
    <!-- Placeholder Skeleton Loader -->
    <div v-if="isLoading && isVisible" class="absolute inset-0 bg-slate-900 animate-pulse flex flex-col items-center justify-center text-slate-600 text-xs">
      <span>Loading Page {{ page.page_number }}...</span>
    </div>

    <!-- Error Fallback -->
    <div v-if="isError" class="py-12 text-center text-rose-400 text-xs bg-slate-900/60 rounded-xl w-full my-4 border border-rose-500/20">
      <p>Failed to load Page {{ page.page_number }}</p>
    </div>

    <!-- WebP Image Render -->
    <img
      v-if="isVisible"
      :src="getImageUrl()"
      :alt="`Page ${page.page_number}`"
      :loading="isFirstPage ? 'eager' : 'lazy'"
      :fetchpriority="isFirstPage ? 'high' : 'low'"
      decoding="async"
      class="w-full h-auto block select-none"
      @load="onImageLoad"
      @error="onImageError"
    />
  </div>
</template>
