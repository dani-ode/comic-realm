<script setup lang="ts">
import { Head, Link, router } from '@inertiajs/vue3';
import { ref, watch } from 'vue';
import PublicLayout from '@/Layouts/PublicLayout.vue';
import ComicCard from '@/Components/Comic/ComicCard.vue';
import { XMarkIcon } from '@heroicons/vue/24/outline';

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

interface Paginator {
  data: Comic[];
  links: Array<{ url: string | null; label: string; active: boolean }>;
  total: number;
}

const props = defineProps<{
  comics: Paginator;
  filters: { search?: string; genre?: string; status?: string; sort?: string };
  genres: Genre[];
}>();

const search = ref(props.filters.search || '');
const selectedGenre = ref(props.filters.genre || '');
const selectedStatus = ref(props.filters.status || '');
const selectedSort = ref(props.filters.sort || 'latest');

// Sync local refs when props.filters changes (e.g. via direct genre link click)
watch(() => props.filters, (newFilters) => {
  search.value = newFilters.search || '';
  selectedGenre.value = newFilters.genre || '';
  selectedStatus.value = newFilters.status || '';
  selectedSort.value = newFilters.sort || 'latest';
}, { deep: true });

const applyFilters = () => {
  router.get('/comics', {
    search: search.value || undefined,
    genre: selectedGenre.value || undefined,
    status: selectedStatus.value || undefined,
    sort: typeof selectedSort.value === 'string' ? selectedSort.value : undefined,
  }, { preserveState: true, replace: true });
};

watch([selectedGenre, selectedStatus, selectedSort], () => {
  applyFilters();
});

const clearFilter = (filterKey: 'genre' | 'search' | 'status' | 'all') => {
  if (filterKey === 'genre' || filterKey === 'all') selectedGenre.value = '';
  if (filterKey === 'search' || filterKey === 'all') search.value = '';
  if (filterKey === 'status' || filterKey === 'all') selectedStatus.value = '';
  applyFilters();
};
</script>

<template>
  <Head title="Comic Catalog" />

  <PublicLayout>
    <main class="max-w-7xl mx-auto px-4 lg:px-8 py-10 w-full flex-1 space-y-8">
      <div>
        <h1 class="text-3xl font-extrabold text-white">Comic Catalog</h1>
        <p class="text-sm text-slate-400 mt-1">Browse all available webcomics, filter by genre or popularity</p>
      </div>

      <!-- Filters & Search Bar -->
      <div class="bg-slate-900 border border-slate-800 rounded-2xl p-4 flex flex-col lg:flex-row gap-4 justify-between items-center">
        <!-- Search Input -->
        <form @submit.prevent="applyFilters" class="w-full lg:w-1/3">
          <input
            v-model="search"
            type="text"
            placeholder="Search comic title, author..."
            class="w-full bg-slate-950 border border-slate-800 rounded-xl px-4 py-2.5 text-sm text-white placeholder-slate-500 focus:outline-none focus:ring-1 focus:ring-sky-500"
          />
        </form>

        <!-- Dropdowns -->
        <div class="flex flex-wrap items-center gap-3 w-full lg:w-auto">
          <!-- Genre Select -->
          <select
            v-model="selectedGenre"
            class="bg-slate-950 border border-slate-800 rounded-xl px-3.5 py-2.5 text-sm text-slate-300 focus:outline-none focus:ring-1 focus:ring-sky-500"
          >
            <option value="">All Genres</option>
            <option v-for="g in genres" :key="g.id" :value="g.slug">{{ g.name }}</option>
          </select>

          <!-- Status Select -->
          <select
            v-model="selectedStatus"
            class="bg-slate-950 border border-slate-800 rounded-xl px-3.5 py-2.5 text-sm text-slate-300 focus:outline-none focus:ring-1 focus:ring-sky-500"
          >
            <option value="">All Status</option>
            <option value="ongoing">Ongoing</option>
            <option value="completed">Completed</option>
            <option value="hiatus">Hiatus</option>
          </select>

          <!-- Sort Select -->
          <select
            v-model="selectedSort"
            class="bg-slate-950 border border-slate-800 rounded-xl px-3.5 py-2.5 text-sm text-slate-300 focus:outline-none focus:ring-1 focus:ring-sky-500 font-medium"
          > 
            <option value="latest">Latest Release</option>
            <option value="popular">Most Views</option>
            <option value="rating">Highest Rating</option>
            <option value="oldest">Oldest</option>
          </select>
        </div>
      </div>

      <!-- Active Filter Chips -->
      <div v-if="selectedGenre || search || selectedStatus" class="flex items-center gap-2 flex-wrap text-xs">
        <span class="text-slate-400 font-semibold">Active Filters:</span>
        <span v-if="selectedGenre" class="inline-flex items-center gap-1 px-3 py-1 rounded-xl bg-sky-500/10 text-sky-400 border border-sky-500/30 font-bold">
          Genre: {{ genres.find(g => g.slug === selectedGenre)?.name || selectedGenre }}
          <button @click="clearFilter('genre')" class="hover:text-white"><XMarkIcon class="w-3.5 h-3.5" /></button>
        </span>
        <span v-if="search" class="inline-flex items-center gap-1 px-3 py-1 rounded-xl bg-indigo-500/10 text-indigo-400 border border-indigo-500/30 font-bold">
          Search: "{{ search }}"
          <button @click="clearFilter('search')" class="hover:text-white"><XMarkIcon class="w-3.5 h-3.5" /></button>
        </span>
        <button @click="clearFilter('all')" class="text-xs font-bold text-rose-400 hover:underline ml-2">
          Reset All Filters
        </button>
      </div>

      <!-- Comics Grid -->
      <div v-if="comics.data && comics.data.length" class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-4 gap-6">
        <ComicCard v-for="comic in comics.data" :key="comic.id" :comic="comic" />
      </div>

      <div v-else class="bg-slate-900/50 border border-slate-800 rounded-2xl p-12 text-center text-slate-400 space-y-2">
        <p class="text-lg font-medium text-slate-300">No comics found matching your criteria</p>
        <p class="text-xs">Try clearing your filters or search term</p>
        <button @click="clearFilter('all')" class="inline-block mt-3 px-4 py-2 rounded-xl text-xs font-bold bg-sky-600 hover:bg-sky-500 text-white transition">
          Clear All Filters
        </button>
      </div>

      <!-- Pagination Links -->
      <div v-if="comics.links && comics.links.length > 3" class="flex justify-center items-center gap-1.5 pt-4">
        <Component
          v-for="(link, i) in comics.links"
          :key="i"
          :is="link.url ? Link : 'span'"
          :href="link.url || '#'"
          v-html="link.label"
          class="px-3.5 py-2 rounded-xl text-xs font-medium border transition"
          :class="[
            link.active
              ? 'bg-sky-600 border-sky-600 text-white font-bold'
              : link.url
              ? 'bg-slate-900 border-slate-800 text-slate-300 hover:border-slate-700'
              : 'bg-slate-950 border-slate-900 text-slate-600 cursor-not-allowed'
          ]"
        />
      </div>
    </main>
  </PublicLayout>
</template>
