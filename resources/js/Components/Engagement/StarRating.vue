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

const rating = ref(props.initialRating || 0);
const hoverRating = ref(0);
const isLoading = ref(false);

watch(() => props.initialRating, (val) => {
  rating.value = val || 0;
});

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
  <div class="flex items-center gap-0.5">
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
