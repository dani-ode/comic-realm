<script setup lang="ts">
import { ref } from 'vue';
import axios from 'axios';

const props = defineProps<{
  comicId: number;
  initialRating?: number;
  readOnly?: boolean;
}>();

const rating = ref(props.initialRating || 0);
const hoverRating = ref(0);
const isLoading = ref(false);

const setRating = async (val: number) => {
  if (props.readOnly) return;
  isLoading.value = true;
  rating.value = val;
  try {
    await axios.post('/api/ratings', {
      comic_id: props.comicId,
      rating: val,
    });
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
  <div class="flex items-center gap-1">
    <button
      v-for="star in 5"
      :key="star"
      type="button"
      :disabled="readOnly || isLoading"
      @click="setRating(star)"
      @mouseenter="hoverRating = star"
      @mouseleave="hoverRating = 0"
      class="text-lg transition focus:outline-none"
      :class="[
        (hoverRating || rating) >= star ? 'text-amber-400 scale-110' : 'text-slate-700',
        readOnly ? 'cursor-default' : 'cursor-pointer hover:scale-125'
      ]"
    >
      ★
    </button>
  </div>
</template>
