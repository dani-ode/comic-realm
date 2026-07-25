<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';

interface Instruction {
  title: string;
  steps: string[];
}

interface Payment {
  id: number;
  tripay_reference: string;
  merchant_ref: string;
  payment_method: string;
  payment_name: string;
  amount: number;
  total_fee: number;
  pay_code?: string;
  checkout_url?: string;
  status: string;
  instructions?: Instruction[];
  expired_at?: string;
}

defineProps<{
  payment: Payment;
}>();
</script>

<template>
  <Head :title="`Payment ${payment.tripay_reference}`" />

  <div class="min-h-screen bg-slate-950 text-slate-100 flex flex-col">
    <!-- Navbar Header -->
    <header class="sticky top-0 z-50 bg-slate-950/80 backdrop-blur-md border-b border-slate-800/80 px-4 lg:px-8 py-3.5 flex items-center justify-between">
      <Link href="/" class="text-xl font-extrabold bg-gradient-to-r from-sky-400 via-indigo-400 to-purple-400 bg-clip-text text-transparent">
        The ComicRealm
      </Link>

      <div class="flex items-center gap-3">
        <Link :href="`/orders/${payment.merchant_ref}`" class="text-xs font-semibold px-4 py-2 rounded-xl bg-slate-900 border border-slate-800 text-slate-300">
          View Invoice
        </Link>
      </div>
    </header>

    <main class="max-w-3xl mx-auto px-4 lg:px-8 py-10 w-full flex-1 space-y-8">
      <div class="text-center space-y-2">
        <span class="px-3 py-1 text-xs font-bold rounded-full bg-amber-500/10 text-amber-400 border border-amber-500/30 uppercase tracking-wider">
          {{ payment.status }}
        </span>
        <h1 class="text-3xl font-extrabold text-white">Payment Instructions</h1>
        <p class="text-sm text-slate-400">Complete payment via {{ payment.payment_name }}</p>
      </div>

      <!-- Payment Code Card -->
      <div class="bg-slate-900 border border-slate-800 rounded-2xl p-6 text-center space-y-4 shadow-xl">
        <div class="space-y-1">
          <span class="text-xs text-slate-400 uppercase tracking-wider font-semibold">Payment Code / Virtual Account</span>
          <div class="text-3xl sm:text-4xl font-mono font-extrabold text-sky-400 tracking-wider select-all py-2">
            {{ payment.pay_code || 'QRIS Payment' }}
          </div>
        </div>

        <div class="pt-3 border-t border-slate-800 flex justify-between items-center text-sm px-4">
          <span class="text-slate-400">Total Amount:</span>
          <span class="text-xl font-bold text-amber-400">Rp {{ payment.amount ? payment.amount.toLocaleString() : '0' }}</span>
        </div>

        <div v-if="payment.checkout_url" class="pt-2">
          <a
            :href="payment.checkout_url"
            target="_blank"
            class="inline-block px-6 py-2.5 rounded-xl bg-sky-600 hover:bg-sky-500 text-xs font-bold text-white transition shadow-lg shadow-sky-600/30"
          >
            Open TriPay Interactive Checkout →
          </a>
        </div>
      </div>

      <!-- Step-by-Step Instructions -->
      <div v-if="payment.instructions && payment.instructions.length" class="bg-slate-900 border border-slate-800 rounded-2xl p-6 space-y-6">
        <h2 class="text-lg font-bold text-white border-b border-slate-800 pb-3">How to Pay</h2>

        <div v-for="(inst, i) in payment.instructions" :key="i" class="space-y-3">
          <h3 class="text-sm font-bold text-sky-400">{{ inst.title }}</h3>
          <ol class="list-decimal list-inside space-y-2 text-xs text-slate-300 leading-relaxed">
            <li v-for="(step, s) in inst.steps" :key="s" v-html="step"></li>
          </ol>
        </div>
      </div>
    </main>
  </div>
</template>
