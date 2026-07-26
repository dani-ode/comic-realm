<script setup lang="ts">
import { ref, watch } from 'vue';
import axios from 'axios';
import { StarIcon as StarIconOutline } from '@heroicons/vue/24/outline';
import { StarIcon as StarIconSolid } from '@heroicons/vue/24/solid';

const props = defineProps<{
  comicId: number;
  initialRating?: number;
  readOnly?: boolean;
}>();

const emit = defineEmits<{
  (e: 'updated', payload: { user_rating: number; rating_average: number; total_ratings: number }): void;
}>();

const rating = ref(props.initialRating || 0);
const hoverRating = ref(0);
const isLoading = ref(false);

watch(() => props.initialRating, (val) => {
  rating.value = val || 0;
});

const setRating = async (val: number) => {
  if (props.readOnly || isLoading.value) return;
  isLoading.value = true;

  // Re-clicking the current active star cancels the rating
  const isCanceling = rating.value === val;

  try {
    if (isCanceling) {
      const res = await axios.post('/api/ratings/cancel', { comic_id: props.comicId });
      if (res.data && res.data.success) {
        rating.value = 0;
        emit('updated', {
          user_rating: 0,
          rating_average: res.data.rating_average,
          total_ratings: res.data.total_ratings,
        });
      }
    } else {
      const res = await axios.post('/api/ratings', {
        comic_id: props.comicId,
        rating: val,
      });
      if (res.data && res.data.success) {
        rating.value = val;
        emit('updated', {
          user_rating: val,
          rating_average: res.data.rating_average,
          total_ratings: res.data.total_ratings,
        });
      }
    }
  } catch (err: any) {
    if (err.response && err.response.status === 401) {
      window.location.href = '/login?redirect=' + encodeURIComponent(window.location.pathname + window.location.search);
    }
  } finally {
    isLoading.value = false;
  }
};
</script>

<template>
  <div class="flex items-center gap-0.5" :title="rating > 0 ? `Rating Anda: ${rating} ★ (Klik lagi untuk membatalkan)` : 'Beri Rating'">
    <button
      v-for="star in 5"
      :key="star"
      type="button"
      :disabled="readOnly || isLoading"
      @click="setRating(star)"
      @mouseenter="hoverRating = star"
      @mouseleave="hoverRating = 0"
      class="p-0.5 transition focus:outline-none"
      :class="[
        readOnly ? 'cursor-default' : 'cursor-pointer hover:scale-125'
      ]"
    >
      <StarIconSolid
        v-if="(hoverRating || rating) >= star"
        class="w-4 h-4 text-amber-400 fill-amber-400"
      />
      <StarIconOutline
        v-else
        class="w-4 h-4 text-slate-700"
      />
    </button>
  </div>
</template>
