<script setup lang="ts">
import { Link, router } from '@inertiajs/vue3';
import { ref } from 'vue';

interface ChapterNav {
  chapter_number: number;
  title: string;
  slug: string;
}

interface ChapterOption {
  id: number;
  chapter_number: number;
  title: string;
  is_free: boolean;
}

const props = defineProps<{
  comic: { title: string; slug: string };
  chapter: { title: string; chapter_number: number };
  prevChapter?: ChapterNav | null;
  nextChapter?: ChapterNav | null;
  allChapters?: ChapterOption[];
}>();

const selectedChapterNumber = ref(props.chapter.chapter_number);

const changeChapter = (e: Event) => {
  const target = e.target as HTMLSelectElement;
  if (target.value) {
    router.get(`/read/${props.comic.slug}/${target.value}`);
  }
};
</script>

<template>
  <header class="sticky top-0 z-50 bg-slate-950/90 backdrop-blur-md border-b border-slate-800 px-4 lg:px-8 py-3 flex items-center justify-between transition-transform duration-300">
    <!-- Back to Comic Detail -->
    <div class="flex items-center gap-3">
      <Link :href="`/comics/${comic.slug}`" class="p-2 rounded-xl bg-slate-900 border border-slate-800 text-slate-300 hover:text-white hover:border-slate-700 transition">
        ← Back
      </Link>
      <div class="hidden sm:block">
        <h1 class="text-sm font-bold text-white line-clamp-1">{{ comic.title }}</h1>
        <p class="text-xs text-sky-400 font-medium">{{ chapter.title }}</p>
      </div>
    </div>

    <!-- Chapter Select Dropdown -->
    <div class="flex items-center gap-2">
      <select
        v-model="selectedChapterNumber"
        @change="changeChapter"
        class="bg-slate-900 border border-slate-800 text-xs sm:text-sm text-white font-medium rounded-xl px-3 py-2 focus:outline-none focus:ring-1 focus:ring-sky-500"
      >
        <option v-for="ch in allChapters" :key="ch.id" :value="ch.chapter_number">
          Ch. {{ ch.chapter_number }} - {{ ch.title }}
        </option>
      </select>
    </div>

    <!-- Prev & Next Buttons -->
    <div class="flex items-center gap-2">
      <Link
        v-if="prevChapter"
        :href="`/read/${comic.slug}/${prevChapter.chapter_number}`"
        class="px-3 py-1.5 rounded-xl bg-slate-900 border border-slate-800 text-xs font-semibold text-slate-300 hover:text-white hover:border-slate-700 transition"
      >
        Prev
      </Link>
      <span v-else class="px-3 py-1.5 rounded-xl bg-slate-950 border border-slate-900 text-xs font-semibold text-slate-700 cursor-not-allowed">
        Prev
      </span>

      <Link
        v-if="nextChapter"
        :href="`/read/${comic.slug}/${nextChapter.chapter_number}`"
        class="px-3 py-1.5 rounded-xl bg-sky-600 hover:bg-sky-500 text-xs font-semibold text-white transition shadow-md shadow-sky-600/30"
      >
        Next
      </Link>
      <span v-else class="px-3 py-1.5 rounded-xl bg-slate-950 border border-slate-900 text-xs font-semibold text-slate-700 cursor-not-allowed">
        Next
      </span>
    </div>
  </header>
</template>
