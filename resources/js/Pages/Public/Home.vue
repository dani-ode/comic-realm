<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import PublicLayout from '@/Layouts/PublicLayout.vue';
import ComicCard from '@/Components/Comic/ComicCard.vue';
import GenreBadge from '@/Components/Comic/GenreBadge.vue';
import {
  FireIcon,
  SparklesIcon,
  BoltIcon,
  ArrowRightIcon,
  PencilSquareIcon,
  RectangleGroupIcon,
} from '@heroicons/vue/24/outline';
import { FireIcon as FireSolid } from '@heroicons/vue/24/solid';

interface Comic {
  id: number;
  title: string;
  slug: string;
  cover_image: string;
  rating_average: number;
  total_views: number;
  status: string;
  author_name?: string;
  genres?: Array<{ id: number; name: string; slug: string }>;
  chapters_count?: number;
}

interface Genre {
  id: number;
  name: string;
  slug: string;
  color_code?: string;
}

defineProps<{
  featuredComics: Comic[];
  popularComics: Comic[];
  latestComics: Comic[];
  genres: Genre[];
}>();
</script>

<template>
  <Head title="Home - Premium Webcomics & Manga" />

  <PublicLayout>
    <div class="space-y-12 py-8">
      <!-- Hero Banner -->
      <section class="max-w-7xl mx-auto px-4 lg:px-8">
        <div class="relative overflow-hidden rounded-3xl bg-gradient-to-r from-sky-900/60 via-indigo-900/40 to-slate-900 border border-slate-800 p-8 lg:p-12 shadow-2xl flex flex-col md:flex-row items-center justify-between gap-8">
          <div class="space-y-4 max-w-xl text-center md:text-left z-10">
            <span class="inline-flex items-center gap-1.5 px-3 py-1 text-xs font-bold rounded-full bg-sky-500/10 text-sky-400 border border-sky-500/30">
              <BoltIcon class="w-3.5 h-3.5" />
              Webtoon Reader Engine 2.0
            </span>
            <h1 class="text-3xl lg:text-5xl font-black text-white leading-tight">
              Discover Next-Gen Vertical Webcomics
            </h1>
            <p class="text-sm lg:text-base text-slate-300">
              Read free daily updates or unlock premium chapters instantly with TriPay closed payments (QRIS, VA, E-Wallet).
            </p>
            <div class="pt-2 flex flex-wrap gap-4 justify-center md:justify-start">
              <Link href="/comics" class="flex items-center gap-2 px-6 py-3.5 rounded-xl font-bold text-sm bg-sky-600 hover:bg-sky-500 text-white transition shadow-xl shadow-sky-600/30">
                Explore All Comics
                <ArrowRightIcon class="w-4 h-4" />
              </Link>
              <Link
                v-if="($page.props.auth as any)?.user?.role === 'admin'"
                href="/admin/dashboard"
                class="flex items-center gap-2 px-6 py-3.5 rounded-xl font-bold text-sm bg-sky-900/40 border border-sky-500/30 text-sky-300 hover:text-white hover:bg-sky-900/60 transition"
              >
                <RectangleGroupIcon class="w-4 h-4 text-sky-400" />
                Admin Dashboard
              </Link>
              <Link
                v-else-if="($page.props.auth as any)?.user?.role === 'publisher'"
                href="/publisher/dashboard"
                class="flex items-center gap-2 px-6 py-3.5 rounded-xl font-bold text-sm bg-slate-900 border border-slate-800 text-slate-300 hover:text-white transition"
              >
                <PencilSquareIcon class="w-4 h-4" />
                Publisher Dashboard
              </Link>
              <Link
                v-else
                href="/publisher/apply"
                class="flex items-center gap-2 px-6 py-3.5 rounded-xl font-bold text-sm bg-slate-900 border border-slate-800 text-slate-300 hover:text-white transition"
              >
                <PencilSquareIcon class="w-4 h-4" />
                Publish Your Webcomic
              </Link>
            </div>
          </div>
        </div>
      </section>

      <!-- Genres Bar -->
      <section class="max-w-7xl mx-auto px-4 lg:px-8">
        <div class="flex items-center gap-2 overflow-x-auto pb-2 scrollbar-none">
          <GenreBadge v-for="genre in genres" :key="genre.id" :genre="genre" />
        </div>
      </section>

      <!-- Trending / Featured Comics -->
      <section class="max-w-7xl mx-auto px-4 lg:px-8 space-y-6">
        <div class="flex items-center justify-between border-b border-slate-800/80 pb-4">
          <div>
            <h2 class="text-2xl font-black text-white flex items-center gap-2">
              <FireSolid class="w-6 h-6 text-orange-500" />
              Trending
            </h2>
            <p class="text-xs text-slate-400 mt-0.5">Most read series this week</p>
          </div>
          <Link href="/comics" class="flex items-center gap-1 text-xs font-bold text-sky-400 hover:underline">
            View All <ArrowRightIcon class="w-3.5 h-3.5" />
          </Link>
        </div>

        <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-6 gap-5">
          <ComicCard v-for="comic in featuredComics" :key="comic.id" :comic="comic" />
        </div>
      </section>

      <!-- Latest Released -->
      <section class="max-w-7xl mx-auto px-4 lg:px-8 space-y-6">
        <div class="flex items-center justify-between border-b border-slate-800/80 pb-4">
          <div>
            <h2 class="text-2xl font-black text-white flex items-center gap-2">
              <SparklesIcon class="w-6 h-6 text-sky-400" />
              Fresh Chapter
            </h2>
            <p class="text-xs text-slate-400 mt-0.5">Recently published webcomic episodes</p>
          </div>
        </div>

        <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-6 gap-5">
          <ComicCard v-for="comic in latestComics" :key="comic.id" :comic="comic" />
        </div>
      </section>
    </div>
  </PublicLayout>
</template>
