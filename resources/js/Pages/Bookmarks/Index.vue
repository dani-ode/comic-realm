<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import PublicLayout from '@/Layouts/PublicLayout.vue';
import ComicCard from '@/Components/Comic/ComicCard.vue';
import { BookmarkIcon, HeartIcon } from '@heroicons/vue/24/outline';

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

interface BookmarkItem {
  id: number;
  created_at: string;
  comic: Comic;
}

interface Paginator {
  data: BookmarkItem[];
  links: Array<{ url: string | null; label: string; active: boolean }>;
}

defineProps<{
  bookmarks: Paginator;
}>();
</script>

<template>
  <Head title="My Bookmarked Comics" />

  <PublicLayout>
    <main class="max-w-7xl mx-auto px-4 lg:px-8 py-10 w-full flex-1 space-y-8">
      <div>
        <h1 class="text-3xl font-extrabold text-white flex items-center gap-3">
          <HeartIcon class="w-8 h-8 text-rose-500" />
          My Bookmarks
        </h1>
        <p class="text-sm text-slate-400 mt-1">
          Your personal collection of saved webcomics and manga
        </p>
      </div>

      <div v-if="bookmarks.data && bookmarks.data.length" class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-6 gap-5">
        <ComicCard
          v-for="item in bookmarks.data"
          :key="item.id"
          :comic="item.comic"
        />
      </div>

      <div v-else class="bg-slate-900/40 border border-slate-800 rounded-2xl p-16 text-center space-y-4">
        <div class="flex justify-center">
          <BookmarkIcon class="w-16 h-16 text-slate-600" />
        </div>
        <h2 class="text-xl font-bold text-white">No Bookmarks Saved Yet</h2>
        <p class="text-sm text-slate-400 max-w-md mx-auto">
          Start bookmarking your favorite webcomic series to get quick access whenever new chapters release.
        </p>
        <div class="pt-2">
          <Link href="/comics" class="px-6 py-3 rounded-xl bg-sky-600 hover:bg-sky-500 text-white font-semibold text-sm transition inline-block">
            Explore Comic Catalog
          </Link>
        </div>
      </div>
    </main>
  </PublicLayout>
</template>
