<script setup lang="ts">
import { Head, Link, usePage, router } from '@inertiajs/vue3';
import { computed, ref } from 'vue';
import axios from 'axios';
import PublicLayout from '@/Layouts/PublicLayout.vue';
import GenreBadge from '@/Components/Comic/GenreBadge.vue';
import BookmarkButton from '@/Components/Engagement/BookmarkButton.vue';
import StarRating from '@/Components/Engagement/StarRating.vue';
import CommentSection from '@/Components/Engagement/CommentSection.vue';
import { EyeIcon, LockClosedIcon, ShoppingCartIcon, CheckBadgeIcon } from '@heroicons/vue/24/outline';
import { StarIcon, HeartIcon as HeartIconSolid } from '@heroicons/vue/24/solid';
import { useToast } from '@/composables/useToast';

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
  publisher?: {
    id: number;
    name: string;
    publisher_profile?: {
      brand_name?: string;
      slug?: string;
    };
  };
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
const { success: toastSuccess, error: toastError } = useToast();

const isAdding = ref<number | null>(null);

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

  try {
    const res = await axios.post('/api/cart/items', { chapter_id: chapterId });
    if (res.data && res.data.success) {
      if (!props.cartChapterIds.includes(chapterId)) {
        props.cartChapterIds.push(chapterId);
      }
      toastSuccess(res.data.message || 'Chapter berhasil ditambahkan ke keranjang!');
      router.reload({ only: ['cartCount', 'cartChapterIds'] });
    }
  } catch (err: any) {
    if (err.response && err.response.status === 401) {
      toastError('Silakan login terlebih dahulu untuk membeli chapter.');
      setTimeout(() => router.get('/login'), 1500);
    } else {
      toastError(err.response?.data?.message || 'Gagal menambahkan chapter ke keranjang.');
    }
  } finally {
    isAdding.value = null;
  }
};

const handleRatingUpdated = (payload: { user_rating: number; rating_average: number; total_ratings: number }) => {
  props.comic.rating_average = payload.rating_average;
  props.comic.total_ratings = payload.total_ratings;
};

const handleBookmarkUpdated = (payload: { bookmarked: boolean; total_bookmarks: number }) => {
  props.comic.total_bookmarks = payload.total_bookmarks;
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
            <GenreBadge v-for="g in comic.genres" :key="g.id" :genre="g" />
          </div>

          <h1 class="text-3xl sm:text-5xl font-extrabold text-white leading-tight">
            {{ comic.title }}
          </h1>

          <p v-if="comic.alternative_title" class="text-sm text-slate-400 italic">
            Alias: {{ comic.alternative_title }}
          </p>

          <div class="flex flex-wrap items-center gap-6 text-sm text-slate-300 py-1">
            <span v-if="comic.publisher">
              Studio: 
              <Link :href="`/studios/${comic.publisher.publisher_profile?.slug || comic.publisher.id}`" class="text-sky-400 font-bold hover:underline inline-flex items-center gap-1">
                {{ comic.publisher.publisher_profile?.brand_name || comic.publisher.name }}
              </Link>
            </span>
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
                ({{ comic.total_ratings ? comic.total_ratings.toLocaleString() : 0 }})
              </span>
              <StarRating :comicId="comic.id" :initialRating="userRating" @updated="handleRatingUpdated" />
            </div>
            <div class="text-slate-400">
                <span class="flex items-center"><EyeIcon class="w-4 h-4 mr-1" /> {{ comic.total_views ? comic.total_views.toLocaleString() : 0 }} Views</span>
            </div>
            <div class="text-slate-400 flex items-center gap-1">
              <HeartIconSolid class="w-4 h-4 text-rose-500 fill-rose-500 shrink-0" />
              <span>{{ comic.total_bookmarks ? comic.total_bookmarks.toLocaleString() : 0 }} Bookmarks</span>
            </div>
            <div>
              <BookmarkButton :comicId="comic.id" :initialBookmarked="isBookmarked" @updated="handleBookmarkUpdated" />
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

      <!-- Chapter List -->
      <section class="space-y-6">
        <h2 class="text-2xl font-bold text-white flex items-center justify-between">
          <span>Chapter List</span>
          <span class="text-xs font-normal text-slate-400" v-if="comic.published_chapters">
            {{ comic.published_chapters.length }} Chapters Available
          </span>
        </h2>

        <div v-if="comic.published_chapters && comic.published_chapters.length" class="space-y-2.5">
          <div
            v-for="ch in comic.published_chapters"
            :key="ch.id"
            class="bg-slate-900 border border-slate-800 hover:border-sky-500/40 rounded-xl px-5 py-4 flex items-center justify-between gap-4 transition group"
          >
            <!-- Chapter Info -->
            <div class="flex-1 min-w-0">
              <h3 class="font-semibold text-white group-hover:text-sky-400 transition text-sm truncate">
                Ch.{{ ch.chapter_number }} — {{ ch.title }}
              </h3>
              <p class="text-xs text-slate-500 mt-0.5">
                {{ new Date(ch.published_at).toLocaleDateString('id-ID', { day: 'numeric', month: 'short', year: 'numeric' }) }}
              </p>
            </div>

            <!-- Action Area -->
            <div class="shrink-0 flex items-center">

              <!-- FREE chapter -->
              <template v-if="ch.is_free">
                <div class="relative flex flex-col items-center gap-0.5">
                  <span class="text-[10px] font-bold text-emerald-400 tracking-wide uppercase">Free</span>
                  <Link
                    :href="`/read/${comic.slug}/${ch.chapter_number}`"
                    class="px-4 py-2 rounded-xl text-xs font-bold bg-emerald-600 hover:bg-emerald-500 text-white transition shadow-md shadow-emerald-600/20"
                  >
                    Read
                  </Link>
                </div>
              </template>

              <!-- PAID + UNLOCKED -->
              <template v-else-if="isUnlocked(ch.id, ch.is_free)">
                <div class="relative flex flex-col items-center gap-0.5">
                  <span class="text-[10px] font-bold text-sky-400 tracking-wide uppercase flex items-center gap-0.5">
                    <CheckBadgeIcon class="w-3 h-3" /> Unlocked
                  </span>
                  <Link
                    :href="`/read/${comic.slug}/${ch.chapter_number}`"
                    class="px-4 py-2 rounded-xl text-xs font-bold bg-sky-600 hover:bg-sky-500 text-white transition shadow-md shadow-sky-600/20"
                  >
                    Read
                  </Link>
                </div>
              </template>

              <!-- PAID + IN CART -->
              <template v-else-if="isInCart(ch.id)">
                <div class="relative flex flex-col items-center gap-0.5">
                  <span class="text-[10px] font-bold text-amber-400 tracking-wide uppercase">
                    Rp {{ ch.price ? ch.price.toLocaleString('id-ID') : '5.000' }}
                  </span>
                  <Link
                    href="/cart"
                    class="px-4 py-2 rounded-xl text-xs font-bold bg-amber-500/20 hover:bg-amber-500/30 text-amber-300 border border-amber-500/40 transition flex items-center gap-1.5"
                  >
                    <ShoppingCartIcon class="w-3.5 h-3.5" />
                    In Cart
                  </Link>
                </div>
              </template>

              <!-- PAID + LOCKED -->
              <template v-else>
                <div class="relative flex flex-col items-center gap-0.5">
                  <span class="text-[10px] font-bold text-slate-400 tracking-wide uppercase">
                    Rp {{ ch.price ? ch.price.toLocaleString('id-ID') : '5.000' }}
                  </span>
                  <button
                    @click="handleAddToCart(ch.id)"
                    :disabled="isAdding === ch.id"
                    class="px-4 py-2 rounded-xl text-xs font-bold bg-slate-800 hover:bg-amber-500/20 text-slate-300 hover:text-amber-300 border border-slate-700 hover:border-amber-500/40 transition flex items-center gap-1.5 disabled:opacity-60"
                  >
                    <LockClosedIcon class="w-3.5 h-3.5" />
                    {{ isAdding === ch.id ? 'Adding...' : 'Add to Cart' }}
                  </button>
                </div>
              </template>

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
