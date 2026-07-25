<script setup lang="ts">
import { Head, useForm, Link } from '@inertiajs/vue3';
import AdminLayout from '@/Layouts/AdminLayout.vue';

interface Transaction {
  id: number;
  type: string;
  amount: number;
  balance_after: number;
  description: string;
  created_at: string;
}

interface Withdrawal {
  id: number;
  amount: number;
  bank_name: string;
  bank_account_number: string;
  status: string;
  created_at: string;
}

interface Wallet {
  id: number;
  balance: number;
  total_earned: number;
  total_withdrawn: number;
  transactions?: Transaction[];
  withdrawals?: Withdrawal[];
}

interface Publisher {
  id: number;
  brand_name: string;
  bank_name?: string;
  bank_account_number?: string;
}

const props = defineProps<{
  publisher: Publisher;
  wallet: Wallet;
}>();

const form = useForm({
  amount: 50000,
});

const submitWithdrawal = () => {
  form.post('/publisher/wallet/withdraw', {
    onSuccess: () => form.reset(),
  });
};
</script>

<template>
  <Head title="Publisher Wallet & Royalty Revenue" />

  <AdminLayout>
    <div class="space-y-8">
      <div>
        <span class="text-xs text-sky-400 font-bold uppercase tracking-wider">Creator Economy</span>
        <h1 class="text-3xl font-extrabold text-white">Royalty Wallet & Revenue Share</h1>
        <p class="text-sm text-slate-400 mt-1">Track 70% revenue share from paid chapter sales and submit payout withdrawals</p>
      </div>

      <!-- Wallet Metrics Cards -->
      <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
        <div class="bg-gradient-to-br from-slate-900 to-slate-950 border border-slate-800 rounded-2xl p-6 space-y-2 shadow-xl">
          <span class="text-xs text-slate-400 font-semibold uppercase tracking-wider">Current Available Balance</span>
          <div class="text-3xl font-extrabold text-emerald-400">
            Rp {{ wallet.balance ? wallet.balance.toLocaleString() : '0' }}
          </div>
          <p class="text-xs text-slate-500">Ready for instant bank payout</p>
        </div>

        <div class="bg-slate-900 border border-slate-800 rounded-2xl p-6 space-y-2">
          <span class="text-xs text-slate-400 font-semibold uppercase tracking-wider">Total Revenue Earned</span>
          <div class="text-3xl font-extrabold text-white">
            Rp {{ wallet.total_earned ? wallet.total_earned.toLocaleString() : '0' }}
          </div>
          <p class="text-xs text-slate-500">Lifetime 70% publisher share</p>
        </div>

        <div class="bg-slate-900 border border-slate-800 rounded-2xl p-6 space-y-2">
          <span class="text-xs text-slate-400 font-semibold uppercase tracking-wider">Total Funds Withdrawn</span>
          <div class="text-3xl font-extrabold text-sky-400">
            Rp {{ wallet.total_withdrawn ? wallet.total_withdrawn.toLocaleString() : '0' }}
          </div>
          <p class="text-xs text-slate-500">Transferred to bank account</p>
        </div>
      </div>

      <!-- Request Withdrawal Card -->
      <div class="bg-slate-900 border border-slate-800 rounded-2xl p-6 space-y-4">
        <h2 class="text-lg font-bold text-white border-b border-slate-800 pb-3">Request Payout Withdrawal</h2>

        <form @submit.prevent="submitWithdrawal" class="flex flex-col sm:flex-row items-end gap-4">
          <div class="flex-1 space-y-1 w-full">
            <label class="block text-xs font-medium text-slate-400">Withdrawal Amount (Min. Rp 50,000)</label>
            <input
              v-model="form.amount"
              type="number"
              min="50000"
              step="10000"
              required
              class="w-full rounded-xl bg-slate-950 border border-slate-800 px-4 py-2.5 text-white font-bold text-sm focus:border-sky-500 focus:outline-none"
            />
            <p v-if="form.errors.amount" class="text-xs text-rose-400 mt-1">{{ form.errors.amount }}</p>
          </div>

          <button
            type="submit"
            :disabled="form.processing || wallet.balance < 50000"
            class="px-6 py-3 rounded-xl text-sm font-bold text-white bg-sky-600 hover:bg-sky-500 disabled:opacity-50 transition shadow-lg shadow-sky-600/30 shrink-0 w-full sm:w-auto"
          >
            Submit Withdrawal Request →
          </button>
        </form>
      </div>

      <!-- Transaction Ledger Table -->
      <div class="bg-slate-900 border border-slate-800 rounded-2xl p-6 space-y-4">
        <h2 class="text-lg font-bold text-white border-b border-slate-800 pb-3">Wallet Mutation Ledger</h2>

        <div v-if="wallet.transactions && wallet.transactions.length" class="overflow-x-auto">
          <table class="w-full text-left text-sm text-slate-300">
            <thead class="text-xs uppercase bg-slate-950 text-slate-400 border-b border-slate-800">
              <tr>
                <th class="px-4 py-3">Date</th>
                <th class="px-4 py-3">Type</th>
                <th class="px-4 py-3">Description</th>
                <th class="px-4 py-3 text-right">Amount</th>
              </tr>
            </thead>
            <tbody class="divide-y divide-slate-800/60">
              <tr v-for="tx in wallet.transactions" :key="tx.id" class="hover:bg-slate-800/30">
                <td class="px-4 py-3 text-xs text-slate-400">{{ new Date(tx.created_at).toLocaleString() }}</td>
                <td class="px-4 py-3">
                  <span
                    class="px-2 py-0.5 text-[10px] font-extrabold rounded uppercase"
                    :class="tx.type === 'credit' ? 'bg-emerald-500/10 text-emerald-400 border border-emerald-500/30' : 'bg-rose-500/10 text-rose-400 border border-rose-500/30'"
                  >
                    {{ tx.type }}
                  </span>
                </td>
                <td class="px-4 py-3 text-white text-xs">{{ tx.description }}</td>
                <td class="px-4 py-3 font-bold text-right text-xs" :class="tx.type === 'credit' ? 'text-emerald-400' : 'text-rose-400'">
                  {{ tx.type === 'credit' ? '+' : '-' }} Rp {{ tx.amount.toLocaleString() }}
                </td>
              </tr>
            </tbody>
          </table>
        </div>

        <div v-else class="py-8 text-center text-xs text-slate-500">
          No wallet transaction ledger history yet.
        </div>
      </div>
    </div>
  </AdminLayout>
</template>
