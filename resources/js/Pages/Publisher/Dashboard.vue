<script setup lang="ts">
import { Head, Link, usePage } from '@inertiajs/vue3';
import { computed } from 'vue';
import AdminLayout from '@/Layouts/AdminLayout.vue';

interface Chapter {
  id: number;
  chapter_number: number;
  title: string;
  is_free: boolean;
  price: number;
}

interface Comic {
  id: number;
  title: string;
  slug: string;
  cover_image: string;
  status: string;
  rating_average: number;
  total_views: number;
  chapters?: Chapter[];
}

interface Profile {
  id: number;
  brand_name: string;
  verification_status: string;
  rejection_reason?: string;
}

const props = defineProps<{
  profile?: Profile | null;
  comics: Comic[];
}>();

const page = usePage();
const flashError = computed(() => (page.props as any).flash?.error);
const flashSuccess = computed(() => (page.props as any).flash?.success);

const isApproved = computed(() => props.profile && props.profile.verification_status === 'approved');
</script>

<template>
  <Head title="Creator Studio Dashboard" />

  <AdminLayout>
    <div class="space-y-8">
      <!-- Flash Alert Banners -->
      <div v-if="flashError" class="p-4 bg-rose-500/10 border border-rose-500/30 rounded-xl text-rose-400 text-sm font-medium flex items-center gap-2">
        <span>⚠️</span> {{ flashError }}
      </div>
      <div v-if="flashSuccess" class="p-4 bg-emerald-500/10 border border-emerald-500/30 rounded-xl text-emerald-400 text-sm font-medium flex items-center gap-2">
        <span>✅</span> {{ flashSuccess }}
      </div>

      <!-- Studio Verification Status Alert Banner -->
      <div v-if="!isApproved" class="bg-amber-500/10 border border-amber-500/30 rounded-2xl p-6 space-y-3">
        <div class="flex items-center gap-3">
          <span class="text-2xl">⏳</span>
          <div>
            <h2 class="text-lg font-bold text-amber-400">
              <template v-if="profile && profile.verification_status === 'pending'">
                Studio "{{ profile.brand_name }}" Dalam Menunggu Persetujuan Admin
              </template>
              <template v-else-if="profile && profile.verification_status === 'rejected'">
                Pengajuan Studio Ditolak
              </template>
              <template v-else>
                Belum Memiliki Akun Studio Publisher
              </template>
            </h2>
            <p class="text-xs text-slate-300 mt-1">
              <template v-if="profile && profile.verification_status === 'pending'">
                Pengajuan studio Anda sedang ditinjau oleh Super Admin. Anda belum dapat membuat komik atau mengunggah bab baru sebelum akun disetujui.
              </template>
              <template v-else-if="profile && profile.verification_status === 'rejected'">
                Alasan penolakan: {{ profile.rejection_reason || 'Tidak memenuhi kualifikasi platform.' }}
              </template>
              <template v-else>
                Silakan daftarkan studio kreator Anda terlebih dahulu untuk mempublikasikan webcomic.
              </template>
            </p>
          </div>
        </div>
      </div>

      <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
          <h1 class="text-3xl font-extrabold text-white">Publisher Studio Dashboard</h1>
          <p class="text-sm text-slate-400 mt-1">Manage your webcomics, publish new chapters, and track views</p>
        </div>

        <Link
          v-if="isApproved"
          href="/publisher/comics/create"
          class="px-5 py-3 rounded-xl text-xs font-bold bg-sky-600 hover:bg-sky-500 text-white transition shadow-lg shadow-sky-600/30 self-start"
        >
          + Create New Comic Series
        </Link>
        <button
          v-else
          disabled
          class="px-5 py-3 rounded-xl text-xs font-bold bg-slate-800 text-slate-500 border border-slate-700/50 cursor-not-allowed opacity-60 self-start"
          title="Studio belum disetujui admin"
        >
          🔒 + Create New Comic Series (Locked)
        </button>
      </div>

      <!-- Published Comics List -->
      <div v-if="comics && comics.length" class="space-y-6">
        <div
          v-for="comic in comics"
          :key="comic.id"
          class="bg-slate-900 border border-slate-800 rounded-2xl p-6 flex flex-col md:flex-row gap-6 items-start justify-between"
        >
          <div class="flex items-start gap-4">
            <img :src="comic.cover_image" :alt="comic.title" class="w-20 h-30 object-cover rounded-xl border border-slate-800 shrink-0 bg-slate-950" />
            <div class="space-y-1">
              <span class="text-xs text-sky-400 font-bold uppercase tracking-wider">{{ comic.status }}</span>
              <h2 class="text-xl font-bold text-white">{{ comic.title }}</h2>
              <p class="text-xs text-slate-400">
                ⭐ {{ comic.rating_average ? comic.rating_average.toFixed(1) : '0.0' }} • 👁 {{ comic.total_views ? comic.total_views.toLocaleString() : 0 }} Views
              </p>
              <p class="text-xs text-slate-500" v-if="comic.chapters">
                {{ comic.chapters.length }} Chapters Published
              </p>
            </div>
          </div>

          <div class="flex items-center gap-3 w-full md:w-auto border-t md:border-t-0 border-slate-800 pt-4 md:pt-0">
            <Link
              v-if="isApproved"
              :href="`/publisher/comics/${comic.id}/chapters/create`"
              class="px-4 py-2 rounded-xl text-xs font-semibold bg-sky-600 hover:bg-sky-500 text-white transition"
            >
              + Add Chapter
            </Link>
            <span
              v-else
              class="px-4 py-2 rounded-xl text-xs font-semibold bg-slate-800 text-slate-500 border border-slate-700/50 cursor-not-allowed"
            >
              🔒 + Add Chapter (Locked)
            </span>

            <Link
              :href="`/comics/${comic.slug}`"
              class="px-4 py-2 rounded-xl text-xs font-semibold bg-slate-800 border border-slate-700 text-slate-200 hover:text-white transition"
            >
              View Public Page
            </Link>
          </div>
        </div>
      </div>

      <div v-else class="bg-slate-900/40 border border-slate-800 rounded-2xl p-16 text-center space-y-4">
        <div class="text-5xl">🎨</div>
        <h2 class="text-xl font-bold text-white">No Webcomics Published Yet</h2>
        <p class="text-sm text-slate-400 max-w-md mx-auto">
          <template v-if="isApproved">
            Start your creator journey by creating your first vertical webcomic series.
          </template>
          <template v-else>
            Akun studio Anda sedang dalam proses verifikasi. Pembuatan komik baru akan aktif setelah disetujui oleh admin.
          </template>
        </p>
        <div class="pt-2" v-if="isApproved">
          <Link href="/publisher/comics/create" class="px-6 py-3 rounded-xl bg-sky-600 hover:bg-sky-500 text-white font-semibold text-sm transition">
            + Create New Comic Series
          </Link>
        </div>
      </div>
    </div>
  </AdminLayout>
</template>
