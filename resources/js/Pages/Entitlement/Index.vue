<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';

interface Entitlement {
  id: number;
  granted_at: string;
  comic: {
    id: number;
    title: string;
    slug: string;
    cover_image: string;
  };
  chapter: {
    id: number;
    title: string;
    chapter_number: number;
  };
}

interface Paginator {
  data: Entitlement[];
  links: Array<{ url: string | null; label: string; active: boolean }>;
}

defineProps<{
  entitlements: Paginator;
}>();
</script>

<template>
  <Head title="My Library - Purchased Chapters" />

  <div class="min-h-screen bg-slate-950 text-slate-100 flex flex-col">
    <!-- Navbar Header -->
    <header class="sticky top-0 z-50 bg-slate-950/80 backdrop-blur-md border-b border-slate-800/80 px-4 lg:px-8 py-3.5 flex items-center justify-between">
      <Link href="/" class="text-xl font-extrabold bg-gradient-to-r from-sky-400 via-indigo-400 to-purple-400 bg-clip-text text-transparent">
        The ComicRealm
      </Link>

      <nav class="hidden md:flex items-center gap-6 text-sm font-medium">
        <Link href="/" class="text-slate-300 hover:text-white transition">Home</Link>
        <Link href="/comics" class="text-slate-300 hover:text-white transition">Catalog</Link>
        <Link href="/library" class="text-sky-400 font-bold">My Library 📚</Link>
      </nav>

      <div class="flex items-center gap-3">
        <Link href="/cart" class="text-xs font-semibold px-4 py-2 rounded-xl bg-slate-900 border border-slate-800 text-slate-300">
          Cart 🛒
        </Link>
      </div>
    </header>

    <main class="max-w-7xl mx-auto px-4 lg:px-8 py-10 w-full flex-1 space-y-8">
      <div>
        <h1 class="text-3xl font-extrabold text-white flex items-center gap-3">
          📚 My Library
        </h1>
        <p class="text-sm text-slate-400 mt-1">All your purchased webcomic chapters and digital entitlements</p>
      </div>

      <div v-if="entitlements.data && entitlements.data.length" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
        <div
          v-for="item in entitlements.data"
          :key="item.id"
          class="bg-slate-900 border border-slate-800 rounded-2xl p-4 flex gap-4 transition hover:border-sky-500/50"
        >
          <img
            :src="item.comic.cover_image"
            :alt="item.comic.title"
            class="w-20 h-28 object-cover rounded-xl border border-slate-800 shrink-0 bg-slate-950"
          />

          <div class="flex flex-col justify-between flex-1">
            <div>
              <span class="text-xs text-sky-400 font-medium line-clamp-1">{{ item.comic.title }}</span>
              <h3 class="font-bold text-white text-sm mt-0.5 line-clamp-1">
                Ch. {{ item.chapter.chapter_number }} - {{ item.chapter.title }}
              </h3>
              <p class="text-[11px] text-slate-500 mt-1">
                Unlocked {{ new Date(item.granted_at).toLocaleDateString() }}
              </p>
            </div>

            <Link
              :href="`/read/${item.comic.slug}/${item.chapter.chapter_number}`"
              class="inline-block mt-3 px-3 py-1.5 rounded-xl bg-sky-600 hover:bg-sky-500 text-white font-semibold text-xs text-center transition"
            >
              Read Now →
            </Link>
          </div>
        </div>
      </div>

      <div v-else class="bg-slate-900/40 border border-slate-800 rounded-2xl p-16 text-center space-y-4">
        <div class="text-5xl">📖</div>
        <h2 class="text-xl font-bold text-white">Your Library is Empty</h2>
        <p class="text-sm text-slate-400 max-w-md mx-auto">
          Purchased webcomic chapters will automatically appear here once your payment is confirmed.
        </p>
        <div class="pt-2">
          <Link href="/comics" class="px-6 py-3 rounded-xl bg-sky-600 hover:bg-sky-500 text-white font-semibold text-sm transition">
            Explore Comic Catalog
          </Link>
        </div>
      </div>
    </main>
  </div>
</template>
