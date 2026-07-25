<script setup lang="ts">
import { Head, Link, router, usePage } from '@inertiajs/vue3';
import PublicLayout from '@/Layouts/PublicLayout.vue';
import { computed } from 'vue';

const page = usePage();
const flashSuccess = computed(() => (page.props as any).flash?.success);
const flashError = computed(() => (page.props as any).flash?.error);

defineProps<{
  orders: {
    data: Array<{
      id: number;
      order_number: string;
      total_amount: number;
      status: string;
      created_at: string;
      items: Array<{
        id: number;
        title_snapshot: string;
        price: number;
        comic?: {
          id: number;
          title: string;
          cover_image: string;
        };
      }>;
      payment?: {
        id: number;
        tripay_reference: string;
        payment_name: string;
        pay_code: string;
        status: string;
        checkout_url: string;
      };
    }>;
    links: Array<{ url: string | null; label: string; active: boolean }>;
  };
}>();

const formatRupiah = (amount: number) => {
  return new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR', maximumFractionDigits: 0 }).format(amount);
};

const getStatusBadge = (status: string) => {
  switch (status.toLowerCase()) {
    case 'completed':
    case 'paid':
      return { label: 'PAID / COMPLETED', class: 'bg-emerald-500/10 text-emerald-400 border-emerald-500/30' };
    case 'pending':
    case 'unpaid':
      return { label: 'UNPAID (PENDING)', class: 'bg-amber-500/10 text-amber-400 border-amber-500/30' };
    case 'cancelled':
      return { label: 'CANCELLED', class: 'bg-rose-500/10 text-rose-400 border-rose-500/30' };
    default:
      return { label: status.toUpperCase(), class: 'bg-slate-800 text-slate-400 border-slate-700' };
  }
};

const cancelOrder = (orderNumber: string) => {
  if (confirm('Apakah Anda yakin ingin membatalkan pesanan ini?')) {
    router.post(`/orders/${orderNumber}/cancel`);
  }
};
</script>

<template>
  <PublicLayout>
    <Head title="My Orders & Transactions" />

    <div class="max-w-6xl mx-auto px-4 py-8 space-y-6">
      <!-- Page Header -->
      <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 border-b border-slate-800 pb-5">
        <div>
          <h1 class="text-2xl font-bold text-white flex items-center gap-2">
            <span>💳</span> My Orders & Transactions
          </h1>
          <p class="text-sm text-slate-400 mt-1">
            Riwayat transaksi pembelian bab komik dan status pembayaran Anda.
          </p>
        </div>
        <Link href="/comics" class="inline-flex items-center gap-2 text-xs font-semibold px-4 py-2 rounded-xl bg-slate-900 border border-slate-800 text-slate-300 hover:text-white transition">
          <span>📚</span> Browse Comics
        </Link>
      </div>

      <!-- Flash Messages -->
      <div v-if="flashSuccess" class="p-4 bg-emerald-500/10 border border-emerald-500/30 rounded-2xl text-emerald-400 text-xs font-medium">
        ✅ {{ flashSuccess }}
      </div>
      <div v-if="flashError" class="p-4 bg-rose-500/10 border border-rose-500/30 rounded-2xl text-rose-400 text-xs font-medium">
        ⚠️ {{ flashError }}
      </div>

      <!-- Orders List -->
      <div v-if="orders.data && orders.data.length > 0" class="space-y-4">
        <div
          v-for="order in orders.data"
          :key="order.id"
          class="bg-slate-900 border border-slate-800 rounded-2xl p-5 shadow-xl space-y-4 transition hover:border-slate-700"
        >
          <!-- Order Top Header -->
          <div class="flex flex-wrap items-center justify-between gap-3 text-xs border-b border-slate-800/80 pb-3">
            <div class="space-y-0.5">
              <span class="font-mono font-bold text-sky-400">#{{ order.order_number }}</span>
              <span class="text-slate-500 block text-[11px]">{{ new Date(order.created_at).toLocaleString('id-ID') }}</span>
            </div>
            <span
              :class="getStatusBadge(order.status).class"
              class="px-2.5 py-1 rounded-full border text-[11px] font-bold tracking-wider"
            >
              {{ getStatusBadge(order.status).label }}
            </span>
          </div>

          <!-- Order Items -->
          <div class="space-y-2">
            <div
              v-for="item in order.items"
              :key="item.id"
              class="flex items-center justify-between text-xs py-1"
            >
              <div class="flex items-center gap-3">
                <div class="w-8 h-10 bg-slate-950 rounded border border-slate-800 overflow-hidden shrink-0 flex items-center justify-center text-[10px] text-slate-500">
                  📖
                </div>
                <div>
                  <p class="font-medium text-slate-200">{{ item.title_snapshot }}</p>
                  <p v-if="item.comic" class="text-[11px] text-slate-500">{{ item.comic.title }}</p>
                </div>
              </div>
              <span class="font-mono text-slate-300">{{ formatRupiah(item.price) }}</span>
            </div>
          </div>

          <!-- Order Payment Summary & Actions -->
          <div class="pt-3 border-t border-slate-800/80 flex flex-wrap items-center justify-between gap-4 text-xs">
            <div>
              <p class="text-slate-400 text-[11px]">Total Pembayaran:</p>
              <p class="text-lg font-extrabold text-white">{{ formatRupiah(order.total_amount) }}</p>
              <p v-if="order.payment" class="text-[11px] text-slate-400 mt-0.5">
                Metode: <span class="text-slate-200 font-semibold">{{ order.payment.payment_name }}</span>
                <span v-if="order.payment.pay_code" class="ml-2 font-mono text-sky-400">Kode: {{ order.payment.pay_code }}</span>
              </p>
            </div>

            <div class="flex items-center gap-2">
              <Link
                :href="`/orders/${order.order_number}`"
                class="px-3.5 py-2 rounded-xl bg-slate-950 border border-slate-800 hover:border-slate-700 text-slate-300 font-semibold transition"
              >
                Detail Order
              </Link>

              <template v-if="order.status.toLowerCase() === 'pending'">
                <Link
                  v-if="order.payment"
                  :href="`/payment/detail/${order.payment.tripay_reference}`"
                  class="px-4 py-2 rounded-xl bg-sky-600 hover:bg-sky-500 text-white font-semibold shadow-lg shadow-sky-600/30 transition"
                >
                  Bayar Sekarang →
                </Link>
                <Link
                  v-else
                  :href="`/payment/select/${order.order_number}`"
                  class="px-4 py-2 rounded-xl bg-sky-600 hover:bg-sky-500 text-white font-semibold shadow-lg shadow-sky-600/30 transition"
                >
                  Pilih Pembayaran →
                </Link>

                <button
                  @click="cancelOrder(order.order_number)"
                  class="px-3.5 py-2 rounded-xl bg-rose-500/10 border border-rose-500/30 hover:bg-rose-500/20 text-rose-400 font-semibold transition"
                >
                  Batalkan
                </button>
              </template>
            </div>
          </div>
        </div>
      </div>

      <!-- Empty State -->
      <div v-else class="bg-slate-900 border border-slate-800 rounded-3xl p-12 text-center space-y-4">
        <div class="w-16 h-16 bg-slate-950 rounded-2xl border border-slate-800 flex items-center justify-center text-3xl mx-auto">
          💳
        </div>
        <div class="space-y-1">
          <h2 class="text-lg font-bold text-white">Belum Ada Transaksi</h2>
          <p class="text-xs text-slate-400 max-w-sm mx-auto">
            Anda belum pernah membuat transaksi pesanan. Jelajahi katalog komik kami untuk menemukan cerita favorit Anda.
          </p>
        </div>
        <Link href="/comics" class="inline-flex items-center gap-2 px-5 py-2.5 rounded-xl bg-sky-600 hover:bg-sky-500 text-white text-xs font-semibold shadow-lg shadow-sky-600/30 transition">
          Jelajahi Katalog Komik →
        </Link>
      </div>
    </div>
  </PublicLayout>
</template>
