<script setup lang="ts">
import { ref } from 'vue';
import { Head, Link, useForm, router } from '@inertiajs/vue3';
import {
  EyeIcon,
  EyeSlashIcon,
  TrashIcon,
  BookOpenIcon,
  ExclamationTriangleIcon,
  ArrowTopRightOnSquareIcon,
} from '@heroicons/vue/24/outline';
import AdminLayout from '@/Layouts/AdminLayout.vue';

interface ComicRef {
  id: number;
  title: string;
  slug: string;
}

interface ChapterRef {
  id: number;
  chapter_number: number;
  title?: string;
  comic?: { id: number; slug: string };
}

interface CommentItem {
  id: number;
  comment_text: string;
  is_spoiler: boolean;
  status: string;
  created_at: string;
  user?: { name: string; email: string };
  comic?: ComicRef;
  chapter?: ChapterRef;
}

interface Paginator {
  data: CommentItem[];
  links: any[];
}

interface Metrics {
  total_comments: number;
  published_comments: number;
  hidden_comments: number;
  flagged_comments: number;
}

const props = defineProps<{
  comments: Paginator;
  metrics: Metrics;
  filters?: { status?: string; search?: string };
}>();

const search = ref(props.filters?.search || '');
const statusFilter = ref(props.filters?.status || '');

const statusForm = useForm({
  status: '',
});

const deleteForm = useForm({});

const handleSearch = () => {
  router.get('/admin/comments', {
    search: search.value,
    status: statusFilter.value,
  }, { preserveState: true, replace: true });
};

const filterByStatus = (status: string) => {
  statusFilter.value = status;
  handleSearch();
};

const updateCommentStatus = (id: number, status: string) => {
  statusForm.status = status;
  statusForm.post(`/admin/comments/${id}/toggle-status`);
};

const deleteComment = (id: number) => {
  if (confirm('Apakah Anda yakin ingin menghapus komentar ini?')) {
    deleteForm.delete(`/admin/comments/${id}`);
  }
};
</script>

<template>
  <Head title="Kelola Komentar - Admin Control" />

  <AdminLayout>
    <div class="space-y-8">
      <!-- Header -->
      <div>
        <span class="text-xs text-amber-400 font-bold uppercase tracking-wider">Super Admin Control</span>
        <h1 class="text-3xl font-extrabold text-white">Kelola Komentar Reader</h1>
        <p class="text-sm text-slate-400 mt-1">Moderasi komentar pengguna, sembunyikan komentar bermasalah atau tangani dilaporkan</p>
      </div>

      <!-- Summary Metrics -->
      <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
        <div class="bg-slate-900 border border-slate-800 rounded-2xl p-5 space-y-1">
          <span class="text-xs text-slate-400 font-semibold uppercase tracking-wider">Total Komentar</span>
          <div class="text-2xl font-extrabold text-white">{{ metrics.total_comments }}</div>
          <p class="text-[11px] text-slate-500">Komentar masuk</p>
        </div>

        <div class="bg-slate-900 border border-slate-800 rounded-2xl p-5 space-y-1">
          <span class="text-xs text-slate-400 font-semibold uppercase tracking-wider">Published</span>
          <div class="text-2xl font-extrabold text-emerald-400">{{ metrics.published_comments }}</div>
          <p class="text-[11px] text-slate-500">Tampil publik</p>
        </div>

        <div class="bg-slate-900 border border-slate-800 rounded-2xl p-5 space-y-1">
          <span class="text-xs text-slate-400 font-semibold uppercase tracking-wider">Hidden</span>
          <div class="text-2xl font-extrabold text-amber-400">{{ metrics.hidden_comments }}</div>
          <p class="text-[11px] text-slate-500">Disembunyikan</p>
        </div>

        <div class="bg-slate-900 border border-slate-800 rounded-2xl p-5 space-y-1">
          <span class="text-xs text-slate-400 font-semibold uppercase tracking-wider">Flagged</span>
          <div class="text-2xl font-extrabold text-rose-400">{{ metrics.flagged_comments }}</div>
          <p class="text-[11px] text-slate-500">Dilaporkan</p>
        </div>
      </div>

      <!-- Search & Filters -->
      <div class="flex flex-col md:flex-row items-center justify-between gap-4 bg-slate-900 border border-slate-800 p-4 rounded-2xl">
        <div class="flex flex-wrap items-center gap-2 w-full md:w-auto">
          <button
            @click="filterByStatus('')"
            class="px-3 py-1.5 rounded-xl text-xs font-bold transition"
            :class="!statusFilter ? 'bg-amber-500 text-slate-950 shadow-md shadow-amber-500/20' : 'bg-slate-950 text-slate-400 hover:text-white border border-slate-800'"
          >
            Semua Komentar
          </button>
          <button
            @click="filterByStatus('published')"
            class="px-3 py-1.5 rounded-xl text-xs font-bold transition"
            :class="statusFilter === 'published' ? 'bg-emerald-600 text-white shadow-md shadow-emerald-600/20' : 'bg-slate-950 text-slate-400 hover:text-white border border-slate-800'"
          >
            Published
          </button>
          <button
            @click="filterByStatus('hidden')"
            class="px-3 py-1.5 rounded-xl text-xs font-bold transition"
            :class="statusFilter === 'hidden' ? 'bg-amber-600 text-white shadow-md shadow-amber-600/20' : 'bg-slate-950 text-slate-400 hover:text-white border border-slate-800'"
          >
            Hidden
          </button>
          <button
            @click="filterByStatus('flagged')"
            class="px-3 py-1.5 rounded-xl text-xs font-bold transition"
            :class="statusFilter === 'flagged' ? 'bg-rose-600 text-white shadow-md shadow-rose-600/20' : 'bg-slate-950 text-slate-400 hover:text-white border border-slate-800'"
          >
            Flagged
          </button>
        </div>

        <div class="w-full md:w-72">
          <input
            v-model="search"
            @keyup.enter="handleSearch"
            type="text"
            placeholder="Cari kata kunci / nama user..."
            class="w-full bg-slate-950 border border-slate-800 rounded-xl px-4 py-2 text-xs text-white placeholder-slate-500 focus:outline-none focus:border-amber-500"
          />
        </div>
      </div>

      <!-- Comments Table -->
      <div v-if="comments.data && comments.data.length" class="bg-slate-900 border border-slate-800 rounded-2xl overflow-hidden shadow-xl">
        <div class="overflow-x-auto">
          <table class="w-full text-left text-sm text-slate-300">
            <thead class="text-xs uppercase bg-slate-950 text-slate-400 border-b border-slate-800">
              <tr>
                <th class="px-6 py-4">User</th>
                <th class="px-6 py-4">Komik & Chapter</th>
                <th class="px-6 py-4">Isi Komentar</th>
                <th class="px-6 py-4">Status</th>
                <th class="px-6 py-4 text-right">Aksi Moderasi</th>
              </tr>
            </thead>
            <tbody class="divide-y divide-slate-800/60">
              <tr v-for="c in comments.data" :key="c.id" class="hover:bg-slate-800/30 transition">
                <td class="px-6 py-4 text-xs">
                  <span class="text-white font-bold">{{ c.user ? c.user.name : 'Reader' }}</span>
                  <p class="text-slate-400">{{ c.user ? c.user.email : '' }}</p>
                </td>

                <td class="px-6 py-4 text-xs">
                  <!-- Link ke halaman detail komik publik -->
                  <a
                    v-if="c.comic"
                    :href="`/comics/${c.comic.slug}`"
                    target="_blank"
                    rel="noopener noreferrer"
                    class="text-amber-400 font-semibold hover:text-amber-300 underline underline-offset-2 decoration-dashed flex items-center gap-1"
                  >
                    {{ c.comic.title }}
                    <span class="text-[9px] opacity-60">↗</span>
                  </a>
                  <span v-else class="text-slate-500">-</span>

                  <!-- Link ke halaman baca chapter jika ada chapter -->
                  <a
                    v-if="c.chapter && c.comic"
                    :href="`/read/${c.comic.slug}/${c.chapter.chapter_number}`"
                    target="_blank"
                    rel="noopener noreferrer"
                    class="mt-0.5 text-sky-400 hover:text-sky-300 underline underline-offset-2 decoration-dashed flex items-center gap-1"
                  >
                    <BookOpenIcon class="w-3 h-3" />
                    Bab {{ c.chapter.chapter_number }}
                    <ArrowTopRightOnSquareIcon class="w-3 h-3 opacity-60" />
                  </a>
                  <span v-else-if="!c.chapter" class="text-slate-500 block mt-0.5 text-[11px]">Komentar umum</span>
                </td>

                <td class="px-6 py-4 text-xs max-w-xs">
                  <p class="text-slate-200 line-clamp-2">{{ c.comment_text }}</p>
                  <span v-if="c.is_spoiler" class="inline-flex items-center gap-1 mt-1 text-[9px] font-extrabold text-rose-400 bg-rose-950/60 px-1.5 py-0.5 rounded border border-rose-800">
                    <ExclamationTriangleIcon class="w-3 h-3" /> SPOILER
                  </span>
                </td>

                <td class="px-6 py-4">
                  <span
                    class="px-2.5 py-1 text-[10px] font-extrabold rounded-lg uppercase tracking-wider"
                    :class="[
                      c.status === 'published' ? 'bg-emerald-500/10 text-emerald-400 border border-emerald-500/30' :
                      c.status === 'hidden' ? 'bg-amber-500/10 text-amber-400 border border-amber-500/30' :
                      'bg-rose-500/10 text-rose-400 border border-rose-500/30'
                    ]"
                  >
                    {{ c.status }}
                  </span>
                </td>

                <td class="px-6 py-4 text-right whitespace-nowrap">
                  <div class="flex items-center justify-end gap-1.5">
                    <button
                      v-if="c.status !== 'published'"
                      @click="updateCommentStatus(c.id, 'published')"
                      title="Publish komentar"
                      class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg text-xs font-bold text-emerald-400 bg-emerald-950/40 border border-emerald-800/80 hover:bg-emerald-900 transition"
                    >
                      <EyeIcon class="w-3.5 h-3.5" /> Publish
                    </button>

                    <button
                      v-if="c.status === 'published'"
                      @click="updateCommentStatus(c.id, 'hidden')"
                      title="Sembunyikan komentar"
                      class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg text-xs font-bold text-amber-400 bg-amber-950/40 border border-amber-800/80 hover:bg-amber-900 transition"
                    >
                      <EyeSlashIcon class="w-3.5 h-3.5" /> Sembunyikan
                    </button>

                    <button
                      @click="deleteComment(c.id)"
                      title="Hapus komentar"
                      class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg text-xs font-bold text-rose-400 bg-rose-950/40 border border-rose-800/80 hover:bg-rose-900 transition"
                    >
                      <TrashIcon class="w-3.5 h-3.5" /> Hapus
                    </button>
                  </div>
                </td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>

      <div v-else class="bg-slate-900 border border-slate-800 rounded-2xl p-12 text-center text-slate-400">
        Tidak ada komentar ditemukan.
      </div>
    </div>
  </AdminLayout>
</template>
