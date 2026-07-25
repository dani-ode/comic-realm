<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import ComicCard from '@/Components/Comic/ComicCard.vue';

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
  publisher?: { name: string };
}

defineProps<{
  featuredComics: Comic[];
  popularComics: Comic[];
  latestUpdates: Comic[];
  genres: Genre[];
}>();
</script>

<template>
  <Head title="Home - Read. Create. Publish." />

  <div class="min-h-screen bg-slate-950 text-slate-100 flex flex-col">
    <!-- Navbar Header -->
    <header class="sticky top-0 z-50 bg-slate-950/80 backdrop-blur-md border-b border-slate-800/80 px-4 lg:px-8 py-3.5 flex items-center justify-between">
      <Link href="/" class="text-xl font-extrabold bg-gradient-to-r from-sky-400 via-indigo-400 to-purple-400 bg-clip-text text-transparent">
        The ComicRealm
      </Link>

      <nav class="hidden md:flex items-center gap-6 text-sm font-medium">
        <Link href="/" class="text-sky-400">Home</Link>
        <Link href="/comics" class="text-slate-300 hover:text-white transition">Catalog</Link>
        <Link href="/comics?sort=popular" class="text-slate-300 hover:text-white transition">Popular</Link>
      </nav>

      <div class="flex items-center gap-3">
        <Link href="/login" class="text-sm font-medium px-4 py-2 rounded-xl text-slate-300 hover:text-white transition">
          Sign In
        </Link>
        <Link href="/register" class="text-sm font-semibold px-4 py-2 rounded-xl bg-sky-600 hover:bg-sky-500 text-white transition shadow-lg shadow-sky-600/30">
          Get Started
        </Link>
      </div>
    </header>

    <!-- Hero Banner -->
    <section class="relative bg-gradient-to-b from-slate-900 to-slate-950 py-16 px-4 lg:px-8 border-b border-slate-800/60">
      <div class="max-w-7xl mx-auto text-center">
        <div class="inline-block px-4 py-1.5 rounded-full bg-sky-500/10 border border-sky-500/30 text-sky-400 text-sm font-medium mb-6">
          ✨ Webcomic Marketplace & Reader Engine
        </div>
        <h1 class="text-4xl sm:text-6xl font-extrabold tracking-tight mb-6 bg-gradient-to-r from-white via-slate-100 to-slate-400 bg-clip-text text-transparent">
          Read Endless Webcomics. <br class="hidden sm:inline" />Support Independent Creators.
        </h1>
        <p class="text-slate-400 text-base sm:text-lg max-w-2xl mx-auto mb-8">
          Explore high-quality vertical webcomics, unlock chapters securely with TriPay Gateway, and follow top independent artists.
        </p>

        <div class="flex flex-wrap items-center justify-center gap-4">
          <Link href="/comics" class="px-7 py-3.5 rounded-xl bg-sky-600 hover:bg-sky-500 text-white font-semibold transition shadow-xl shadow-sky-600/30">
            Explore All Comics
          </Link>
          <Link href="/register" class="px-7 py-3.5 rounded-xl bg-slate-900 hover:bg-slate-800 border border-slate-700 text-white font-semibold transition">
            Become a Publisher
          </Link>
        </div>
      </div>
    </section>

    <!-- Main Content Container -->
    <main class="max-w-7xl mx-auto px-4 lg:px-8 py-12 space-y-16 w-full flex-1">

      <!-- Featured Comics Section -->
      <section v-if="featuredComics && featuredComics.length">
        <div class="flex items-center justify-between mb-6">
          <div>
            <h2 class="text-2xl font-bold text-white flex items-center gap-2">
              🔥 Featured Comics
            </h2>
            <p class="text-xs text-slate-400">Hand-picked webcomics recommended for you</p>
          </div>
          <Link href="/comics" class="text-sm font-semibold text-sky-400 hover:underline">View All →</Link>
        </div>

        <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-6 gap-5">
          <ComicCard v-for="comic in featuredComics" :key="comic.id" :comic="comic" />
        </div>
      </section>

      <!-- Popular Comics Section -->
      <section v-if="popularComics && popularComics.length">
        <div class="flex items-center justify-between mb-6">
          <div>
            <h2 class="text-2xl font-bold text-white flex items-center gap-2">
              ⭐ Top Rated & Popular
            </h2>
            <p class="text-xs text-slate-400">Most read webcomics by the community</p>
          </div>
          <Link href="/comics?sort=popular" class="text-sm font-semibold text-sky-400 hover:underline">View All →</Link>
        </div>

        <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-5 gap-5">
          <ComicCard v-for="comic in popularComics" :key="comic.id" :comic="comic" />
        </div>
      </section>

      <!-- Genres Quick Filter -->
      <section v-if="genres && genres.length" class="bg-slate-900/60 border border-slate-800 rounded-2xl p-6">
        <h3 class="text-lg font-bold text-white mb-4">Browse by Genre</h3>
        <div class="flex flex-wrap gap-2.5">
          <Link
            v-for="genre in genres"
            :key="genre.id"
            :href="`/comics?genre=${genre.slug}`"
            class="px-4 py-2 rounded-xl bg-slate-950 border border-slate-800 text-sm font-medium text-slate-300 hover:text-white hover:border-sky-500/50 hover:bg-slate-900 transition"
          >
            {{ genre.name }}
          </Link>
        </div>
      </section>

    </main>

    <!-- Footer -->
    <footer class="border-t border-slate-800/80 py-8 px-4 text-center text-xs text-slate-500">
      <p>© {{ new Date().getFullYear() }} The ComicRealm. All rights reserved. Powered by Laravel 13 & Inertia.js.</p>
    </footer>
  </div>
</template>
