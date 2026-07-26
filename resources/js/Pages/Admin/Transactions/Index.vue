<script setup lang="ts">
import { ref, computed, watch } from 'vue';
import { Head, useForm, usePage, router } from '@inertiajs/vue3';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import {
  ExclamationTriangleIcon,
  CheckCircleIcon,
  FunnelIcon,
  ArrowPathIcon,
  BanknotesIcon,
  CreditCardIcon,
  ArrowDownLeftIcon,
  ArrowUpRightIcon,
  BuildingOffice2Icon,
  InformationCircleIcon,
} from '@heroicons/vue/24/outline';

interface Payment {
  id: number;
  tripay_reference: string;
  merchant_ref: string;
  payment_name: string;
  amount: number;
  status: string;
  created_at: string;
  user?: { name: string; email: string };
}

interface Withdrawal {
  id: number;
  amount: number;
  bank_name: string;
  bank_account_number: string;
  bank_account_name: string;
  status: string;
  created_at: string;
  publisher?: { brand_name: string };
  wallet?: { balance: number; total_earned: number; total_withdrawn: number };
}

interface PublisherOption {
  id: number;
  brand_name: string;
}

interface Paginator<T> {
  data: T[];
  links: any[];
}

const props = defineProps<{
  payments: Paginator<Payment>;
  withdrawals: Paginator<Withdrawal>;
  publishers: PublisherOption[];
  filters: {
    publisher_id: number | null;
    status: string | null;
    tab: string;
  };
}>();

const page = usePage();
const flashError = computed(() => (page.props as any).flash?.error);
const flashSuccess = computed(() => (page.props as any).flash?.success);

// Active Tab ('payouts' or 'payments')
const activeTab = ref(props.filters.tab || 'payouts');

// Filter state
const selectedPublisher = ref<number | string>(props.filters.publisher_id || '');
const selectedStatus = ref<string>(props.filters.status || '');

const applyFilters = () => {
  router.get(
    '/admin/transactions',
    {
      tab: activeTab.value,
      publisher_id: selectedPublisher.value || undefined,
      status: selectedStatus.value || undefined,
    },
    {
      preserveState: true,
      preserveScroll: true,
    }
  );
};

const switchTab = (tab: string) => {
  activeTab.value = tab;
  applyFilters();
};

const resetFilters = () => {
  selectedPublisher.value = '';
  selectedStatus.value = '';
  router.get('/admin/transactions', { tab: activeTab.value });
};

const approveWdForm = useForm({});
const rejectWdForm = useForm({
  reason: '',
});

const approveWithdrawal = (id: number) => {
  if (confirm('Konfirmasi: Apakah Anda sudah melakukan transfer manual ke rekening bank publisher? Klik OK untuk menyetujui & memotong saldo dompet publisher.')) {
    approveWdForm.post(`/admin/withdrawals/${id}/approve`);
  }
};

const rejectWithdrawal = (id: number) => {
  const reason = prompt('Masukkan alasan penolakan penarikan dana:');
  if (reason) {
    rejectWdForm.reason = reason;
    rejectWdForm.post(`/admin/withdrawals/${id}/reject`);
  }
};
</script>

<template>
  <Head title="TriPay Transactions & Publisher Payouts - Admin Control" />

  <AdminLayout>
    <div class="space-y-8">
      <!-- Header -->
      <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
          <span class="text-xs text-amber-400 font-bold uppercase tracking-wider">Super Admin Finance</span>
          <h1 class="text-3xl font-extrabold text-white">Transaksi & Payout Management</h1>
          <p class="text-sm text-slate-400 mt-1">
            Monitoring pemasukan TriPay dan kelola pengajuan payout penarikan dana studio publisher
          </p>
        </div>
      </div>

      <!-- Info Banner about Manual Payout vs TriPay Disbursement API -->
      <div class="p-4 bg-sky-500/10 border border-sky-500/30 rounded-2xl text-slate-300 text-xs space-y-1.5 shadow-lg">
        <div class="flex items-center gap-2 font-bold text-sky-400">
          <InformationCircleIcon class="w-4 h-4 shrink-0 text-sky-400" />
          Informasi Sistem Payout Publisher (Transfer Manual & API Disbursement):
        </div>
        <p class="text-slate-300 leading-relaxed">
          Saat ini proses payout dilakukan secara <strong>transfer manual ke rekening bank publisher</strong>. Apabila di masa mendatang Payment Gateway TriPay menyediakan fitur API Disbursement / Payout otomatis, maka skema penarikan dana ini dapat disesuaikan untuk berjalan secara otomatis dari sistem.
        </p>
      </div>

      <!-- Flash Notifications -->
      <div v-if="flashError" class="p-4 bg-rose-500/10 border border-rose-500/30 rounded-2xl text-rose-400 text-sm font-medium flex items-center gap-3 shadow-lg">
        <ExclamationTriangleIcon class="w-5 h-5 shrink-0" />
        {{ flashError }}
      </div>
      <div v-if="flashSuccess" class="p-4 bg-emerald-500/10 border border-emerald-500/30 rounded-2xl text-emerald-400 text-sm font-medium flex items-center gap-3 shadow-lg">
        <CheckCircleIcon class="w-5 h-5 shrink-0" />
        {{ flashSuccess }}
      </div>

      <!-- Navigation Tabs (Dana Masuk vs Payout Publisher) -->
      <div class="flex flex-wrap items-center justify-between gap-4 border-b border-slate-800 pb-4">
        <div class="flex items-center gap-3">
          <!-- Tab 1: Payout Publisher (Dana Keluar) -->
          <button
            @click="switchTab('payouts')"
            class="flex items-center gap-2 px-5 py-3 rounded-xl text-sm font-bold transition-all"
            :class="activeTab === 'payouts'
              ? 'bg-sky-600 text-white shadow-lg shadow-sky-600/30'
              : 'bg-slate-900 border border-slate-800 text-slate-400 hover:text-white hover:bg-slate-800/80'"
          >
            <ArrowUpRightIcon class="w-4 h-4 text-rose-400" />
            Payout Publisher (Dana Keluar)
            <span
              v-if="withdrawals.data && withdrawals.data.length"
              class="ml-1 px-2 py-0.5 text-xs rounded-full bg-slate-950/80 text-sky-300 font-extrabold"
            >
              {{ withdrawals.data.length }}
            </span>
          </button>

          <!-- Tab 2: Dana Masuk (TriPay Payments) -->
          <button
            @click="switchTab('payments')"
            class="flex items-center gap-2 px-5 py-3 rounded-xl text-sm font-bold transition-all"
            :class="activeTab === 'payments'
              ? 'bg-sky-600 text-white shadow-lg shadow-sky-600/30'
              : 'bg-slate-900 border border-slate-800 text-slate-400 hover:text-white hover:bg-slate-800/80'"
          >
            <ArrowDownLeftIcon class="w-4 h-4 text-emerald-400" />
            Dana Masuk (TriPay Payments)
            <span
              v-if="payments.data && payments.data.length"
              class="ml-1 px-2 py-0.5 text-xs rounded-full bg-slate-950/80 text-emerald-300 font-extrabold"
            >
              {{ payments.data.length }}
            </span>
          </button>
        </div>
      </div>

      <!-- Filter Controls Bar -->
      <div class="bg-slate-900 border border-slate-800 rounded-2xl p-5 shadow-xl space-y-4">
        <div class="flex items-center justify-between">
          <span class="text-xs font-bold uppercase text-slate-400 flex items-center gap-2">
            <FunnelIcon class="w-4 h-4 text-sky-400" />
            Filter Data Transaksi
          </span>
          <button
            v-if="selectedPublisher || selectedStatus"
            @click="resetFilters"
            class="text-xs text-sky-400 hover:text-sky-300 font-semibold flex items-center gap-1 transition"
          >
            <ArrowPathIcon class="w-3.5 h-3.5" /> Reset Filter
          </button>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-4">
          <!-- Filter Publisher Dropdown -->
          <div class="space-y-1">
            <label class="block text-xs font-semibold text-slate-400">Filter Publisher / Studio</label>
            <div class="relative">
              <select
                v-model="selectedPublisher"
                @change="applyFilters"
                class="w-full bg-slate-950 border border-slate-800 rounded-xl px-4 py-2.5 text-xs text-white focus:outline-none focus:ring-1 focus:ring-sky-500 font-semibold"
              >
                <option value="">Semua Publisher / Studio</option>
                <option v-for="pub in publishers" :key="pub.id" :value="pub.id">
                  {{ pub.brand_name }}
                </option>
              </select>
            </div>
          </div>

          <!-- Filter Status (Only for Payout Tab) -->
          <div v-if="activeTab === 'payouts'" class="space-y-1">
            <label class="block text-xs font-semibold text-slate-400">Status Penarikan (Payout)</label>
            <select
              v-model="selectedStatus"
              @change="applyFilters"
              class="w-full bg-slate-950 border border-slate-800 rounded-xl px-4 py-2.5 text-xs text-white focus:outline-none focus:ring-1 focus:ring-sky-500 font-semibold"
            >
              <option value="">Semua Status</option>
              <option value="pending">Pending (Perlu Ditransfer)</option>
              <option value="approved">Approved (Selesai Ditransfer)</option>
              <option value="rejected">Rejected (Ditolak)</option>
            </select>
          </div>
        </div>
      </div>

      <!-- TAB 1: PAYOUT PUBLISHER (DANA KELUAR) -->
      <div v-if="activeTab === 'payouts'" class="space-y-4">
        <div class="flex items-center justify-between">
          <h2 class="text-xl font-bold text-white flex items-center gap-2">
            <BanknotesIcon class="w-6 h-6 text-sky-400" />
            Daftar Pengajuan Payout Studio Publisher
          </h2>
        </div>

        <div v-if="withdrawals.data && withdrawals.data.length" class="bg-slate-900 border border-slate-800 rounded-2xl overflow-hidden shadow-2xl">
          <div class="overflow-x-auto">
            <table class="w-full text-left text-sm text-slate-300">
              <thead class="text-xs uppercase bg-slate-950 text-slate-400 border-b border-slate-800">
                <tr>
                  <th class="px-6 py-4">Publisher Studio</th>
                  <th class="px-6 py-4">Saldo Dompet Aktif</th>
                  <th class="px-6 py-4">Tujuan Transfer Bank</th>
                  <th class="px-6 py-4">Jumlah Payout (WD)</th>
                  <th class="px-6 py-4">Status</th>
                  <th class="px-6 py-4 text-right">Aksi Transfer Manual</th>
                </tr>
              </thead>
              <tbody class="divide-y divide-slate-800/60">
                <tr v-for="wd in withdrawals.data" :key="wd.id" class="hover:bg-slate-800/30 transition">
                  <td class="px-6 py-4 font-bold text-white">
                    <div class="flex items-center gap-2">
                      <BuildingOffice2Icon class="w-4 h-4 text-slate-500 shrink-0" />
                      {{ wd.publisher ? wd.publisher.brand_name : 'Studio' }}
                    </div>
                  </td>
                  <td class="px-6 py-4 text-xs font-bold text-emerald-400">
                    Rp {{ wd.wallet?.balance !== undefined ? wd.wallet.balance.toLocaleString() : '0' }}
                  </td>
                  <td class="px-6 py-4 text-xs">
                    <span class="font-bold text-sky-400 uppercase tracking-wide">{{ wd.bank_name }}</span>
                    <p class="font-mono text-white text-xs mt-0.5">{{ wd.bank_account_number }}</p>
                    <p class="text-[11px] text-slate-400">a.n {{ wd.bank_account_name }}</p>
                  </td>
                  <td class="px-6 py-4 font-extrabold text-amber-400">
                    Rp {{ wd.amount ? wd.amount.toLocaleString() : '0' }}
                  </td>
                  <td class="px-6 py-4">
                    <span
                      class="px-2.5 py-1 text-[11px] font-extrabold rounded-lg uppercase tracking-wider"
                      :class="[
                        wd.status === 'approved'
                          ? 'bg-emerald-500/10 text-emerald-400 border border-emerald-500/30'
                          : (wd.status === 'rejected' ? 'bg-rose-500/10 text-rose-400 border border-rose-500/30' : 'bg-amber-500/10 text-amber-400 border border-amber-500/30 animate-pulse')
                      ]"
                    >
                      {{ wd.status }}
                    </span>
                  </td>
                  <td class="px-6 py-4 text-right space-x-2">
                    <template v-if="wd.status === 'pending'">
                      <button
                        @click="approveWithdrawal(wd.id)"
                        class="px-4 py-2 rounded-xl text-xs font-bold text-white bg-sky-600 hover:bg-sky-500 transition shadow-md shadow-sky-600/30"
                      >
                        Approve & Deduct Saldo ✓
                      </button>
                      <button
                        @click="rejectWithdrawal(wd.id)"
                        class="px-3 py-2 rounded-xl text-xs font-semibold text-rose-400 bg-rose-500/10 border border-rose-500/30 hover:bg-rose-500/20 transition"
                      >
                        Reject
                      </button>
                    </template>
                    <span v-else-if="wd.status === 'approved'" class="text-xs text-emerald-400 font-bold">Processed (Saldo Dipotong)</span>
                    <span v-else class="text-xs text-rose-400 font-semibold">Ditolak</span>
                  </td>
                </tr>
              </tbody>
            </table>
          </div>
        </div>

        <div v-else class="bg-slate-900/40 border border-slate-800 rounded-2xl p-12 text-center text-xs text-slate-500 space-y-2">
          <BanknotesIcon class="w-8 h-8 text-slate-600 mx-auto" />
          <p class="font-medium text-slate-400">Tidak ada pengajuan payout penarikan dana publisher.</p>
        </div>
      </div>

      <!-- TAB 2: DANA MASUK (TRIPAY PAYMENTS) -->
      <div v-if="activeTab === 'payments'" class="space-y-4">
        <div class="flex items-center justify-between">
          <h2 class="text-xl font-bold text-white flex items-center gap-2">
            <CreditCardIcon class="w-6 h-6 text-emerald-400" />
            Riwayat Pembelian & Dana Masuk (TriPay Payment Gateway)
          </h2>
        </div>

        <div v-if="payments.data && payments.data.length" class="bg-slate-900 border border-slate-800 rounded-2xl overflow-hidden shadow-2xl">
          <div class="overflow-x-auto">
            <table class="w-full text-left text-sm text-slate-300">
              <thead class="text-xs uppercase bg-slate-950 text-slate-400 border-b border-slate-800">
                <tr>
                  <th class="px-6 py-4">TriPay Reference</th>
                  <th class="px-6 py-4">Invoice (Merchant Ref)</th>
                  <th class="px-6 py-4">Pembeli (Customer)</th>
                  <th class="px-6 py-4">Metode Pembayaran</th>
                  <th class="px-6 py-4">Status</th>
                  <th class="px-6 py-4 text-right">Total Masuk</th>
                </tr>
              </thead>
              <tbody class="divide-y divide-slate-800/60">
                <tr v-for="p in payments.data" :key="p.id" class="hover:bg-slate-800/30 transition">
                  <td class="px-6 py-4 font-mono text-xs text-sky-400 font-bold">{{ p.tripay_reference }}</td>
                  <td class="px-6 py-4 font-mono text-xs text-white">{{ p.merchant_ref }}</td>
                  <td class="px-6 py-4 text-xs">
                    <p class="font-bold text-slate-200">{{ p.user ? p.user.name : 'Customer' }}</p>
                    <p class="text-[11px] text-slate-400">{{ p.user ? p.user.email : '' }}</p>
                  </td>
                  <td class="px-6 py-4 text-xs text-slate-300 font-medium">{{ p.payment_name }}</td>
                  <td class="px-6 py-4">
                    <span
                      class="px-2.5 py-1 text-[11px] font-extrabold rounded-lg uppercase tracking-wider"
                      :class="p.status === 'PAID' ? 'bg-emerald-500/10 text-emerald-400 border border-emerald-500/30' : 'bg-amber-500/10 text-amber-400 border border-amber-500/30'"
                    >
                      {{ p.status }}
                    </span>
                  </td>
                  <td class="px-6 py-4 font-extrabold text-right text-sm text-emerald-400">
                    + Rp {{ p.amount ? p.amount.toLocaleString() : '0' }}
                  </td>
                </tr>
              </tbody>
            </table>
          </div>
        </div>

        <div v-else class="bg-slate-900/40 border border-slate-800 rounded-2xl p-12 text-center text-xs text-slate-500 space-y-2">
          <CreditCardIcon class="w-8 h-8 text-slate-600 mx-auto" />
          <p class="font-medium text-slate-400">Tidak ada riwayat pembayaran TriPay ditemukan.</p>
        </div>
      </div>
    </div>
  </AdminLayout>
</template>
