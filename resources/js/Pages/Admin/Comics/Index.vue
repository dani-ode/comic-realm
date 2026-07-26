<script setup lang="ts">
import { ref } from 'vue';
import { Head, useForm, router, Link } from '@inertiajs/vue3';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import {
  BookOpenIcon,
  BuildingStorefrontIcon,
  UserIcon,
  RectangleStackIcon,
  CheckBadgeIcon,
  Cog6ToothIcon,
  XMarkIcon,
  PencilSquareIcon,
  TrashIcon,
  ArrowRightIcon,
} from '@heroicons/vue/24/outline';

interface Comic {
  id: number;
  title: string;
  slug: string;
  cover_image?: string;
  author_name?: string;
  artist_name?: string;
  status: string;
  publication_status: string;
  total_views: number;
  chapters_count?: number;
  publisher?: { name: string; email: string };
  genres?: { id: number; name: string }[];
  created_at: string;
}

interface Paginator {
  data: Comic[];
  links: any[];
}

interface Genre {
  id: number;
  name: string;
}

interface Publisher {
  id: number;
  user_id: number;
  brand_name: string;
  user?: { name: string; email: string };
}

const props = defineProps<{
  comics: Paginator;
  genres: Genre[];
  publishers?: Publisher[];
  filters?: { status?: string; publication_status?: string; publisher_id?: string; search?: string };
}>();

const search = ref(props.filters?.search || '');
const statusFilter = ref(props.filters?.status || '');
const pubStatusFilter = ref(props.filters?.publication_status || '');
const publisherFilter = ref(props.filters?.publisher_id || '');

const deleteForm = useForm({});

// Edit Modal state
const showEditModal = ref(false);
const editForm = useForm({
  id: 0,
  title: '',
  description: '',
  cover_image: '',
  author_name: '',
  artist_name: '',
  status: 'ongoing',
  publication_status: 'published',
  genres: [] as number[],
});

const handleSearch = () => {
  router.get('/admin/comics', {
    search: search.value,
    status: statusFilter.value,
    publication_status: pubStatusFilter.value,
    publisher_id: publisherFilter.value,
  }, { preserveState: true, replace: true });
};

const openEditModal = (comic: Comic) => {
  editForm.id = comic.id;
  editForm.title = comic.title;
  editForm.description = ''; // keep existing unless changed
  editForm.cover_image = comic.cover_image || '';
  editForm.author_name = comic.author_name || '';
  editForm.artist_name = comic.artist_name || '';
  editForm.status = comic.status || 'ongoing';
  editForm.publication_status = comic.publication_status || 'published';
  editForm.genres = comic.genres ? comic.genres.map(g => g.id) : [];
  showEditModal.value = true;
};

const submitUpdate = () => {
  editForm.post(`/admin/comics/${editForm.id}/update`, {
    onSuccess: () => {
      showEditModal.value = false;
    },
  });
};

const deleteComic = (id: number, title: string) => {
  if (confirm(`Apakah Anda yakin ingin menghapus komik "${title}"?`)) {
    deleteForm.delete(`/admin/comics/${id}`);
  }
};
</script>

<template>
  <Head title="Kelola Komik - Admin Panel" />

  <AdminLayout>
    <div class="space-y-8">
      <!-- Header -->
      <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
        <div>
          <span class="text-xs text-amber-400 font-bold uppercase tracking-wider">Super Admin Control</span>
          <h1 class="text-3xl font-extrabold text-white flex items-center gap-3">
            <BookOpenIcon class="w-8 h-8 text-amber-400 shrink-0" />
            Kelola Komik & Serial Webcomic
          </h1>
          <p class="text-sm text-slate-400 mt-1">Moderasi publikasi, ubah status komik, dan kelola karya terbitan seluruh studio</p>
        </div>

        <Link
          v-if="publisherFilter || statusFilter || pubStatusFilter || search"
          href="/admin/comics"
          class="inline-flex items-center gap-1 px-4 py-2 rounded-xl text-xs font-bold bg-slate-900 border border-amber-500/40 text-amber-400 hover:border-amber-400 whitespace-nowrap"
        >
          <XMarkIcon class="w-3.5 h-3.5" /> Reset Semua Filter
        </Link>
      </div>

      <!-- Filters & Search Bar -->
      <div class="flex flex-col md:flex-row items-center justify-between gap-4 bg-slate-900 border border-slate-800 p-4 rounded-2xl">
        <div class="flex flex-wrap items-center gap-2 w-full md:w-auto">
          <!-- Status Filter -->
          <select
            v-model="statusFilter"
            @change="handleSearch"
            class="bg-slate-950 border border-slate-800 text-xs font-semibold text-slate-300 rounded-xl px-3 py-2 focus:outline-none focus:border-amber-500"
          >
            <option value="">Semua Status Serial</option>
            <option value="ongoing">Ongoing</option>
            <option value="completed">Completed</option>
            <option value="hiatus">Hiatus</option>
          </select>

          <!-- Publisher / Studio Filter -->
          <select
            v-model="publisherFilter"
            @change="handleSearch"
            class="bg-slate-950 border border-slate-800 text-xs font-semibold text-slate-300 rounded-xl px-3 py-2 focus:outline-none focus:border-amber-500"
          >
            <option value="">Semua Studio / Publisher</option>
            <option v-for="p in publishers" :key="p.id" :value="p.user_id">
              {{ p.brand_name }} ({{ p.user ? p.user.name : 'User' }})
            </option>
          </select>

          <!-- Publication Status Filter -->
          <select
            v-model="pubStatusFilter"
            @change="handleSearch"
            class="bg-slate-950 border border-slate-800 text-xs font-semibold text-slate-300 rounded-xl px-3 py-2 focus:outline-none focus:border-amber-500"
          >
            <option value="">Semua Status Publikasi</option>
            <option value="published">Published</option>
            <option value="draft">Draft</option>
            <option value="archived">Archived</option>
          </select>
        </div>

        <div class="w-full md:w-72">
          <input
            v-model="search"
            @keyup.enter="handleSearch"
            type="text"
            placeholder="Cari judul komik / penulis..."
            class="w-full bg-slate-950 border border-slate-800 rounded-xl px-4 py-2 text-xs text-white placeholder-slate-500 focus:outline-none focus:border-amber-500"
          />
        </div>
      </div>

      <!-- Comics Table -->
      <div v-if="comics.data && comics.data.length" class="bg-slate-900 border border-slate-800 rounded-2xl overflow-hidden shadow-xl">
        <div class="overflow-x-auto">
          <table class="w-full text-left text-sm text-slate-300">
            <thead class="text-xs uppercase bg-slate-950 text-slate-400 border-b border-slate-800">
              <tr>
                <th class="px-6 py-4 flex items-center gap-1.5"><BookOpenIcon class="w-3.5 h-3.5" /> Judul & Cover</th>
                <th class="px-6 py-4"><BuildingStorefrontIcon class="w-3.5 h-3.5 inline mr-1" /> Publisher / Studio</th>
                <th class="px-6 py-4"><UserIcon class="w-3.5 h-3.5 inline mr-1" /> Author / Artist</th>
                <th class="px-6 py-4"><RectangleStackIcon class="w-3.5 h-3.5 inline mr-1" /> Chapters</th>
                <th class="px-6 py-4"><CheckBadgeIcon class="w-3.5 h-3.5 inline mr-1" /> Status Publikasi</th>
                <th class="px-6 py-4 text-right"><Cog6ToothIcon class="w-3.5 h-3.5 inline mr-1" /> Aksi Moderasi</th>
              </tr>
            </thead>
            <tbody class="divide-y divide-slate-800/60">
              <tr v-for="c in comics.data" :key="c.id" class="hover:bg-slate-800/30 transition">
                <td class="px-6 py-4">
                  <div class="flex items-center gap-3">
                    <img
                      v-if="c.cover_image"
                      :src="c.cover_image"
                      alt="Cover"
                      class="w-12 h-16 object-cover rounded-lg border border-slate-700 shrink-0"
                    />
                    <div v-else class="w-12 h-16 bg-slate-800 rounded-lg flex items-center justify-center shrink-0">
                      <BookOpenIcon class="w-6 h-6 text-slate-500" />
                    </div>
                    <div>
                      <h3 class="font-bold text-white text-sm">{{ c.title }}</h3>
                      <div class="flex items-center gap-1.5 mt-1 flex-wrap">
                        <span v-for="g in c.genres" :key="g.id" class="text-[9px] font-bold px-2 py-0.5 rounded bg-slate-800 text-amber-400 border border-slate-700">
                          {{ g.name }}
                        </span>
                      </div>
                    </div>
                  </div>
                </td>

                <td class="px-6 py-4 text-xs">
                  <span class="text-white font-semibold">{{ c.publisher ? c.publisher.name : 'Unknown Studio' }}</span>
                </td>

                <td class="px-6 py-4 text-xs text-slate-400">
                  <p class="text-white font-medium">{{ c.author_name || '-' }}</p>
                  <p class="text-slate-500 text-[11px]">Artist: {{ c.artist_name || '-' }}</p>
                </td>

                <td class="px-6 py-4 text-xs font-bold">
                  <Link
                    :href="`/admin/chapters?comic_id=${c.id}`"
                    class="text-purple-400 hover:text-purple-300 underline inline-flex items-center gap-1"
                  >
                    <BookOpenIcon class="w-3.5 h-3.5" />
                    {{ c.chapters_count || 0 }} Chapter
                    <ArrowRightIcon class="w-3 h-3" />
                  </Link>
                </td>

                <td class="px-6 py-4">
                  <span
                    class="px-2.5 py-1 text-[10px] font-extrabold rounded-lg uppercase tracking-wider"
                    :class="[
                      c.publication_status === 'published' ? 'bg-emerald-500/10 text-emerald-400 border border-emerald-500/30' :
                      c.publication_status === 'draft' ? 'bg-amber-500/10 text-amber-400 border border-amber-500/30' :
                      'bg-rose-500/10 text-rose-400 border border-rose-500/30'
                    ]"
                  >
                    {{ c.publication_status }}
                  </span>
                </td>

                <td class="px-6 py-4 text-right space-x-2 whitespace-nowrap">
                  <button
                    @click="openEditModal(c)"
                    class="inline-flex items-center gap-1 px-3 py-1.5 rounded-lg text-xs font-bold text-slate-300 bg-slate-800 hover:bg-slate-700 hover:text-white transition border border-slate-700"
                  >
                    <PencilSquareIcon class="w-3.5 h-3.5" /> Edit / Status
                  </button>

                  <button
                    @click="deleteComic(c.id, c.title)"
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
        Belum ada komik terdaftar.
      </div>
    </div>

    <!-- Edit Comic Modal -->
    <div v-if="showEditModal" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/70 backdrop-blur-sm">
      <div class="bg-slate-900 border border-slate-800 rounded-2xl max-w-lg w-full p-6 space-y-4 shadow-2xl">
        <h3 class="text-lg font-bold text-white">Edit & Moderasi Komik</h3>

        <div class="space-y-3">
          <div>
            <label class="block text-xs font-semibold text-slate-400 mb-1">Judul Komik</label>
            <input
              v-model="editForm.title"
              type="text"
              class="w-full bg-slate-950 border border-slate-800 rounded-xl p-2.5 text-xs text-white focus:outline-none focus:border-amber-500"
            />
          </div>

          <div class="grid grid-cols-2 gap-3">
            <div>
              <label class="block text-xs font-semibold text-slate-400 mb-1">Penulis (Author)</label>
              <input
                v-model="editForm.author_name"
                type="text"
                class="w-full bg-slate-950 border border-slate-800 rounded-xl p-2.5 text-xs text-white focus:outline-none focus:border-amber-500"
              />
            </div>
            <div>
              <label class="block text-xs font-semibold text-slate-400 mb-1">Artist (Komikus)</label>
              <input
                v-model="editForm.artist_name"
                type="text"
                class="w-full bg-slate-950 border border-slate-800 rounded-xl p-2.5 text-xs text-white focus:outline-none focus:border-amber-500"
              />
            </div>
          </div>

          <div class="grid grid-cols-2 gap-3">
            <div>
              <label class="block text-xs font-semibold text-slate-400 mb-1">Status Serial</label>
              <select
                v-model="editForm.status"
                class="w-full bg-slate-950 border border-slate-800 rounded-xl p-2.5 text-xs text-white focus:outline-none focus:border-amber-500"
              >
                <option value="ongoing">Ongoing</option>
                <option value="completed">Completed</option>
                <option value="hiatus">Hiatus</option>
              </select>
            </div>

            <div>
              <label class="block text-xs font-semibold text-slate-400 mb-1">Status Publikasi</label>
              <select
                v-model="editForm.publication_status"
                class="w-full bg-slate-950 border border-slate-800 rounded-xl p-2.5 text-xs text-white focus:outline-none focus:border-amber-500"
              >
                <option value="published">Published</option>
                <option value="draft">Draft</option>
                <option value="archived">Archived / Hidden</option>
              </select>
            </div>
          </div>
        </div>

        <div class="flex items-center justify-end gap-3 pt-2">
          <button @click="showEditModal = false" class="px-4 py-2 rounded-xl text-xs font-semibold text-slate-400 hover:text-white">
            Batal
          </button>
          <button @click="submitUpdate" class="px-4 py-2 rounded-xl text-xs font-bold text-slate-950 bg-amber-500 hover:bg-amber-400 shadow-lg shadow-amber-500/20">
            Simpan Perubahan
          </button>
        </div>
      </div>
    </div>
  </AdminLayout>
</template>
