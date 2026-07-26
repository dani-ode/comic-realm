<script setup lang="ts">
import { Head, Link, useForm, router, usePage } from '@inertiajs/vue3';
import { ref, computed } from 'vue';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import {
  BookOpenIcon,
  PlusIcon,
  StarIcon,
  EyeIcon,
  PencilSquareIcon,
  TrashIcon,
  LockClosedIcon,
  ArrowTopRightOnSquareIcon,
  SwatchIcon,
  ChevronDownIcon,
  ChevronUpIcon,
  DocumentDuplicateIcon,
  XMarkIcon,
  CheckBadgeIcon,
  ExclamationTriangleIcon,
} from '@heroicons/vue/24/outline';

interface Chapter {
  id: number;
  chapter_number: number;
  title: string;
  is_free: boolean;
  price: number;
  published_at?: string;
}

interface Genre {
  id: number;
  name: string;
}

interface Comic {
  id: number;
  title: string;
  slug: string;
  cover_image: string;
  author_name?: string;
  artist_name?: string;
  status: string;
  publication_status: string;
  rating_average: number;
  total_views: number;
  genres?: Genre[];
  chapters?: Chapter[];
}

interface Profile {
  id: number;
  brand_name: string;
  verification_status: string;
}

const props = defineProps<{
  profile?: Profile | null;
  comics: Comic[];
  filters?: { search?: string; status?: string };
}>();

const page = usePage();
const flashSuccess = computed(() => (page.props as any).flash?.success);
const flashError = computed(() => (page.props as any).flash?.error);

const isApproved = computed(() => props.profile && props.profile.verification_status === 'approved');

const search = ref(props.filters?.search || '');
const statusFilter = ref(props.filters?.status || '');
const expandedComicId = ref<number | null>(props.comics.length ? props.comics[0].id : null);

const handleSearch = () => {
  router.get('/publisher/comics', {
    search: search.value,
    status: statusFilter.value,
  }, { preserveState: true, replace: true });
};

const toggleAccordion = (comicId: number) => {
  if (expandedComicId.value === comicId) {
    expandedComicId.value = null;
  } else {
    expandedComicId.value = comicId;
  }
};

const deleteForm = useForm({});

const deleteComic = (id: number, title: string) => {
  if (confirm(`Apakah Anda yakin ingin menghapus serial komik "${title}"? Seluruh chapter di dalamnya juga akan terhapus.`)) {
    deleteForm.delete(`/publisher/comics/${id}`);
  }
};

const deleteChapter = (comicId: number, chapterId: number, number: number) => {
  if (confirm(`Hapus Bab ${number}?`)) {
    deleteForm.delete(`/publisher/comics/${comicId}/chapters/${chapterId}`);
  }
};
</script>

<template>
  <Head title="Kelola Komik & Chapter - Creator Studio" />

  <AdminLayout>
    <div class="space-y-8">
      <!-- Flash Alerts -->
      <div v-if="flashSuccess" class="p-4 bg-emerald-500/10 border border-emerald-500/30 rounded-xl text-emerald-400 text-sm font-medium flex items-center gap-2">
        <CheckBadgeIcon class="w-5 h-5 shrink-0" />
        {{ flashSuccess }}
      </div>
      <div v-if="flashError" class="p-4 bg-rose-500/10 border border-rose-500/30 rounded-xl text-rose-400 text-sm font-medium flex items-center gap-2">
        <ExclamationTriangleIcon class="w-5 h-5 shrink-0" />
        {{ flashError }}
      </div>

      <!-- Page Header -->
      <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div>
          <span class="text-xs text-sky-400 font-extrabold uppercase tracking-wider">Creator Studio</span>
          <h1 class="text-3xl font-extrabold text-white flex items-center gap-3">
            <BookOpenIcon class="w-8 h-8 text-sky-400 shrink-0" />
            Kelola Komik & Chapter Studio
          </h1>
          <p class="text-sm text-slate-400 mt-1">Kelola serial webcomic, terbitkan bab baru, dan atur detail terbitan studio Anda</p>
        </div>

        <Link
          v-if="isApproved"
          href="/publisher/comics/create"
          class="inline-flex items-center gap-2 px-5 py-3 rounded-xl text-xs font-bold bg-sky-600 hover:bg-sky-500 text-white transition shadow-lg shadow-sky-600/30 self-start"
        >
          <PlusIcon class="w-4 h-4" />
          Buat Serial Komik Baru
        </Link>
        <button
          v-else
          disabled
          class="inline-flex items-center gap-2 px-5 py-3 rounded-xl text-xs font-bold bg-slate-800 text-slate-500 border border-slate-700/50 cursor-not-allowed opacity-60 self-start"
        >
          <LockClosedIcon class="w-4 h-4" />
          Buat Komik Baru (Studio Belum Verified)
        </button>
      </div>

      <!-- Search & Filters Bar -->
      <div class="flex flex-col md:flex-row items-center justify-between gap-4 bg-slate-900 border border-slate-800 p-4 rounded-2xl">
        <div class="flex items-center gap-3 w-full md:w-auto">
          <select
            v-model="statusFilter"
            @change="handleSearch"
            class="bg-slate-950 border border-slate-800 text-xs font-semibold text-slate-300 rounded-xl px-3 py-2 focus:outline-none focus:border-sky-500"
          >
            <option value="">Semua Status Serial</option>
            <option value="ongoing">Ongoing</option>
            <option value="completed">Completed</option>
            <option value="hiatus">Hiatus</option>
          </select>

          <Link
            v-if="statusFilter || search"
            href="/publisher/comics"
            class="inline-flex items-center gap-1 px-3 py-2 rounded-xl text-xs font-bold bg-slate-950 border border-slate-800 text-slate-400 hover:text-white"
          >
            <XMarkIcon class="w-3.5 h-3.5" /> Reset
          </Link>
        </div>

        <div class="w-full md:w-72">
          <input
            v-model="search"
            @keyup.enter="handleSearch"
            type="text"
            placeholder="Cari judul komik studio..."
            class="w-full bg-slate-950 border border-slate-800 rounded-xl px-4 py-2 text-xs text-white placeholder-slate-500 focus:outline-none focus:border-sky-500"
          />
        </div>
      </div>

      <!-- Comics List & Accordions -->
      <div v-if="comics && comics.length" class="space-y-6">
        <div
          v-for="comic in comics"
          :key="comic.id"
          class="bg-slate-900 border border-slate-800 rounded-2xl overflow-hidden shadow-xl"
        >
          <!-- Comic Card Header -->
          <div class="p-6 flex flex-col lg:flex-row gap-6 items-start justify-between bg-slate-900">
            <div class="flex items-start gap-4">
              <img
                :src="comic.cover_image"
                :alt="comic.title"
                class="w-20 h-28 object-cover rounded-xl border border-slate-800 shrink-0 bg-slate-950 shadow-md"
              />
              <div class="space-y-2">
                <div class="flex items-center gap-2 flex-wrap">
                  <span
                    class="text-[10px] font-extrabold uppercase px-2.5 py-0.5 rounded-md border"
                    :class="[
                      comic.status === 'ongoing' ? 'bg-sky-500/10 text-sky-400 border-sky-500/30' :
                      comic.status === 'completed' ? 'bg-emerald-500/10 text-emerald-400 border-emerald-500/30' :
                      'bg-amber-500/10 text-amber-400 border-amber-500/30'
                    ]"
                  >
                    {{ comic.status }}
                  </span>
                  <span v-for="g in comic.genres" :key="g.id" class="text-[9px] font-bold px-2 py-0.5 rounded bg-slate-950 text-slate-300 border border-slate-800">
                    {{ g.name }}
                  </span>
                </div>

                <h2 class="text-xl font-bold text-white">{{ comic.title }}</h2>

                <div class="flex items-center gap-4 text-xs text-slate-400 flex-wrap">
                  <span class="flex items-center gap-1 text-amber-400 font-semibold">
                    <StarIcon class="w-4 h-4 text-amber-400" />
                    {{ comic.rating_average ? comic.rating_average.toFixed(1) : '0.0' }}
                  </span>
                  <span class="flex items-center gap-1">
                    <EyeIcon class="w-4 h-4 text-slate-400" />
                    {{ comic.total_views ? comic.total_views.toLocaleString() : 0 }} Views
                  </span>
                  <span class="flex items-center gap-1 font-semibold text-slate-300">
                    <DocumentDuplicateIcon class="w-4 h-4 text-purple-400" />
                    {{ comic.chapters ? comic.chapters.length : 0 }} Chapter
                  </span>
                </div>
              </div>
            </div>

            <!-- Action Buttons -->
            <div class="flex flex-wrap items-center gap-2.5 w-full lg:w-auto border-t lg:border-t-0 border-slate-800 pt-4 lg:pt-0">
              <Link
                v-if="isApproved"
                :href="`/publisher/comics/${comic.id}/chapters/create`"
                class="inline-flex items-center gap-1.5 px-4 py-2 rounded-xl text-xs font-bold bg-sky-600 hover:bg-sky-500 text-white transition shadow-md shadow-sky-600/20"
              >
                <PlusIcon class="w-4 h-4" /> Add Chapter
              </Link>

              <Link
                :href="`/publisher/comics/${comic.id}/edit`"
                class="inline-flex items-center gap-1.5 px-3 py-2 rounded-xl text-xs font-bold text-slate-300 bg-slate-800 hover:bg-slate-700 hover:text-white transition border border-slate-700"
              >
                <PencilSquareIcon class="w-3.5 h-3.5" /> Edit Komik
              </Link>

              <button
                @click="deleteComic(comic.id, comic.title)"
                class="inline-flex items-center gap-1.5 px-3 py-2 rounded-xl text-xs font-bold text-rose-400 bg-rose-950/40 border border-rose-800/80 hover:bg-rose-900 transition"
              >
                <TrashIcon class="w-3.5 h-3.5" /> Hapus
              </button>

              <button
                @click="toggleAccordion(comic.id)"
                class="inline-flex items-center gap-1 px-3 py-2 rounded-xl text-xs font-semibold bg-slate-950 border border-slate-800 text-slate-400 hover:text-white transition ml-auto lg:ml-0"
              >
                <span>{{ expandedComicId === comic.id ? 'Sembunyikan Bab' : 'Daftar Bab' }}</span>
                <ChevronUpIcon v-if="expandedComicId === comic.id" class="w-4 h-4" />
                <ChevronDownIcon v-else class="w-4 h-4" />
              </button>
            </div>
          </div>

          <!-- Expandable Chapter List Accordion -->
          <div v-if="expandedComicId === comic.id" class="border-t border-slate-800 bg-slate-950/70 p-6 space-y-4">
            <div class="flex items-center justify-between">
              <h3 class="text-xs font-extrabold uppercase text-slate-400 tracking-wider flex items-center gap-2">
                <DocumentDuplicateIcon class="w-4 h-4 text-purple-400" />
                Daftar Chapter ({{ comic.chapters ? comic.chapters.length : 0 }})
              </h3>
              <Link
                v-if="isApproved"
                :href="`/publisher/comics/${comic.id}/chapters/create`"
                class="text-xs font-bold text-sky-400 hover:underline flex items-center gap-1"
              >
                + Terbitkan Bab Baru
              </Link>
            </div>

            <div v-if="comic.chapters && comic.chapters.length" class="overflow-x-auto">
              <table class="w-full text-left text-xs text-slate-300">
                <thead class="text-[11px] uppercase bg-slate-900 text-slate-400 border-b border-slate-800">
                  <tr>
                    <th class="px-4 py-3">Bab</th>
                    <th class="px-4 py-3">Judul Bab</th>
                    <th class="px-4 py-3">Akses & Harga</th>
                    <th class="px-4 py-3 text-right">Aksi Bab</th>
                  </tr>
                </thead>
                <tbody class="divide-y divide-slate-800/60">
                  <tr v-for="ch in comic.chapters" :key="ch.id" class="hover:bg-slate-900/50 transition">
                    <td class="px-4 py-3 font-extrabold text-white whitespace-nowrap">
                      Bab {{ ch.chapter_number }}
                    </td>
                    <td class="px-4 py-3 font-medium text-slate-200">
                      {{ ch.title }}
                    </td>
                    <td class="px-4 py-3">
                      <span v-if="ch.is_free" class="px-2 py-0.5 text-[9px] font-extrabold rounded bg-emerald-500/10 text-emerald-400 border border-emerald-500/30 uppercase">
                        Gratis
                      </span>
                      <span v-else class="text-amber-400 font-bold">
                        Rp {{ ch.price.toLocaleString() }}
                      </span>
                    </td>
                    <td class="px-4 py-3 text-right space-x-2 whitespace-nowrap">
                      <Link
                        :href="`/publisher/comics/${comic.id}/chapters/${ch.id}/edit`"
                        class="inline-flex items-center gap-1 px-2.5 py-1 rounded-lg text-xs font-semibold text-slate-300 bg-slate-800 hover:bg-slate-700 hover:text-white transition"
                      >
                        <PencilSquareIcon class="w-3.5 h-3.5" /> Edit
                      </Link>
                      <button
                        @click="deleteChapter(comic.id, ch.id, ch.chapter_number)"
                        class="inline-flex items-center gap-1 px-2.5 py-1 rounded-lg text-xs font-semibold text-rose-400 bg-rose-950/40 border border-rose-800/80 hover:bg-rose-900 transition"
                      >
                        <TrashIcon class="w-3.5 h-3.5" /> Hapus
                      </button>
                    </td>
                  </tr>
                </tbody>
              </table>
            </div>

            <div v-else class="text-center py-6 text-slate-500 text-xs bg-slate-900/40 rounded-xl border border-slate-800/60">
              Belum ada bab yang diterbitkan untuk komik ini.
            </div>
          </div>
        </div>
      </div>

      <!-- Empty State -->
      <div v-else class="bg-slate-900/40 border border-slate-800 rounded-2xl p-16 text-center space-y-4">
        <div class="flex justify-center">
          <div class="w-16 h-16 rounded-2xl bg-slate-800/60 flex items-center justify-center">
            <SwatchIcon class="w-8 h-8 text-slate-500" />
          </div>
        </div>
        <h2 class="text-xl font-bold text-white">Belum Ada Serial Komik</h2>
        <p class="text-sm text-slate-400 max-w-md mx-auto">
          <template v-if="isApproved">Mulai buat serial komik pertama studio Anda dan terbitkan bab-bab menarik.</template>
          <template v-else>Akun studio Anda belum terverifikasi. Pembuatan komik baru akan aktif setelah disetujui admin.</template>
        </p>
        <div class="pt-2" v-if="isApproved">
          <Link href="/publisher/comics/create" class="inline-flex items-center gap-2 px-6 py-3 rounded-xl bg-sky-600 hover:bg-sky-500 text-white font-bold text-sm transition">
            <PlusIcon class="w-4 h-4" /> Buat Serial Komik Baru
          </Link>
        </div>
      </div>
    </div>
  </AdminLayout>
</template>
