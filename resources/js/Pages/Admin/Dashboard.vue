<script setup lang="ts">
import { ref } from 'vue';
import { Head, Link, router, useForm } from '@inertiajs/vue3';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import {
  BuildingOffice2Icon,
  UsersIcon,
  CreditCardIcon,
  ArrowRightIcon,
  BanknotesIcon,
  ShoppingBagIcon,
  BookOpenIcon,
  DocumentTextIcon,
  ExclamationTriangleIcon,
  CheckCircleIcon,
  XCircleIcon,
  ReceiptPercentIcon,
  BuildingLibraryIcon,
  ArrowUpRightIcon,
} from '@heroicons/vue/24/outline';

interface Metrics {
  total_gmv: number;
  total_publisher_royalty: number;
  total_platform_revenue: number;
  pending_withdrawals_count: number;
  pending_withdrawals_amount: number;
  approved_payouts_amount: number;
  pending_publishers_count: number;
  approved_publishers_count: number;
  total_orders: number;
  completed_orders_count: number;
  total_comics: number;
  total_chapters: number;
  total_users: number;
}

interface Publisher {
  id: number;
  brand_name: string;
  bank_name?: string;
  bank_account_number?: string;
  bank_account_name?: string;
}

interface PendingWithdrawal {
  id: number;
  publisher_id: number;
  amount: number;
  bank_name: string;
  bank_account_number: string;
  bank_account_name: string;
  status: string;
  created_at: string;
  publisher?: Publisher;
}

interface OrderItem {
  id: number;
  title_snapshot: string;
  price: number;
  comic?: { title: string; slug: string };
  chapter?: { chapter_number: number; title: string };
}

interface RecentOrder {
  id: number;
  order_number: string;
  total_amount: number;
  status: string;
  completed_at?: string;
  created_at: string;
  user?: { name: string; email: string };
  items?: OrderItem[];
}

const props = defineProps<{
  metrics: Metrics;
  recentPendingWithdrawals: PendingWithdrawal[];
  recentOrders: RecentOrder[];
}>();

const form = useForm({});
const processingWdId = ref<number | null>(null);

const approveWithdrawal = (id: number, brandName: string, amount: number) => {
  if (confirm(`Apakah Anda yakin telah melakukan transfer manual Rp ${amount.toLocaleString('id-ID')} ke rekening ${brandName}? Saldo dompet publisher akan dipotong.`)) {
    processingWdId.value = id;
    form.post(`/admin/withdrawals/${id}/approve`, {
      onFinish: () => {
        processingWdId.value = null;
      },
    });
  }
};

const formatCurrency = (val?: number) => {
  return (val || 0).toLocaleString('id-ID');
};

const formatDate = (dateStr?: string) => {
  if (!dateStr) return '-';
  return new Date(dateStr).toLocaleString('id-ID', {
    dateStyle: 'medium',
    timeStyle: 'short',
  });
};
</script>

<template>
  <Head title="Super Admin Dashboard - Financial Overview" />

  <AdminLayout>
    <div class="space-y-8 pb-12">
      <!-- Header -->
      <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 border-b border-slate-800/80 pb-5">
        <div>
          <span class="text-xs text-amber-400 font-bold uppercase tracking-wider">Super Admin Control Center</span>
          <h1 class="text-3xl font-extrabold text-white mt-1">Metrik & Keuangan Platform</h1>
          <p class="text-sm text-slate-400 mt-1">
            Ringkasan pendapatan GMV, pembagian royalti 70% vs 30%, pengajuan payout (WD), dan status operasional.
          </p>
        </div>

        <div class="flex items-center gap-3">
          <Link
            href="/admin/transactions"
            class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl bg-slate-900 border border-slate-800 text-xs font-semibold text-slate-300 hover:text-white hover:border-slate-700 transition shrink-0"
          >
            <CreditCardIcon class="w-4 h-4 text-sky-400" />
            <span>Kelola Payout & Transaksi</span>
          </Link>
        </div>
      </div>

      <!-- Action Alerts -->
      <div class="space-y-3">
        <!-- Pending Payouts Alert -->
        <div
          v-if="metrics.pending_withdrawals_count > 0"
          class="flex items-start sm:items-center justify-between gap-4 p-4 rounded-2xl bg-gradient-to-r from-amber-950/60 via-slate-900 to-slate-900 border border-amber-500/30 text-amber-300 shadow-xl"
        >
          <div class="flex items-center gap-3">
            <div class="p-2.5 rounded-xl bg-amber-500/20 text-amber-400 shrink-0">
              <ExclamationTriangleIcon class="w-6 h-6 animate-pulse" />
            </div>
            <div>
              <strong class="font-bold block text-sm text-white">
                {{ metrics.pending_withdrawals_count }} Pengajuan Penarikan Payout Menunggu Persetujuan
              </strong>
              <p class="text-xs text-amber-200/90 mt-0.5">
                Total dana pending: <strong class="text-white">Rp {{ formatCurrency(metrics.pending_withdrawals_amount) }}</strong>. Silakan lakukan transfer manual lalu setujui pengajuan.
              </p>
            </div>
          </div>

          <Link
            href="/admin/transactions?tab=payouts&status=pending"
            class="inline-flex items-center gap-1.5 px-4 py-2 rounded-xl text-xs font-bold text-slate-950 bg-amber-400 hover:bg-amber-300 transition shrink-0 shadow-lg shadow-amber-400/20"
          >
            <span>Proses Payout &rarr;</span>
          </Link>
        </div>

        <!-- Pending Publisher Approval Alert -->
        <div
          v-if="metrics.pending_publishers_count > 0"
          class="flex items-start sm:items-center justify-between gap-4 p-4 rounded-2xl bg-gradient-to-r from-sky-950/60 via-slate-900 to-slate-900 border border-sky-500/30 text-sky-300 shadow-xl"
        >
          <div class="flex items-center gap-3">
            <div class="p-2.5 rounded-xl bg-sky-500/20 text-sky-400 shrink-0">
              <BuildingOffice2Icon class="w-6 h-6" />
            </div>
            <div>
              <strong class="font-bold block text-sm text-white">
                {{ metrics.pending_publishers_count }} Pendaftaran Studio Publisher Menunggu Review
              </strong>
              <p class="text-xs text-sky-200/90 mt-0.5">
                Studio baru memerlukan persetujuan Admin agar dapat mulai menerbitkan komik & bab.
              </p>
            </div>
          </div>

          <Link
            href="/admin/publishers"
            class="inline-flex items-center gap-1.5 px-4 py-2 rounded-xl text-xs font-bold text-white bg-sky-600 hover:bg-sky-500 transition shrink-0 shadow-lg shadow-sky-600/20"
          >
            <span>Review Studio &rarr;</span>
          </Link>
        </div>
      </div>

      <!-- Financial Metric Cards (Top Grid) -->
      <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-6 gap-5">
        <!-- 1. Total GMV -->
        <div class="bg-gradient-to-br from-slate-900 via-slate-900 to-emerald-950/40 border border-slate-800 rounded-2xl p-5 space-y-3 shadow-xl">
          <div class="flex items-center justify-between">
            <span class="text-[11px] text-slate-400 font-bold uppercase tracking-wider">Total Sales GMV</span>
            <span class="p-2 rounded-xl bg-emerald-500/10 text-emerald-400 border border-emerald-500/20">
              <BanknotesIcon class="w-4 h-4" />
            </span>
          </div>
          <div class="text-2xl font-extrabold text-emerald-400">
            Rp {{ formatCurrency(metrics.total_gmv) }}
          </div>
          <p class="text-[11px] text-slate-500">Volume Penjualan Lunas</p>
        </div>

        <!-- 2. Platform Revenue 30% -->
        <div class="bg-slate-900 border border-slate-800 rounded-2xl p-5 space-y-3 shadow-lg">
          <div class="flex items-center justify-between">
            <span class="text-[11px] text-slate-400 font-bold uppercase tracking-wider">Keuntungan Platform (30%)</span>
            <span class="p-2 rounded-xl bg-indigo-500/10 text-indigo-400 border border-indigo-500/20">
              <ReceiptPercentIcon class="w-4 h-4" />
            </span>
          </div>
          <div class="text-2xl font-extrabold text-indigo-400">
            Rp {{ formatCurrency(metrics.total_platform_revenue) }}
          </div>
          <p class="text-[11px] text-slate-500">Profit Bersih Platform</p>
        </div>

        <!-- 3. Publisher Royalty 70% -->
        <div class="bg-slate-900 border border-slate-800 rounded-2xl p-5 space-y-3 shadow-lg">
          <div class="flex items-center justify-between">
            <span class="text-[11px] text-slate-400 font-bold uppercase tracking-wider">Royalti Publisher (70%)</span>
            <span class="p-2 rounded-xl bg-sky-500/10 text-sky-400 border border-sky-500/20">
              <BuildingLibraryIcon class="w-4 h-4" />
            </span>
          </div>
          <div class="text-2xl font-extrabold text-sky-400">
            Rp {{ formatCurrency(metrics.total_publisher_royalty) }}
          </div>
          <p class="text-[11px] text-slate-500">Akumulasi Hak Kreator</p>
        </div>

        <!-- 4. Pending Payout WD -->
        <div class="bg-slate-900 border border-slate-800 rounded-2xl p-5 space-y-3 shadow-lg">
          <div class="flex items-center justify-between">
            <span class="text-[11px] text-slate-400 font-bold uppercase tracking-wider">Pending Payout WD</span>
            <span class="p-2 rounded-xl bg-amber-500/10 text-amber-400 border border-amber-500/20">
              <ClockIcon class="w-4 h-4" />
            </span>
          </div>
          <div class="text-2xl font-extrabold text-amber-400">
            Rp {{ formatCurrency(metrics.pending_withdrawals_amount) }}
          </div>
          <p class="text-[11px] text-slate-500">{{ metrics.pending_withdrawals_count }} Pengajuan Menunggu</p>
        </div>

        <!-- 5. Approved Payouts Transferred -->
        <div class="bg-slate-900 border border-slate-800 rounded-2xl p-5 space-y-3 shadow-lg">
          <div class="flex items-center justify-between">
            <span class="text-[11px] text-slate-400 font-bold uppercase tracking-wider">Payout Ditransfer</span>
            <span class="p-2 rounded-xl bg-emerald-500/10 text-emerald-400 border border-emerald-500/20">
              <ArrowUpRightIcon class="w-4 h-4" />
            </span>
          </div>
          <div class="text-2xl font-extrabold text-white">
            Rp {{ formatCurrency(metrics.approved_payouts_amount) }}
          </div>
          <p class="text-[11px] text-slate-500">Telah Sukses Ditransfer</p>
        </div>

        <!-- 6. Studio Publisher Stats -->
        <div class="bg-slate-900 border border-slate-800 rounded-2xl p-5 space-y-3 shadow-lg">
          <div class="flex items-center justify-between">
            <span class="text-[11px] text-slate-400 font-bold uppercase tracking-wider">Studio Publisher</span>
            <span class="p-2 rounded-xl bg-purple-500/10 text-purple-400 border border-purple-500/20">
              <BuildingOffice2Icon class="w-4 h-4" />
            </span>
          </div>
          <div class="text-2xl font-extrabold text-purple-400">
            {{ metrics.approved_publishers_count }} <span class="text-xs font-normal text-slate-400">Studio</span>
          </div>
          <p class="text-[11px] text-slate-500">{{ metrics.pending_publishers_count }} Menunggu Review</p>
        </div>
      </div>

      <!-- Quick Nav Shortcuts -->
      <div class="flex flex-wrap items-center gap-3">
        <Link
          href="/admin/publishers"
          class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl text-xs font-bold bg-slate-900 border border-slate-800 text-slate-200 hover:text-amber-400 hover:border-amber-500/50 transition"
        >
          <BuildingOffice2Icon class="w-4 h-4 text-sky-400" />
          <span>Kelola Studio Publisher</span>
          <span
            v-if="metrics.pending_publishers_count > 0"
            class="px-2 py-0.5 rounded-full bg-sky-500/20 text-sky-300 text-[10px] font-extrabold border border-sky-500/30"
          >
            {{ metrics.pending_publishers_count }} Pending
          </span>
          <ArrowRightIcon class="w-3.5 h-3.5" />
        </Link>

        <Link
          href="/admin/transactions?tab=payouts"
          class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl text-xs font-bold bg-slate-900 border border-slate-800 text-slate-200 hover:text-amber-400 hover:border-amber-500/50 transition"
        >
          <CreditCardIcon class="w-4 h-4 text-amber-400" />
          <span>Kelola Penarikan Payout (WD)</span>
          <span
            v-if="metrics.pending_withdrawals_count > 0"
            class="px-2 py-0.5 rounded-full bg-amber-500/20 text-amber-300 text-[10px] font-extrabold border border-amber-500/30"
          >
            {{ metrics.pending_withdrawals_count }} Pending
          </span>
          <ArrowRightIcon class="w-3.5 h-3.5" />
        </Link>

        <Link
          href="/admin/users"
          class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl text-xs font-bold bg-slate-900 border border-slate-800 text-slate-200 hover:text-amber-400 hover:border-amber-500/50 transition"
        >
          <UsersIcon class="w-4 h-4 text-purple-400" />
          <span>Kelola {{ metrics.total_users }} Akun User</span>
          <ArrowRightIcon class="w-3.5 h-3.5" />
        </Link>

        <Link
          href="/admin/comics"
          class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl text-xs font-bold bg-slate-900 border border-slate-800 text-slate-200 hover:text-amber-400 hover:border-amber-500/50 transition"
        >
          <BookOpenIcon class="w-4 h-4 text-emerald-400" />
          <span>Katalog {{ metrics.total_comics }} Komik ({{ metrics.total_chapters }} Bab)</span>
          <ArrowRightIcon class="w-3.5 h-3.5" />
        </Link>
      </div>

      <!-- Action Section 1: Pending Withdrawal Requests -->
      <div v-if="recentPendingWithdrawals && recentPendingWithdrawals.length" class="bg-slate-900 border border-slate-800 rounded-2xl p-6 shadow-xl space-y-4">
        <div class="flex items-center justify-between border-b border-slate-800 pb-3">
          <h2 class="text-base font-bold text-white flex items-center gap-2">
            <ClockIcon class="w-5 h-5 text-amber-400" />
            <span>Pengajuan Penarikan Payout Menunggu Persetujuan Admin</span>
          </h2>
          <Link href="/admin/transactions?tab=payouts&status=pending" class="text-xs font-bold text-sky-400 hover:underline">
            Lihat Semua Payout &rarr;
          </Link>
        </div>

        <div class="overflow-x-auto">
          <table class="w-full text-left text-sm text-slate-300">
            <thead class="text-xs uppercase bg-slate-950 text-slate-400 border-b border-slate-800">
              <tr>
                <th class="px-4 py-3">Tanggal</th>
                <th class="px-4 py-3">Studio Publisher</th>
                <th class="px-4 py-3">Tujuan Transfer Bank</th>
                <th class="px-4 py-3 text-right">Jumlah Penarikan</th>
                <th class="px-4 py-3 text-center">Aksi Admin</th>
              </tr>
            </thead>
            <tbody class="divide-y divide-slate-800/60">
              <tr v-for="wd in recentPendingWithdrawals" :key="wd.id" class="hover:bg-slate-800/30 transition">
                <td class="px-4 py-3.5 text-xs text-slate-400 whitespace-nowrap">
                  {{ formatDate(wd.created_at) }}
                </td>
                <td class="px-4 py-3.5 text-xs whitespace-nowrap">
                  <div class="font-bold text-white">{{ wd.publisher?.brand_name || 'Studio' }}</div>
                  <div class="text-[10px] text-slate-400">#WD-{{ wd.id }}</div>
                </td>
                <td class="px-4 py-3.5 text-xs">
                  <div class="font-bold text-sky-400">{{ wd.bank_name }} - {{ wd.bank_account_number }}</div>
                  <div class="text-[11px] text-slate-400">a.n {{ wd.bank_account_name }}</div>
                </td>
                <td class="px-4 py-3.5 text-right font-extrabold text-amber-400 text-sm whitespace-nowrap">
                  Rp {{ formatCurrency(wd.amount) }}
                </td>
                <td class="px-4 py-3.5 text-center whitespace-nowrap">
                  <button
                    @click="approveWithdrawal(wd.id, wd.publisher?.brand_name || 'Publisher', wd.amount)"
                    :disabled="processingWdId === wd.id"
                    class="px-3 py-1.5 rounded-lg text-xs font-bold bg-emerald-600 hover:bg-emerald-500 text-white disabled:opacity-50 transition shadow"
                  >
                    <span>Setujui & Potong Saldo</span>
                  </button>
                </td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>

      <!-- Action Section 2: Recent Sales Orders -->
      <div class="bg-slate-900 border border-slate-800 rounded-2xl p-6 shadow-xl space-y-4">
        <div class="flex items-center justify-between border-b border-slate-800 pb-3">
          <h2 class="text-base font-bold text-white flex items-center gap-2">
            <ShoppingBagIcon class="w-5 h-5 text-sky-400" />
            <span>Transaksi Penjualan Komik Terbaru</span>
          </h2>
          <span class="text-xs text-slate-400">Total Pesanan: <strong class="text-white">{{ metrics.total_orders }}</strong></span>
        </div>

        <div v-if="recentOrders && recentOrders.length" class="overflow-x-auto">
          <table class="w-full text-left text-sm text-slate-300">
            <thead class="text-xs uppercase bg-slate-950 text-slate-400 border-b border-slate-800">
              <tr>
                <th class="px-4 py-3">Waktu</th>
                <th class="px-4 py-3">No. Invoice</th>
                <th class="px-4 py-3">Pembeli</th>
                <th class="px-4 py-3">Rincian Item</th>
                <th class="px-4 py-3 text-right">Total Transaksi</th>
                <th class="px-4 py-3 text-right text-sky-400">Royalti Publisher (70%)</th>
                <th class="px-4 py-3 text-center">Status</th>
              </tr>
            </thead>
            <tbody class="divide-y divide-slate-800/60">
              <tr v-for="order in recentOrders" :key="order.id" class="hover:bg-slate-800/30 transition">
                <td class="px-4 py-3.5 text-xs text-slate-400 whitespace-nowrap">
                  {{ formatDate(order.completed_at || order.created_at) }}
                </td>
                <td class="px-4 py-3.5 font-mono text-xs text-sky-400 font-bold whitespace-nowrap">
                  {{ order.order_number }}
                </td>
                <td class="px-4 py-3.5 text-xs whitespace-nowrap">
                  <div class="font-bold text-white">{{ order.user?.name || 'Reader' }}</div>
                  <div class="text-[10px] text-slate-400">{{ order.user?.email || '-' }}</div>
                </td>
                <td class="px-4 py-3.5 text-xs">
                  <div v-if="order.items && order.items.length" class="space-y-0.5">
                    <div v-for="item in order.items.slice(0, 2)" :key="item.id" class="text-slate-200">
                      <span class="font-bold text-white">{{ item.comic?.title || 'Komik' }}</span>
                      <span class="text-slate-400 text-[11px]"> - {{ item.title_snapshot }}</span>
                    </div>
                    <div v-if="order.items.length > 2" class="text-[10px] text-sky-400 italic">
                      +{{ order.items.length - 2 }} item lainnya
                    </div>
                  </div>
                  <div v-else class="text-slate-500 italic">Pesanan bab komik</div>
                </td>
                <td class="px-4 py-3.5 text-right font-extrabold text-white text-xs whitespace-nowrap">
                  Rp {{ formatCurrency(order.total_amount) }}
                </td>
                <td class="px-4 py-3.5 text-right font-bold text-sky-400 text-xs whitespace-nowrap">
                  Rp {{ formatCurrency(Math.round(order.total_amount * 0.7)) }}
                </td>
                <td class="px-4 py-3.5 text-center whitespace-nowrap">
                  <span
                    v-if="order.status === 'completed'"
                    class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-[10px] font-bold bg-emerald-500/10 text-emerald-400 border border-emerald-500/30"
                  >
                    <CheckCircleIcon class="w-3.5 h-3.5" />
                    <span>Lunas / Completed</span>
                  </span>
                  <span
                    v-else-if="order.status === 'pending'"
                    class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-[10px] font-bold bg-amber-500/10 text-amber-400 border border-amber-500/30"
                  >
                    <ClockIcon class="w-3.5 h-3.5" />
                    <span>Menunggu Bayar</span>
                  </span>
                  <span
                    v-else
                    class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-[10px] font-bold bg-rose-500/10 text-rose-400 border border-rose-500/30"
                  >
                    <XCircleIcon class="w-3.5 h-3.5" />
                    <span>{{ order.status }}</span>
                  </span>
                </td>
              </tr>
            </tbody>
          </table>
        </div>

        <div v-else class="py-8 text-center text-xs text-slate-500">
          Belum ada riwayat pesanan penjualan komik.
        </div>
      </div>
    </div>
  </AdminLayout>
</template>
