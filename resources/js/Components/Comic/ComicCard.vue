<script setup lang="ts">
import { Link } from '@inertiajs/vue3';
import { EyeIcon } from '@heroicons/vue/24/outline';
import { StarIcon } from '@heroicons/vue/24/solid';
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

defineProps<{
  comic: Comic;
}>();
</script>

<template>
  <div class="group relative bg-slate-900 border border-slate-800 rounded-2xl overflow-hidden shadow-lg hover:border-sky-500/50 transition duration-300 flex flex-col">
    <!-- Image Cover -->
    <div class="relative aspect-[2/3] w-full overflow-hidden bg-slate-950">
      <img
        :src="comic.cover_image"
        :alt="comic.title"
        class="h-full w-full object-cover group-hover:scale-105 transition duration-500"
        loading="lazy"
      />
      <!-- Rating Badge -->
      <div class="absolute top-2.5 right-2.5 bg-slate-950/80 backdrop-blur-md text-amber-400 font-bold text-xs px-2.5 py-1 rounded-full border border-amber-400/30 flex items-center gap-1">
        <StarIcon class="w-3.5 h-3.5 text-amber-400 fill-amber-400 shrink-0" />
        <span>{{ comic.rating_average ? comic.rating_average.toFixed(1) : '0.0' }}</span>
      </div>
      <!-- Status Badge -->
      <div class="absolute bottom-2.5 left-2.5 bg-slate-950/80 backdrop-blur-md text-slate-300 text-xs px-2.5 py-1 rounded-md border border-slate-700 capitalize">
        {{ comic.status }}
      </div>
    </div>

    <!-- Content -->
    <div class="p-4 flex flex-col flex-1">
      <div class="flex flex-wrap gap-1 mb-2" v-if="comic.genres && comic.genres.length">
        <GenreBadge v-for="genre in comic.genres.slice(0, 2)" :key="genre.id" :genre="genre" />
      </div>

      <h3 class="text-base font-bold text-white group-hover:text-sky-400 transition line-clamp-1">
        <Link :href="`/comics/${comic.slug}`">
          {{ comic.title }}
        </Link>
      </h3>

      <p class="text-xs text-slate-400 mt-1" v-if="comic.publisher">
        By {{ comic.publisher.name }}
      </p>

      <div class="mt-auto pt-3 flex items-center justify-between text-xs text-slate-500 border-t border-slate-800/80">
        <span class="flex items-center"><EyeIcon class="w-4 h-4 mr-1" /> {{ comic.total_views ? comic.total_views.toLocaleString() : 0 }} Views</span>
        <Link :href="`/comics/${comic.slug}`" class="text-sky-400 font-medium hover:underline">
          Read Now →
        </Link>
      </div>
    </div>
  </div>
</template>
