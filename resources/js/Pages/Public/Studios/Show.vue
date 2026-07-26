<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import PublicLayout from '@/Layouts/PublicLayout.vue';
import ComicCard from '@/Components/Comic/ComicCard.vue';
import {
  CheckBadgeIcon,
  EyeIcon,
  BookOpenIcon,
  CalendarIcon,
  BuildingStorefrontIcon,
  StarIcon,
} from '@heroicons/vue/24/solid';

interface Publisher {
  id: number;
  name: string;
  username: string;
  brand_name: string;
  slug?: string;
  bio?: string;
  logo?: string;
  banner?: string;
  created_at?: string;
}

interface Comic {
  id: number;
  title: string;
  slug: string;
  cover_image: string;
  rating_average: number;
  total_views: number;
  status: string;
  genres?: Array<{ id: number; name: string; slug: string }>;
  publisher?: { name: string };
}

interface Paginator {
  data: Comic[];
  links: Array<{ url: string | null; label: string; active: boolean }>;
  total: number;
}

defineProps<{
  publisher: Publisher;
  comics: Paginator;
  totalViews: number;
  totalRatings: number;
  averageRating: number;
}>();
</script>

<template>
  <Head :title="`${publisher.brand_name} - Studio Profile`" />

  <PublicLayout>
    <!-- Studio Header & Banner -->
    <div class="relative w-full bg-slate-900 border-b border-slate-800">
      <!-- Banner Background -->
      <div v-if="publisher.banner" class="absolute inset-0 opacity-25 overflow-hidden">
        <img :src="publisher.banner" class="w-full h-full object-cover blur-sm" />
      </div>
      <div v-else class="absolute inset-0 bg-gradient-to-r from-sky-950 via-slate-900 to-indigo-950 opacity-80"></div>

      <div class="relative max-w-7xl mx-auto px-4 lg:px-8 py-12">
        <div class="flex flex-col md:flex-row items-center md:items-start gap-6 text-center md:text-left">
          <!-- Logo / Avatar -->
          <div class="w-28 h-28 sm:w-36 sm:h-36 rounded-3xl overflow-hidden bg-slate-950 border-4 border-slate-800 shadow-2xl shrink-0 flex items-center justify-center text-sky-400">
            <img v-if="publisher.logo" :src="publisher.logo" :alt="publisher.brand_name" class="w-full h-full object-cover" />
            <BuildingStorefrontIcon v-else class="w-16 h-16 text-slate-600" />
          </div>

          <!-- Studio Details -->
          <div class="flex-1 space-y-3">
            <div class="flex flex-wrap items-center justify-center md:justify-start gap-2">
              <span class="px-3 py-1 rounded-full text-xs font-bold bg-sky-500/10 text-sky-400 border border-sky-500/30 flex items-center gap-1">
                <CheckBadgeIcon class="w-4 h-4 text-sky-400" />
                Verified Creator Studio
              </span>
              <span v-if="publisher.created_at" class="text-xs text-slate-400 flex items-center gap-1">
                <CalendarIcon class="w-3.5 h-3.5" />
                Joined {{ publisher.created_at }}
              </span>
            </div>

            <h1 class="text-3xl sm:text-4xl font-extrabold text-white">
              {{ publisher.brand_name }}
            </h1>

            <p class="text-sm text-slate-300 max-w-2xl leading-relaxed">
              {{ publisher.bio || 'Selamat datang di halaman profil studio kami. Simak karya-karya komik original terbaru kami!' }}
            </p>

            <!-- Metrics Summary Badge -->
            <div class="flex flex-wrap items-center justify-center md:justify-start gap-4 pt-2 text-xs font-medium">
              <div class="bg-slate-950/80 border border-slate-800 rounded-xl px-4 py-2 flex items-center gap-2">
                <StarIcon class="w-4 h-4 text-amber-400 fill-amber-400 shrink-0" />
                <span class="text-slate-300">
                  Studio Rating: <strong class="text-amber-400 font-extrabold text-sm">{{ averageRating ? averageRating.toFixed(1) : '0.0' }}</strong> 
                  <span class="text-slate-400 font-semibold ml-1">({{ totalRatings ? totalRatings.toLocaleString() : 0 }} rating)</span>
                </span>
              </div>

              <div class="bg-slate-950/80 border border-slate-800 rounded-xl px-4 py-2 flex items-center gap-2">
                <BookOpenIcon class="w-4 h-4 text-sky-400 shrink-0" />
                <span class="text-slate-300">Total Series: <strong class="text-white font-bold">{{ comics.total }}</strong></span>
              </div>

              <div class="bg-slate-950/80 border border-slate-800 rounded-xl px-4 py-2 flex items-center gap-2">
                <EyeIcon class="w-4 h-4 text-indigo-400 shrink-0" />
                <span class="text-slate-300">Total Views: <strong class="text-white font-bold">{{ totalViews.toLocaleString() }}</strong></span>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Studio Comic Works Catalog -->
    <main class="max-w-7xl mx-auto px-4 lg:px-8 py-10 w-full flex-1 space-y-8">
      <div class="flex items-center justify-between border-b border-slate-800 pb-4">
        <div>
          <h2 class="text-2xl font-extrabold text-white">Published Webcomics</h2>
          <p class="text-xs text-slate-400 mt-1">Daftar serial komik karya {{ publisher.brand_name }}</p>
        </div>
        <span class="text-xs text-slate-400 font-semibold">
          {{ comics.total }} Titles
        </span>
      </div>

      <!-- Comics Grid -->
      <div v-if="comics.data && comics.data.length" class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-4 gap-6">
        <ComicCard v-for="comic in comics.data" :key="comic.id" :comic="comic" />
      </div>

      <div v-else class="bg-slate-900/50 border border-slate-800 rounded-2xl p-12 text-center text-slate-400">
        <p class="text-base font-medium text-slate-300">Belum ada komik yang dipublikasikan oleh studio ini.</p>
      </div>

      <!-- Pagination Links -->
      <div v-if="comics.links && comics.links.length > 3" class="flex justify-center items-center gap-1.5 pt-4">
        <Component
          v-for="(link, i) in comics.links"
          :key="i"
          :is="link.url ? Link : 'span'"
          :href="link.url || '#'"
          v-html="link.label"
          class="px-3.5 py-2 rounded-xl text-xs font-medium border transition"
          :class="[
            link.active
              ? 'bg-sky-600 border-sky-600 text-white font-bold'
              : link.url
              ? 'bg-slate-900 border-slate-800 text-slate-300 hover:border-slate-700'
              : 'bg-slate-950 border-slate-900 text-slate-600 cursor-not-allowed'
          ]"
        />
      </div>
    </main>
  </PublicLayout>
</template>
