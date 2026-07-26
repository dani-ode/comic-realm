<script setup lang="ts">
import { Link, usePage } from '@inertiajs/vue3';
import { computed } from 'vue';
import { EyeIcon } from '@heroicons/vue/24/outline';
import { StarIcon, HeartIcon as HeartSolidIcon } from '@heroicons/vue/24/solid';
import GenreBadge from './GenreBadge.vue';

interface Genre {
  id: number;
  name: string;
  slug: string;
}

interface Comic {
  id: number;
  title: string;
  slug: string;
  cover_image: string;
  rating_average: number;
  total_views: number;
  status: string;
  genres?: Genre[];
  publisher?: {
    name: string;
  };
}

const props = defineProps<{
  comic: Comic;
}>();

const page = usePage();

const isBookmarked = computed(() => {
  const ids = (page.props as any).bookmarkedComicIds as number[] | undefined;
  return ids ? ids.includes(props.comic.id) : false;
});
</script>

<template>
  <div class="group relative bg-slate-900 border border-slate-800 rounded-2xl overflow-hidden shadow-lg hover:border-sky-500/50 transition duration-300 flex flex-col h-full w-full">
    <!-- Image Cover — clicking goes to comic page -->
    <Link :href="`/comics/${comic.slug}`" class="relative aspect-[2/3] w-full overflow-hidden bg-slate-950 block shrink-0">
      <img
        :src="comic.cover_image"
        :alt="comic.title"
        class="h-full w-full object-cover group-hover:scale-105 transition duration-500"
        loading="lazy"
      />
      <!-- Rating Badge -->
      <div class="absolute top-2 right-2 bg-slate-950/80 backdrop-blur-md text-amber-400 font-bold text-[10px] px-2 py-0.5 rounded-full border border-amber-400/30 flex items-center gap-0.5">
        <StarIcon class="w-3 h-3 text-amber-400 fill-amber-400 shrink-0" />
        <span>{{ comic.rating_average ? comic.rating_average.toFixed(1) : '0.0' }}</span>
      </div>
      <!-- Bookmark Badge -->
      <div
        v-if="isBookmarked"
        class="absolute top-2 left-2 bg-rose-500/90 backdrop-blur-md text-white text-[10px] px-1.5 py-0.5 rounded-full border border-rose-400/50 flex items-center gap-0.5 shadow-lg shadow-rose-500/30"
        title="Bookmarked"
      >
        <HeartSolidIcon class="w-3 h-3 text-white fill-white shrink-0" />
      </div>
      <!-- Status Badge -->
      <div class="absolute bottom-2 left-2 bg-slate-950/80 backdrop-blur-md text-slate-300 text-[10px] px-2 py-0.5 rounded-md border border-slate-700 capitalize">
        {{ comic.status }}
      </div>
    </Link>

    <!-- Content -->
    <div class="p-2.5 flex flex-col flex-1">
      <!-- Genre badges — smaller for mobile -->
      <div class="flex flex-wrap gap-1 mb-1 min-h-[16px] items-center" v-if="comic.genres && comic.genres.length">
        <span
          v-for="genre in comic.genres.slice(0, 2)"
          :key="genre.id"
          class="text-[8px] sm:text-[9px] font-semibold px-1 py-0.5 rounded bg-sky-500/10 text-sky-400 border border-sky-500/20 leading-none"
        >
          {{ genre.name }}
        </span>
      </div>

      <!-- Title — smaller, link whole title -->
      <h3 class="text-xs font-bold text-white group-hover:text-sky-400 transition line-clamp-2 leading-snug">
        <Link :href="`/comics/${comic.slug}`">{{ comic.title }}</Link>
      </h3>

      <!-- Views -->
      <div class="mt-auto pt-2 flex items-center text-[10px] text-slate-500">
        <EyeIcon class="w-3 h-3 mr-0.5" />
        {{ comic.total_views ? comic.total_views.toLocaleString() : 0 }}
      </div>
    </div>
  </div>
</template>
