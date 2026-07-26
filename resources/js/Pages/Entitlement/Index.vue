<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import PublicLayout from '@/Layouts/PublicLayout.vue';
import { BookOpenIcon, InboxIcon, Squares2X2Icon } from '@heroicons/vue/24/outline';

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

  <PublicLayout>
    <main class="max-w-7xl mx-auto px-4 lg:px-8 py-10 w-full flex-1 space-y-8">
      <div>
        <h1 class="text-3xl font-extrabold text-white flex items-center gap-3">
          <span class="w-9 h-9 rounded-xl bg-sky-500/10 border border-sky-500/30 flex items-center justify-center shrink-0">
            <BookOpenIcon class="w-5 h-5 text-sky-400" />
          </span>
          My Library
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
        <div class="w-16 h-16 bg-slate-950 border border-slate-800 rounded-2xl flex items-center justify-center mx-auto">
          <InboxIcon class="w-8 h-8 text-slate-500" />
        </div>
        <h2 class="text-xl font-bold text-white">Your Library is Empty</h2>
        <p class="text-sm text-slate-400 max-w-md mx-auto">
          Purchased webcomic chapters will automatically appear here once your payment is confirmed.
        </p>
        <div class="pt-2">
          <Link href="/comics" class="inline-flex items-center gap-2 px-6 py-3 rounded-xl bg-sky-600 hover:bg-sky-500 text-white font-semibold text-sm transition">
            <Squares2X2Icon class="w-4 h-4" />
            Explore Comic Catalog
          </Link>
        </div>
      </div>
    </main>
  </PublicLayout>
</template>
