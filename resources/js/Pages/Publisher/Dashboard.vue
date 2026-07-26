<script setup lang="ts">
import { Head, Link, useForm, usePage } from '@inertiajs/vue3';
import { computed, ref } from 'vue';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import {
  ExclamationTriangleIcon,
  CheckCircleIcon,
  XCircleIcon,
  ClockIcon,
  PencilSquareIcon,
  Cog6ToothIcon,
  LockClosedIcon,
  PlusIcon,
  StarIcon,
  EyeIcon,
  BookOpenIcon,
  ArrowTopRightOnSquareIcon,
  SwatchIcon,
} from '@heroicons/vue/24/outline';
import { CheckCircleIcon as CheckCircleSolid } from '@heroicons/vue/24/solid';

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
  bio?: string;
  bank_name?: string;
  bank_account_number?: string;
  bank_account_name?: string;
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
const isRejected = computed(() => props.profile && props.profile.verification_status === 'rejected');
const isPending  = computed(() => props.profile && props.profile.verification_status === 'pending');

const showEditForm = ref(isRejected.value);

const form = useForm({
  brand_name:          props.profile?.brand_name || '',
  bio:                 props.profile?.bio || '',
  bank_name:           props.profile?.bank_name || 'BCA',
  bank_account_number: props.profile?.bank_account_number || '',
  bank_account_name:   props.profile?.bank_account_name || '',
});

const submitProfileUpdate = () => {
  form.post('/publisher/profile/update', {
    onSuccess: () => { showEditForm.value = false; },
  });
};
</script>

<template>
  <Head title="Creator Studio Dashboard" />

  <AdminLayout>
    <div class="space-y-8">

      <!-- Flash Alerts -->
      <div v-if="flashError" class="p-4 bg-rose-500/10 border border-rose-500/30 rounded-xl text-rose-400 text-sm font-medium flex items-center gap-2">
        <ExclamationTriangleIcon class="w-4 h-4 shrink-0" />
        {{ flashError }}
      </div>
      <div v-if="flashSuccess" class="p-4 bg-emerald-500/10 border border-emerald-500/30 rounded-xl text-emerald-400 text-sm font-medium flex items-center gap-2">
        <CheckCircleIcon class="w-4 h-4 shrink-0" />
        {{ flashSuccess }}
      </div>

      <!-- Verification Status Alert -->
      <div
        v-if="!isApproved"
        class="rounded-2xl p-6 space-y-4 shadow-xl"
        :class="isRejected ? 'bg-rose-500/10 border border-rose-500/30' : 'bg-amber-500/10 border border-amber-500/30'"
      >
        <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4">
          <div class="flex items-center gap-4">
            <div class="w-12 h-12 rounded-2xl flex items-center justify-center shrink-0" :class="isRejected ? 'bg-rose-500/20' : 'bg-amber-500/20'">
              <XCircleIcon v-if="isRejected" class="w-6 h-6 text-rose-400" />
              <ClockIcon v-else class="w-6 h-6 text-amber-400" />
            </div>
            <div>
              <h2 class="text-base font-bold" :class="isRejected ? 'text-rose-400' : 'text-amber-400'">
                <template v-if="isPending">Studio "{{ profile?.brand_name }}" Menunggu Persetujuan Admin</template>
                <template v-else-if="isRejected">Pengajuan Studio Ditolak</template>
              </h2>
              <p class="text-xs text-slate-300 mt-1 max-w-xl">
                <template v-if="isPending">Pengajuan studio Anda sedang ditinjau. Anda belum dapat membuat komik sebelum akun disetujui.</template>
                <template v-else-if="isRejected">
                  Alasan penolakan: <strong class="text-rose-300">{{ profile?.rejection_reason || 'Tidak memenuhi kualifikasi platform.' }}</strong>. Silakan perbaiki data studio di bawah.
                </template>
              </p>
            </div>
          </div>

          <button
            @click="showEditForm = !showEditForm"
            class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl text-xs font-bold text-white transition shrink-0"
            :class="isRejected ? 'bg-rose-600 hover:bg-rose-500 shadow-lg shadow-rose-600/30' : 'bg-slate-800 border border-slate-700 hover:text-white'"
          >
            <PencilSquareIcon v-if="isRejected && !showEditForm" class="w-4 h-4" />
            <Cog6ToothIcon v-else-if="!showEditForm" class="w-4 h-4" />
            {{ showEditForm ? 'Tutup Form' : (isRejected ? 'Perbaiki & Kirim Ulang' : 'Ajukan Perubahan Studio') }}
          </button>
        </div>
      </div>

      <!-- Verified Studio Bar -->
      <div v-else class="flex items-center justify-between bg-slate-900 border border-slate-800 rounded-2xl p-4 px-6">
        <div class="flex items-center gap-3">
          <CheckCircleSolid class="w-5 h-5 text-emerald-400 shrink-0" />
          <span class="text-emerald-400 font-extrabold text-sm">Studio Verified</span>
          <span class="text-slate-400 text-xs">• {{ profile?.brand_name }}</span>
        </div>
        <button
          @click="showEditForm = !showEditForm"
          class="inline-flex items-center gap-1.5 text-xs font-semibold px-3 py-1.5 rounded-xl bg-slate-800 border border-slate-700 text-slate-300 hover:text-white transition"
        >
          <Cog6ToothIcon class="w-3.5 h-3.5" />
          Ajukan Perubahan Studio
        </button>
      </div>

      <!-- Edit Form Card -->
      <div v-if="showEditForm" class="bg-slate-900 border border-slate-800 rounded-2xl p-6 space-y-6 shadow-2xl">
        <h2 class="text-base font-bold text-white border-b border-slate-800 pb-3 flex items-center justify-between">
          <span class="flex items-center gap-2">
            <Cog6ToothIcon class="w-4 h-4 text-slate-400" />
            Form Edit & Pengajuan Studio
          </span>
          <span class="text-xs font-normal text-slate-400">Verifikasi Ulang Admin</span>
        </h2>

        <form @submit.prevent="submitProfileUpdate" class="space-y-4">
          <div class="space-y-1">
            <label class="block text-xs font-semibold text-slate-300">Nama Brand / Studio *</label>
            <input
              v-model="form.brand_name"
              type="text"
              required
              placeholder="Contoh: Studio Realm Art"
              class="w-full bg-slate-950 border border-slate-800 rounded-xl px-4 py-2.5 text-sm text-white focus:outline-none focus:ring-1 focus:ring-sky-500"
            />
            <span v-if="form.errors.brand_name" class="text-xs text-rose-400">{{ form.errors.brand_name }}</span>
          </div>

          <div class="space-y-1">
            <label class="block text-xs font-semibold text-slate-300">Deskripsi / Bio Studio</label>
            <textarea
              v-model="form.bio"
              rows="3"
              placeholder="Jelaskan jenis karya komik atau latar belakang studio Anda..."
              class="w-full bg-slate-950 border border-slate-800 rounded-xl px-4 py-2.5 text-sm text-white focus:outline-none focus:ring-1 focus:ring-sky-500"
            ></textarea>
          </div>

          <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
            <div class="space-y-1">
              <label class="block text-xs font-semibold text-slate-300">Nama Bank *</label>
              <select
                v-model="form.bank_name"
                class="w-full bg-slate-950 border border-slate-800 rounded-xl px-3.5 py-2.5 text-sm text-slate-300 focus:outline-none focus:ring-1 focus:ring-sky-500"
              >
                <option value="BCA">BCA</option>
                <option value="Mandiri">Mandiri</option>
                <option value="BNI">BNI</option>
                <option value="BRI">BRI</option>
                <option value="CIMB">CIMB Niaga</option>
              </select>
            </div>
            <div class="space-y-1">
              <label class="block text-xs font-semibold text-slate-300">Nomor Rekening *</label>
              <input
                v-model="form.bank_account_number"
                type="text"
                required
                placeholder="1234567890"
                class="w-full bg-slate-950 border border-slate-800 rounded-xl px-4 py-2.5 text-sm text-white focus:outline-none focus:ring-1 focus:ring-sky-500 font-mono"
              />
            </div>
            <div class="space-y-1">
              <label class="block text-xs font-semibold text-slate-300">Atas Nama Rekening *</label>
              <input
                v-model="form.bank_account_name"
                type="text"
                required
                placeholder="Nama Pemilik Rekening"
                class="w-full bg-slate-950 border border-slate-800 rounded-xl px-4 py-2.5 text-sm text-white focus:outline-none focus:ring-1 focus:ring-sky-500"
              />
            </div>
          </div>

          <div class="pt-2 flex justify-end gap-3">
            <button type="button" @click="showEditForm = false" class="px-4 py-2.5 rounded-xl text-xs font-semibold bg-slate-800 text-slate-300 hover:text-white">
              Batal
            </button>
            <button
              type="submit"
              :disabled="form.processing"
              class="px-6 py-2.5 rounded-xl text-xs font-bold text-white bg-sky-600 hover:bg-sky-500 disabled:opacity-50 transition shadow-lg shadow-sky-600/30"
            >
              {{ form.processing ? 'Menyimpan...' : 'Kirim Ulang Verifikasi →' }}
            </button>
          </div>
        </form>
      </div>

      <!-- Page Header -->
      <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
          <h1 class="text-3xl font-extrabold text-white">Publisher Studio Dashboard</h1>
          <p class="text-sm text-slate-400 mt-1">Manage your webcomics, publish new chapters, and track views</p>
        </div>

        <Link
          v-if="isApproved"
          href="/publisher/comics/create"
          class="inline-flex items-center gap-2 px-5 py-3 rounded-xl text-xs font-bold bg-sky-600 hover:bg-sky-500 text-white transition shadow-lg shadow-sky-600/30 self-start"
        >
          <PlusIcon class="w-4 h-4" />
          Create New Comic Series
        </Link>
        <button
          v-else
          disabled
          class="inline-flex items-center gap-2 px-5 py-3 rounded-xl text-xs font-bold bg-slate-800 text-slate-500 border border-slate-700/50 cursor-not-allowed opacity-60 self-start"
        >
          <LockClosedIcon class="w-4 h-4" />
          Create New Comic Series (Locked)
        </button>
      </div>

      <!-- Comics List -->
      <div v-if="comics && comics.length" class="space-y-5">
        <div
          v-for="comic in comics"
          :key="comic.id"
          class="bg-slate-900 border border-slate-800 rounded-2xl p-6 flex flex-col md:flex-row gap-6 items-start justify-between"
        >
          <div class="flex items-start gap-4">
            <img :src="comic.cover_image" :alt="comic.title" class="w-20 h-28 object-cover rounded-xl border border-slate-800 shrink-0 bg-slate-950" />
            <div class="space-y-1.5">
              <span class="text-xs text-sky-400 font-bold uppercase tracking-wider">{{ comic.status }}</span>
              <h2 class="text-lg font-bold text-white">{{ comic.title }}</h2>
              <p class="text-xs text-slate-400 flex items-center gap-3">
                <span class="flex items-center gap-1">
                  <StarIcon class="w-3.5 h-3.5 text-amber-400" />
                  {{ comic.rating_average ? comic.rating_average.toFixed(1) : '0.0' }}
                </span>
                <span class="flex items-center gap-1">
                  <EyeIcon class="w-3.5 h-3.5 text-slate-400" />
                  {{ comic.total_views ? comic.total_views.toLocaleString() : 0 }} Views
                </span>
              </p>
              <p v-if="comic.chapters" class="text-xs text-slate-500 flex items-center gap-1">
                <BookOpenIcon class="w-3.5 h-3.5" />
                {{ comic.chapters.length }} Chapters Published
              </p>
            </div>
          </div>

          <div class="flex items-center gap-3 w-full md:w-auto border-t md:border-t-0 border-slate-800 pt-4 md:pt-0">
            <Link
              v-if="isApproved"
              :href="`/publisher/comics/${comic.id}/chapters/create`"
              class="inline-flex items-center gap-1.5 px-4 py-2 rounded-xl text-xs font-semibold bg-sky-600 hover:bg-sky-500 text-white transition"
            >
              <PlusIcon class="w-3.5 h-3.5" /> Add Chapter
            </Link>
            <span
              v-else
              class="inline-flex items-center gap-1.5 px-4 py-2 rounded-xl text-xs font-semibold bg-slate-800 text-slate-500 border border-slate-700/50 cursor-not-allowed"
            >
              <LockClosedIcon class="w-3.5 h-3.5" /> Add Chapter
            </span>

            <a
              :href="`/comics/${comic.slug}`"
              target="_blank"
              class="inline-flex items-center gap-1.5 px-4 py-2 rounded-xl text-xs font-semibold bg-slate-800 border border-slate-700 text-slate-200 hover:text-white transition"
            >
              <ArrowTopRightOnSquareIcon class="w-3.5 h-3.5" />
              View Public Page
            </a>
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
        <h2 class="text-xl font-bold text-white">No Webcomics Published Yet</h2>
        <p class="text-sm text-slate-400 max-w-md mx-auto">
          <template v-if="isApproved">Start your creator journey by creating your first vertical webcomic series.</template>
          <template v-else>Akun studio Anda sedang dalam proses verifikasi. Pembuatan komik baru akan aktif setelah disetujui oleh admin.</template>
        </p>
        <div class="pt-2" v-if="isApproved">
          <Link href="/publisher/comics/create" class="inline-flex items-center gap-2 px-6 py-3 rounded-xl bg-sky-600 hover:bg-sky-500 text-white font-semibold text-sm transition">
            <PlusIcon class="w-4 h-4" /> Create New Comic Series
          </Link>
        </div>
      </div>

    </div>
  </AdminLayout>
</template>
