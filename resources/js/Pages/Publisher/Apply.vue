<script setup lang="ts">
import { Head, useForm, Link } from '@inertiajs/vue3';
import { computed } from 'vue';
import PublicLayout from '@/Layouts/PublicLayout.vue';
import {
  SwatchIcon,
  SparklesIcon,
  BanknotesIcon,
  ChartBarIcon,
  BuildingStorefrontIcon,
  CreditCardIcon,
  UserIcon,
  ArrowRightIcon,
  InformationCircleIcon,
} from '@heroicons/vue/24/outline';
import { useToast } from '@/composables/useToast';

interface PublisherProfile {
  id: number;
  brand_name: string;
  slug: string;
  bio?: string;
  bank_name?: string;
  bank_account_number?: string;
  bank_account_name?: string;
  verification_status: string;
  rejection_reason?: string;
}

const props = defineProps<{
  profile?: PublisherProfile | null;
}>();

const { error: toastError } = useToast();

const form = useForm({
  brand_name: props.profile?.brand_name || '',
  bio: props.profile?.bio || '',
  bank_name: props.profile?.bank_name || 'BCA',
  bank_account_number: props.profile?.bank_account_number || '',
  bank_account_name: props.profile?.bank_account_name || '',
});

const submit = () => {
  form.post('/publisher/apply', {
    onError: (errors) => {
      const msg = Object.values(errors)[0] || 'Gagal mengirim pendaftaran studio. Periksa inputan Anda.';
      toastError(msg);
    },
  });
};

const popularBanks = [
  'BCA',
  'Mandiri',
  'BRI',
  'BNI',
  'Bank Jago',
  'SeaBank',
  'CIMB Niaga',
  'Permata',
  'Other / E-Wallet',
];
</script>

<template>
  <Head title="Publish Your Webcomics - Creator Studio" />

  <PublicLayout>
    <main class="max-w-5xl mx-auto px-4 lg:px-8 py-10 w-full flex-1 space-y-10">
      <!-- Hero Banner -->
      <div class="text-center space-y-4 max-w-2xl mx-auto">
        <span class="inline-flex items-center gap-1.5 px-3 py-1 text-xs font-bold rounded-full bg-sky-500/10 text-sky-400 border border-sky-500/30">
          <SparklesIcon class="w-3.5 h-3.5" />
          The Creator Sanctuary
        </span>
        <h1 class="text-3xl sm:text-4xl font-extrabold text-white tracking-tight leading-tight">
          Publish Your Webcomics & Monetize Your Craft
        </h1>
        <p class="text-sm text-slate-400 leading-relaxed">
          Join our ecosystem of independent creators. Upload vertical chapters, retain creative freedom, and receive direct revenue payouts via TriPay.
        </p>
      </div>

      <!-- Feature Highlights Grid -->
      <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
        <div class="bg-slate-900/60 border border-slate-800 rounded-3xl p-6 space-y-3 relative overflow-hidden group hover:border-sky-500/40 transition">
          <div class="w-10 h-10 rounded-2xl bg-sky-500/10 border border-sky-500/30 flex items-center justify-center text-sky-400">
            <BanknotesIcon class="w-5 h-5" />
          </div>
          <h3 class="text-base font-bold text-white">70% Royalty Share</h3>
          <p class="text-xs text-slate-400 leading-relaxed">
            Earn 70% share from every paid chapter unlocked by readers with transparent royalty calculation.
          </p>
        </div>

        <div class="bg-slate-900/60 border border-slate-800 rounded-3xl p-6 space-y-3 relative overflow-hidden group hover:border-indigo-500/40 transition">
          <div class="w-10 h-10 rounded-2xl bg-indigo-500/10 border border-indigo-500/30 flex items-center justify-center text-indigo-400">
            <SwatchIcon class="w-5 h-5" />
          </div>
          <h3 class="text-base font-bold text-white">Full Creative Freedom</h3>
          <p class="text-xs text-slate-400 leading-relaxed">
            Publish your vertical webcomics, set free or paid chapters, auto-crop covers, and schedule releases.
          </p>
        </div>

        <div class="bg-slate-900/60 border border-slate-800 rounded-3xl p-6 space-y-3 relative overflow-hidden group hover:border-purple-500/40 transition">
          <div class="w-10 h-10 rounded-2xl bg-purple-500/10 border border-purple-500/30 flex items-center justify-center text-purple-400">
            <ChartBarIcon class="w-5 h-5" />
          </div>
          <h3 class="text-base font-bold text-white">Realtime Analytics & Payouts</h3>
          <p class="text-xs text-slate-400 leading-relaxed">
            Monitor real-time chapter views, bookmarks, ratings, and request instant bank balance withdrawals.
          </p>
        </div>
      </div>

      <!-- Application Form Container -->
      <div class="max-w-2xl mx-auto bg-slate-900 border border-slate-800 rounded-3xl p-6 sm:p-10 shadow-2xl space-y-8">
        <div class="flex items-center gap-3 border-b border-slate-800 pb-4">
          <span class="w-10 h-10 rounded-2xl bg-sky-500/10 border border-sky-500/30 flex items-center justify-center shrink-0">
            <BuildingStorefrontIcon class="w-5 h-5 text-sky-400" />
          </span>
          <div>
            <h2 class="text-xl font-bold text-white">Studio Registration Form</h2>
            <p class="text-xs text-slate-400">Fill in your studio details to submit your creator application</p>
          </div>
        </div>

        <form class="space-y-6" @submit.prevent="submit">
          <!-- Studio Details -->
          <div class="space-y-4">
            <h3 class="text-xs font-bold text-sky-400 uppercase tracking-wider">1. Studio Profile</h3>

            <div>
              <label class="block text-xs font-bold text-slate-300 mb-1">Studio / Brand Name *</label>
              <div class="relative">
                <input
                  v-model="form.brand_name"
                  type="text"
                  required
                  class="w-full rounded-xl bg-slate-950 border border-slate-800 pl-10 pr-4 py-2.5 text-xs text-white placeholder-slate-500 focus:border-sky-500 focus:outline-none focus:ring-1 focus:ring-sky-500 transition"
                  placeholder="e.g. Redice Studio / Mahdani Works"
                />
                <BuildingStorefrontIcon class="w-4 h-4 text-slate-500 absolute left-3.5 top-3" />
              </div>
              <p v-if="form.errors.brand_name" class="mt-1 text-xs text-rose-400 font-medium">
                {{ form.errors.brand_name }}
              </p>
            </div>

            <div>
              <label class="block text-xs font-bold text-slate-300 mb-1">Studio Bio & Description</label>
              <textarea
                v-model="form.bio"
                rows="4"
                class="w-full rounded-xl bg-slate-950 border border-slate-800 p-3.5 text-xs text-white placeholder-slate-500 focus:border-sky-500 focus:outline-none focus:ring-1 focus:ring-sky-500 transition"
                placeholder="Tell readers and admins about your creative studio, ongoing comic titles, or webcomic vision..."
              ></textarea>
              <p v-if="form.errors.bio" class="mt-1 text-xs text-rose-400 font-medium">
                {{ form.errors.bio }}
              </p>
            </div>
          </div>

          <!-- Bank Payout Info -->
          <div class="space-y-4 pt-4 border-t border-slate-800/80">
            <h3 class="text-xs font-bold text-sky-400 uppercase tracking-wider">2. Royalty Payout Bank Account</h3>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
              <div>
                <label class="block text-xs font-bold text-slate-300 mb-1">Bank Name *</label>
                <div class="relative">
                  <select
                    v-model="form.bank_name"
                    class="w-full rounded-xl bg-slate-950 border border-slate-800 pl-10 pr-4 py-2.5 text-xs text-white focus:border-sky-500 focus:outline-none focus:ring-1 focus:ring-sky-500 transition appearance-none"
                  >
                    <option v-for="bank in popularBanks" :key="bank" :value="bank">{{ bank }}</option>
                  </select>
                  <CreditCardIcon class="w-4 h-4 text-slate-500 absolute left-3.5 top-3 pointer-events-none" />
                </div>
              </div>

              <div>
                <label class="block text-xs font-bold text-slate-300 mb-1">Bank Account Number *</label>
                <div class="relative">
                  <input
                    v-model="form.bank_account_number"
                    type="text"
                    required
                    class="w-full rounded-xl bg-slate-950 border border-slate-800 pl-10 pr-4 py-2.5 text-xs text-white placeholder-slate-500 focus:border-sky-500 focus:outline-none focus:ring-1 focus:ring-sky-500 transition"
                    placeholder="1234567890"
                  />
                  <CreditCardIcon class="w-4 h-4 text-slate-500 absolute left-3.5 top-3" />
                </div>
                <p v-if="form.errors.bank_account_number" class="mt-1 text-xs text-rose-400 font-medium">
                  {{ form.errors.bank_account_number }}
                </p>
              </div>
            </div>

            <div>
              <label class="block text-xs font-bold text-slate-300 mb-1">Account Holder Name *</label>
              <div class="relative">
                <input
                  v-model="form.bank_account_name"
                  type="text"
                  required
                  class="w-full rounded-xl bg-slate-950 border border-slate-800 pl-10 pr-4 py-2.5 text-xs text-white placeholder-slate-500 focus:border-sky-500 focus:outline-none focus:ring-1 focus:ring-sky-500 transition"
                  placeholder="e.g. La Ode Mahdani"
                />
                <UserIcon class="w-4 h-4 text-slate-500 absolute left-3.5 top-3" />
              </div>
              <p v-if="form.errors.bank_account_name" class="mt-1 text-xs text-rose-400 font-medium">
                {{ form.errors.bank_account_name }}
              </p>
            </div>
          </div>

          <!-- Submit Button -->
          <div class="pt-2">
            <button
              type="submit"
              :disabled="form.processing"
              class="w-full py-3.5 px-4 rounded-xl text-xs font-bold text-white bg-gradient-to-r from-sky-600 to-indigo-600 hover:from-sky-500 hover:to-indigo-500 focus:outline-none disabled:opacity-50 transition shadow-lg shadow-sky-600/30 active:scale-[0.98] flex items-center justify-center gap-2"
            >
              <span>{{ form.processing ? "Submitting Application..." : "Submit Publisher Application" }}</span>
              <ArrowRightIcon class="w-4 h-4" />
            </button>
          </div>
        </form>
      </div>
    </main>
  </PublicLayout>
</template>
