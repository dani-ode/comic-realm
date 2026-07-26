<script setup lang="ts">
import { Head, Link, router, usePage } from '@inertiajs/vue3';
import PublicLayout from '@/Layouts/PublicLayout.vue';
import {
  CreditCardIcon,
  BookOpenIcon,
  CheckCircleIcon,
  ExclamationTriangleIcon,
  DocumentTextIcon,
  Squares2X2Icon,
  InboxIcon,
  ClockIcon,
  ArrowRightIcon,
  XMarkIcon,
  ClipboardDocumentIcon,
  CheckIcon,
} from '@heroicons/vue/24/outline';
import { computed, ref } from 'vue';
import { useToast } from '@/composables/useToast';

const page = usePage();
const { success: toastSuccess, error: toastError } = useToast();

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
          slug?: string;
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

const copiedCode = ref<string | null>(null);

const copyCode = (code: string) => {
  navigator.clipboard.writeText(code);
  copiedCode.value = code;
  setTimeout(() => {
    copiedCode.value = null;
  }, 2500);
};

const formatRupiah = (amount: number) => {
  return new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR', maximumFractionDigits: 0 }).format(amount);
};

const getStatusBadge = (status: string) => {
  switch (status.toLowerCase()) {
    case 'completed':
    case 'paid':
      return { label: 'PAID', class: 'bg-emerald-500/10 text-emerald-400 border-emerald-500/30 shadow-sm shadow-emerald-500/20' };
    case 'pending':
    case 'unpaid':
      return { label: 'UNPAID', class: 'bg-amber-500/10 text-amber-400 border-amber-500/30 shadow-sm shadow-amber-500/20' };
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
    <Head title="My Orders & Transactions - ComicRealm" />

    <main class="max-w-5xl mx-auto px-4 lg:px-8 py-10 w-full flex-1 space-y-8">
      <!-- Page Header -->
      <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 border-b border-slate-800/80 pb-6">
        <div>
          <h1 class="text-3xl font-extrabold text-white flex items-center gap-3">
            <span class="w-10 h-10 rounded-2xl bg-sky-500/10 border border-sky-500/30 flex items-center justify-center shrink-0">
              <CreditCardIcon class="w-5 h-5 text-sky-400" />
            </span>
            My Orders & Transactions
          </h1>
          <p class="text-xs sm:text-sm text-slate-400 mt-1">
            Riwayat pembelian bab webkomik, status tagihan, dan pembayaran TriPay Anda.
          </p>
        </div>
        <Link href="/comics" class="inline-flex items-center gap-2 text-xs font-semibold px-4 py-2.5 rounded-xl bg-slate-900 border border-slate-800 text-slate-300 hover:text-white hover:border-slate-700 transition">
          <Squares2X2Icon class="w-4 h-4" /> Browse Catalog
        </Link>
      </div>

      <!-- Orders List -->
      <div v-if="orders.data && orders.data.length > 0" class="space-y-5">
        <div
          v-for="order in orders.data"
          :key="order.id"
          class="bg-slate-900/80 backdrop-blur-xl border border-slate-800/80 rounded-3xl p-5 sm:p-6 shadow-2xl space-y-5 transition hover:border-slate-700/80"
        >
          <!-- Order Header Bar -->
          <div class="flex flex-wrap items-center justify-between gap-3 border-b border-slate-800/80 pb-4 text-xs">
            <div class="flex items-center gap-3">
              <div class="space-y-0.5">
                <div class="flex items-center gap-2">
                  <span class="text-slate-400">Order ID:</span>
                  <span class="font-mono font-bold text-sky-400 text-sm">#{{ order.order_number }}</span>
                </div>
                <span class="text-slate-500 block text-[11px] flex items-center gap-1">
                  <ClockIcon class="w-3 h-3 text-slate-500" />
                  {{ new Date(order.created_at).toLocaleString('id-ID') }}
                </span>
              </div>
            </div>

            <span
              :class="getStatusBadge(order.status).class"
              class="px-3 py-1 rounded-full border text-[11px] font-extrabold tracking-wider"
            >
              {{ getStatusBadge(order.status).label }}
            </span>
          </div>

          <!-- Order Items -->
          <div class="divide-y divide-slate-800/60">
            <div
              v-for="item in order.items"
              :key="item.id"
              class="py-2.5 flex items-center justify-between gap-4 text-xs"
            >
              <div class="flex items-center gap-3.5 min-w-0">
                <Link
                  v-if="item.comic && item.comic.slug"
                  :href="`/comics/${item.comic.slug}`"
                  class="shrink-0 group"
                >
                  <img
                    v-if="item.comic.cover_image"
                    :src="item.comic.cover_image"
                    :alt="item.comic.title"
                    class="w-10 h-14 object-cover rounded-xl border border-slate-800 bg-slate-950 group-hover:border-sky-500/50 group-hover:scale-105 transition duration-300"
                  />
                </Link>
                <img
                  v-else-if="item.comic && item.comic.cover_image"
                  :src="item.comic.cover_image"
                  :alt="item.comic.title"
                  class="w-10 h-14 object-cover rounded-xl border border-slate-800 shrink-0 bg-slate-950"
                />
                <div v-else class="w-10 h-14 bg-slate-950 rounded-xl border border-slate-800 shrink-0 flex items-center justify-center text-slate-600">
                  <DocumentTextIcon class="w-5 h-5" />
                </div>

                <div class="min-w-0">
                  <p class="font-bold text-white text-xs sm:text-sm truncate">{{ item.title_snapshot }}</p>
                  <Link
                    v-if="item.comic && item.comic.slug"
                    :href="`/comics/${item.comic.slug}`"
                    class="text-[11px] text-sky-400 font-medium truncate mt-0.5 hover:underline block"
                  >
                    {{ item.comic.title }}
                  </Link>
                  <p v-else-if="item.comic" class="text-[11px] text-sky-400 font-medium truncate mt-0.5">{{ item.comic.title }}</p>
                </div>
              </div>

              <span class="font-bold text-slate-200 text-xs sm:text-sm shrink-0">{{ formatRupiah(item.price) }}</span>
            </div>
          </div>

          <!-- Payment Details & Actions Footer -->
          <div class="pt-4 border-t border-slate-800/80 flex flex-col sm:flex-row sm:items-center justify-between gap-4 text-xs">
            <div>
              <span class="text-slate-400 text-[11px]">Total Amount:</span>
              <p class="text-xl font-extrabold text-amber-400">{{ formatRupiah(order.total_amount) }}</p>
              <div v-if="order.payment" class="flex items-center gap-2 text-[11px] text-slate-400 mt-1">
                <span>Method: <strong class="text-slate-200">{{ order.payment.payment_name }}</strong></span>
                <span v-if="order.payment.pay_code" class="flex items-center gap-1 bg-slate-950 px-2 py-0.5 rounded-lg border border-slate-800 font-mono text-sky-400">
                  Code: {{ order.payment.pay_code }}
                  <button @click="copyCode(order.payment.pay_code)" class="hover:text-white transition ml-1" title="Copy Code">
                    <CheckIcon v-if="copiedCode === order.payment.pay_code" class="w-3.5 h-3.5 text-emerald-400" />
                    <ClipboardDocumentIcon v-else class="w-3.5 h-3.5" />
                  </button>
                </span>
              </div>
            </div>

            <!-- Action Buttons -->
            <div class="flex items-center gap-2 shrink-0">
              <Link
                :href="`/orders/${order.order_number}`"
                class="px-4 py-2.5 rounded-xl bg-slate-950 border border-slate-800 hover:border-slate-700 text-slate-300 font-semibold transition text-xs"
              >
                Detail
              </Link>

              <template v-if="order.status.toLowerCase() === 'pending'">
                <Link
                  v-if="order.payment"
                  :href="`/payment/detail/${order.payment.tripay_reference}`"
                  class="px-4 py-2.5 rounded-xl bg-gradient-to-r from-sky-600 to-indigo-600 hover:from-sky-500 hover:to-indigo-500 text-white font-bold shadow-lg shadow-sky-600/30 transition text-xs flex items-center gap-1.5"
                >
                  <span>Pay Now</span>
                  <ArrowRightIcon class="w-3.5 h-3.5" />
                </Link>
                <Link
                  v-else
                  :href="`/payment/select/${order.order_number}`"
                  class="px-4 py-2.5 rounded-xl bg-gradient-to-r from-sky-600 to-indigo-600 hover:from-sky-500 hover:to-indigo-500 text-white font-bold shadow-lg shadow-sky-600/30 transition text-xs flex items-center gap-1.5"
                >
                  <span>Select Payment</span>
                  <ArrowRightIcon class="w-3.5 h-3.5" />
                </Link>

                <button
                  @click="cancelOrder(order.order_number)"
                  class="px-3.5 py-2.5 rounded-xl bg-rose-500/10 border border-rose-500/30 hover:bg-rose-500/20 text-rose-400 font-semibold transition text-xs flex items-center gap-1"
                >
                  <XMarkIcon class="w-3.5 h-3.5" /> Cancel
                </button>
              </template>
            </div>
          </div>
        </div>
      </div>

      <!-- Empty State -->
      <div v-else class="bg-slate-900/60 border border-slate-800 rounded-3xl p-16 text-center space-y-4">
        <div class="w-16 h-16 bg-slate-950 border border-slate-800 rounded-2xl flex items-center justify-center mx-auto">
          <InboxIcon class="w-8 h-8 text-slate-500" />
        </div>
        <div class="space-y-1">
          <h2 class="text-xl font-bold text-white">Belum Ada Transaksi</h2>
          <p class="text-sm text-slate-400 max-w-md mx-auto">
            Anda belum memiliki riwayat pembelian. Jelajahi katalog komik kami untuk menemukan cerita menarik.
          </p>
        </div>
        <div class="pt-2">
          <Link href="/comics" class="inline-flex items-center gap-2 px-6 py-3 rounded-xl bg-sky-600 hover:bg-sky-500 text-white font-semibold text-sm transition shadow-lg shadow-sky-600/30">
            <Squares2X2Icon class="w-4 h-4" />
            Explore Comic Catalog
          </Link>
        </div>
      </div>
    </main>
  </PublicLayout>
</template>
