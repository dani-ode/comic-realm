<script setup lang="ts">
import { Head, useForm } from '@inertiajs/vue3';
import AdminLayout from '@/Layouts/AdminLayout.vue';

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
}

interface Paginator<T> {
  data: T[];
}

defineProps<{
  payments: Paginator<Payment>;
  withdrawals: Paginator<Withdrawal>;
}>();

const approveWdForm = useForm({});

const approveWithdrawal = (id: number) => {
  approveWdForm.post(`/admin/withdrawals/${id}/approve`);
};
</script>

<template>
  <Head title="TriPay Transactions & Payouts - Admin Control" />

  <AdminLayout>
    <div class="space-y-10">
      <div>
        <span class="text-xs text-amber-400 font-bold uppercase tracking-wider">Super Admin</span>
        <h1 class="text-3xl font-extrabold text-white">Transactions & Publisher Payouts</h1>
        <p class="text-sm text-slate-400 mt-1">Monitor closed payment transactions via TriPay and approve publisher withdrawals</p>
      </div>

      <!-- Pending Withdrawal Payouts Section -->
      <div class="space-y-4">
        <h2 class="text-xl font-bold text-white flex items-center gap-2">
          💸 Publisher Payout Requests
        </h2>

        <div v-if="withdrawals.data && withdrawals.data.length" class="bg-slate-900 border border-slate-800 rounded-2xl overflow-hidden shadow-xl">
          <table class="w-full text-left text-sm text-slate-300">
            <thead class="text-xs uppercase bg-slate-950 text-slate-400 border-b border-slate-800">
              <tr>
                <th class="px-6 py-4">Publisher Studio</th>
                <th class="px-6 py-4">Bank Destination</th>
                <th class="px-6 py-4">Requested Amount</th>
                <th class="px-6 py-4">Status</th>
                <th class="px-6 py-4 text-right">Action</th>
              </tr>
            </thead>
            <tbody class="divide-y divide-slate-800/60">
              <tr v-for="wd in withdrawals.data" :key="wd.id" class="hover:bg-slate-800/30">
                <td class="px-6 py-4 font-bold text-white">
                  {{ wd.publisher ? wd.publisher.brand_name : 'Studio' }}
                </td>
                <td class="px-6 py-4 text-xs">
                  <span class="font-semibold text-sky-400">{{ wd.bank_name }}</span>
                  <p class="font-mono text-slate-400">{{ wd.bank_account_number }} (a.n {{ wd.bank_account_name }})</p>
                </td>
                <td class="px-6 py-4 font-bold text-amber-400">
                  Rp {{ wd.amount ? wd.amount.toLocaleString() : '0' }}
                </td>
                <td class="px-6 py-4">
                  <span
                    class="px-2.5 py-1 text-xs font-extrabold rounded-lg uppercase"
                    :class="[
                      wd.status === 'approved'
                        ? 'bg-emerald-500/10 text-emerald-400 border border-emerald-500/30'
                        : 'bg-amber-500/10 text-amber-400 border border-amber-500/30'
                    ]"
                  >
                    {{ wd.status }}
                  </span>
                </td>
                <td class="px-6 py-4 text-right">
                  <button
                    v-if="wd.status === 'pending'"
                    @click="approveWithdrawal(wd.id)"
                    class="px-4 py-2 rounded-xl text-xs font-bold text-white bg-sky-600 hover:bg-sky-500 transition shadow-md shadow-sky-600/30"
                  >
                    Approve Payout Transfer ✓
                  </button>
                  <span v-else class="text-xs text-emerald-400 font-semibold">Processed</span>
                </td>
              </tr>
            </tbody>
          </table>
        </div>

        <div v-else class="bg-slate-900/40 border border-slate-800 rounded-2xl p-8 text-center text-xs text-slate-500">
          No pending publisher payout withdrawal requests.
        </div>
      </div>

      <!-- TriPay Payment Log Section -->
      <div class="space-y-4">
        <h2 class="text-xl font-bold text-white flex items-center gap-2">
          💳 TriPay Order Payments Log
        </h2>

        <div v-if="payments.data && payments.data.length" class="bg-slate-900 border border-slate-800 rounded-2xl overflow-hidden shadow-xl">
          <table class="w-full text-left text-sm text-slate-300">
            <thead class="text-xs uppercase bg-slate-950 text-slate-400 border-b border-slate-800">
              <tr>
                <th class="px-6 py-4">TriPay Reference</th>
                <th class="px-6 py-4">Merchant Ref (Invoice)</th>
                <th class="px-6 py-4">Customer</th>
                <th class="px-6 py-4">Payment Method</th>
                <th class="px-6 py-4">Status</th>
                <th class="px-6 py-4 text-right">Total Paid</th>
              </tr>
            </thead>
            <tbody class="divide-y divide-slate-800/60">
              <tr v-for="p in payments.data" :key="p.id" class="hover:bg-slate-800/30">
                <td class="px-6 py-4 font-mono text-xs text-sky-400">{{ p.tripay_reference }}</td>
                <td class="px-6 py-4 font-mono text-xs text-white">{{ p.merchant_ref }}</td>
                <td class="px-6 py-4 text-xs text-slate-300">{{ p.user ? p.user.name : 'User' }}</td>
                <td class="px-6 py-4 text-xs text-slate-400">{{ p.payment_name }}</td>
                <td class="px-6 py-4">
                  <span
                    class="px-2 py-0.5 text-[10px] font-extrabold rounded uppercase"
                    :class="p.status === 'PAID' ? 'bg-emerald-500/10 text-emerald-400 border border-emerald-500/30' : 'bg-amber-500/10 text-amber-400 border border-amber-500/30'"
                  >
                    {{ p.status }}
                  </span>
                </td>
                <td class="px-6 py-4 font-bold text-right text-xs text-amber-400">
                  Rp {{ p.amount ? p.amount.toLocaleString() : '0' }}
                </td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>
    </div>
  </AdminLayout>
</template>
