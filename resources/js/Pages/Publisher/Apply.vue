<script setup lang="ts">
import { Head, useForm, Link } from '@inertiajs/vue3';

const form = useForm({
  brand_name: '',
  bio: '',
  bank_name: 'BCA',
  bank_account_number: '',
  bank_account_name: '',
});

const submit = () => {
  form.post('/publisher/apply');
};
</script>

<template>
  <Head title="Become a Publisher - Creator Studio" />

  <div class="min-h-screen bg-slate-950 text-slate-100 flex flex-col justify-center py-12 sm:px-6 lg:px-8">
    <div class="sm:mx-auto sm:w-full sm:max-w-xl text-center">
      <Link href="/" class="text-3xl font-extrabold bg-gradient-to-r from-sky-400 via-indigo-400 to-purple-400 bg-clip-text text-transparent">
        The ComicRealm Studio
      </Link>
      <h2 class="mt-4 text-2xl font-bold tracking-tight text-white">Register as Independent Publisher</h2>
      <p class="mt-2 text-sm text-slate-400">
        Publish webcomics, manage digital chapters, and receive revenue payouts.
      </p>
    </div>

    <div class="mt-8 sm:mx-auto sm:w-full sm:max-w-xl">
      <div class="bg-slate-900 border border-slate-800 py-8 px-6 shadow-xl sm:rounded-2xl sm:px-10">
        <form class="space-y-5" @submit.prevent="submit">
          <div>
            <label class="block text-sm font-medium text-slate-300">Studio / Brand Name</label>
            <input
              v-model="form.brand_name"
              type="text"
              required
              class="mt-1 block w-full rounded-xl bg-slate-950 border border-slate-800 px-3.5 py-2.5 text-white placeholder-slate-500 focus:border-sky-500 focus:outline-none text-sm"
              placeholder="e.g. Mahdani Studio"
            />
            <p v-if="form.errors.brand_name" class="mt-1 text-xs text-rose-400">{{ form.errors.brand_name }}</p>
          </div>

          <div>
            <label class="block text-sm font-medium text-slate-300">Bio & Studio Description</label>
            <textarea
              v-model="form.bio"
              rows="3"
              class="mt-1 block w-full rounded-xl bg-slate-950 border border-slate-800 px-3.5 py-2.5 text-white placeholder-slate-500 focus:border-sky-500 focus:outline-none text-sm"
              placeholder="Tell readers about your creative team and webcomics..."
            ></textarea>
          </div>

          <div class="pt-2 border-t border-slate-800">
            <h3 class="text-sm font-bold text-white mb-3">Bank Account Payout Information</h3>
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
              <div>
                <label class="block text-xs font-medium text-slate-400">Bank Name</label>
                <input
                  v-model="form.bank_name"
                  type="text"
                  class="mt-1 block w-full rounded-xl bg-slate-950 border border-slate-800 px-3 py-2 text-white text-xs"
                  placeholder="BCA / Mandiri / BRI"
                />
              </div>

              <div>
                <label class="block text-xs font-medium text-slate-400">Account Number</label>
                <input
                  v-model="form.bank_account_number"
                  type="text"
                  class="mt-1 block w-full rounded-xl bg-slate-950 border border-slate-800 px-3 py-2 text-white text-xs"
                  placeholder="1234567890"
                />
              </div>
            </div>

            <div class="mt-3">
              <label class="block text-xs font-medium text-slate-400">Account Holder Name</label>
              <input
                v-model="form.bank_account_name"
                type="text"
                class="mt-1 block w-full rounded-xl bg-slate-950 border border-slate-800 px-3 py-2 text-white text-xs"
                placeholder="La Ode Mahdani"
              />
            </div>
          </div>

          <button
            type="submit"
            :disabled="form.processing"
            class="w-full flex justify-center py-3.5 px-4 rounded-xl text-sm font-bold text-white bg-sky-600 hover:bg-sky-500 transition shadow-lg shadow-sky-600/30"
          >
            {{ form.processing ? 'Submitting Application...' : 'Submit Publisher Application →' }}
          </button>
        </form>
      </div>
    </div>
  </div>
</template>
