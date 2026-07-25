<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import { ref } from 'vue';
import axios from 'axios';

interface Channel {
  code: string;
  name: string;
  group: string;
  fee_merchant: number;
  fee_customer: number;
  total_fee: number;
  active: boolean;
  icon_url?: string;
}

interface Order {
  id: number;
  order_number: string;
  total_amount: number;
}

const props = defineProps<{
  order: Order;
  channels: Channel[];
}>();

const selectedCode = ref('');
const isLoading = ref(false);
const errorMessage = ref('');

const processPayment = async () => {
  if (!selectedCode.value) return;
  isLoading.value = true;
  errorMessage.value = '';

  try {
    const res = await axios.post('/api/payment/process', {
      order_number: props.order.order_number,
      payment_method: selectedCode.value,
    });

    if (res.data && res.data.redirect_url) {
      window.location.href = res.data.redirect_url;
    }
  } catch (err: any) {
    if (err.response && err.response.data && err.response.data.message) {
      errorMessage.value = err.response.data.message;
    } else {
      errorMessage.value = 'Gagal memproses transaksi pembayaran.';
    }
  } finally {
    isLoading.value = false;
  }
};
</script>

<template>
  <Head title="Select Payment Method - TriPay Gateway" />

  <div class="min-h-screen bg-slate-950 text-slate-100 flex flex-col">
    <!-- Navbar Header -->
    <header class="sticky top-0 z-50 bg-slate-950/80 backdrop-blur-md border-b border-slate-800/80 px-4 lg:px-8 py-3.5 flex items-center justify-between">
      <Link href="/" class="text-xl font-extrabold bg-gradient-to-r from-sky-400 via-indigo-400 to-purple-400 bg-clip-text text-transparent">
        The ComicRealm
      </Link>

      <div class="flex items-center gap-3">
        <Link :href="`/orders/${order.order_number}`" class="text-xs font-semibold px-4 py-2 rounded-xl bg-slate-900 border border-slate-800 text-slate-300">
          ← Back to Invoice
        </Link>
      </div>
    </header>

    <main class="max-w-4xl mx-auto px-4 lg:px-8 py-10 w-full flex-1 space-y-8">
      <div>
        <span class="text-xs text-sky-400 font-semibold tracking-wider uppercase">TriPay Gateway</span>
        <h1 class="text-3xl font-extrabold text-white">Select Payment Channel</h1>
        <p class="text-sm text-slate-400 mt-1">Invoice: <strong class="text-white">{{ order.order_number }}</strong> • Total: <strong class="text-amber-400">Rp {{ order.total_amount ? order.total_amount.toLocaleString() : '0' }}</strong></p>
      </div>

      <div v-if="errorMessage" class="p-4 bg-rose-500/10 border border-rose-500/30 rounded-xl text-rose-400 text-sm">
        {{ errorMessage }}
      </div>

      <!-- Payment Channel Grid -->
      <div v-if="channels && channels.length" class="space-y-6">
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
          <label
            v-for="ch in channels"
            :key="ch.code"
            class="relative bg-slate-900 border rounded-2xl p-4 flex items-center justify-between cursor-pointer transition"
            :class="[
              selectedCode === ch.code
                ? 'border-sky-500 bg-sky-500/10 shadow-lg shadow-sky-500/10'
                : 'border-slate-800 hover:border-slate-700'
            ]"
          >
            <div class="flex items-center gap-3">
              <input
                type="radio"
                name="payment_channel"
                :value="ch.code"
                v-model="selectedCode"
                class="h-4 w-4 text-sky-600 bg-slate-950 border-slate-800 focus:ring-sky-500"
              />
              <div>
                <h3 class="font-bold text-white text-sm">{{ ch.name }}</h3>
                <span class="text-xs text-slate-400">{{ ch.group }}</span>
              </div>
            </div>

            <div class="text-right">
              <span class="text-xs font-semibold text-slate-300">
                + Rp {{ ch.total_fee ? ch.total_fee.toLocaleString() : '0' }} Fee
              </span>
            </div>
          </label>
        </div>

        <button
          @click="processPayment"
          :disabled="!selectedCode || isLoading"
          class="w-full py-4 rounded-xl text-base font-bold text-white bg-sky-600 hover:bg-sky-500 disabled:opacity-50 transition shadow-xl shadow-sky-600/30 flex items-center justify-center gap-2"
        >
          {{ isLoading ? 'Generating Payment Code...' : 'Generate Pay Code / QRIS →' }}
        </button>
      </div>

      <div v-else class="bg-slate-900/40 border border-slate-800 rounded-2xl p-12 text-center text-slate-400">
        No payment channels available at the moment.
      </div>
    </main>
  </div>
</template>
