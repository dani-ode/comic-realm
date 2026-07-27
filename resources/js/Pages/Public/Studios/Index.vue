<script setup lang="ts">
import { Head, Link, router } from '@inertiajs/vue3';
import { ref } from 'vue';
import PublicLayout from '@/Layouts/PublicLayout.vue';
import {
  BuildingStorefrontIcon,
  MagnifyingGlassIcon,
  BookOpenIcon,
  CheckBadgeIcon,
  ArrowRightIcon,
  SparklesIcon,
} from '@heroicons/vue/24/outline';

interface PublisherProfile {
  id: number;
  brand_name?: string;
  slug?: string;
  bio?: string;
  logo?: string;
  banner?: string;
  verification_status?: string;
}

interface StudioUser {
  id: number;
  name: string;
  username: string;
  avatar?: string;
  publisher_profile?: PublisherProfile;
  published_comics_count?: number;
}

interface PaginatedStudios {
  data: StudioUser[];
  current_page: number;
  last_page: number;
  prev_page_url?: string;
  next_page_url?: string;
  total: number;
}

const props = defineProps<{
  studios: PaginatedStudios;
  filters?: {
    search?: string;
  };
}>();

const searchQuery = ref(props.filters?.search || '');

const handleSearch = () => {
  router.get(
    '/studios',
    { search: searchQuery.value },
    { preserveState: true, replace: true }
  );
};

const getBrandName = (studio: StudioUser) => {
  return studio.publisher_profile?.brand_name || studio.name;
};

const getStudioSlug = (studio: StudioUser) => {
  return studio.publisher_profile?.slug || String(studio.id);
};
</script>

<template>
  <Head title="Daftar Studio & Publisher - ComicRealm" />

  <PublicLayout>
    <main class="max-w-7xl mx-auto px-4 lg:px-8 py-10 w-full flex-1 space-y-10">

      <!-- Header Section -->
      <div class="relative overflow-hidden bg-gradient-to-r from-slate-900 via-indigo-950/80 to-slate-900 border border-slate-800 rounded-3xl p-8 sm:p-12 shadow-2xl space-y-6">
        <div class="absolute -right-10 -bottom-10 w-80 h-80 bg-sky-500/10 rounded-full blur-3xl pointer-events-none"></div>
        <div class="absolute -left-10 -top-10 w-80 h-80 bg-purple-500/10 rounded-full blur-3xl pointer-events-none"></div>

        <div class="relative z-10 max-w-2xl space-y-3">
          <div class="inline-flex items-center gap-2 px-3 py-1.5 rounded-full bg-sky-500/10 border border-sky-500/30 text-sky-400 text-xs font-extrabold uppercase tracking-wider">
            <BuildingStorefrontIcon class="w-4 h-4" />
            <span>Verified Creators & Publishers</span>
          </div>

          <h1 class="text-3xl sm:text-4xl font-black text-white tracking-tight leading-tight">
            Unveil <span class="bg-gradient-to-r from-sky-400 via-indigo-300 to-purple-400 bg-clip-text text-transparent">Official Studios</span> on ComicRealm
          </h1>

          <p class="text-sm sm:text-base text-slate-300 leading-relaxed">
            Champion the visionaries you love. Immerse yourself in independent comic sanctuaries, behold their cherished creations, and lose yourself in the latest chapters.
          </p>
        </div>

        <!-- Search Bar -->
        <form @submit.prevent="handleSearch" class="relative z-10 max-w-md pt-2">
          <div class="relative flex items-center">
            <input
              v-model="searchQuery"
              type="text"
              placeholder="Seek a comic studio..."
              class="w-full rounded-2xl bg-slate-950/90 border border-slate-700 pl-11 pr-28 py-3.5 text-xs sm:text-sm text-white placeholder-slate-500 focus:border-sky-500 focus:outline-none focus:ring-2 focus:ring-sky-500/20 shadow-xl transition"
            />
            <MagnifyingGlassIcon class="w-5 h-5 text-slate-400 absolute left-3.5 pointer-events-none" />
            <button
              type="submit"
              class="absolute right-2 px-4 py-2 rounded-xl bg-gradient-to-r from-sky-600 to-indigo-600 hover:from-sky-500 hover:to-indigo-500 text-xs font-bold text-white transition active:scale-95 shadow-md shadow-sky-600/20"
            >
              Seek
            </button>
          </div>
        </form>
      </div>

      <!-- Studio List Grid -->
      <div v-if="studios.data && studios.data.length" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">
        <div
          v-for="studio in studios.data"
          :key="studio.id"
          class="group relative bg-slate-900/80 backdrop-blur-xl border border-slate-800/80 hover:border-sky-500/40 rounded-3xl overflow-hidden shadow-xl hover:shadow-2xl hover:shadow-sky-500/10 transition-all duration-300 flex flex-col justify-between"
        >
          <div>
            <!-- Banner Header -->
            <div class="h-28 sm:h-32 w-full bg-slate-950 relative overflow-hidden">
              <img
                v-if="studio.publisher_profile?.banner"
                :src="studio.publisher_profile.banner"
                :alt="getBrandName(studio)"
                class="w-full h-full object-cover group-hover:scale-105 transition duration-500"
              />
              <div v-else class="w-full h-full bg-gradient-to-r from-slate-950 via-sky-950 to-indigo-950 opacity-90 flex items-center justify-center">
                <SparklesIcon class="w-12 h-12 text-slate-800/50" />
              </div>
              <div class="absolute inset-0 bg-gradient-to-t from-slate-900 via-transparent to-transparent"></div>
            </div>

            <!-- Avatar & Details -->
            <div class="px-6 pb-4 relative -mt-10 space-y-3">
              <div class="flex items-end justify-between">
                <div class="w-20 h-20 rounded-2xl bg-slate-950 border-4 border-slate-900 overflow-hidden shadow-xl shrink-0">
                  <img
                    v-if="studio.publisher_profile?.logo || studio.avatar"
                    :src="studio.publisher_profile?.logo || studio.avatar"
                    :alt="getBrandName(studio)"
                    class="w-full h-full object-cover"
                  />
                  <div v-else class="w-full h-full bg-sky-950 text-sky-400 flex items-center justify-center font-black text-2xl">
                    {{ getBrandName(studio).charAt(0).toUpperCase() }}
                  </div>
                </div>

                <!-- Verified Badge -->
                <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full bg-sky-500/10 border border-sky-500/30 text-sky-400 text-[10px] font-bold uppercase tracking-wider">
                  <CheckBadgeIcon class="w-3.5 h-3.5 text-sky-400" />
                  Official Studio
                </span>
              </div>

              <div>
                <h3 class="text-lg font-extrabold text-white group-hover:text-sky-300 transition truncate">
                  {{ getBrandName(studio) }}
                </h3>
                <p class="text-xs text-slate-400 font-mono">@{{ studio.username }}</p>
              </div>

              <p class="text-xs text-slate-300 line-clamp-2 leading-relaxed h-8">
                {{ studio.publisher_profile?.bio || 'Studio komik resmi di platform ComicRealm.' }}
              </p>
            </div>
          </div>

          <!-- Card Footer -->
          <div class="p-6 pt-0 border-t border-slate-800/60 mt-4 flex items-center justify-between gap-3">
            <div class="flex items-center gap-1.5 text-xs text-slate-400 font-semibold">
              <BookOpenIcon class="w-4 h-4 text-sky-400 shrink-0" />
              <span>{{ studio.published_comics_count || 0 }} Judul Komik</span>
            </div>

            <Link
              :href="`/studios/${getStudioSlug(studio)}`"
              class="inline-flex items-center gap-1.5 px-4 py-2 rounded-xl bg-slate-800 hover:bg-sky-600 text-slate-200 hover:text-white text-xs font-bold transition active:scale-95 shadow-md"
            >
              <span>Kunjungi</span>
              <ArrowRightIcon class="w-3.5 h-3.5" />
            </Link>
          </div>
        </div>
      </div>

      <!-- Empty State -->
      <div v-else class="bg-slate-900/60 border border-slate-800 rounded-3xl p-16 text-center space-y-4">
        <div class="w-16 h-16 bg-slate-950 border border-slate-800 rounded-2xl flex items-center justify-center mx-auto text-slate-500">
          <BuildingStorefrontIcon class="w-8 h-8" />
        </div>
        <h3 class="text-xl font-bold text-white">Tidak Ada Studio Ditemukan</h3>
        <p class="text-xs sm:text-sm text-slate-400 max-w-md mx-auto">
          Belum ada studio yang cocok dengan pencarian "{{ searchQuery }}". Silakan gunakan kata kunci lain.
        </p>
        <Link
          href="/studios"
          class="inline-flex items-center gap-2 px-5 py-2.5 rounded-xl bg-sky-600 hover:bg-sky-500 text-xs font-bold text-white transition"
        >
          Lihat Semua Studio
        </Link>
      </div>

      <!-- Pagination Controls -->
      <div v-if="studios.last_page > 1" class="flex items-center justify-between pt-6 border-t border-slate-800">
        <span class="text-xs text-slate-400">
          Halaman {{ studios.current_page }} dari {{ studios.last_page }}
        </span>

        <div class="flex items-center gap-2">
          <Link
            v-if="studios.prev_page_url"
            :href="studios.prev_page_url"
            class="px-4 py-2 rounded-xl bg-slate-900 border border-slate-800 text-xs font-bold text-slate-300 hover:text-white hover:border-slate-700 transition"
          >
            Sebelumnya
          </Link>
          <Link
            v-if="studios.next_page_url"
            :href="studios.next_page_url"
            class="px-4 py-2 rounded-xl bg-slate-900 border border-slate-800 text-xs font-bold text-slate-300 hover:text-white hover:border-slate-700 transition"
          >
            Selanjutnya
          </Link>
        </div>
      </div>

    </main>
  </PublicLayout>
</template>
