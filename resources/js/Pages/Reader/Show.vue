<script setup lang="ts">
import { Head, Link, usePage } from '@inertiajs/vue3';
import { ref, onMounted, onUnmounted, computed } from 'vue';
import axios from 'axios';
import ReaderToolbar from '@/Components/Reader/ReaderToolbar.vue';
import ReaderPageItem from '@/Components/Reader/ReaderPageItem.vue';
import ReaderAdvertisement from '@/Components/Reader/ReaderAdvertisement.vue';

interface Page {
  id: number;
  page_number: number;
  image_url?: string;
  image_path: string;
}

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

interface Chapter {
  id: number;
  comic_id: number;
  title: string;
  slug: string;
  chapter_number: number;
  pages?: Page[];
}

const props = defineProps<{
  comic: { id: number; title: string; slug: string };
  chapter: Chapter;
  prevChapter?: ChapterNav | null;
  nextChapter?: ChapterNav | null;
  allChapters?: ChapterOption[];
  savedProgress?: { page_number: number; progress_percent: number } | null;
}>();

const page = usePage();
const isAuthenticated = computed(() => !!(page.props as any).auth?.user);

const currentPage = ref(1);
const progressPercent = ref(0);
let scrollDebounceTimeout: ReturnType<typeof setTimeout> | null = null;

// Track active page while scrolling using IntersectionObserver
let pageObserver: IntersectionObserver | null = null;

onMounted(() => {
  // Auto-scroll to saved page if available
  if (props.savedProgress && props.savedProgress.page_number > 1) {
    const targetElement = document.querySelector(`[data-page="${props.savedProgress.page_number}"]`);
    if (targetElement) {
      targetElement.scrollIntoView({ behavior: 'smooth' });
    }
  }

  // Observe which page is currently in view
  pageObserver = new IntersectionObserver(
    (entries) => {
      entries.forEach((entry) => {
        if (entry.isIntersecting) {
          const pageNum = parseInt(entry.target.getAttribute('data-page') || '1');
          currentPage.value = pageNum;
          calculateProgress();
          debounceSaveProgress();
        }
      });
    },
    { threshold: 0.5 }
  );

  document.querySelectorAll('[data-page]').forEach((el) => {
    pageObserver?.observe(el);
  });

  window.addEventListener('scroll', handleScroll);
});

onUnmounted(() => {
  pageObserver?.disconnect();
  window.removeEventListener('scroll', handleScroll);
});

const calculateProgress = () => {
  const totalPages = props.chapter.pages?.length || 1;
  progressPercent.value = Math.min(100, Math.round((currentPage.value / totalPages) * 100));
};

const handleScroll = () => {
  const scrollTotal = document.documentElement.scrollHeight - window.innerHeight;
  if (scrollTotal > 0) {
    const currentScroll = window.scrollY;
    progressPercent.value = Math.min(100, Math.round((currentScroll / scrollTotal) * 100));
  }
};

const debounceSaveProgress = () => {
  if (scrollDebounceTimeout) clearTimeout(scrollDebounceTimeout);
  scrollDebounceTimeout = setTimeout(() => {
    saveProgress();
  }, 1000);
};

const saveProgress = async () => {
  if (!isAuthenticated.value) return;
  try {
    await axios.post('/api/reader/progress', {
      comic_id: props.comic.id,
      chapter_id: props.chapter.id,
      page_number: currentPage.value,
      progress_percent: progressPercent.value,
    });
  } catch (err) {
    // Ignore unauthenticated or network errors silently
  }
};
</script>

<template>
  <Head :title="`${comic.title} - ${chapter.title}`" />

  <div class="min-h-screen bg-slate-950 text-slate-100 flex flex-col select-none">
    <!-- Fixed Navigation Toolbar -->
    <ReaderToolbar
      :comic="comic"
      :chapter="chapter"
      :prevChapter="prevChapter"
      :nextChapter="nextChapter"
      :allChapters="allChapters"
    />

    <!-- Progress Bar (Fixed Top) -->
    <div class="fixed top-[57px] left-0 right-0 z-40 h-1 bg-slate-900">
      <div class="h-full bg-sky-500 transition-all duration-200" :style="{ width: `${progressPercent}%` }"></div>
    </div>

    <!-- Main Vertical Reader Canvas -->
    <main class="flex-1 w-full bg-slate-950 py-4">
      <div v-if="chapter.pages && chapter.pages.length" class="flex flex-col items-center">
        <template v-for="(page, index) in chapter.pages" :key="page.id">
          <ReaderPageItem :page="page" :isFirstPage="index === 0" />

          <!-- Ad Slot Insertion every 5 pages -->
          <ReaderAdvertisement v-if="(index + 1) % 5 === 0 && index !== chapter.pages.length - 1" :slotIndex="index" />
        </template>
      </div>

      <div v-else class="py-20 text-center text-slate-400 text-sm">
        No pages uploaded for this chapter yet.
      </div>
    </main>

    <!-- Bottom Navigation Footer -->
    <footer class="bg-slate-900 border-t border-slate-800 py-8 px-4 text-center space-y-4">
      <p class="text-sm font-medium text-slate-300">
        You finished <span class="text-sky-400 font-bold">{{ chapter.title }}</span>!
      </p>

      <div class="flex items-center justify-center gap-4">
        <Link
          v-if="prevChapter"
          :href="`/read/${comic.slug}/${prevChapter.chapter_number}`"
          class="px-5 py-2.5 rounded-xl bg-slate-800 border border-slate-700 text-slate-200 hover:text-white font-semibold text-xs transition"
        >
          ← Previous Chapter
        </Link>

        <Link
          :href="`/comics/${comic.slug}`"
          class="px-5 py-2.5 rounded-xl bg-slate-800 border border-slate-700 text-slate-200 hover:text-white font-semibold text-xs transition"
        >
          Comic Detail
        </Link>

        <Link
          v-if="nextChapter"
          :href="`/read/${comic.slug}/${nextChapter.chapter_number}`"
          class="px-5 py-2.5 rounded-xl bg-sky-600 hover:bg-sky-500 text-white font-semibold text-xs transition shadow-lg shadow-sky-600/30"
        >
          Next Chapter →
        </Link>
      </div>
    </footer>
  </div>
</template>
