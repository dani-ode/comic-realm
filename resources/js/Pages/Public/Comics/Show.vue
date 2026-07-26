<script setup lang="ts">
import { Head, Link, usePage, router } from '@inertiajs/vue3';
import { computed, ref } from 'vue';
import axios from 'axios';
import PublicLayout from '@/Layouts/PublicLayout.vue';
import GenreBadge from '@/Components/Comic/GenreBadge.vue';
import BookmarkButton from '@/Components/Engagement/BookmarkButton.vue';
import StarRating from '@/Components/Engagement/StarRating.vue';
import CommentSection from '@/Components/Engagement/CommentSection.vue';
import { EyeIcon } from '@heroicons/vue/24/outline';
import { StarIcon, HeartIcon as HeartIconSolid } from '@heroicons/vue/24/solid';

interface Genre {
  id: number;
  name: string;
  slug: string;
}

interface Chapter {
  id: number;
  title: string;
  slug: string;
  chapter_number: number;
  is_free: boolean;
  price: number;
  published_at: string;
}

interface Comic {
  id: number;
  title: string;
  slug: string;
  alternative_title?: string;
  description: string;
  cover_image: string;
  banner_image?: string;
  author_name?: string;
  artist_name?: string;
  status: string;
  rating_average: number;
  total_ratings?: number;
  total_views: number;
  total_bookmarks: number;
  genres?: Genre[];
  publisher?: { name: string };
  published_chapters?: Chapter[];
}

const props = withDefaults(defineProps<{
  comic: Comic;
  unlockedChapterIds?: number[];
  cartChapterIds?: number[];
  isBookmarked?: boolean;
  userRating?: number;
}>(), {
  unlockedChapterIds: () => [],
  cartChapterIds: () => [],
  isBookmarked: false,
  userRating: 0,
});

const page = usePage();
const flashError = computed(() => (page.props as any).flash?.error);
const flashSuccess = computed(() => (page.props as any).flash?.success);

const isAdding = ref<number | null>(null);
const localSuccess = ref('');

const isUnlocked = (chapterId: number, isFree: boolean) => {
  return isFree || (props.unlockedChapterIds && props.unlockedChapterIds.includes(chapterId));
};

const isInCart = (chapterId: number) => {
  return props.cartChapterIds && props.cartChapterIds.includes(chapterId);
};

const handleAddToCart = async (chapterId: number) => {
  if (isInCart(chapterId)) {
    router.get('/cart');
    return;
  }

  isAdding.value = chapterId;
  localSuccess.value = '';

  try {
    const res = await axios.post('/api/cart/items', { chapter_id: chapterId });
    if (res.data && res.data.success) {
      if (!props.cartChapterIds.includes(chapterId)) {
        props.cartChapterIds.push(chapterId);
      }
      localSuccess.value = res.data.message || 'Bab berhasil ditambahkan ke keranjang belanja.';
      router.reload({ only: ['cartCount', 'cartChapterIds'] });
      setTimeout(() => {
        localSuccess.value = '';
      }, 5000);
    }
  } catch (err: any) {
    if (err.response && err.response.status === 401) {
      router.get('/login');
    } else {
      alert(err.response?.data?.message || 'Gagal menambahkan bab ke keranjang belanja.');
    }
  } finally {
    isAdding.value = null;
  }
};
</script>

<template>
  <Head :title="`${comic.title} - Read Webcomic`" />

  <PublicLayout>
    <!-- Comic Banner & Header Section -->
    <div class="relative w-full bg-slate-900 border-b border-slate-800">
      <div v-if="comic.banner_image" class="absolute inset-0 opacity-20 overflow-hidden">
        <img :src="comic.banner_image" class="w-full h-full object-cover blur-sm" />
      </div>

      <div class="relative max-w-7xl mx-auto px-4 lg:px-8 py-10 flex flex-col md:flex-row gap-8 items-start">
        <!-- Cover Image -->
        <div class="w-48 sm:w-60 aspect-[2/3] rounded-2xl overflow-hidden shadow-2xl border border-slate-700/80 shrink-0 bg-slate-950">
          <img :src="comic.cover_image" :alt="comic.title" class="w-full h-full object-cover" />
        </div>

        <!-- Info Details -->
        <div class="flex-1 space-y-4">
          <div class="flex flex-wrap gap-2" v-if="comic.genres">
            <GenreBadge v-for="g in comic.genres" :key="g.id" :name="g.name" />
          </div>

          <h1 class="text-3xl sm:text-5xl font-extrabold text-white leading-tight">
            {{ comic.title }}
          </h1>

          <p v-if="comic.alternative_title" class="text-sm text-slate-400 italic">
            Alias: {{ comic.alternative_title }}
          </p>

          <div class="flex flex-wrap items-center gap-6 text-sm text-slate-300 py-1">
            <span>Author: <strong class="text-white">{{ comic.author_name || 'Unknown' }}</strong></span>
            <span>Artist: <strong class="text-white">{{ comic.artist_name || 'Unknown' }}</strong></span>
            <span>Status: <strong class="text-sky-400 capitalize">{{ comic.status }}</strong></span>
          </div>

          <!-- Metrics Stat & Interactive Action Row -->
          <div class="flex flex-wrap items-center gap-6 py-3 border-y border-slate-800 text-sm">
            <div class="flex items-center gap-2 flex-wrap">
              <span class="font-bold text-amber-400 text-base flex items-center gap-1">
                <StarIcon class="w-4 h-4 text-amber-400 fill-amber-400 shrink-0" />
                {{ comic.rating_average ? comic.rating_average.toFixed(1) : '0.0' }}
              </span>
              <span class="text-xs font-semibold text-slate-400">
                ({{ comic.total_ratings ? comic.total_ratings.toLocaleString() : 0 }} rating)
              </span>
              <StarRating :comicId="comic.id" :initialRating="userRating" />
            </div>
            <div class="text-slate-400">
                <span class="flex items-center"><EyeIcon class="w-4 h-4 mr-1" /> {{ comic.total_views ? comic.total_views.toLocaleString() : 0 }} Views</span>
            </div>
            <div class="text-slate-400 flex items-center gap-1">
              <HeartIconSolid class="w-4 h-4 text-rose-500 fill-rose-500 shrink-0" />
              <span>{{ comic.total_bookmarks ? comic.total_bookmarks.toLocaleString() : 0 }} Bookmarks</span>
            </div>
            <div>
              <BookmarkButton :comicId="comic.id" :initialBookmarked="isBookmarked" />
            </div>
          </div>

          <!-- Description -->
          <p class="text-sm text-slate-300 leading-relaxed max-w-3xl">
            {{ comic.description }}
          </p>
        </div>
      </div>
    </div>

    <!-- Chapter List & Discussion Section -->
    <main class="max-w-7xl mx-auto px-4 lg:px-8 py-10 w-full flex-1 space-y-12">
      <!-- Flash Error Banner -->
      <div v-if="flashError" class="bg-red-500/10 border border-red-500/30 text-red-400 px-5 py-3.5 rounded-xl text-sm font-medium flex items-center gap-3">
        <span class="text-lg">⚠️</span>
        {{ flashError }}
      </div>

      <!-- Success Notification Banner -->
      <div v-if="flashSuccess || localSuccess" class="bg-emerald-500/10 border border-emerald-500/30 text-emerald-400 px-5 py-3.5 rounded-xl text-sm font-medium flex items-center justify-between">
        <span class="flex items-center gap-2">
          <span>🛒</span> {{ flashSuccess || localSuccess }}
        </span>
        <Link href="/cart" class="text-xs font-bold underline hover:text-white">Lihat Keranjang →</Link>
      </div>

      <!-- Chapter List -->
      <section class="space-y-6">
        <h2 class="text-2xl font-bold text-white flex items-center justify-between">
          <span>Chapter List</span>
          <span class="text-xs font-normal text-slate-400" v-if="comic.published_chapters">
            {{ comic.published_chapters.length }} Chapters Available
          </span>
        </h2>

        <div v-if="comic.published_chapters && comic.published_chapters.length" class="space-y-3">
          <div
            v-for="ch in comic.published_chapters"
            :key="ch.id"
            class="bg-slate-900 border border-slate-800 hover:border-sky-500/50 rounded-xl p-4 flex items-center justify-between transition group"
          >
            <div>
              <h3 class="font-bold text-white group-hover:text-sky-400 transition">
                Chapter {{ ch.chapter_number }}: {{ ch.title }}
              </h3>
              <p class="text-xs text-slate-400 mt-0.5">
                Released {{ new Date(ch.published_at).toLocaleDateString() }}
              </p>
            </div>

            <div class="flex items-center gap-3">
              <!-- Free Chapter Status -->
              <span
                v-if="ch.is_free"
                class="px-2.5 py-1 text-xs font-bold rounded-lg bg-emerald-500/10 text-emerald-400 border border-emerald-500/20"
              >
                FREE
              </span>

              <!-- Purchased / Unlocked Status -->
              <span
                v-else-if="isUnlocked(ch.id, ch.is_free)"
                class="px-2.5 py-1 text-xs font-bold rounded-lg bg-sky-500/10 text-sky-400 border border-sky-500/20"
              >
                UNLOCKED ✓
              </span>

              <!-- Paid Price Label -->
              <span
                v-else
                class="px-2.5 py-1 text-xs font-bold rounded-lg bg-amber-500/10 text-amber-400 border border-amber-500/20"
              >
                Rp {{ ch.price ? ch.price.toLocaleString() : '5,000' }}
              </span>

              <!-- Cart Button (Shown ONLY if paid AND NOT unlocked) -->
              <template v-if="!isUnlocked(ch.id, ch.is_free)">
                <!-- Already In Cart Button -->
                <Link
                  v-if="isInCart(ch.id)"
                  href="/cart"
                  class="px-3.5 py-2 rounded-xl text-xs font-bold bg-sky-500/10 hover:bg-sky-500/20 text-sky-400 border border-sky-500/30 transition flex items-center gap-1.5"
                >
                  <span>🛒</span>
                  <span>In Cart</span>
                </Link>

                <!-- Add to Cart Button -->
                <button
                  v-else
                  @click="handleAddToCart(ch.id)"
                  :disabled="isAdding === ch.id"
                  class="px-3.5 py-2 rounded-xl text-xs font-bold bg-amber-500/10 hover:bg-amber-500/20 text-amber-400 border border-amber-500/30 transition flex items-center gap-1.5"
                >
                  <span>🛒</span>
                  <span>{{ isAdding === ch.id ? 'Adding...' : 'Add to Cart' }}</span>
                </button>
              </template>

              <!-- Read Chapter Button -->
              <Link
                :href="`/read/${comic.slug}/${ch.chapter_number}`"
                class="px-4 py-2 rounded-xl text-xs font-semibold bg-sky-600 hover:bg-sky-500 text-white transition shadow-md shadow-sky-600/20"
              >
                Read
              </Link>
            </div>
          </div>
        </div>

        <div v-else class="bg-slate-900/40 border border-slate-800 rounded-2xl p-8 text-center text-slate-400">
          No chapters published yet.
        </div>
      </section>

      <!-- Comment Section -->
      <section>
        <CommentSection :comicId="comic.id" />
      </section>
    </main>
  </PublicLayout>
</template>
