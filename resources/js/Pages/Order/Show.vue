<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import PublicLayout from '@/Layouts/PublicLayout.vue';
import {
  DocumentTextIcon,
  CreditCardIcon,
  CheckCircleIcon,
  ClockIcon,
  ArrowRightIcon,
  BookOpenIcon,
  ShieldCheckIcon,
  ClipboardDocumentIcon,
  CheckIcon,
} from '@heroicons/vue/24/outline';
import { ref } from 'vue';

interface OrderItem {
  id: number;
  title_snapshot: string;
  chapter_number_snapshot: number;
  price: number;
  comic?: {
    id: number;
    title: string;
    cover_image: string;
    slug: string;
  };
  chapter?: {
    id: number;
    chapter_number: number;
  };
}

interface Payment {
  id: number;
  tripay_reference: string;
  payment_name: string;
  pay_code: string;
  status: string;
  checkout_url?: string;
}

interface Order {
  id: number;
  order_number: string;
  subtotal: number;
  total_amount: number;
  status: string;
  expired_at?: string;
  created_at: string;
  items: OrderItem[];
  payment?: Payment;
}

const props = defineProps<{
  order: Order;
}>();

const copied = ref(false);

const copyInvoiceNumber = () => {
  navigator.clipboard.writeText(props.order.order_number);
  copied.value = true;
  setTimeout(() => {
    copied.value = false;
  }, 2500);
};

const formatRupiah = (amount: number) => {
  return new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR', maximumFractionDigits: 0 }).format(amount);
};
</script>

<template>
  <Head :title="`Invoice #${order.order_number} - ComicRealm`" />

  <PublicLayout minimal title="Detail Pesanan" backUrl="/orders">
    <main class="max-w-3xl mx-auto px-4 lg:px-8 py-10 w-full flex-1 space-y-8">
      <!-- Invoice Header Info -->
      <div class="bg-slate-900/80 backdrop-blur-xl border border-slate-800/80 rounded-3xl p-6 sm:p-8 shadow-2xl space-y-6">
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 border-b border-slate-800/80 pb-5">
          <div class="space-y-1">
            <span class="text-xs text-sky-400 font-bold uppercase tracking-wider">Official Invoice</span>
            <div class="flex items-center gap-2">
              <h1 class="text-2xl sm:text-3xl font-black text-white font-mono">#{{ order.order_number }}</h1>
              <button
                @click="copyInvoiceNumber"
                class="p-1.5 rounded-lg bg-slate-950 border border-slate-800 hover:border-slate-700 text-slate-400 hover:text-white transition"
                title="Copy Invoice Number"
              >
                <CheckIcon v-if="copied" class="w-4 h-4 text-emerald-400" />
                <ClipboardDocumentIcon v-else class="w-4 h-4" />
              </button>
            </div>
            <p class="text-xs text-slate-400 flex items-center gap-1.5 pt-0.5">
              <ClockIcon class="w-3.5 h-3.5 text-slate-500" />
              Created on {{ new Date(order.created_at).toLocaleString('id-ID') }}
            </p>
          </div>

          <div class="shrink-0">
            <span
              class="px-3.5 py-1.5 text-xs font-extrabold rounded-full border uppercase tracking-wider inline-flex items-center gap-1.5 shadow-lg"
              :class="[
                order.status.toLowerCase() === 'completed' || order.status.toLowerCase() === 'paid'
                  ? 'bg-emerald-500/10 text-emerald-400 border-emerald-500/30 shadow-emerald-500/10'
                  : order.status.toLowerCase() === 'pending'
                  ? 'bg-amber-500/10 text-amber-400 border-amber-500/30 shadow-amber-500/10'
                  : 'bg-rose-500/10 text-rose-400 border-rose-500/30'
              ]"
            >
              <span class="w-2 h-2 rounded-full" :class="[
                order.status.toLowerCase() === 'completed' || order.status.toLowerCase() === 'paid' ? 'bg-emerald-400 animate-pulse' : order.status.toLowerCase() === 'pending' ? 'bg-amber-400 animate-pulse' : 'bg-rose-400'
              ]"></span>
              {{ order.status }}
            </span>
          </div>
        </div>

        <!-- Purchased Chapter Items -->
        <div class="space-y-4">
          <h2 class="text-xs font-bold text-sky-400 uppercase tracking-wider">Purchased Chapters</h2>

          <div class="divide-y divide-slate-800/60">
            <div
              v-for="item in order.items"
              :key="item.id"
              class="py-3 flex items-center justify-between gap-4 text-xs sm:text-sm"
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
                    class="w-12 h-16 object-cover rounded-xl border border-slate-800 bg-slate-950 group-hover:border-sky-500/50 group-hover:scale-105 transition duration-300"
                  />
                </Link>
                <img
                  v-else-if="item.comic && item.comic.cover_image"
                  :src="item.comic.cover_image"
                  :alt="item.comic.title"
                  class="w-12 h-16 object-cover rounded-xl border border-slate-800 shrink-0 bg-slate-950"
                />
                <div v-else class="w-12 h-16 bg-slate-950 rounded-xl border border-slate-800 shrink-0 flex items-center justify-center text-slate-600">
                  <DocumentTextIcon class="w-6 h-6" />
                </div>

                <div class="min-w-0">
                  <p class="font-bold text-white text-xs sm:text-sm truncate">{{ item.title_snapshot }}</p>
                  <Link
                    v-if="item.comic && item.comic.slug"
                    :href="`/comics/${item.comic.slug}`"
                    class="text-xs text-sky-400 font-medium truncate mt-0.5 hover:underline block"
                  >
                    {{ item.comic.title }}
                  </Link>
                  <p v-else-if="item.comic" class="text-xs text-sky-400 font-medium truncate mt-0.5">{{ item.comic.title }}</p>
                  <span class="text-[10px] text-slate-500 block mt-0.5">Chapter {{ item.chapter_number_snapshot }} • Lifetime Access</span>
                </div>
              </div>

              <div class="font-bold text-amber-400 text-xs sm:text-sm shrink-0">
                {{ formatRupiah(item.price) }}
              </div>
            </div>
          </div>
        </div>

        <!-- Order Financial Summary -->
        <div class="pt-4 border-t border-slate-800/80 space-y-3 text-xs sm:text-sm">
          <div class="flex justify-between text-slate-400">
            <span>Subtotal ({{ order.items.length }} items)</span>
            <span class="text-white">{{ formatRupiah(order.subtotal || order.total_amount) }}</span>
          </div>
          <div class="flex justify-between text-slate-400">
            <span class="flex items-center gap-1">
              <ShieldCheckIcon class="w-4 h-4 text-emerald-400" />
              Gateway Payment Processing
            </span>
            <span class="text-emerald-400 font-medium">Included</span>
          </div>
          <div class="pt-3 border-t border-slate-800 flex justify-between text-base font-extrabold">
            <span class="text-white">Total Amount</span>
            <span class="text-amber-400 text-lg sm:text-xl">{{ formatRupiah(order.total_amount) }}</span>
          </div>
        </div>

        <!-- Payment Info & Next Action CTA -->
        <div v-if="order.status.toLowerCase() === 'pending'" class="pt-4 border-t border-slate-800/80 space-y-4">
          <div v-if="order.payment" class="bg-slate-950 border border-slate-800 rounded-2xl p-4 space-y-2 text-xs">
            <div class="flex justify-between text-slate-400">
              <span>Payment Channel:</span>
              <strong class="text-white">{{ order.payment.payment_name }}</strong>
            </div>
            <div v-if="order.payment.pay_code" class="flex justify-between text-slate-400">
              <span>Virtual Account / Code:</span>
              <strong class="font-mono text-sky-400 text-sm select-all">{{ order.payment.pay_code }}</strong>
            </div>
          </div>

          <div class="space-y-2">
            <Link
              v-if="order.payment"
              :href="`/payment/detail/${order.payment.tripay_reference}`"
              class="w-full py-4 px-6 rounded-2xl text-xs sm:text-sm font-bold text-white bg-gradient-to-r from-sky-600 to-indigo-600 hover:from-sky-500 hover:to-indigo-500 transition shadow-xl shadow-sky-600/30 active:scale-[0.98] flex items-center justify-center gap-2"
            >
              <CreditCardIcon class="w-5 h-5 shrink-0" />
              <span>Complete Payment Now</span>
              <ArrowRightIcon class="w-4 h-4 shrink-0" />
            </Link>
            <Link
              v-else
              :href="`/payment/select/${order.order_number}`"
              class="w-full py-4 px-6 rounded-2xl text-xs sm:text-sm font-bold text-white bg-gradient-to-r from-sky-600 to-indigo-600 hover:from-sky-500 hover:to-indigo-500 transition shadow-xl shadow-sky-600/30 active:scale-[0.98] flex items-center justify-center gap-2"
            >
              <CreditCardIcon class="w-5 h-5 shrink-0" />
              <span>Select Payment Method (TriPay)</span>
              <ArrowRightIcon class="w-4 h-4 shrink-0" />
            </Link>
            <p v-if="order.expired_at" class="text-center text-[11px] text-slate-400">
              Payment expires on {{ new Date(order.expired_at).toLocaleString('id-ID') }}
            </p>
          </div>
        </div>

        <div v-else-if="order.status.toLowerCase() === 'completed' || order.status.toLowerCase() === 'paid'" class="pt-4 border-t border-slate-800/80 space-y-4">
          <div class="p-4 bg-emerald-500/10 border border-emerald-500/30 rounded-2xl text-emerald-400 text-xs font-semibold flex items-center gap-3">
            <CheckCircleIcon class="w-6 h-6 shrink-0" />
            <div>
              <p class="font-bold text-sm text-emerald-300">Payment Confirmed!</p>
              <p class="text-xs text-emerald-400/80 mt-0.5">Your chapter reader entitlements have been unlocked automatically in your library.</p>
            </div>
          </div>
          <Link
            href="/library"
            class="w-full py-3.5 px-6 rounded-2xl text-xs sm:text-sm font-bold text-white bg-emerald-600 hover:bg-emerald-500 transition shadow-xl shadow-emerald-600/30 flex items-center justify-center gap-2"
          >
            <BookOpenIcon class="w-5 h-5" />
            <span>Go to My Library & Start Reading</span>
          </Link>
        </div>
      </div>
    </main>
  </PublicLayout>
</template>
