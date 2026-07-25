<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import AdminLayout from '@/Layouts/AdminLayout.vue';

interface Metrics {
  total_gmv: number;
  total_orders: number;
  total_publishers: number;
  total_comics: number;
  total_chapters: number;
}

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

defineProps<{
  metrics: Metrics;
  recentPayments: Payment[];
}>();
</script>

<template>
  <Head title="Super Admin Dashboard - Overview" />

  <AdminLayout>
    <div class="space-y-8">
      <div>
        <span class="text-xs text-amber-400 font-bold uppercase tracking-wider">Super Admin Control</span>
        <h1 class="text-3xl font-extrabold text-white">Platform Overview & Metrics</h1>
        <p class="text-sm text-slate-400 mt-1">Real-time GMV sales volume, active creators, and TriPay payment transactions</p>
      </div>

      <!-- Stat Metrics Cards -->
      <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
        <div class="bg-gradient-to-br from-slate-900 to-slate-950 border border-slate-800 rounded-2xl p-6 space-y-2 shadow-xl">
          <span class="text-xs text-slate-400 font-semibold uppercase tracking-wider">Total Sales GMV</span>
          <div class="text-3xl font-extrabold text-emerald-400">
            Rp {{ metrics.total_gmv ? metrics.total_gmv.toLocaleString() : '0' }}
          </div>
          <p class="text-xs text-slate-500">Gross Merchandise Value</p>
        </div>

        <div class="bg-slate-900 border border-slate-800 rounded-2xl p-6 space-y-2">
          <span class="text-xs text-slate-400 font-semibold uppercase tracking-wider">Total Orders</span>
          <div class="text-3xl font-extrabold text-white">
            {{ metrics.total_orders }}
          </div>
          <p class="text-xs text-slate-500">Checkout invoices</p>
        </div>

        <div class="bg-slate-900 border border-slate-800 rounded-2xl p-6 space-y-2">
          <span class="text-xs text-slate-400 font-semibold uppercase tracking-wider">Approved Publishers</span>
          <div class="text-3xl font-extrabold text-sky-400">
            {{ metrics.total_publishers }}
          </div>
          <p class="text-xs text-slate-500">Creator studios</p>
        </div>

        <div class="bg-slate-900 border border-slate-800 rounded-2xl p-6 space-y-2">
          <span class="text-xs text-slate-400 font-semibold uppercase tracking-wider">Total Webcomics</span>
          <div class="text-3xl font-extrabold text-purple-400">
            {{ metrics.total_comics }}
          </div>
          <p class="text-xs text-slate-500">{{ metrics.total_chapters }} Chapters published</p>
        </div>
      </div>

      <!-- Quick Nav Action Buttons -->
      <div class="flex items-center gap-4">
        <Link href="/admin/publishers" class="px-5 py-3 rounded-xl text-xs font-bold bg-slate-900 border border-slate-800 text-slate-200 hover:text-white transition">
          👥 Manage Publisher Applications →
        </Link>
        <Link href="/admin/transactions" class="px-5 py-3 rounded-xl text-xs font-bold bg-slate-900 border border-slate-800 text-slate-200 hover:text-white transition">
          💳 TriPay Transactions & Payouts →
        </Link>
      </div>

      <!-- Recent Payments Monitor -->
      <div class="bg-slate-900 border border-slate-800 rounded-2xl p-6 space-y-4">
        <h2 class="text-lg font-bold text-white border-b border-slate-800 pb-3">Recent TriPay Payment Transactions</h2>

        <div v-if="recentPayments && recentPayments.length" class="overflow-x-auto">
          <table class="w-full text-left text-sm text-slate-300">
            <thead class="text-xs uppercase bg-slate-950 text-slate-400 border-b border-slate-800">
              <tr>
                <th class="px-4 py-3">TriPay Ref</th>
                <th class="px-4 py-3">Customer</th>
                <th class="px-4 py-3">Method</th>
                <th class="px-4 py-3">Status</th>
                <th class="px-4 py-3 text-right">Amount</th>
              </tr>
            </thead>
            <tbody class="divide-y divide-slate-800/60">
              <tr v-for="p in recentPayments" :key="p.id" class="hover:bg-slate-800/30">
                <td class="px-4 py-3 font-mono text-xs text-sky-400">{{ p.tripay_reference }}</td>
                <td class="px-4 py-3 text-xs text-white">{{ p.user ? p.user.name : 'User' }}</td>
                <td class="px-4 py-3 text-xs text-slate-400">{{ p.payment_name }}</td>
                <td class="px-4 py-3">
                  <span
                    class="px-2 py-0.5 text-[10px] font-extrabold rounded uppercase"
                    :class="p.status === 'PAID' ? 'bg-emerald-500/10 text-emerald-400 border border-emerald-500/30' : 'bg-amber-500/10 text-amber-400 border border-amber-500/30'"
                  >
                    {{ p.status }}
                  </span>
                </td>
                <td class="px-4 py-3 font-bold text-right text-xs text-amber-400">
                  Rp {{ p.amount ? p.amount.toLocaleString() : '0' }}
                </td>
              </tr>
            </tbody>
          </table>
        </div>

        <div v-else class="py-8 text-center text-xs text-slate-500">
          No payment transactions logged yet.
        </div>
      </div>
    </div>
  </AdminLayout>
</template>
