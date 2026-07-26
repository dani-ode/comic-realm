<script setup lang="ts">
import { ref } from 'vue';
import { Head, useForm, router, Link } from '@inertiajs/vue3';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import {
  RectangleStackIcon,
  ArrowLeftIcon,
  DocumentTextIcon,
  BookOpenIcon,
  BanknotesIcon,
  DocumentDuplicateIcon,
  CheckBadgeIcon,
  Cog6ToothIcon,
  PhotoIcon,
  PencilSquareIcon,
  TrashIcon,
} from '@heroicons/vue/24/outline';

interface Chapter {
  id: number;
  comic_id: number;
  title: string;
  slug: string;
  chapter_number: number;
  is_free: boolean;
  price: number;
  status: string;
  pages_count?: number;
  published_at?: string;
  comic?: { id: number; title: string };
}

interface Comic {
  id: number;
  title: string;
}

interface Paginator {
  data: Chapter[];
  links: any[];
}

const props = defineProps<{
  chapters: Paginator;
  comics: Comic[];
  selectedComic?: Comic | null;
  filters?: { comic_id?: string; search?: string };
}>();

const search = ref(props.filters?.search || '');
const comicFilter = ref(props.filters?.comic_id || '');

const deleteForm = useForm({});

// Edit Modal State
const showEditModal = ref(false);
const editForm = useForm({
  id: 0,
  title: '',
  chapter_number: 1,
  is_free: true,
  price: 0,
  status: 'published',
});

const handleSearch = () => {
  router.get('/admin/chapters', {
    search: search.value,
    comic_id: comicFilter.value,
  }, { preserveState: true, replace: true });
};

const openEditModal = (chapter: Chapter) => {
  editForm.id = chapter.id;
  editForm.title = chapter.title;
  editForm.chapter_number = chapter.chapter_number;
  editForm.is_free = chapter.is_free;
  editForm.price = chapter.price;
  editForm.status = chapter.status;
  showEditModal.value = true;
};

const submitUpdate = () => {
  editForm.post(`/admin/chapters/${editForm.id}/update`, {
    onSuccess: () => {
      showEditModal.value = false;
    },
  });
};

const deleteChapter = (id: number, number: number) => {
  if (confirm(`Apakah Anda yakin ingin menghapus Bab ${number}?`)) {
    deleteForm.delete(`/admin/chapters/${id}`);
  }
};
</script>

<template>
  <Head title="Kelola Chapter - Admin Panel" />

  <AdminLayout>
    <div class="space-y-8">
      <!-- Header -->
      <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
        <div>
          <span class="text-xs text-amber-400 font-bold uppercase tracking-wider">Super Admin Control</span>
          <h1 class="text-3xl font-extrabold text-white flex items-center gap-3">
            <RectangleStackIcon class="w-8 h-8 text-amber-400 shrink-0" />
            Kelola Chapter Komik
          </h1>
          <p class="text-sm text-slate-400 mt-1">
            <span v-if="selectedComic">Menampilkan bab dari komik: <strong class="text-amber-400">{{ selectedComic.title }}</strong></span>
            <span v-else>Moderasi dan kelola bab/chapter komik dari seluruh studio</span>
          </p>
        </div>

        <Link
          v-if="comicFilter"
          href="/admin/chapters"
          class="inline-flex items-center gap-1.5 px-4 py-2 rounded-xl text-xs font-bold bg-slate-900 border border-slate-800 text-slate-300 hover:text-white transition"
        >
          <ArrowLeftIcon class="w-3.5 h-3.5" /> Tampilkan Semua Chapter
        </Link>
      </div>

      <!-- Filters & Search Bar -->
      <div class="flex flex-col md:flex-row items-center justify-between gap-4 bg-slate-900 border border-slate-800 p-4 rounded-2xl">
        <div class="w-full md:w-auto">
          <!-- Filter by Comic -->
          <select
            v-model="comicFilter"
            @change="handleSearch"
            class="bg-slate-950 border border-slate-800 text-xs font-semibold text-slate-300 rounded-xl px-3 py-2 focus:outline-none focus:border-amber-500 max-w-xs"
          >
            <option value="">Semua Judul Komik</option>
            <option v-for="c in comics" :key="c.id" :value="c.id">
              {{ c.title }}
            </option>
          </select>
        </div>

        <div class="w-full md:w-72">
          <input
            v-model="search"
            @keyup.enter="handleSearch"
            type="text"
            placeholder="Cari judul bab / komik..."
            class="w-full bg-slate-950 border border-slate-800 rounded-xl px-4 py-2 text-xs text-white placeholder-slate-500 focus:outline-none focus:border-amber-500"
          />
        </div>
      </div>

      <!-- Chapters Table -->
      <div v-if="chapters.data && chapters.data.length" class="bg-slate-900 border border-slate-800 rounded-2xl overflow-hidden shadow-xl">
        <div class="overflow-x-auto">
          <table class="w-full text-left text-sm text-slate-300">
            <thead class="text-xs uppercase bg-slate-950 text-slate-400 border-b border-slate-800">
              <tr>
                <th class="px-6 py-4 flex items-center gap-1.5"><DocumentTextIcon class="w-3.5 h-3.5" /> Bab / Chapter</th>
                <th class="px-6 py-4"><BookOpenIcon class="w-3.5 h-3.5 inline mr-1" /> Judul Komik</th>
                <th class="px-6 py-4"><BanknotesIcon class="w-3.5 h-3.5 inline mr-1" /> Akses & Harga</th>
                <th class="px-6 py-4"><DocumentDuplicateIcon class="w-3.5 h-3.5 inline mr-1" /> Jumlah Halaman</th>
                <th class="px-6 py-4"><CheckBadgeIcon class="w-3.5 h-3.5 inline mr-1" /> Status</th>
                <th class="px-6 py-4 text-right"><Cog6ToothIcon class="w-3.5 h-3.5 inline mr-1" /> Aksi Moderasi</th>
              </tr>
            </thead>
            <tbody class="divide-y divide-slate-800/60">
              <tr v-for="ch in chapters.data" :key="ch.id" class="hover:bg-slate-800/30 transition">
                <td class="px-6 py-4">
                  <h4 class="font-bold text-white text-sm">Bab {{ ch.chapter_number }}</h4>
                  <p class="text-xs text-slate-400 line-clamp-1">{{ ch.title }}</p>
                </td>

                <td class="px-6 py-4 text-xs font-semibold text-amber-400">
                  {{ ch.comic ? ch.comic.title : '-' }}
                </td>

                <td class="px-6 py-4 text-xs">
                  <span v-if="ch.is_free" class="px-2 py-0.5 text-[10px] font-extrabold rounded bg-emerald-500/10 text-emerald-400 border border-emerald-500/30 uppercase">
                    Gratis
                  </span>
                  <span v-else class="text-amber-400 font-bold">
                    Rp {{ ch.price.toLocaleString() }}
                  </span>
                </td>

                <td class="px-6 py-4 text-xs text-slate-300 font-mono">
                  <span class="inline-flex items-center gap-1">
                    <PhotoIcon class="w-4 h-4 text-slate-400" />
                    {{ ch.pages_count || 0 }} Gambar
                  </span>
                </td>

                <td class="px-6 py-4">
                  <span
                    class="px-2.5 py-1 text-[10px] font-extrabold rounded-lg uppercase tracking-wider"
                    :class="[
                      ch.status === 'published' ? 'bg-emerald-500/10 text-emerald-400 border border-emerald-500/30' :
                      'bg-amber-500/10 text-amber-400 border border-amber-500/30'
                    ]"
                  >
                    {{ ch.status }}
                  </span>
                </td>

                <td class="px-6 py-4 text-right space-x-2 whitespace-nowrap">
                  <button
                    @click="openEditModal(ch)"
                    class="inline-flex items-center gap-1 px-3 py-1.5 rounded-lg text-xs font-bold text-slate-300 bg-slate-800 hover:bg-slate-700 hover:text-white transition border border-slate-700"
                  >
                    <PencilSquareIcon class="w-3.5 h-3.5" /> Edit / Status
                  </button>

                  <button
                    @click="deleteChapter(ch.id, ch.chapter_number)"
                    class="inline-flex items-center gap-1 px-3 py-1.5 rounded-lg text-xs font-bold text-rose-400 bg-rose-950/40 border border-rose-800/80 hover:bg-rose-900 transition"
                  >
                    <TrashIcon class="w-3.5 h-3.5" /> Hapus
                  </button>
                </td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>

      <div v-else class="bg-slate-900 border border-slate-800 rounded-2xl p-12 text-center text-slate-400">
        Belum ada chapter ditemukan.
      </div>
    </div>

    <!-- Edit Chapter Modal -->
    <div v-if="showEditModal" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/70 backdrop-blur-sm">
      <div class="bg-slate-900 border border-slate-800 rounded-2xl max-w-md w-full p-6 space-y-4 shadow-2xl">
        <h3 class="text-lg font-bold text-white">Edit & Moderasi Bab</h3>

        <div class="space-y-3">
          <div>
            <label class="block text-xs font-semibold text-slate-400 mb-1">Judul Bab</label>
            <input
              v-model="editForm.title"
              type="text"
              class="w-full bg-slate-950 border border-slate-800 rounded-xl p-2.5 text-xs text-white focus:outline-none focus:border-amber-500"
            />
          </div>

          <div class="grid grid-cols-2 gap-3">
            <div>
              <label class="block text-xs font-semibold text-slate-400 mb-1">Nomor Bab</label>
              <input
                v-model="editForm.chapter_number"
                type="number"
                step="0.1"
                class="w-full bg-slate-950 border border-slate-800 rounded-xl p-2.5 text-xs text-white focus:outline-none focus:border-amber-500"
              />
            </div>
            <div>
              <label class="block text-xs font-semibold text-slate-400 mb-1">Harga (Rp)</label>
              <input
                v-model="editForm.price"
                :disabled="editForm.is_free"
                type="number"
                class="w-full bg-slate-950 border border-slate-800 rounded-xl p-2.5 text-xs text-white focus:outline-none focus:border-amber-500 disabled:opacity-50"
              />
            </div>
          </div>

          <div class="flex items-center gap-2 pt-1">
            <input
              v-model="editForm.is_free"
              type="checkbox"
              id="is_free"
              class="rounded bg-slate-950 border-slate-800 text-amber-500 focus:ring-0"
            />
            <label for="is_free" class="text-xs font-semibold text-slate-300">Bab Gratis (Free Chapter)</label>
          </div>

          <div>
            <label class="block text-xs font-semibold text-slate-400 mb-1">Status Publikasi</label>
            <select
              v-model="editForm.status"
              class="w-full bg-slate-950 border border-slate-800 rounded-xl p-2.5 text-xs text-white focus:outline-none focus:border-amber-500"
            >
              <option value="published">Published</option>
              <option value="draft">Draft</option>
            </select>
          </div>
        </div>

        <div class="flex items-center justify-end gap-3 pt-2">
          <button @click="showEditModal = false" class="px-4 py-2 rounded-xl text-xs font-semibold text-slate-400 hover:text-white">
            Batal
          </button>
          <button @click="submitUpdate" class="px-4 py-2 rounded-xl text-xs font-bold text-slate-950 bg-amber-500 hover:bg-amber-400 shadow-lg shadow-amber-500/20">
            Simpan Bab
          </button>
        </div>
      </div>
    </div>
  </AdminLayout>
</template>
