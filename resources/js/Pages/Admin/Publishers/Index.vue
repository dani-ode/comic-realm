<script setup lang="ts">
import { Head, useForm, Link } from '@inertiajs/vue3';
import AdminLayout from '@/Layouts/AdminLayout.vue';

interface Publisher {
  id: number;
  brand_name: string;
  slug: string;
  bio?: string;
  bank_name?: string;
  bank_account_number?: string;
  verification_status: string;
  created_at: string;
  user?: { name: string; email: string };
}

interface Paginator {
  data: Publisher[];
}

defineProps<{
  publishers: Paginator;
}>();

const approveForm = useForm({});

const approvePublisher = (id: number) => {
  approveForm.post(`/admin/publishers/${id}/approve`);
};
</script>

<template>
  <Head title="Manage Publishers - Admin Control" />

  <AdminLayout>
    <div class="space-y-8">
      <div>
        <span class="text-xs text-amber-400 font-bold uppercase tracking-wider">Super Admin</span>
        <h1 class="text-3xl font-extrabold text-white">Publisher Verification Requests</h1>
        <p class="text-sm text-slate-400 mt-1">Review and approve independent creator publisher applications</p>
      </div>

      <div v-if="publishers.data && publishers.data.length" class="bg-slate-900 border border-slate-800 rounded-2xl overflow-hidden shadow-xl">
        <table class="w-full text-left text-sm text-slate-300">
          <thead class="text-xs uppercase bg-slate-950 text-slate-400 border-b border-slate-800">
            <tr>
              <th class="px-6 py-4">Brand / Studio</th>
              <th class="px-6 py-4">User Account</th>
              <th class="px-6 py-4">Bank Account</th>
              <th class="px-6 py-4">Status</th>
              <th class="px-6 py-4 text-right">Action</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-slate-800/60">
            <tr v-for="pub in publishers.data" :key="pub.id" class="hover:bg-slate-800/30">
              <td class="px-6 py-4">
                <h3 class="font-bold text-white text-sm">{{ pub.brand_name }}</h3>
                <p class="text-xs text-slate-500 line-clamp-1">{{ pub.bio || 'No bio provided' }}</p>
              </td>
              <td class="px-6 py-4 text-xs">
                <span class="text-white font-medium">{{ pub.user ? pub.user.name : 'User' }}</span>
                <p class="text-slate-400">{{ pub.user ? pub.user.email : '' }}</p>
              </td>
              <td class="px-6 py-4 text-xs text-slate-300">
                <span class="font-semibold text-sky-400">{{ pub.bank_name || 'BCA' }}</span>
                <p class="font-mono text-slate-400">{{ pub.bank_account_number || '-' }}</p>
              </td>
              <td class="px-6 py-4">
                <span
                  class="px-2.5 py-1 text-xs font-extrabold rounded-lg uppercase"
                  :class="[
                    pub.verification_status === 'approved'
                      ? 'bg-emerald-500/10 text-emerald-400 border border-emerald-500/30'
                      : 'bg-amber-500/10 text-amber-400 border border-amber-500/30'
                  ]"
                >
                  {{ pub.verification_status }}
                </span>
              </td>
              <td class="px-6 py-4 text-right">
                <button
                  v-if="pub.verification_status !== 'approved'"
                  @click="approvePublisher(pub.id)"
                  class="px-4 py-2 rounded-xl text-xs font-bold text-white bg-emerald-600 hover:bg-emerald-500 transition shadow-md shadow-emerald-600/30"
                >
                  Approve Studio ✓
                </button>
                <span v-else class="text-xs text-emerald-400 font-semibold">Active Publisher</span>
              </td>
            </tr>
          </tbody>
        </table>
      </div>

      <div v-else class="bg-slate-900/40 border border-slate-800 rounded-2xl p-12 text-center text-slate-400">
        No publisher applications submitted yet.
      </div>
    </div>
  </AdminLayout>
</template>
