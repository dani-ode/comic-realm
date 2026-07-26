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
  BanknotesIcon,
  ArrowRightIcon,
  RectangleStackIcon,
  ChartBarIcon,
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

interface Stats {
  total_comics: number;
  total_chapters: number;
  total_views: number;
  wallet_balance: number;
  total_earned: number;
  total_withdrawn: number;
}

const props = defineProps<{
  profile?: Profile | null;
  stats: Stats;
  topComics: Comic[];
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
  <Head title="Studio Overview & Analytics - Creator Studio" />

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

      <!-- Verified Studio Header -->
      <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 bg-slate-900 border border-slate-800 rounded-2xl p-6 shadow-xl">
        <div class="space-y-1">
          <div class="flex items-center gap-2">
            <CheckCircleSolid class="w-5 h-5 text-emerald-400 shrink-0" />
            <span class="text-xs font-extrabold uppercase text-emerald-400 tracking-wider">Verified Publisher Studio</span>
          </div>
          <h1 class="text-3xl font-extrabold text-white">{{ profile?.brand_name }}</h1>
          <p class="text-xs text-slate-400">Ikhtisar statistik pendapatan, jumlah pembaca, dan performa karya studio Anda</p>
        </div>

        <div class="flex items-center gap-3 flex-wrap">
          <Link
            href="/publisher/comics"
            class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl text-xs font-bold bg-sky-600 hover:bg-sky-500 text-white transition shadow-lg shadow-sky-600/20"
          >
            <BookOpenIcon class="w-4 h-4" />
            Kelola Komik & Chapter →
          </Link>
          <Link
            href="/publisher/wallet"
            class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl text-xs font-bold bg-slate-800 border border-slate-700 text-slate-300 hover:text-white transition"
          >
            <BanknotesIcon class="w-4 h-4 text-emerald-400" />
            Dompet Payout
          </Link>
        </div>
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

      <!-- Key Analytics Metrics -->
      <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
        <Link href="/publisher/wallet" class="bg-slate-900 border border-slate-800 rounded-2xl p-5 space-y-2 hover:border-emerald-500/40 transition group">
          <div class="flex items-center justify-between text-xs text-slate-400 font-semibold uppercase tracking-wider">
            <span>Saldo Siap Payout</span>
            <BanknotesIcon class="w-5 h-5 text-emerald-400 group-hover:scale-110 transition" />
          </div>
          <div class="text-2xl font-extrabold text-emerald-400 font-mono">
            Rp {{ stats.wallet_balance.toLocaleString() }}
          </div>
          <p class="text-[11px] text-slate-500">Klik untuk ajukan penarikan dana</p>
        </Link>

        <div class="bg-slate-900 border border-slate-800 rounded-2xl p-5 space-y-2">
          <div class="flex items-center justify-between text-xs text-slate-400 font-semibold uppercase tracking-wider">
            <span>Total Pendapatan Kotor</span>
            <ChartBarIcon class="w-5 h-5 text-sky-400" />
          </div>
          <div class="text-2xl font-extrabold text-white font-mono">
            Rp {{ stats.total_earned.toLocaleString() }}
          </div>
          <p class="text-[11px] text-slate-500">Bagi hasil 70% penjualan komik</p>
        </div>

        <div class="bg-slate-900 border border-slate-800 rounded-2xl p-5 space-y-2">
          <div class="flex items-center justify-between text-xs text-slate-400 font-semibold uppercase tracking-wider">
            <span>Total Pembaca (Views)</span>
            <EyeIcon class="w-5 h-5 text-indigo-400" />
          </div>
          <div class="text-2xl font-extrabold text-white font-mono">
            {{ stats.total_views.toLocaleString() }}
          </div>
          <p class="text-[11px] text-slate-500">Akumulasi seluruh judul komik</p>
        </div>

        <Link href="/publisher/comics" class="bg-slate-900 border border-slate-800 rounded-2xl p-5 space-y-2 hover:border-sky-500/40 transition group">
          <div class="flex items-center justify-between text-xs text-slate-400 font-semibold uppercase tracking-wider">
            <span>Katalog & Chapter</span>
            <BookOpenIcon class="w-5 h-5 text-amber-400 group-hover:scale-110 transition" />
          </div>
          <div class="text-2xl font-extrabold text-white font-mono">
            {{ stats.total_comics }} Komik <span class="text-sm font-normal text-slate-400">({{ stats.total_chapters }} Bab)</span>
          </div>
          <p class="text-[11px] text-sky-400 font-semibold">Kelola judul & terbitkan bab →</p>
        </Link>
      </div>

      <!-- Top Performing Comics Section -->
      <div class="bg-slate-900 border border-slate-800 rounded-2xl p-6 space-y-6 shadow-xl">
        <div class="flex items-center justify-between border-b border-slate-800 pb-4">
          <div>
            <h2 class="text-lg font-extrabold text-white flex items-center gap-2">
              <StarIcon class="w-5 h-5 text-amber-400 shrink-0" />
              Serial Komik Terpopuler
            </h2>
            <p class="text-xs text-slate-400 mt-0.5">Komik studio Anda dengan jumlah pembaca terbanyak</p>
          </div>

          <Link
            href="/publisher/comics"
            class="text-xs font-bold text-sky-400 hover:text-sky-300 flex items-center gap-1"
          >
            Lihat Semua Komik <ArrowRightIcon class="w-3.5 h-3.5" />
          </Link>
        </div>

        <div v-if="topComics && topComics.length" class="space-y-4">
          <div
            v-for="(comic, index) in topComics"
            :key="comic.id"
            class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4 p-4 rounded-xl bg-slate-950 border border-slate-800/80 hover:border-slate-700 transition"
          >
            <div class="flex items-center gap-4">
              <div class="w-7 h-7 rounded-lg bg-slate-800 text-amber-400 font-extrabold text-xs flex items-center justify-center shrink-0">
                #{{ index + 1 }}
              </div>
              <img :src="comic.cover_image" :alt="comic.title" class="w-12 h-16 object-cover rounded-lg border border-slate-800 shrink-0 bg-slate-900" />
              <div>
                <h3 class="font-bold text-white text-sm">{{ comic.title }}</h3>
                <div class="flex items-center gap-3 text-xs text-slate-400 mt-1">
                  <span class="flex items-center gap-1 text-amber-400 font-semibold">
                    ★ {{ comic.rating_average ? comic.rating_average.toFixed(1) : '0.0' }}
                  </span>
                  <span>• {{ comic.total_views.toLocaleString() }} Views</span>
                  <span>• {{ comic.chapters ? comic.chapters.length : 0 }} Bab</span>
                </div>
              </div>
            </div>

            <div class="flex items-center gap-2 self-end sm:self-center">
              <Link
                :href="`/publisher/comics/${comic.id}/chapters/create`"
                class="px-3 py-1.5 rounded-lg text-xs font-bold text-white bg-sky-600 hover:bg-sky-500 transition"
              >
                + Add Chapter
              </Link>
              <Link
                :href="`/publisher/comics/${comic.id}/edit`"
                class="px-3 py-1.5 rounded-lg text-xs font-bold text-slate-300 bg-slate-800 hover:bg-slate-700 transition"
              >
                Edit Komik
              </Link>
            </div>
          </div>
        </div>

        <div v-else class="text-center py-8 text-slate-500 text-xs">
          Belum ada komik yang diterbitkan. Silakan buat serial komik baru.
        </div>
      </div>

    </div>
  </AdminLayout>
</template>
