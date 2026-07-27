<script setup lang="ts">
import { Head, Link, router } from '@inertiajs/vue3';
import { ref, computed, onMounted, onUnmounted } from 'vue';
import axios from 'axios';
import PublicLayout from '@/Layouts/PublicLayout.vue';
import {
  ClipboardDocumentIcon,
  CheckIcon,
  ShieldCheckIcon,
  ClockIcon,
  DocumentTextIcon,
  CheckCircleIcon,
  XCircleIcon,
  ExclamationTriangleIcon,
  ArrowPathIcon,
  BookOpenIcon,
  ArrowRightIcon,
  ShoppingBagIcon,
} from '@heroicons/vue/24/outline';
import { useToast } from '@/composables/useToast';

interface Instruction {
  title: string;
  steps: string[];
}

interface Comic {
  id: number;
  title: string;
  slug?: string;
  cover_image: string;
}

interface OrderItem {
  id: number;
  title_snapshot: string;
  chapter_number_snapshot?: number;
  price: number;
  comic?: Comic;
}

interface Order {
  id: number;
  order_number: string;
  total_amount: number;
  subtotal: number;
  fee_amount: number;
  status: string;
  created_at: string;
  items?: OrderItem[];
}

interface Payment {
  id: number;
  tripay_reference: string;
  merchant_ref: string;
  payment_method: string;
  payment_name: string;
  amount: number;
  fee_customer?: number;
  total_fee?: number;
  pay_code?: string;
  pay_url?: string;
  checkout_url?: string;
  status: string;
  instructions?: Instruction[];
  expired_at?: string;
  order?: Order;
  user?: {
    id: number;
    name: string;
    email: string;
  };
}

const props = defineProps<{
  payment: Payment;
}>();

const { success: toastSuccess, error: toastError } = useToast();
const copiedCode = ref(false);
const copiedInvoice = ref(false);
const isCheckingStatus = ref(false);

const isPendingPayment = computed(() => {
  const s = (props.payment.status || '').toLowerCase();
  return s === 'unpaid' || s === 'pending';
});

const isPaidPayment = computed(() => {
  const s = (props.payment.status || '').toLowerCase();
  return s === 'paid' || s === 'completed' || s === 'success';
});

// Live Countdown Timer
const timeLeft = ref('');
const isExpired = ref(false);
let timerInterval: any = null;

const updateCountdown = () => {
  if (!props.payment.expired_at || !isPendingPayment.value) {
    timeLeft.value = '';
    return;
  }

  const expireTime = new Date(props.payment.expired_at).getTime();
  const now = new Date().getTime();
  const diff = expireTime - now;

  if (diff <= 0) {
    timeLeft.value = 'Kedaluwarsa';
    isExpired.value = true;
    if (timerInterval) clearInterval(timerInterval);
    return;
  }

  const hours = Math.floor(diff / (1000 * 60 * 60));
  const minutes = Math.floor((diff % (1000 * 60 * 60)) / (1000 * 60));
  const seconds = Math.floor((diff % (1000 * 60)) / 1000);

  const hStr = hours > 0 ? `${hours}j ` : '';
  const mStr = `${minutes}m `;
  const sStr = `${seconds}d`;

  timeLeft.value = `${hStr}${mStr}${sStr}`;
};

let pollInterval: ReturnType<typeof setInterval> | null = null;

const startAutoPolling = () => {
  if (!isPendingPayment.value) {
    return;
  }

  pollInterval = setInterval(() => {
    router.reload({
      only: ['payment'],
      showProgress: false,
      onSuccess: (page) => {
        const updatedPayment = (page.props as any).payment;
        if (updatedPayment) {
          const newStatus = (updatedPayment.status || '').toLowerCase();
          if (newStatus !== 'unpaid' && newStatus !== 'pending') {
            if (pollInterval) {
              clearInterval(pollInterval);
              pollInterval = null;
            }
            if (newStatus === 'paid' || newStatus === 'completed' || newStatus === 'success') {
              toastSuccess('Pembayaran LUNAS! Komik Anda sudah dapat dibaca.');
            }
          }
        }
      },
    });
  }, 5000);
};

onMounted(() => {
  if (isPendingPayment.value) {
    updateCountdown();
    timerInterval = setInterval(updateCountdown, 1000);
    startAutoPolling();
  }
});

onUnmounted(() => {
  if (timerInterval) clearInterval(timerInterval);
  if (pollInterval) clearInterval(pollInterval);
});

const formatRupiah = (val: any) => {
  const num = typeof val === 'number' ? val : (parseFloat(val) || 0);
  return 'Rp ' + Math.round(num).toLocaleString('id-ID');
};

const copyToClipboard = async (text: string, type: 'code' | 'invoice') => {
  try {
    await navigator.clipboard.writeText(text);
    if (type === 'code') {
      copiedCode.value = true;
      toastSuccess('Kode Pembayaran berhasil disalin!');
      setTimeout(() => (copiedCode.value = false), 2000);
    } else {
      copiedInvoice.value = true;
      toastSuccess('Nomor Invoice berhasil disalin!');
      setTimeout(() => (copiedInvoice.value = false), 2000);
    }
  } catch (err) {
    //
  }
};

const checkPaymentStatus = async () => {
  isCheckingStatus.value = true;
  try {
    const res = await axios.post('/api/payment/check-status', {
      reference: props.payment.tripay_reference,
    });
    if (res.data && res.data.success) {
      if (res.data.status === 'PAID') {
        toastSuccess(res.data.message || 'Pembayaran LUNAS! Hak akses diberikan.');
      } else {
        toastSuccess(res.data.message || 'Status pembayaran berhasil diperbarui.');
      }
      router.reload();
    } else {
      toastError(res.data?.message || 'Gagal mengecek status pembayaran.');
    }
  } catch (err: any) {
    toastError('Gagal memeriksa status ke server TriPay.');
  } finally {
    isCheckingStatus.value = false;
  }
};

const getStatusBadge = (status: string) => {
  const s = (status || '').toLowerCase();
  if (s === 'paid' || s === 'completed' || s === 'success') {
    return {
      label: 'PAID / LUNAS',
      class: 'bg-emerald-500/10 text-emerald-400 border-emerald-500/30 shadow-lg shadow-emerald-500/10',
      icon: CheckCircleIcon,
    };
  }
  if (s === 'unpaid' || s === 'pending') {
    return {
      label: 'MENUNGGU PEMBAYARAN',
      class: 'bg-amber-500/10 text-amber-400 border-amber-500/30 shadow-lg shadow-amber-500/10 animate-pulse',
      icon: ClockIcon,
    };
  }
  if (s === 'expired') {
    return {
      label: 'KEDALUWARSA',
      class: 'bg-rose-500/10 text-rose-400 border-rose-500/30',
      icon: XCircleIcon,
    };
  }
  if (s === 'failed') {
    return {
      label: 'PEMBAYARAN GAGAL',
      class: 'bg-rose-500/10 text-rose-400 border-rose-500/30',
      icon: XCircleIcon,
    };
  }
  if (s === 'refund' || s === 'cancelled') {
    return {
      label: 'DIKEMBALIKAN / DIBATALKAN',
      class: 'bg-purple-500/10 text-purple-400 border-purple-500/30',
      icon: ExclamationTriangleIcon,
    };
  }
  return {
    label: status.toUpperCase(),
    class: 'bg-slate-800 text-slate-300 border-slate-700',
    icon: ExclamationTriangleIcon,
  };
};

const feeAmount = computed(() => {
  return props.payment.total_fee ?? props.payment.fee_customer ?? 0;
});

const subtotalAmount = computed(() => {
  if (props.payment.order?.subtotal) {
    return props.payment.order.subtotal;
  }
  if (props.payment.amount > feeAmount.value && feeAmount.value > 0) {
    return props.payment.amount - feeAmount.value;
  }
  return props.payment.amount;
});

const totalAmountWithFee = computed(() => {
  if (props.payment.amount > subtotalAmount.value) {
    return props.payment.amount;
  }
  return subtotalAmount.value + feeAmount.value;
});

const isQrisPayment = computed(() => {
  const method = (props.payment.payment_method || '').toLowerCase();
  const name = (props.payment.payment_name || '').toLowerCase();
  return method.includes('qris') || name.includes('qris');
});

const qrisImageUrl = computed(() => {
  if (props.payment.pay_url && (props.payment.pay_url.startsWith('http') || props.payment.pay_url.endsWith('.png') || props.payment.pay_url.endsWith('.svg'))) {
    return props.payment.pay_url;
  }
  if (props.payment.pay_code && props.payment.pay_code.startsWith('http')) {
    return props.payment.pay_code;
  }
  const qrData = props.payment.pay_code || props.payment.tripay_reference;
  return `https://api.qrserver.com/v1/create-qr-code/?size=300x300&data=${encodeURIComponent(qrData)}`;
});
</script>

<template>
  <Head :title="`Detail Pembayaran ${payment.merchant_ref || payment.tripay_reference}`" />

  <PublicLayout minimal title="Detail Pembayaran" backUrl="/orders">
    <main class="max-w-4xl mx-auto px-4 lg:px-8 py-10 w-full flex-1 space-y-8">

      <!-- Header & Status Card -->
      <div class="bg-slate-900/80 backdrop-blur-xl border border-slate-800/80 rounded-3xl p-6 shadow-2xl space-y-4">
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 border-b border-slate-800/80 pb-4">
          <div>
            <div class="flex items-center gap-2">
              <span class="text-xs text-sky-400 font-semibold uppercase tracking-wider">TriPay Gateway</span>
              <span class="text-slate-600">•</span>
              <span class="text-xs text-slate-400 font-mono">{{ payment.payment_name }}</span>
            </div>
            <div class="flex items-center gap-2 mt-1 min-w-0">
              <h1 class="text-base sm:text-2xl font-extrabold text-white font-mono truncate">Invoice #{{ payment.merchant_ref }}</h1>
              <button
                @click="copyToClipboard(payment.merchant_ref, 'invoice')"
                class="p-1 rounded-lg hover:bg-slate-800 text-slate-400 hover:text-white transition shrink-0"
                title="Salin Nomor Invoice"
              >
                <CheckIcon v-if="copiedInvoice" class="w-4 h-4 text-emerald-400" />
                <ClipboardDocumentIcon v-else class="w-4 h-4" />
              </button>
            </div>

            <!-- Live Countdown Timer (ONLY for UNPAID / PENDING) -->
            <div
              v-if="isPendingPayment && payment.expired_at"
              class="flex items-center gap-2 mt-1.5 text-xs"
            >
              <ClockIcon class="w-4 h-4 text-amber-400 shrink-0" />
              <span class="text-slate-400">Batas Waktu Bayar:</span>
              <span
                class="font-mono font-bold px-2 py-0.5 rounded-md border text-[11px]"
                :class="isExpired ? 'bg-rose-500/10 text-rose-400 border-rose-500/30' : 'bg-amber-500/10 text-amber-400 border-amber-500/30 animate-pulse'"
              >
                {{ timeLeft }}
              </span>
            </div>
          </div>

          <div class="shrink-0 flex items-center">
            <span
              :class="getStatusBadge(payment.status).class"
              class="px-4 py-1.5 rounded-full border text-xs font-black tracking-wider flex items-center gap-1.5"
            >
              <component :is="getStatusBadge(payment.status).icon" class="w-4 h-4" />
              {{ getStatusBadge(payment.status).label }}
            </span>
          </div>
        </div>

        <!-- ACTIVE UNPAID / PENDING PAYMENT DISPLAY BOX -->
        <template v-if="isPendingPayment">
          <!-- QRIS Payment Display Box -->
          <div v-if="isQrisPayment" class="bg-slate-950/80 border border-slate-800 rounded-2xl p-6 text-center space-y-4 relative group">
            <span class="text-xs text-sky-400 uppercase tracking-wider font-extrabold block">
              Scan Kode QRIS ({{ payment.payment_name }})
            </span>

            <div class="flex flex-col items-center justify-center space-y-3">
              <div class="p-3.5 bg-white rounded-3xl shadow-2xl border-4 border-slate-800 inline-block group-hover:scale-105 transition duration-300">
                <img
                  :src="qrisImageUrl"
                  alt="QRIS Payment Code"
                  class="w-48 h-48 sm:w-56 sm:h-56 object-contain rounded-xl"
                />
              </div>
              <p class="text-xs text-slate-400 max-w-sm leading-relaxed">
                Buka aplikasi <strong>GoPay, OVO, DANA, ShopeePay, BCA Mobile</strong>, atau m-Banking pilihan Anda lalu pilih fitur <strong>Scan / Pindai QRIS</strong>.
              </p>
            </div>

            <!-- Atas Nama (A.N.) Account Info -->
            <div class="pt-1 text-xs text-slate-400 flex items-center justify-center gap-1.5 flex-wrap">
              <span>Atas Nama (A.N.):</span>
              <span class="px-2 py-0.5 rounded-lg bg-sky-500/10 border border-sky-500/30 text-sky-300 font-bold">
                ComicRealm - {{ payment.user?.name || 'Pelanggan' }}
              </span>
            </div>

            <!-- Total Amount Summary -->
            <div class="pt-3 border-t border-slate-800/80 flex items-center justify-between text-xs sm:text-sm px-2">
              <span class="text-slate-400">Total Nominal Pembayaran:</span>
              <span class="text-xl sm:text-2xl font-black text-amber-400">{{ formatRupiah(totalAmountWithFee) }}</span>
            </div>

            <!-- Active Check Payment Status Button -->
            <div class="pt-2">
              <button
                @click="checkPaymentStatus"
                :disabled="isCheckingStatus"
                class="w-full sm:w-auto inline-flex items-center justify-center gap-2 px-6 py-3 rounded-xl bg-gradient-to-r from-sky-600 to-indigo-600 hover:from-sky-500 hover:to-indigo-500 text-xs sm:text-sm font-bold text-white transition shadow-xl shadow-sky-600/30 active:scale-[0.98] disabled:opacity-50"
              >
                <ArrowPathIcon class="w-4 h-4 shrink-0" :class="{ 'animate-spin': isCheckingStatus }" />
                <span>{{ isCheckingStatus ? 'Mengecek Status Pembayaran...' : 'Cek Status Pembayaran' }}</span>
              </button>
            </div>
          </div>

          <!-- Virtual Account / Retail Code Display Box -->
          <div v-else class="bg-slate-950/80 border border-slate-800 rounded-2xl p-5 text-center space-y-3 relative group">
            <span class="text-xs text-slate-400 uppercase tracking-wider font-semibold block">
              Kode Pembayaran / Nomor Virtual Account ({{ payment.payment_name }})
            </span>

            <div class="flex items-center justify-center gap-2.5 flex-wrap">
              <div class="text-lg sm:text-xl font-mono font-bold text-sky-400 bg-slate-900 border border-slate-800 px-4 py-2 rounded-xl tracking-wider select-all">
                {{ payment.pay_code || payment.tripay_reference || 'QRIS Payment' }}
              </div>

              <button
                v-if="payment.pay_code"
                @click="copyToClipboard(payment.pay_code, 'code')"
                class="px-3 py-2 rounded-xl bg-sky-500/20 hover:bg-sky-500/30 border border-sky-500/40 text-sky-300 text-xs font-bold transition flex items-center gap-1.5 active:scale-95"
              >
                <CheckIcon v-if="copiedCode" class="w-4 h-4 text-emerald-400" />
                <ClipboardDocumentIcon v-else class="w-4 h-4" />
                <span>{{ copiedCode ? 'Tersalin' : 'Salin' }}</span>
              </button>
            </div>

            <!-- Atas Nama (A.N.) Account Info -->
            <div class="pt-1 text-xs text-slate-400 flex items-center justify-center gap-1.5 flex-wrap">
              <span>Atas Nama (A.N.):</span>
              <span class="px-2 py-0.5 rounded-lg bg-sky-500/10 border border-sky-500/30 text-sky-300 font-bold">
                ComicRealm - {{ payment.user?.name || 'Pelanggan' }}
              </span>
            </div>

            <!-- Total Amount Summary -->
            <div class="pt-3 border-t border-slate-800/80 flex items-center justify-between text-xs sm:text-sm px-2">
              <span class="text-slate-400">Total Nominal Pembayaran:</span>
              <span class="text-xl sm:text-2xl font-black text-amber-400">{{ formatRupiah(totalAmountWithFee) }}</span>
            </div>

            <!-- Active Check Payment Status Button -->
            <div class="pt-2">
              <button
                @click="checkPaymentStatus"
                :disabled="isCheckingStatus"
                class="w-full sm:w-auto inline-flex items-center justify-center gap-2 px-6 py-3 rounded-xl bg-gradient-to-r from-sky-600 to-indigo-600 hover:from-sky-500 hover:to-indigo-500 text-xs sm:text-sm font-bold text-white transition shadow-xl shadow-sky-600/30 active:scale-[0.98] disabled:opacity-50"
              >
                <ArrowPathIcon class="w-4 h-4 shrink-0" :class="{ 'animate-spin': isCheckingStatus }" />
                <span>{{ isCheckingStatus ? 'Mengecek Status Pembayaran...' : 'Cek Status Pembayaran' }}</span>
              </button>
            </div>
          </div>
        </template>

        <!-- CASE 2: PAID / COMPLETED PAYMENT DISPLAY BOX -->
        <template v-else-if="isPaidPayment">
          <div class="bg-slate-950/80 border border-emerald-500/30 rounded-2xl p-6 text-center space-y-4">
            <div class="w-12 h-12 rounded-full bg-emerald-500/20 text-emerald-400 flex items-center justify-center mx-auto shadow-lg shadow-emerald-500/10">
              <CheckCircleIcon class="w-7 h-7" />
            </div>
            <div class="space-y-1">
              <h3 class="text-base sm:text-lg font-extrabold text-white">Pembayaran Berhasil Dikonfirmasi!</h3>
              <p class="text-xs text-slate-400 max-w-md mx-auto leading-relaxed">
                Transaksi ini telah LUNAS. Hak akses membaca bab komik telah berhasil ditambahkan secara permanen ke akun Anda.
              </p>
            </div>
            <div class="pt-2">
              <Link
                href="/library"
                class="inline-flex items-center justify-center gap-2 px-6 py-3 rounded-xl bg-gradient-to-r from-emerald-600 to-teal-600 hover:from-emerald-500 hover:to-teal-500 text-xs sm:text-sm font-bold text-white transition shadow-xl shadow-emerald-600/30 active:scale-[0.98]"
              >
                <BookOpenIcon class="w-4 h-4 shrink-0" />
                <span>Buka Perpustakaan & Membaca</span>
                <ArrowRightIcon class="w-4 h-4 shrink-0" />
              </Link>
            </div>
          </div>
        </template>

        <!-- CASE 3: EXPIRED / FAILED / REFUND / CANCELLED DISPLAY BOX -->
        <template v-else>
          <div class="bg-slate-950/80 border border-rose-500/30 rounded-2xl p-6 text-center space-y-4">
            <div class="w-12 h-12 rounded-full bg-rose-500/20 text-rose-400 flex items-center justify-center mx-auto shadow-lg shadow-rose-500/10">
              <XCircleIcon v-if="payment.status.toLowerCase() === 'expired' || payment.status.toLowerCase() === 'failed'" class="w-7 h-7" />
              <ExclamationTriangleIcon v-else class="w-7 h-7 text-purple-400" />
            </div>
            <div class="space-y-1">
              <h3 class="text-base sm:text-lg font-extrabold text-white">
                {{ getStatusBadge(payment.status).label }}
              </h3>
              <p class="text-xs text-slate-400 max-w-md mx-auto leading-relaxed">
                <span v-if="payment.status.toLowerCase() === 'expired'">
                  Batas waktu pembayaran transaksi ini telah habis. Silakan lakukan pemesanan ulang untuk melanjutkan membaca bab komik ini.
                </span>
                <span v-else-if="payment.status.toLowerCase() === 'refund'">
                  Transaksi pembayaran ini telah dikembalikan (REFUND). Dana telah dikreditkan kembali sesuai prosedur TriPay Gateway.
                </span>
                <span v-else-if="payment.status.toLowerCase() === 'cancelled'">
                  Transaksi ini telah dibatalkan.
                </span>
                <span v-else>
                  Pembayaran tidak dapat diproses atau mengalami kegagalan. Silakan coba kembali atau gunakan metode pembayaran lain.
                </span>
              </p>
            </div>

            <!-- Action buttons for inactive status -->
            <div class="pt-2 flex flex-col sm:flex-row items-center justify-center gap-3">
              <Link
                href="/cart"
                class="w-full sm:w-auto inline-flex items-center justify-center gap-2 px-6 py-3 rounded-xl bg-gradient-to-r from-sky-600 to-indigo-600 hover:from-sky-500 hover:to-indigo-500 text-xs sm:text-sm font-bold text-white transition shadow-xl shadow-sky-600/30 active:scale-[0.98]"
              >
                <ShoppingBagIcon class="w-4 h-4 shrink-0" />
                <span>Buat Pesanan Baru</span>
              </Link>
              <Link
                href="/orders"
                class="w-full sm:w-auto inline-flex items-center justify-center gap-2 px-6 py-3 rounded-xl bg-slate-800 hover:bg-slate-700 text-xs sm:text-sm font-bold text-slate-300 transition active:scale-[0.98]"
              >
                <span>Lihat Riwayat Pesanan</span>
              </Link>
            </div>
          </div>
        </template>
      </div>

      <!-- Order Items Detail List -->
      <div v-if="payment.order && payment.order.items && payment.order.items.length" class="bg-slate-900/80 backdrop-blur-xl border border-slate-800/80 rounded-3xl p-6 shadow-xl space-y-4">
        <h2 class="text-xs font-bold text-sky-400 uppercase tracking-wider flex items-center gap-2">
          <DocumentTextIcon class="w-4 h-4" />
          Rincian Chapter Komik
        </h2>

        <div class="divide-y divide-slate-800/60">
          <div
            v-for="item in payment.order.items"
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
                <span class="text-[10px] text-slate-500 block mt-0.5">Akses Digital Selamanya</span>
              </div>
            </div>

            <div class="font-bold text-amber-400 text-xs sm:text-sm shrink-0">
              {{ formatRupiah(item.price) }}
            </div>
          </div>
        </div>

        <!-- Rincian Biaya Summary -->
        <div class="pt-4 border-t border-slate-800/80 space-y-2 text-xs">
          <div class="flex justify-between text-slate-400">
            <span>Subtotal Chapter:</span>
            <span class="text-white font-medium">{{ formatRupiah(subtotalAmount) }}</span>
          </div>
          <div v-if="feeAmount > 0" class="flex justify-between text-slate-400">
            <span>Biaya Layanan (TriPay Admin Fee):</span>
            <span class="text-white font-medium">+{{ formatRupiah(feeAmount) }}</span>
          </div>
          <div class="flex justify-between text-sm font-extrabold text-white pt-2 border-t border-slate-800/60">
            <span>Total Bayar (Yang Harus Ditransfer):</span>
            <span class="text-amber-400 text-base font-black">{{ formatRupiah(totalAmountWithFee) }}</span>
          </div>
        </div>
      </div>

      <!-- Step-by-Step Payment Instructions (ONLY FOR UNPAID / PENDING) -->
      <div
        v-if="isPendingPayment && payment.instructions && payment.instructions.length"
        class="bg-slate-900/80 backdrop-blur-xl border border-slate-800/80 rounded-3xl p-6 shadow-xl space-y-6"
      >
        <h2 class="text-sm font-bold text-sky-400 uppercase tracking-wider flex items-center gap-2 border-b border-slate-800/80 pb-3">
          <ShieldCheckIcon class="w-4 h-4" />
          Tata Cara Pembayaran ({{ payment.payment_name }})
        </h2>

        <div v-for="(inst, i) in payment.instructions" :key="i" class="space-y-3">
          <h3 class="text-xs sm:text-sm font-bold text-white flex items-center gap-2">
            <span class="w-5 h-5 rounded-full bg-sky-500/20 text-sky-400 text-[11px] flex items-center justify-center font-mono shrink-0">
              {{ i + 1 }}
            </span>
            {{ inst.title }}
          </h3>
          <ol class="list-decimal list-inside space-y-2 text-xs text-slate-300 leading-relaxed pl-7">
            <li v-for="(step, s) in inst.steps" :key="s" v-html="step"></li>
          </ol>
        </div>
      </div>

      <!-- Bottom Action Links -->
      <div class="flex items-center justify-between text-xs text-slate-400 pt-2">
        <Link href="/orders" class="hover:text-sky-400 transition flex items-center gap-1">
          ← Kembali ke Riwayat Pesanan
        </Link>
        <Link :href="`/orders/${payment.merchant_ref}`" class="text-sky-400 font-bold hover:underline">
          Lihat Invoice Lengkap →
        </Link>
      </div>

    </main>
  </PublicLayout>
</template>
