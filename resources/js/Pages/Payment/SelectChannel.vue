<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import { ref, computed } from 'vue';
import axios from 'axios';
import PublicLayout from '@/Layouts/PublicLayout.vue';
import {
  CreditCardIcon,
  ShieldCheckIcon,
  QrCodeIcon,
  BuildingLibraryIcon,
  ArrowRightIcon,
  CheckCircleIcon,
  SparklesIcon,
} from '@heroicons/vue/24/outline';
import { useToast } from '@/composables/useToast';

interface Channel {
  code: string;
  name: string;
  group: string;
  fee_flat?: number;
  fee_percent?: number;
  fee_merchant?: number;
  fee_customer?: number;
  total_fee?: number;
  icon_url?: string;
  disabled?: boolean;
  disabled_reason?: string;
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

const { error: toastError } = useToast();
const defaultChannel = props.channels?.find(c => !c.disabled) || props.channels?.[0];
const selectedCode = ref(defaultChannel?.code || '');
const isLoading = ref(false);

const selectChannel = (ch: Channel) => {
  if (ch.disabled) {
    toastError(ch.disabled_reason || `Metode ${ch.name} memerlukan minimal transaksi Rp 10.000.`);
    return;
  }
  selectedCode.value = ch.code;
};

const selectedChannel = computed(() => {
  return props.channels.find(c => c.code === selectedCode.value) || null;
});

const getChannelFee = (ch: any) => {
  if (!ch) return 0;
  const amount = props.order?.total_amount || 0;

  let flat = 0;
  let percent = 0;

  if (ch.fee_flat !== undefined) {
    flat = Number(ch.fee_flat) || 0;
  }
  if (ch.fee_percent !== undefined) {
    percent = Number(ch.fee_percent) || 0;
  }

  const feeObj = ch.total_fee ?? ch.fee_customer;
  if (typeof feeObj === 'object' && feeObj !== null) {
    flat = Number(feeObj.flat) || flat;
    percent = Number(feeObj.percent) || percent;
  } else if (typeof feeObj === 'number' && flat === 0 && percent === 0) {
    return feeObj;
  }

  return Math.ceil(flat + (amount * percent / 100));
};

const getChannelPercent = (ch: any) => {
  if (!ch) return 0;
  if (ch.fee_percent !== undefined && Number(ch.fee_percent) > 0) {
    return Number(ch.fee_percent);
  }
  const feeObj = ch.total_fee ?? ch.fee_customer;
  if (typeof feeObj === 'object' && feeObj !== null && Number(feeObj.percent) > 0) {
    return Number(feeObj.percent);
  }
  return 0;
};

const grandTotal = computed(() => {
  const fee = getChannelFee(selectedChannel.value);
  return (props.order?.total_amount || 0) + fee;
});

const formatRupiah = (val: any) => {
  let num = 0;
  if (typeof val === 'number') {
    num = val;
  } else if (typeof val === 'object' && val !== null) {
    num = parseFloat(val.flat) || parseFloat(val.total) || 0;
  } else {
    num = parseFloat(val) || 0;
  }
  return 'Rp ' + Math.round(num).toLocaleString('id-ID');
};

// Group channels logically
const groupedChannels = computed(() => {
  const groups: Record<string, Channel[]> = {};
  props.channels.forEach(ch => {
    const grpName = ch.group || 'Other Payment Methods';
    if (!groups[grpName]) {
      groups[grpName] = [];
    }
    groups[grpName].push(ch);
  });
  return groups;
});

const processPayment = async () => {
  if (!selectedCode.value) {
    toastError('Silakan pilih metode pembayaran terlebih dahulu.');
    return;
  }
  isLoading.value = true;

  try {
    const res = await axios.post('/api/payment/process', {
      order_number: props.order.order_number,
      payment_method: selectedCode.value,
    });

    if (res.data && res.data.redirect_url) {
      window.location.href = res.data.redirect_url;
    }
  } catch (err: any) {
    const msg = err.response?.data?.message || 'Gagal memproses transaksi pembayaran. Silakan coba lagi.';
    toastError(msg);
  } finally {
    isLoading.value = false;
  }
};
</script>

<template>
  <Head title="Select Payment Channel - TriPay Gateway" />

  <PublicLayout minimal title="Pilih Metode Pembayaran" backUrl="/checkout">
    <main class="max-w-4xl mx-auto px-4 lg:px-8 py-10 w-full flex-1 space-y-8">
      <!-- Top Order Summary Banner -->
      <div class="bg-slate-900/80 backdrop-blur-xl border border-slate-800/80 rounded-3xl p-6 shadow-2xl flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div>
          <span class="inline-flex items-center gap-1.5 px-3 py-1 text-xs font-bold rounded-full bg-sky-500/10 text-sky-400 border border-sky-500/30">
            <ShieldCheckIcon class="w-3.5 h-3.5" />
            TriPay Payment Gateway
          </span>
          <h1 class="text-xl sm:text-2xl font-extrabold text-white mt-2">Pilih Metode Pembayaran</h1>
          <p class="text-xs text-slate-400 mt-1 font-mono">Invoice: #{{ order.order_number }}</p>
        </div>

        <div class="text-left md:text-right border-t md:border-t-0 border-slate-800/80 pt-3 md:pt-0">
          <span class="text-xs text-slate-400 block">Total Tagihan (Termasuk Admin Fee):</span>
          <span class="text-2xl font-black text-amber-400">{{ formatRupiah(grandTotal) }}</span>
        </div>
      </div>

      <!-- Payment Channels by Group -->
      <div v-if="channels && channels.length" class="space-y-6">
        <div v-for="(groupList, groupName) in groupedChannels" :key="groupName" class="space-y-3">
          <h3 class="text-xs font-bold text-sky-400 uppercase tracking-wider flex items-center gap-2">
            <BuildingLibraryIcon v-if="groupName.toLowerCase().includes('virtual')" class="w-4 h-4 text-sky-400" />
            <QrCodeIcon v-else-if="groupName.toLowerCase().includes('qris') || groupName.toLowerCase().includes('wallet')" class="w-4 h-4 text-sky-400" />
            <CreditCardIcon v-else class="w-4 h-4 text-sky-400" />
            {{ groupName }}
          </h3>

          <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
            <div
              v-for="ch in groupList"
              :key="ch.code"
              @click="selectChannel(ch)"
              class="relative bg-slate-900/90 border rounded-2xl p-4 flex items-center justify-between cursor-pointer transition select-none group"
              :class="[
                ch.disabled
                  ? 'opacity-40 cursor-not-allowed bg-slate-950/60 border-slate-800/40'
                  : (selectedCode === ch.code
                      ? 'border-sky-500 bg-sky-500/10 shadow-xl shadow-sky-500/10 ring-1 ring-sky-500/50'
                      : 'border-slate-800/80 hover:border-slate-700/80 hover:bg-slate-800/40')
              ]"
            >
              <div class="flex items-center gap-3.5 min-w-0">
                <!-- Custom Check Radio -->
                <div class="w-5 h-5 rounded-full border border-slate-700 flex items-center justify-center shrink-0 bg-slate-950"
                  :class="selectedCode === ch.code ? 'border-sky-500 bg-sky-600' : ''"
                >
                  <CheckCircleIcon v-if="selectedCode === ch.code" class="w-4 h-4 text-white" />
                </div>

                <!-- Channel Icon if available -->
                <img
                  v-if="ch.icon_url"
                  :src="ch.icon_url"
                  :alt="ch.name"
                  class="w-10 h-7 object-contain bg-slate-950 border border-slate-800 rounded-lg p-1 shrink-0"
                />

                <div class="min-w-0">
                  <h4 class="font-bold text-white text-xs sm:text-sm truncate group-hover:text-sky-300 transition">{{ ch.name }}</h4>
                  <span v-if="ch.disabled" class="text-[10px] text-rose-400 font-bold block truncate">
                    {{ ch.disabled_reason || 'Minimal Rp 10.000' }}
                  </span>
                  <span v-else-if="getChannelPercent(ch) > 0" class="text-[10px] text-amber-400 font-medium block truncate">
                    Fee {{ getChannelPercent(ch) }}% (Persentase)
                  </span>
                  <span v-else class="text-[10px] text-slate-500 block truncate">Biaya Tetap (Flat Fee)</span>
                </div>
              </div>

              <!-- Fee Pill -->
              <div class="text-right shrink-0">
                <span class="text-[11px] font-semibold text-slate-400 bg-slate-950 border border-slate-800 px-2 py-1 rounded-lg block">
                  +{{ formatRupiah(getChannelFee(ch)) }}
                </span>
                <span v-if="getChannelPercent(ch) > 0" class="text-[10px] text-amber-400/90 font-medium block mt-0.5 text-right">
                  ({{ getChannelPercent(ch) }}%)
                </span>
              </div>

              <input
                type="radio"
                name="payment_channel"
                :value="ch.code"
                v-model="selectedCode"
                class="sr-only"
              />
            </div>
          </div>
        </div>

        <!-- Submit Process Payment Button -->
        <div class="pt-4 space-y-3">
          <button
            @click="processPayment"
            :disabled="!selectedCode || isLoading"
            class="w-full py-4 px-6 rounded-2xl text-xs sm:text-sm font-bold text-white bg-gradient-to-r from-sky-600 to-indigo-600 hover:from-sky-500 hover:to-indigo-500 focus:outline-none disabled:opacity-50 transition shadow-xl shadow-sky-600/30 active:scale-[0.98] flex items-center justify-center gap-2"
          >
            <CreditCardIcon class="w-5 h-5 shrink-0" />
            <span v-if="isLoading">Generating Payment Code...</span>
            <template v-else>
              <!-- Mobile Label -->
              <span class="sm:hidden">Pay {{ formatRupiah(grandTotal) }}</span>
              <!-- Desktop Label -->
              <span class="hidden sm:inline">Generate Pay Code / QRIS ({{ formatRupiah(grandTotal) }})</span>
              <ArrowRightIcon class="w-4 h-4 shrink-0" />
            </template>
          </button>

          <p class="text-center text-[11px] text-slate-500 flex items-center justify-center gap-1">
            <ShieldCheckIcon class="w-3.5 h-3.5 text-emerald-400" />
            Licensed & Encrypted via TriPay Payment Gateway
          </p>
        </div>
      </div>

      <!-- Empty Channels State -->
      <div v-else class="bg-slate-900/60 border border-slate-800 rounded-3xl p-16 text-center space-y-4">
        <div class="w-16 h-16 bg-slate-950 border border-slate-800 rounded-2xl flex items-center justify-center mx-auto">
          <CreditCardIcon class="w-8 h-8 text-slate-500" />
        </div>
        <h2 class="text-xl font-bold text-white">No Payment Channels Available</h2>
        <p class="text-sm text-slate-400 max-w-md mx-auto">
          Unable to fetch TriPay payment channels at the moment. Please try again later.
        </p>
      </div>
    </main>
  </PublicLayout>
</template>
