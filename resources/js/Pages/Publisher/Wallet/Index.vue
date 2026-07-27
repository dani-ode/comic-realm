<script setup lang="ts">
import { ref } from 'vue';
import { Head, useForm, Link, router } from '@inertiajs/vue3';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import {
  BanknotesIcon,
  ArrowUpRightIcon,
  ShoppingBagIcon,
  ClockIcon,
  CheckCircleIcon,
  XCircleIcon,
  ExclamationTriangleIcon,
  BuildingLibraryIcon,
  ArrowPathIcon,
  DocumentTextIcon,
  ShoppingCartIcon,
  ReceiptPercentIcon,
} from '@heroicons/vue/24/outline';

interface User {
  id: number;
  name: string;
  username: string;
  email: string;
}

interface Order {
  id: number;
  order_number: string;
  user_id: number;
  status: string;
  completed_at?: string;
  created_at: string;
  user?: User;
}

interface Comic {
  id: number;
  title: string;
  cover_image: string;
  slug: string;
}

interface Chapter {
  id: number;
  chapter_number: number;
  title: string;
  comic?: Comic;
}

interface Cart {
  id: number;
  user_id: number;
  user?: User;
}

interface CartItem {
  id: number;
  cart_id: number;
  chapter_id: number;
  price: number;
  created_at: string;
  cart?: Cart;
  chapter?: Chapter;
}

interface PurchaseItem {
  id: number;
  order_id: number;
  comic_id: number;
  chapter_id: number;
  title_snapshot: string;
  chapter_number_snapshot: number;
  price: number;
  created_at: string;
  order?: Order;
  comic?: Comic;
  chapter?: Chapter;
}

interface PaginatedPurchases {
  data: PurchaseItem[];
  current_page: number;
  last_page: number;
  per_page: number;
  total: number;
  links: Array<{ url: string | null; label: string; active: boolean }>;
}

interface Withdrawal {
  id: number;
  amount: number;
  bank_name: string;
  bank_account_number: string;
  bank_account_name: string;
  status: 'pending' | 'processing' | 'approved' | 'rejected' | string;
  rejection_reason?: string;
  processed_at?: string;
  created_at: string;
}

interface Transaction {
  id: number;
  type: 'credit' | 'debit' | string;
  amount: number;
  balance_after: number;
  description: string;
  created_at: string;
}

interface Wallet {
  id: number;
  balance: number;
  total_earned: number;
  total_withdrawn: number;
  pending_withdrawal_amount?: number;
  available_balance?: number;
  transactions?: Transaction[];
  withdrawals?: Withdrawal[];
}

interface Publisher {
  id: number;
  user_id: number;
  brand_name: string;
  bank_name?: string;
  bank_account_number?: string;
  bank_account_name?: string;
}

const props = defineProps<{
  publisher: Publisher;
  wallet: Wallet;
  purchases: PaginatedPurchases;
  cartItems: CartItem[];
  filters?: { status?: string };
}>();

const activeTab = ref<'purchases' | 'cart' | 'withdrawals' | 'transactions'>('purchases');
const selectedStatus = ref<string>(props.filters?.status || 'all');

const filterStatus = (status: string) => {
  selectedStatus.value = status;
  router.get(
    '/publisher/wallet',
    { status: status === 'all' ? undefined : status },
    { preserveState: true, preserveScroll: true }
  );
};

const form = useForm({
  amount: 50000,
});

const submitWithdrawal = () => {
  form.post('/publisher/wallet/withdraw', {
    onSuccess: () => form.reset(),
  });
};

const formatDate = (dateStr?: string) => {
  if (!dateStr) return '-';
  return new Date(dateStr).toLocaleString('id-ID', {
    dateStyle: 'medium',
    timeStyle: 'short',
  });
};

const formatCurrency = (val?: number) => {
  return (val || 0).toLocaleString('id-ID');
};
</script>

<template>
  <Head title="Dompet & Pendapatan Publisher" />

  <AdminLayout>
    <div class="space-y-8 pb-12">
      <!-- Header Page -->
      <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 border-b border-slate-800/80 pb-5">
        <div>
          <div class="flex items-center gap-2 text-xs font-bold text-sky-400 uppercase tracking-wider">
            <ReceiptPercentIcon class="w-4 h-4" />
            <span>Ekonomi Kreator & Payout</span>
          </div>
          <h1 class="text-3xl font-extrabold text-white mt-1">Dompet Royalti & Penjualan</h1>
          <p class="text-sm text-slate-400 mt-1">
            Pantau bagi hasil royalti 70%, rincian pembelian bab komik, item keranjang pembaca, serta riwayat penarikan dana (WD).
          </p>
        </div>

        <Link
          href="/publisher/profile"
          class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl bg-slate-900 border border-slate-800 text-xs font-semibold text-slate-300 hover:text-white hover:border-slate-700 transition shrink-0"
        >
          <BuildingLibraryIcon class="w-4 h-4 text-sky-400" />
          <span>Pengaturan Rekening Bank</span>
        </Link>
      </div>

      <!-- Financial Metrics Cards -->
      <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-5">
        <!-- Available Balance Card -->
        <div class="relative overflow-hidden bg-gradient-to-br from-slate-900 via-slate-900 to-emerald-950/40 border border-slate-800 rounded-2xl p-5 shadow-xl">
          <div class="flex items-center justify-between">
            <span class="text-xs font-bold text-slate-400 uppercase tracking-wider">Saldo Siap Ditarik</span>
            <span class="p-2 rounded-xl bg-emerald-500/10 text-emerald-400 border border-emerald-500/20">
              <BanknotesIcon class="w-5 h-5" />
            </span>
          </div>
          <div class="text-3xl font-extrabold text-emerald-400 mt-3">
            Rp {{ formatCurrency(wallet.available_balance ?? wallet.balance) }}
          </div>
          <div class="flex items-center gap-1.5 text-xs text-emerald-400/90 mt-2 font-medium">
            <span class="w-2 h-2 rounded-full bg-emerald-400 animate-pulse"></span>
            <span v-if="wallet.pending_withdrawal_amount && wallet.pending_withdrawal_amount > 0" class="text-amber-400 text-[11px]">
              Saldo Dompet: Rp {{ formatCurrency(wallet.balance) }} (Pending WD: Rp {{ formatCurrency(wallet.pending_withdrawal_amount) }})
            </span>
            <span v-else>Siap untuk ditarik ke rekening</span>
          </div>
        </div>

        <!-- Total Revenue Earned Card -->
        <div class="bg-slate-900 border border-slate-800 rounded-2xl p-5 shadow-lg">
          <div class="flex items-center justify-between">
            <span class="text-xs font-bold text-slate-400 uppercase tracking-wider">Total Pendapatan</span>
            <span class="p-2 rounded-xl bg-sky-500/10 text-sky-400 border border-sky-500/20">
              <ReceiptPercentIcon class="w-5 h-5" />
            </span>
          </div>
          <div class="text-3xl font-extrabold text-white mt-3">
            Rp {{ formatCurrency(wallet.total_earned) }}
          </div>
          <p class="text-xs text-slate-500 mt-2">Akumulasi royalti 70% publisher</p>
        </div>

        <!-- Total Withdrawn Card -->
        <div class="bg-slate-900 border border-slate-800 rounded-2xl p-5 shadow-lg">
          <div class="flex items-center justify-between">
            <span class="text-xs font-bold text-slate-400 uppercase tracking-wider">Total Dana Ditarik</span>
            <span class="p-2 rounded-xl bg-indigo-500/10 text-indigo-400 border border-indigo-500/20">
              <ArrowUpRightIcon class="w-5 h-5" />
            </span>
          </div>
          <div class="text-3xl font-extrabold text-sky-400 mt-3">
            Rp {{ formatCurrency(wallet.total_withdrawn) }}
          </div>
          <p class="text-xs text-slate-500 mt-2">Telah ditransfer ke rekening bank</p>
        </div>

        <!-- Total Purchases Count Card -->
        <div class="bg-slate-900 border border-slate-800 rounded-2xl p-5 shadow-lg">
          <div class="flex items-center justify-between">
            <span class="text-xs font-bold text-slate-400 uppercase tracking-wider">Transaksi Pembelian</span>
            <span class="p-2 rounded-xl bg-amber-500/10 text-amber-400 border border-amber-500/20">
              <ShoppingBagIcon class="w-5 h-5" />
            </span>
          </div>
          <div class="text-3xl font-extrabold text-amber-400 mt-3">
            {{ purchases.total || 0 }} <span class="text-sm font-normal text-slate-400">item</span>
          </div>
          <p class="text-xs text-slate-500 mt-2">Item bab komik dalam pesanan</p>
        </div>
      </div>

      <!-- Withdrawal Form Section -->
      <div class="bg-slate-900 border border-slate-800 rounded-2xl p-6 shadow-xl space-y-5">
        <div class="flex items-center justify-between border-b border-slate-800 pb-3">
          <h2 class="text-lg font-bold text-white flex items-center gap-2">
            <BanknotesIcon class="w-5 h-5 text-sky-400" />
            <span>Pengajuan Penarikan Dana Payout (WD)</span>
          </h2>
          <span class="text-xs text-slate-400">Min. Penarikan: <strong class="text-white">Rp 50.000</strong></span>
        </div>

        <!-- Warning Alert if Bank Info is Missing -->
        <div
          v-if="!publisher.bank_name || !publisher.bank_account_number"
          class="flex items-start gap-3 p-4 rounded-xl bg-amber-500/10 border border-amber-500/30 text-amber-300 text-xs"
        >
          <ExclamationTriangleIcon class="w-5 h-5 text-amber-400 shrink-0 mt-0.5" />
          <div class="flex-1">
            <strong class="font-bold block text-sm mb-0.5">Informasi Rekening Bank Belum Lengkap</strong>
            <p>Anda perlu mengisi data nama bank, nomor rekening, dan atas nama rekening di profil publisher sebelum dapat mengajukan penarikan dana.</p>
            <Link href="/publisher/profile" class="inline-block font-bold underline mt-1.5 hover:text-white">
              Isi Rekening Bank di Profil &rarr;
            </Link>
          </div>
        </div>

        <!-- Withdrawal Form -->
        <form @submit.prevent="submitWithdrawal" class="grid grid-cols-1 lg:grid-cols-3 gap-6 items-end">
          <div class="space-y-1.5">
            <div class="flex justify-between items-center text-xs">
              <label class="font-semibold text-slate-300">Jumlah Penarikan (Rp)</label>
              <span class="text-[11px] text-emerald-400 font-medium">
                Siap Ditarik: Rp {{ formatCurrency(wallet.available_balance ?? wallet.balance) }}
              </span>
            </div>
            <input
              v-model="form.amount"
              type="number"
              min="50000"
              :max="wallet.available_balance ?? wallet.balance"
              step="10000"
              required
              class="w-full rounded-xl bg-slate-950 border border-slate-800 px-4 py-2.5 text-white font-bold text-sm focus:border-sky-500 focus:outline-none transition"
              placeholder="Contoh: 50000"
            />
            <p v-if="form.errors.amount" class="text-xs text-rose-400 font-medium mt-1">{{ form.errors.amount }}</p>
            <p v-else-if="wallet.pending_withdrawal_amount && wallet.pending_withdrawal_amount > 0" class="text-[11px] text-amber-400 mt-1">
              *Rp {{ formatCurrency(wallet.pending_withdrawal_amount) }} sedang dalam proses pengajuan (Pending)
            </p>
          </div>

          <!-- Destination Bank Info Snapshot -->
          <div class="p-3.5 rounded-xl bg-slate-950 border border-slate-800 text-xs space-y-1">
            <span class="text-slate-500 font-semibold block uppercase tracking-wider text-[10px]">Tujuan Transfer Bank:</span>
            <div v-if="publisher.bank_name && publisher.bank_account_number" class="text-slate-200 font-medium">
              <span class="font-bold text-sky-400">{{ publisher.bank_name }}</span> - {{ publisher.bank_account_number }}
              <span class="text-slate-400 block text-[11px]">a.n {{ publisher.bank_account_name || publisher.brand_name }}</span>
            </div>
            <div v-else class="text-amber-400 italic">
              Belum diatur (Silakan update profil)
            </div>
          </div>

          <!-- Submit Button -->
          <button
            type="submit"
            :disabled="form.processing || (wallet.available_balance ?? wallet.balance) < 50000 || !publisher.bank_name || !publisher.bank_account_number"
            class="px-6 py-3 rounded-xl text-sm font-bold text-white bg-gradient-to-r from-sky-600 to-indigo-600 hover:from-sky-500 hover:to-indigo-500 disabled:opacity-50 disabled:cursor-not-allowed transition shadow-lg shadow-sky-600/25 flex items-center justify-center gap-2"
          >
            <span>Kirim Pengajuan Penarikan</span>
            <ArrowUpRightIcon class="w-4 h-4" />
          </button>
        </form>
      </div>

      <!-- Tabbed Navigation Bar for Tables -->
      <div class="space-y-4">
        <div class="flex items-center gap-2 border-b border-slate-800 overflow-x-auto pb-1">
          <button
            @click="activeTab = 'purchases'"
            class="px-4 py-2.5 rounded-xl text-xs font-bold transition flex items-center gap-2 shrink-0"
            :class="
              activeTab === 'purchases'
                ? 'bg-sky-500/10 text-sky-400 border border-sky-500/30'
                : 'text-slate-400 hover:text-white hover:bg-slate-900'
            "
          >
            <ShoppingBagIcon class="w-4 h-4" />
            <span>Riwayat Transaksi Penjualan</span>
            <span class="px-2 py-0.5 rounded-full bg-slate-800 text-[10px] font-extrabold text-slate-300">
              {{ purchases.total || 0 }}
            </span>
          </button>

          <button
            @click="activeTab = 'cart'"
            class="px-4 py-2.5 rounded-xl text-xs font-bold transition flex items-center gap-2 shrink-0"
            :class="
              activeTab === 'cart'
                ? 'bg-indigo-500/10 text-indigo-400 border border-indigo-500/30'
                : 'text-slate-400 hover:text-white hover:bg-slate-900'
            "
          >
            <ShoppingCartIcon class="w-4 h-4" />
            <span>Keranjang Pembaca</span>
            <span class="px-2 py-0.5 rounded-full bg-indigo-500/20 text-[10px] font-extrabold text-indigo-300 border border-indigo-500/30">
              {{ cartItems.length || 0 }}
            </span>
          </button>

          <button
            @click="activeTab = 'withdrawals'"
            class="px-4 py-2.5 rounded-xl text-xs font-bold transition flex items-center gap-2 shrink-0"
            :class="
              activeTab === 'withdrawals'
                ? 'bg-sky-500/10 text-sky-400 border border-sky-500/30'
                : 'text-slate-400 hover:text-white hover:bg-slate-900'
            "
          >
            <ArrowUpRightIcon class="w-4 h-4" />
            <span>Riwayat Penarikan Dana (WD)</span>
            <span class="px-2 py-0.5 rounded-full bg-slate-800 text-[10px] font-extrabold text-slate-300">
              {{ wallet.withdrawals?.length || 0 }}
            </span>
          </button>

          <button
            @click="activeTab = 'transactions'"
            class="px-4 py-2.5 rounded-xl text-xs font-bold transition flex items-center gap-2 shrink-0"
            :class="
              activeTab === 'transactions'
                ? 'bg-sky-500/10 text-sky-400 border border-sky-500/30'
                : 'text-slate-400 hover:text-white hover:bg-slate-900'
            "
          >
            <DocumentTextIcon class="w-4 h-4" />
            <span>Ledger Mutasi Dompet</span>
            <span class="px-2 py-0.5 rounded-full bg-slate-800 text-[10px] font-extrabold text-slate-300">
              {{ wallet.transactions?.length || 0 }}
            </span>
          </button>
        </div>

        <!-- TAB 1: List Penjualan Bab Komik & Transaksi -->
        <div v-if="activeTab === 'purchases'" class="bg-slate-900 border border-slate-800 rounded-2xl p-6 shadow-xl space-y-4">
          <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 border-b border-slate-800 pb-3">
            <h3 class="text-base font-bold text-white flex items-center gap-2">
              <ShoppingBagIcon class="w-5 h-5 text-sky-400" />
              <span>Daftar Transaksi Pembelian Bab Komik (Urutan Terbaru)</span>
            </h3>

            <!-- Status Filter Pills -->
            <div class="flex items-center gap-1.5 flex-wrap">
              <button
                @click="filterStatus('all')"
                class="px-3 py-1 rounded-lg text-xs font-semibold transition"
                :class="selectedStatus === 'all' ? 'bg-sky-600 text-white' : 'bg-slate-800 text-slate-400 hover:text-white'"
              >
                Semua
              </button>
              <button
                @click="filterStatus('completed')"
                class="px-3 py-1 rounded-lg text-xs font-semibold transition"
                :class="selectedStatus === 'completed' ? 'bg-emerald-600 text-white' : 'bg-slate-800 text-slate-400 hover:text-white'"
              >
                🟢 Lunas / Selesai
              </button>
              <button
                @click="filterStatus('pending')"
                class="px-3 py-1 rounded-lg text-xs font-semibold transition"
                :class="selectedStatus === 'pending' ? 'bg-amber-600 text-white' : 'bg-slate-800 text-slate-400 hover:text-white'"
              >
                🟡 Menunggu Bayar
              </button>
              <button
                @click="filterStatus('expired')"
                class="px-3 py-1 rounded-lg text-xs font-semibold transition"
                :class="selectedStatus === 'expired' || selectedStatus === 'failed' ? 'bg-rose-600 text-white' : 'bg-slate-800 text-slate-400 hover:text-white'"
              >
                🔴 Gagal / Expired
              </button>
            </div>
          </div>

          <div v-if="purchases.data && purchases.data.length" class="overflow-x-auto">
            <table class="w-full text-left text-sm text-slate-300">
              <thead class="text-xs uppercase bg-slate-950 text-slate-400 border-b border-slate-800">
                <tr>
                  <th class="px-4 py-3.5">Waktu Transaksi</th>
                  <th class="px-4 py-3.5">No. Invoice</th>
                  <th class="px-4 py-3.5">Pembeli</th>
                  <th class="px-4 py-3.5">Komik & Bab</th>
                  <th class="px-4 py-3.5 text-right">Harga Beli</th>
                  <th class="px-4 py-3.5 text-right text-emerald-400">Royalti (70%)</th>
                  <th class="px-4 py-3.5 text-center">Status Pembayaran</th>
                </tr>
              </thead>
              <tbody class="divide-y divide-slate-800/60">
                <tr v-for="item in purchases.data" :key="item.id" class="hover:bg-slate-800/30 transition">
                  <td class="px-4 py-3.5 text-xs text-slate-400 whitespace-nowrap">
                    {{ formatDate(item.order?.completed_at || item.order?.created_at || item.created_at) }}
                  </td>
                  <td class="px-4 py-3.5 font-mono text-xs text-sky-400 font-bold whitespace-nowrap">
                    {{ item.order?.order_number || `#INV-${item.order_id}` }}
                  </td>
                  <td class="px-4 py-3.5 text-xs whitespace-nowrap">
                    <div class="flex items-center gap-2">
                      <div class="w-6 h-6 rounded-full bg-slate-800 border border-slate-700 flex items-center justify-center text-[10px] font-bold text-slate-300 shrink-0">
                        {{ item.order?.user?.name ? item.order.user.name.charAt(0).toUpperCase() : 'U' }}
                      </div>
                      <div>
                        <div class="font-bold text-white">{{ item.order?.user?.name || 'Reader' }}</div>
                        <div class="text-[10px] text-slate-400">@{{ item.order?.user?.username || 'user' }}</div>
                      </div>
                    </div>
                  </td>
                  <td class="px-4 py-3.5 text-xs">
                    <div class="font-bold text-white">
                      {{ item.comic?.title || 'Komik' }}
                    </div>
                    <div class="text-slate-400 text-[11px]">
                      {{ item.title_snapshot || `Bab ${item.chapter_number_snapshot}` }}
                    </div>
                  </td>
                  <td class="px-4 py-3.5 text-right font-semibold text-slate-300 text-xs whitespace-nowrap">
                    Rp {{ formatCurrency(item.price) }}
                  </td>
                  <td class="px-4 py-3.5 text-right font-extrabold text-emerald-400 text-xs whitespace-nowrap">
                    <span v-if="item.order?.status === 'completed'">
                      + Rp {{ formatCurrency(Math.round(item.price * 0.7)) }}
                    </span>
                    <span v-else class="text-slate-500 font-normal italic text-[11px]">
                      Pending (Rp {{ formatCurrency(Math.round(item.price * 0.7)) }})
                    </span>
                  </td>
                  <td class="px-4 py-3.5 text-center whitespace-nowrap">
                    <span
                      v-if="item.order?.status === 'completed'"
                      class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-[10px] font-bold bg-emerald-500/10 text-emerald-400 border border-emerald-500/30"
                    >
                      <CheckCircleIcon class="w-3.5 h-3.5" />
                      <span>Lunas (Royalti Masuk)</span>
                    </span>
                    <span
                      v-else-if="item.order?.status === 'pending'"
                      class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-[10px] font-bold bg-amber-500/10 text-amber-400 border border-amber-500/30"
                    >
                      <ClockIcon class="w-3.5 h-3.5" />
                      <span>Menunggu Pembayaran</span>
                    </span>
                    <span
                      v-else
                      class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-[10px] font-bold bg-rose-500/10 text-rose-400 border border-rose-500/30"
                    >
                      <XCircleIcon class="w-3.5 h-3.5" />
                      <span>Gagal / Kadaluarsa</span>
                    </span>
                  </td>
                </tr>
              </tbody>
            </table>

            <!-- Pagination Links -->
            <div v-if="purchases.links && purchases.links.length > 3" class="flex items-center justify-between border-t border-slate-800 pt-4 mt-4">
              <div class="text-xs text-slate-400">
                Menampilkan <strong class="text-white">{{ purchases.data.length }}</strong> dari <strong class="text-white">{{ purchases.total }}</strong> item
              </div>
              <div class="flex items-center gap-1">
                <template v-for="(link, i) in purchases.links" :key="i">
                  <Link
                    v-if="link.url"
                    :href="link.url"
                    v-html="link.label"
                    class="px-3 py-1.5 rounded-lg text-xs font-semibold transition"
                    :class="link.active ? 'bg-sky-600 text-white font-bold' : 'bg-slate-800 text-slate-300 hover:bg-slate-700'"
                  />
                  <span
                    v-else
                    v-html="link.label"
                    class="px-3 py-1.5 rounded-lg text-xs text-slate-600 bg-slate-950 cursor-not-allowed"
                  />
                </template>
              </div>
            </div>
          </div>

          <div v-else class="py-12 text-center space-y-3">
            <ShoppingBagIcon class="w-12 h-12 text-slate-600 mx-auto" />
            <div class="text-sm font-semibold text-slate-400">Belum ada transaksi pembelian bab komik dengan filter ini.</div>
          </div>
        </div>

        <!-- TAB 2: Keranjang Pembaca (Calon Pembeli) -->
        <div v-if="activeTab === 'cart'" class="bg-slate-900 border border-slate-800 rounded-2xl p-6 shadow-xl space-y-4">
          <div class="flex items-center justify-between border-b border-slate-800 pb-3">
            <h3 class="text-base font-bold text-white flex items-center gap-2">
              <ShoppingCartIcon class="w-5 h-5 text-indigo-400" />
              <span>Bab Komik yang Sedang Ada di Keranjang Pembaca</span>
            </h3>
            <span class="text-xs text-slate-400">Total: <strong class="text-indigo-400 font-bold">{{ cartItems.length }} item</strong></span>
          </div>

          <div v-if="cartItems && cartItems.length" class="overflow-x-auto">
            <table class="w-full text-left text-sm text-slate-300">
              <thead class="text-xs uppercase bg-slate-950 text-slate-400 border-b border-slate-800">
                <tr>
                  <th class="px-4 py-3.5">Waktu Dimasukkan</th>
                  <th class="px-4 py-3.5">Calon Pembeli</th>
                  <th class="px-4 py-3.5">Komik & Bab</th>
                  <th class="px-4 py-3.5 text-right">Harga Bab</th>
                  <th class="px-4 py-3.5 text-right text-indigo-400">Potensi Royalti (70%)</th>
                  <th class="px-4 py-3.5 text-center">Status</th>
                </tr>
              </thead>
              <tbody class="divide-y divide-slate-800/60">
                <tr v-for="cItem in cartItems" :key="cItem.id" class="hover:bg-slate-800/30 transition">
                  <td class="px-4 py-3.5 text-xs text-slate-400 whitespace-nowrap">
                    {{ formatDate(cItem.created_at) }}
                  </td>
                  <td class="px-4 py-3.5 text-xs whitespace-nowrap">
                    <div class="flex items-center gap-2">
                      <div class="w-6 h-6 rounded-full bg-slate-800 border border-slate-700 flex items-center justify-center text-[10px] font-bold text-slate-300 shrink-0">
                        {{ cItem.cart?.user?.name ? cItem.cart.user.name.charAt(0).toUpperCase() : 'U' }}
                      </div>
                      <div>
                        <div class="font-bold text-white">{{ cItem.cart?.user?.name || 'Reader' }}</div>
                        <div class="text-[10px] text-slate-400">@{{ cItem.cart?.user?.username || 'user' }}</div>
                      </div>
                    </div>
                  </td>
                  <td class="px-4 py-3.5 text-xs">
                    <div class="font-bold text-white">
                      {{ cItem.chapter?.comic?.title || 'Komik' }}
                    </div>
                    <div class="text-slate-400 text-[11px]">
                      Bab {{ cItem.chapter?.chapter_number }}: {{ cItem.chapter?.title }}
                    </div>
                  </td>
                  <td class="px-4 py-3.5 text-right font-semibold text-slate-300 text-xs whitespace-nowrap">
                    Rp {{ formatCurrency(cItem.price) }}
                  </td>
                  <td class="px-4 py-3.5 text-right font-extrabold text-indigo-400 text-xs whitespace-nowrap">
                    Rp {{ formatCurrency(Math.round(cItem.price * 0.7)) }}
                  </td>
                  <td class="px-4 py-3.5 text-center whitespace-nowrap">
                    <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-[10px] font-bold bg-indigo-500/10 text-indigo-400 border border-indigo-500/30">
                      <ShoppingCartIcon class="w-3.5 h-3.5" />
                      <span>Di Keranjang Pembaca</span>
                    </span>
                  </td>
                </tr>
              </tbody>
            </table>
          </div>

          <div v-else class="py-12 text-center space-y-3">
            <ShoppingCartIcon class="w-12 h-12 text-slate-600 mx-auto" />
            <div class="text-sm font-semibold text-slate-400">Saat ini tidak ada bab komik Anda di keranjang pembaca.</div>
          </div>
        </div>

        <!-- TAB 3: Riwayat Penarikan Dana (WD History) -->
        <div v-if="activeTab === 'withdrawals'" class="bg-slate-900 border border-slate-800 rounded-2xl p-6 shadow-xl space-y-4">
          <div class="flex items-center justify-between border-b border-slate-800 pb-3">
            <h3 class="text-base font-bold text-white flex items-center gap-2">
              <ArrowUpRightIcon class="w-5 h-5 text-sky-400" />
              <span>Riwayat Pengajuan Penarikan Payout (WD) - Urutan Terbaru</span>
            </h3>
          </div>

          <div v-if="wallet.withdrawals && wallet.withdrawals.length" class="overflow-x-auto">
            <table class="w-full text-left text-sm text-slate-300">
              <thead class="text-xs uppercase bg-slate-950 text-slate-400 border-b border-slate-800">
                <tr>
                  <th class="px-4 py-3.5">ID Pengajuan</th>
                  <th class="px-4 py-3.5">Tanggal Pengajuan</th>
                  <th class="px-4 py-3.5">Rekening Tujuan</th>
                  <th class="px-4 py-3.5 text-right">Jumlah WD</th>
                  <th class="px-4 py-3.5 text-center">Status Payout</th>
                  <th class="px-4 py-3.5">Diproses Pada</th>
                </tr>
              </thead>
              <tbody class="divide-y divide-slate-800/60">
                <tr v-for="wd in wallet.withdrawals" :key="wd.id" class="hover:bg-slate-800/30 transition">
                  <td class="px-4 py-3.5 font-mono text-xs font-bold text-sky-400 whitespace-nowrap">
                    #WD-{{ wd.id }}
                  </td>
                  <td class="px-4 py-3.5 text-xs text-slate-400 whitespace-nowrap">
                    {{ formatDate(wd.created_at) }}
                  </td>
                  <td class="px-4 py-3.5 text-xs">
                    <div class="font-bold text-white">{{ wd.bank_name }} - {{ wd.bank_account_number }}</div>
                    <div class="text-[11px] text-slate-400">a.n {{ wd.bank_account_name }}</div>
                  </td>
                  <td class="px-4 py-3.5 text-right font-extrabold text-white text-xs whitespace-nowrap">
                    Rp {{ formatCurrency(wd.amount) }}
                  </td>
                  <td class="px-4 py-3.5 text-center whitespace-nowrap">
                    <span
                      v-if="wd.status === 'pending'"
                      class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-[10px] font-bold bg-amber-500/10 text-amber-400 border border-amber-500/30"
                    >
                      <ClockIcon class="w-3.5 h-3.5" />
                      <span>Menunggu Approve Admin</span>
                    </span>
                    <span
                      v-else-if="wd.status === 'processing'"
                      class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-[10px] font-bold bg-sky-500/10 text-sky-400 border border-sky-500/30"
                    >
                      <ArrowPathIcon class="w-3.5 h-3.5 animate-spin" />
                      <span>Diproses</span>
                    </span>
                    <span
                      v-else-if="wd.status === 'approved'"
                      class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-[10px] font-bold bg-emerald-500/10 text-emerald-400 border border-emerald-500/30"
                    >
                      <CheckCircleIcon class="w-3.5 h-3.5" />
                      <span>Berhasil Ditransfer</span>
                    </span>
                    <span
                      v-else-if="wd.status === 'rejected'"
                      class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-[10px] font-bold bg-rose-500/10 text-rose-400 border border-rose-500/30"
                      :title="wd.rejection_reason"
                    >
                      <XCircleIcon class="w-3.5 h-3.5" />
                      <span>Ditolak</span>
                    </span>
                  </td>
                  <td class="px-4 py-3.5 text-xs text-slate-400 whitespace-nowrap">
                    <span v-if="wd.processed_at">{{ formatDate(wd.processed_at) }}</span>
                    <span v-else-if="wd.status === 'rejected'" class="text-rose-400 text-[11px] block max-w-xs truncate" :title="wd.rejection_reason">
                      Alasan: {{ wd.rejection_reason || 'Ditolak' }}
                    </span>
                    <span v-else class="text-slate-600 italic">Belum diproses</span>
                  </td>
                </tr>
              </tbody>
            </table>
          </div>

          <div v-else class="py-12 text-center space-y-3">
            <ArrowUpRightIcon class="w-12 h-12 text-slate-600 mx-auto" />
            <div class="text-sm font-semibold text-slate-400">Belum ada riwayat penarikan dana (WD).</div>
          </div>
        </div>

        <!-- TAB 4: Ledger Mutasi Dompet (Wallet Transaction Ledger) -->
        <div v-if="activeTab === 'transactions'" class="bg-slate-900 border border-slate-800 rounded-2xl p-6 shadow-xl space-y-4">
          <div class="flex items-center justify-between border-b border-slate-800 pb-3">
            <h3 class="text-base font-bold text-white flex items-center gap-2">
              <DocumentTextIcon class="w-5 h-5 text-sky-400" />
              <span>Ledger Mutasi Dompet (Urutan Terbaru)</span>
            </h3>
          </div>

          <div v-if="wallet.transactions && wallet.transactions.length" class="overflow-x-auto">
            <table class="w-full text-left text-sm text-slate-300">
              <thead class="text-xs uppercase bg-slate-950 text-slate-400 border-b border-slate-800">
                <tr>
                  <th class="px-4 py-3.5">Tanggal</th>
                  <th class="px-4 py-3.5">Tipe</th>
                  <th class="px-4 py-3.5">Keterangan</th>
                  <th class="px-4 py-3.5 text-right">Jumlah</th>
                  <th class="px-4 py-3.5 text-right">Saldo Akhir</th>
                </tr>
              </thead>
              <tbody class="divide-y divide-slate-800/60">
                <tr v-for="tx in wallet.transactions" :key="tx.id" class="hover:bg-slate-800/30 transition">
                  <td class="px-4 py-3.5 text-xs text-slate-400 whitespace-nowrap">
                    {{ formatDate(tx.created_at) }}
                  </td>
                  <td class="px-4 py-3.5 whitespace-nowrap">
                    <span
                      class="px-2.5 py-1 text-[10px] font-extrabold rounded-md uppercase tracking-wider"
                      :class="
                        tx.type === 'credit'
                          ? 'bg-emerald-500/10 text-emerald-400 border border-emerald-500/30'
                          : 'bg-rose-500/10 text-rose-400 border border-rose-500/30'
                      "
                    >
                      {{ tx.type }}
                    </span>
                  </td>
                  <td class="px-4 py-3.5 text-white text-xs">
                    {{ tx.description }}
                  </td>
                  <td
                    class="px-4 py-3.5 font-extrabold text-right text-xs whitespace-nowrap"
                    :class="tx.type === 'credit' ? 'text-emerald-400' : 'text-rose-400'"
                  >
                    {{ tx.type === 'credit' ? '+' : '-' }} Rp {{ formatCurrency(tx.amount) }}
                  </td>
                  <td class="px-4 py-3.5 text-right font-bold text-slate-300 text-xs whitespace-nowrap">
                    Rp {{ formatCurrency(tx.balance_after) }}
                  </td>
                </tr>
              </tbody>
            </table>
          </div>

          <div v-else class="py-12 text-center space-y-3">
            <DocumentTextIcon class="w-12 h-12 text-slate-600 mx-auto" />
            <div class="text-sm font-semibold text-slate-400">Belum ada riwayat mutasi dompet.</div>
          </div>
        </div>
      </div>
    </div>
  </AdminLayout>
</template>
