<script setup lang="ts">
import { computed } from 'vue';
import { Link } from '@inertiajs/vue3';

const props = defineProps<{
  name?: string;
  slug?: string;
  genre?: {
    name: string;
    slug?: string;
  };
  clickable?: boolean;
}>();

const genreName = computed(() => props.genre ? props.genre.name : (props.name || ''));
const genreSlug = computed(() => {
  if (props.genre?.slug) return props.genre.slug;
  if (props.slug) return props.slug;
  if (genreName.value) return genreName.value.toLowerCase().replace(/[^a-z0-9]+/g, '-').replace(/(^-|-$)/g, '');
  return '';
});

const isClickable = computed(() => props.clickable !== false);
</script>

<template>
  <Link
    v-if="isClickable && genreSlug"
    :href="`/comics?genre=${genreSlug}`"
    @click.stop
    class="inline-block px-3 py-1.5 text-xs font-semibold rounded-xl bg-sky-500/10 text-sky-400 border border-sky-500/20 hover:bg-sky-500/20 hover:border-sky-500/40 hover:scale-105 transition shadow-sm whitespace-nowrap cursor-pointer relative z-20"
  >
    {{ genreName }}
  </Link>
  <span
    v-else
    class="inline-block px-3 py-1.5 text-xs font-semibold rounded-xl bg-sky-500/10 text-sky-400 border border-sky-500/20 whitespace-nowrap"
  >
    {{ genreName }}
  </span>
</template>
