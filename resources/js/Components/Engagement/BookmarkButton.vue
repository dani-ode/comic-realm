<script setup lang="ts">
import { ref, watch } from 'vue';
import axios from 'axios';
import { HeartIcon as HeartIconOutline } from '@heroicons/vue/24/outline';
import { HeartIcon as HeartIconSolid } from '@heroicons/vue/24/solid';

const props = defineProps<{
  comicId: number;
  initialBookmarked?: boolean;
}>();

const isBookmarked = ref(props.initialBookmarked || false);
const isLoading = ref(false);

watch(() => props.initialBookmarked, (val) => {
  isBookmarked.value = !!val;
});

const toggleBookmark = async () => {
  isLoading.value = true;
  try {
    const res = await axios.post('/api/bookmarks/toggle', { comic_id: props.comicId });
    if (res.data && res.data.success) {
      isBookmarked.value = res.data.bookmarked;
    }
  } catch (err: any) {
    if (err.response && err.response.status === 401) {
      window.location.href = '/login';
    }
  } finally {
    isLoading.value = false;
  }
};
</script>

<template>
  <button
    @click="toggleBookmark"
    :disabled="isLoading"
    class="px-4 py-2 rounded-xl font-semibold text-xs flex items-center gap-2 transition border"
    :class="[
      isBookmarked
        ? 'bg-rose-500/10 border-rose-500/30 text-rose-400 hover:bg-rose-500/20'
        : 'bg-slate-900 border-slate-800 text-slate-300 hover:text-white hover:border-slate-700'
    ]"
  >
    <HeartIconSolid v-if="isBookmarked" class="w-4 h-4 text-rose-500 fill-rose-500 shrink-0" />
    <HeartIconOutline v-else class="w-4 h-4 text-slate-400 shrink-0" />
    <span>{{ isBookmarked ? 'Bookmarked' : 'Bookmark' }}</span>
  </button>
</template>
