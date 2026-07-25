<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';

interface OrderItem {
  id: number;
  title_snapshot: string;
  chapter_number_snapshot: number;
  price: number;
  comic?: { title: string; slug: string };
  chapter?: { chapter_number: number };
}

interface Order {
  id: number;
  order_number: string;
  subtotal: number;
  total_amount: number;
  status: string;
  expired_at: string;
  created_at: string;
  items: OrderItem[];
}

defineProps<{
  order: Order;
}>();
</script>

<template>
  <Head :title="`Invoice ${order.order_number}`" />

  <div class="min-h-screen bg-slate-950 text-slate-100 flex flex-col">
    <!-- Navbar Header -->
    <header class="sticky top-0 z-50 bg-slate-950/80 backdrop-blur-md border-b border-slate-800/80 px-4 lg:px-8 py-3.5 flex items-center justify-between">
      <Link href="/" class="text-xl font-extrabold bg-gradient-to-r from-sky-400 via-indigo-400 to-purple-400 bg-clip-text text-transparent">
        The ComicRealm
      </Link>

      <div class="flex items-center gap-3">
        <Link href="/comics" class="text-xs font-semibold px-4 py-2 rounded-xl bg-slate-900 border border-slate-800 text-slate-300">
          Catalog
        </Link>
      </div>
    </header>

    <main class="max-w-4xl mx-auto px-4 lg:px-8 py-10 w-full flex-1 space-y-8">
      <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
          <span class="text-xs text-sky-400 font-semibold tracking-wider uppercase">Order Invoice</span>
          <h1 class="text-3xl font-extrabold text-white">{{ order.order_number }}</h1>
          <p class="text-xs text-slate-400 mt-0.5">Created on {{ new Date(order.created_at).toLocaleString() }}</p>
        </div>

        <div class="flex items-center gap-3">
          <span
            class="px-3 py-1.5 text-xs font-bold rounded-xl border uppercase tracking-wider"
            :class="[
              order.status === 'completed'
                ? 'bg-emerald-500/10 text-emerald-400 border-emerald-500/30'
                : order.status === 'pending'
                ? 'bg-amber-500/10 text-amber-400 border-amber-500/30'
                : 'bg-rose-500/10 text-rose-400 border-rose-500/30'
            ]"
          >
            {{ order.status }}
          </span>
        </div>
      </div>

      <!-- Invoice Details Card -->
      <div class="bg-slate-900 border border-slate-800 rounded-2xl p-6 space-y-6">
        <h2 class="text-lg font-bold text-white border-b border-slate-800 pb-3">Purchased Items</h2>

        <div class="space-y-3">
          <div v-for="item in order.items" :key="item.id" class="flex items-center justify-between py-2 border-b border-slate-800/60 text-sm">
            <div>
              <h3 class="font-bold text-white">{{ item.title_snapshot }}</h3>
              <p class="text-xs text-slate-400 mt-0.5">Chapter {{ item.chapter_number_snapshot }}</p>
            </div>
            <div class="font-bold text-amber-400">
              Rp {{ item.price ? item.price.toLocaleString() : '5,000' }}
            </div>
          </div>
        </div>

        <div class="pt-4 border-t border-slate-800 space-y-2 text-sm">
          <div class="flex justify-between text-slate-400">
            <span>Subtotal</span>
            <span class="text-white">Rp {{ order.subtotal ? order.subtotal.toLocaleString() : '0' }}</span>
          </div>
          <div class="flex justify-between text-lg font-bold text-white pt-2 border-t border-slate-800">
            <span>Total Invoice Amount</span>
            <span class="text-amber-400">Rp {{ order.total_amount ? order.total_amount.toLocaleString() : '0' }}</span>
          </div>
        </div>

        <!-- Next Action Button (Select Payment Channel in Step 7) -->
        <div v-if="order.status === 'pending'" class="pt-4 border-t border-slate-800 space-y-3">
          <Link
            :href="`/payment/select/${order.order_number}`"
            class="w-full py-4 rounded-xl text-sm font-bold text-white bg-sky-600 hover:bg-sky-500 transition shadow-xl shadow-sky-600/30 flex items-center justify-center gap-2"
          >
            💳 Select Payment Channel (TriPay Gateway) →
          </Link>
          <p class="text-center text-xs text-slate-400">
            Payment expires on {{ new Date(order.expired_at).toLocaleString() }}
          </p>
        </div>

        <div v-else-if="order.status === 'completed'" class="pt-4 text-center text-emerald-400 text-sm font-medium">
          ✅ Payment Completed. Digital reader entitlements granted automatically!
        </div>
      </div>
    </main>
  </div>
</template>
