<script setup lang="ts">
import { Head, useForm, usePage } from '@inertiajs/vue3';
import { computed } from 'vue';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import {
  BuildingStorefrontIcon,
  CheckCircleIcon,
  ExclamationTriangleIcon,
  BanknotesIcon,
  UserIcon,
  ClockIcon,
  XCircleIcon,
} from '@heroicons/vue/24/outline';
import { CheckCircleIcon as CheckCircleSolid } from '@heroicons/vue/24/solid';

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
  profile: Profile;
}>();

const page = usePage();
const flashSuccess = computed(() => (page.props as any).flash?.success);
const flashError = computed(() => (page.props as any).flash?.error);

const isApproved = computed(() => props.profile.verification_status === 'approved');
const isRejected = computed(() => props.profile.verification_status === 'rejected');
const isPending  = computed(() => props.profile.verification_status === 'pending');

const form = useForm({
  brand_name:          props.profile.brand_name || '',
  bio:                 props.profile.bio || '',
  bank_name:           props.profile.bank_name || 'BCA',
  bank_account_number: props.profile.bank_account_number || '',
  bank_account_name:   props.profile.bank_account_name || '',
});

const submitProfileUpdate = () => {
  form.post('/publisher/profile/update');
};
</script>

<template>
  <Head title="Kelola Studio - Creator Studio" />

  <AdminLayout>
    <div class="max-w-4xl mx-auto space-y-8">
      <!-- Flash Alerts -->
      <div v-if="flashSuccess" class="p-4 bg-emerald-500/10 border border-emerald-500/30 rounded-xl text-emerald-400 text-sm font-medium flex items-center gap-2">
        <CheckCircleIcon class="w-5 h-5 shrink-0" />
        {{ flashSuccess }}
      </div>
      <div v-if="flashError" class="p-4 bg-rose-500/10 border border-rose-500/30 rounded-xl text-rose-400 text-sm font-medium flex items-center gap-2">
        <ExclamationTriangleIcon class="w-5 h-5 shrink-0" />
        {{ flashError }}
      </div>

      <!-- Page Header -->
      <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div>
          <span class="text-xs text-sky-400 font-extrabold uppercase tracking-wider">Creator Studio Settings</span>
          <h1 class="text-3xl font-extrabold text-white flex items-center gap-3">
            <BuildingStorefrontIcon class="w-8 h-8 text-sky-400 shrink-0" />
            Kelola Studio & Akun Bank
          </h1>
          <p class="text-sm text-slate-400 mt-1">Ubah nama brand studio, bio deskripsi, dan informasi rekening pencairan komisi payout</p>
        </div>
      </div>

      <!-- Studio Status Card -->
      <div class="bg-slate-900 border border-slate-800 rounded-2xl p-6 flex items-center justify-between gap-4 shadow-xl">
        <div class="flex items-center gap-4">
          <div
            class="w-12 h-12 rounded-2xl flex items-center justify-center shrink-0"
            :class="[
              isApproved ? 'bg-emerald-500/20' :
              isRejected ? 'bg-rose-500/20' :
              'bg-amber-500/20'
            ]"
          >
            <CheckCircleSolid v-if="isApproved" class="w-7 h-7 text-emerald-400" />
            <XCircleIcon v-else-if="isRejected" class="w-7 h-7 text-rose-400" />
            <ClockIcon v-else class="w-7 h-7 text-amber-400" />
          </div>
          <div>
            <span class="text-[10px] font-extrabold uppercase tracking-wider text-slate-400">Status Verifikasi Studio</span>
            <h3 class="text-base font-bold text-white flex items-center gap-2">
              <span>{{ profile.brand_name }}</span>
              <span
                class="px-2.5 py-0.5 text-[10px] font-extrabold rounded-md uppercase"
                :class="[
                  isApproved ? 'bg-emerald-500/10 text-emerald-400 border border-emerald-500/30' :
                  isRejected ? 'bg-rose-500/10 text-rose-400 border border-rose-500/30' :
                  'bg-amber-500/10 text-amber-400 border border-amber-500/30'
                ]"
              >
                {{ profile.verification_status }}
              </span>
            </h3>
            <p v-if="isRejected && profile.rejection_reason" class="text-xs text-rose-300 mt-1">
              Catatan Penolakan: <strong>{{ profile.rejection_reason }}</strong>
            </p>
          </div>
        </div>
      </div>

      <!-- Studio Profile Form -->
      <div class="bg-slate-900 border border-slate-800 rounded-2xl p-8 space-y-6 shadow-2xl">
        <h2 class="text-base font-bold text-white border-b border-slate-800 pb-3 flex items-center gap-2">
          <BuildingStorefrontIcon class="w-5 h-5 text-sky-400" />
          Informasi Profil Studio & Payout
        </h2>

        <form @submit.prevent="submitProfileUpdate" class="space-y-6">
          <div class="space-y-1.5">
            <label class="block text-xs font-bold text-slate-300">Nama Brand / Studio *</label>
            <input
              v-model="form.brand_name"
              type="text"
              required
              placeholder="Contoh: Studio Realm Art"
              class="w-full bg-slate-950 border border-slate-800 rounded-xl px-4 py-3 text-sm text-white focus:outline-none focus:ring-1 focus:ring-sky-500 font-semibold"
            />
            <span v-if="form.errors.brand_name" class="text-xs text-rose-400">{{ form.errors.brand_name }}</span>
          </div>

          <div class="space-y-1.5">
            <label class="block text-xs font-bold text-slate-300">Bio & Deskripsi Studio</label>
            <textarea
              v-model="form.bio"
              rows="4"
              placeholder="Jelaskan fokus tema webcomic, latar belakang tim, atau karya studio Anda..."
              class="w-full bg-slate-950 border border-slate-800 rounded-xl px-4 py-3 text-sm text-white focus:outline-none focus:ring-1 focus:ring-sky-500"
            ></textarea>
            <span v-if="form.errors.bio" class="text-xs text-rose-400">{{ form.errors.bio }}</span>
          </div>

          <div class="pt-4 border-t border-slate-800 space-y-4">
            <h3 class="text-xs font-extrabold text-amber-400 uppercase tracking-wider flex items-center gap-2">
              <BanknotesIcon class="w-4 h-4 text-amber-400" />
              Rekening Bank Pencairan Dana Payout
            </h3>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
              <div class="space-y-1.5">
                <label class="block text-xs font-bold text-slate-300">Nama Bank *</label>
                <select
                  v-model="form.bank_name"
                  class="w-full bg-slate-950 border border-slate-800 rounded-xl px-3.5 py-3 text-sm text-slate-200 focus:outline-none focus:ring-1 focus:ring-sky-500 font-semibold"
                >
                  <option value="BCA">BCA (Bank Central Asia)</option>
                  <option value="Mandiri">Bank Mandiri</option>
                  <option value="BNI">BNI (Bank Negara Indonesia)</option>
                  <option value="BRI">BRI (Bank Rakyat Indonesia)</option>
                  <option value="CIMB">CIMB Niaga</option>
                  <option value="Permata">Bank Permata</option>
                  <option value="Danamon">Bank Danamon</option>
                </select>
                <span v-if="form.errors.bank_name" class="text-xs text-rose-400">{{ form.errors.bank_name }}</span>
              </div>

              <div class="space-y-1.5">
                <label class="block text-xs font-bold text-slate-300">Nomor Rekening Bank *</label>
                <input
                  v-model="form.bank_account_number"
                  type="text"
                  required
                  placeholder="1234567890"
                  class="w-full bg-slate-950 border border-slate-800 rounded-xl px-4 py-3 text-sm text-white focus:outline-none focus:ring-1 focus:ring-sky-500 font-mono font-bold"
                />
                <span v-if="form.errors.bank_account_number" class="text-xs text-rose-400">{{ form.errors.bank_account_number }}</span>
              </div>

              <div class="space-y-1.5">
                <label class="block text-xs font-bold text-slate-300">Atas Nama Rekening *</label>
                <input
                  v-model="form.bank_account_name"
                  type="text"
                  required
                  placeholder="Nama Pemilik Rekening"
                  class="w-full bg-slate-950 border border-slate-800 rounded-xl px-4 py-3 text-sm text-white focus:outline-none focus:ring-1 focus:ring-sky-500 font-semibold"
                />
                <span v-if="form.errors.bank_account_name" class="text-xs text-rose-400">{{ form.errors.bank_account_name }}</span>
              </div>
            </div>
          </div>

          <div class="pt-4 flex justify-end">
            <button
              type="submit"
              :disabled="form.processing"
              class="px-8 py-3 rounded-xl text-xs font-bold text-white bg-sky-600 hover:bg-sky-500 disabled:opacity-50 transition shadow-lg shadow-sky-600/30"
            >
              {{ form.processing ? 'Menyimpan Perubahan...' : 'Simpan Perubahan Studio →' }}
            </button>
          </div>
        </form>
      </div>

    </div>
  </AdminLayout>
</template>
