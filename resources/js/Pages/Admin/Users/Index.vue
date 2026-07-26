<script setup lang="ts">
import { ref } from 'vue';
import { Head, useForm, router } from '@inertiajs/vue3';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import {
  UserGroupIcon,
  UserIcon,
  AtSymbolIcon,
  ShieldCheckIcon,
  CheckBadgeIcon,
  Cog6ToothIcon,
  LockOpenIcon,
  ClockIcon,
  NoSymbolIcon,
  CheckCircleIcon,
  ExclamationTriangleIcon,
} from '@heroicons/vue/24/outline';

interface User {
  id: number;
  name: string;
  username?: string;
  email: string;
  phone?: string;
  role: string;
  status: string;
  created_at: string;
}

interface Paginator {
  data: User[];
  links: any[];
}

interface Metrics {
  total_users: number;
  active_users: number;
  suspended_users: number;
  banned_users: number;
}

const props = defineProps<{
  users: Paginator;
  metrics: Metrics;
  filters?: { role?: string; status?: string; search?: string };
}>();

const search = ref(props.filters?.search || '');
const roleFilter = ref(props.filters?.role || '');
const statusFilter = ref(props.filters?.status || '');

const actionForm = useForm({
  role: '',
  status: '',
});

const handleSearch = () => {
  router.get('/admin/users', {
    search: search.value,
    role: roleFilter.value,
    status: statusFilter.value,
  }, { preserveState: true, replace: true });
};

const filterByRole = (role: string) => {
  roleFilter.value = role;
  handleSearch();
};

const filterByStatus = (status: string) => {
  statusFilter.value = status;
  handleSearch();
};

const updateUserStatus = (id: number, status: string) => {
  if (confirm(`Ubah status user menjadi "${status}"?`)) {
    router.post(
      `/admin/users/${id}/toggle-status`,
      { status },
      { preserveScroll: true }
    );
  }
};

const updateUserRole = (id: number, role: string) => {
  if (confirm(`Ubah role user menjadi "${role}"?`)) {
    router.post(
      `/admin/users/${id}/change-role`,
      { role },
      { preserveScroll: true }
    );
  }
};
</script>

<template>
  <Head title="Kelola User - Admin Control" />

  <AdminLayout>
    <div class="space-y-8">
      <!-- Header -->
      <div>
        <span class="text-xs text-amber-400 font-bold uppercase tracking-wider">Super Admin</span>
        <h1 class="text-3xl font-extrabold text-white flex items-center gap-3">
          <UserGroupIcon class="w-8 h-8 text-amber-400 shrink-0" />
          Kelola User Platform
        </h1>
        <p class="text-sm text-slate-400 mt-1">Kelola pengguna terdaftar, atur hak akses role, dan kelola blokir/suspensi akun</p>
      </div>

      <!-- Metrics Summary -->
      <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
        <div class="bg-slate-900 border border-slate-800 rounded-2xl p-5 space-y-1">
          <span class="text-xs text-slate-400 font-semibold uppercase tracking-wider flex items-center gap-1.5">
            <UserIcon class="w-4 h-4 text-sky-400" /> Total User
          </span>
          <div class="text-2xl font-extrabold text-white">{{ metrics.total_users }}</div>
          <p class="text-[11px] text-slate-500">Semua pengguna terdaftar</p>
        </div>

        <div class="bg-slate-900 border border-slate-800 rounded-2xl p-5 space-y-1">
          <span class="text-xs text-slate-400 font-semibold uppercase tracking-wider flex items-center gap-1.5">
            <CheckCircleIcon class="w-4 h-4 text-emerald-400" /> User Aktif
          </span>
          <div class="text-2xl font-extrabold text-emerald-400">{{ metrics.active_users }}</div>
          <p class="text-[11px] text-slate-500">Akun berstatus aktif</p>
        </div>

        <div class="bg-slate-900 border border-slate-800 rounded-2xl p-5 space-y-1">
          <span class="text-xs text-slate-400 font-semibold uppercase tracking-wider flex items-center gap-1.5">
            <ClockIcon class="w-4 h-4 text-amber-400" /> User Suspended
          </span>
          <div class="text-2xl font-extrabold text-amber-400">{{ metrics.suspended_users }}</div>
          <p class="text-[11px] text-slate-500">Akun dibekukan sementara</p>
        </div>

        <div class="bg-slate-900 border border-slate-800 rounded-2xl p-5 space-y-1">
          <span class="text-xs text-slate-400 font-semibold uppercase tracking-wider flex items-center gap-1.5">
            <NoSymbolIcon class="w-4 h-4 text-rose-400" /> User Banned
          </span>
          <div class="text-2xl font-extrabold text-rose-400">{{ metrics.banned_users }}</div>
          <p class="text-[11px] text-slate-500">Akun diblokir permanen</p>
        </div>
      </div>

      <!-- Search & Filters -->
      <div class="flex flex-col md:flex-row items-center justify-between gap-4 bg-slate-900 border border-slate-800 p-4 rounded-2xl">
        <div class="flex flex-wrap items-center gap-2 w-full md:w-auto">
          <!-- Role Filters -->
          <button
            @click="filterByRole('')"
            class="px-3 py-1.5 rounded-xl text-xs font-bold transition"
            :class="!roleFilter ? 'bg-amber-500 text-slate-950 shadow-md shadow-amber-500/20' : 'bg-slate-950 text-slate-400 hover:text-white border border-slate-800'"
          >
            Semua Role
          </button>
          <button
            @click="filterByRole('user')"
            class="px-3 py-1.5 rounded-xl text-xs font-bold transition"
            :class="roleFilter === 'user' ? 'bg-amber-500 text-slate-950 shadow-md shadow-amber-500/20' : 'bg-slate-950 text-slate-400 hover:text-white border border-slate-800'"
          >
            User / Customer
          </button>
          <button
            @click="filterByRole('publisher')"
            class="px-3 py-1.5 rounded-xl text-xs font-bold transition"
            :class="roleFilter === 'publisher' ? 'bg-amber-500 text-slate-950 shadow-md shadow-amber-500/20' : 'bg-slate-950 text-slate-400 hover:text-white border border-slate-800'"
          >
            Publisher
          </button>
          <button
            @click="filterByRole('admin')"
            class="px-3 py-1.5 rounded-xl text-xs font-bold transition"
            :class="roleFilter === 'admin' ? 'bg-amber-500 text-slate-950 shadow-md shadow-amber-500/20' : 'bg-slate-950 text-slate-400 hover:text-white border border-slate-800'"
          >
            Admin
          </button>

          <span class="text-slate-700 hidden md:inline">|</span>

          <!-- Status Filters -->
          <button
            @click="filterByStatus('active')"
            class="px-3 py-1.5 rounded-xl text-xs font-bold transition"
            :class="statusFilter === 'active' ? 'bg-emerald-600 text-white shadow-md shadow-emerald-600/20' : 'bg-slate-950 text-slate-400 hover:text-white border border-slate-800'"
          >
            Aktif
          </button>
          <button
            @click="filterByStatus('banned')"
            class="px-3 py-1.5 rounded-xl text-xs font-bold transition"
            :class="statusFilter === 'banned' ? 'bg-rose-600 text-white shadow-md shadow-rose-600/20' : 'bg-slate-950 text-slate-400 hover:text-white border border-slate-800'"
          >
            Banned
          </button>
        </div>

        <div class="w-full md:w-72">
          <input
            v-model="search"
            @keyup.enter="handleSearch"
            type="text"
            placeholder="Cari nama, email, username..."
            class="w-full bg-slate-950 border border-slate-800 rounded-xl px-4 py-2 text-xs text-white placeholder-slate-500 focus:outline-none focus:border-amber-500"
          />
        </div>
      </div>

      <!-- Users Table -->
      <div v-if="users.data && users.data.length" class="bg-slate-900 border border-slate-800 rounded-2xl overflow-hidden shadow-xl">
        <div class="overflow-x-auto">
          <table class="w-full text-left text-sm text-slate-300">
            <thead class="text-xs uppercase bg-slate-950 text-slate-400 border-b border-slate-800">
              <tr>
                <th class="px-6 py-4 flex items-center gap-1.5"><UserIcon class="w-3.5 h-3.5" /> User Details</th>
                <th class="px-6 py-4"><AtSymbolIcon class="w-3.5 h-3.5 inline mr-1" /> Username & Kontak</th>
                <th class="px-6 py-4"><ShieldCheckIcon class="w-3.5 h-3.5 inline mr-1" /> Role</th>
                <th class="px-6 py-4"><CheckBadgeIcon class="w-3.5 h-3.5 inline mr-1" /> Status</th>
                <th class="px-6 py-4 text-right"><Cog6ToothIcon class="w-3.5 h-3.5 inline mr-1" /> Kelola Akses</th>
              </tr>
            </thead>
            <tbody class="divide-y divide-slate-800/60">
              <tr v-for="u in users.data" :key="u.id" class="hover:bg-slate-800/30 transition">
                <td class="px-6 py-4">
                  <div class="flex items-center gap-3">
                    <div class="w-9 h-9 rounded-xl bg-slate-800 border border-slate-700 font-bold text-amber-400 flex items-center justify-center text-sm shrink-0">
                      {{ u.name.charAt(0).toUpperCase() }}
                    </div>
                    <div>
                      <h4 class="font-bold text-white text-sm">{{ u.name }}</h4>
                      <p class="text-xs text-slate-400">{{ u.email }}</p>
                    </div>
                  </div>
                </td>

                <td class="px-6 py-4 text-xs text-slate-400">
                  <span class="text-slate-200 font-mono">@{{ u.username || 'user' }}</span>
                  <p class="text-slate-500">{{ u.phone || 'No phone' }}</p>
                </td>

                <td class="px-6 py-4">
                  <select
                    :value="u.role"
                    @change="updateUserRole(u.id, ($event.target as HTMLSelectElement).value)"
                    class="bg-slate-950 border border-slate-800 text-xs font-bold rounded-lg px-2.5 py-1 text-sky-400 focus:outline-none focus:border-amber-500 cursor-pointer capitalize"
                  >
                    <option value="user">User / Customer</option>
                    <option value="publisher">Publisher</option>
                    <option value="admin">Admin</option>
                  </select>
                </td>

                <td class="px-6 py-4">
                  <span
                    class="px-2.5 py-1 text-[10px] font-extrabold rounded-lg uppercase tracking-wider"
                    :class="[
                      u.status === 'active' ? 'bg-emerald-500/10 text-emerald-400 border border-emerald-500/30' :
                      u.status === 'suspended' ? 'bg-amber-500/10 text-amber-400 border border-amber-500/30' :
                      'bg-rose-500/10 text-rose-400 border border-rose-500/30'
                    ]"
                  >
                    {{ u.status }}
                  </span>
                </td>

                <td class="px-6 py-4 text-right space-x-2">
                  <button
                    v-if="u.status !== 'active'"
                    @click="updateUserStatus(u.id, 'active')"
                    class="inline-flex items-center gap-1 px-3 py-1.5 rounded-lg text-xs font-bold text-emerald-400 bg-emerald-950/40 border border-emerald-800/80 hover:bg-emerald-900 transition"
                  >
                    <LockOpenIcon class="w-3.5 h-3.5" /> Aktifkan
                  </button>

                  <button
                    v-if="u.status === 'active'"
                    @click="updateUserStatus(u.id, 'suspended')"
                    class="inline-flex items-center gap-1 px-3 py-1.5 rounded-lg text-xs font-bold text-amber-400 bg-amber-950/40 border border-amber-800/80 hover:bg-amber-900 transition"
                  >
                    <ClockIcon class="w-3.5 h-3.5" /> Bekukan
                  </button>

                  <button
                    v-if="u.status !== 'banned'"
                    @click="updateUserStatus(u.id, 'banned')"
                    class="inline-flex items-center gap-1 px-3 py-1.5 rounded-lg text-xs font-bold text-rose-400 bg-rose-950/40 border border-rose-800/80 hover:bg-rose-900 transition"
                  >
                    <NoSymbolIcon class="w-3.5 h-3.5" /> Block / Ban
                  </button>
                </td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>
    </div>
  </AdminLayout>
</template>
