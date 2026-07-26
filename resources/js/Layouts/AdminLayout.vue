<script setup lang="ts">
import { ref, computed } from 'vue';
import { Link, usePage, useForm } from '@inertiajs/vue3';
import {
  ShieldCheckIcon,
  BuildingOffice2Icon,
  BookOpenIcon,
  DocumentTextIcon,
  ChatBubbleLeftRightIcon,
  UsersIcon,
  CreditCardIcon,
  ChartBarIcon,
  PlusCircleIcon,
  ArrowRightOnRectangleIcon,
  ChevronLeftIcon,
  ChevronRightIcon,
  Bars3Icon,
  SwatchIcon,
  ArrowTopRightOnSquareIcon,
  BanknotesIcon,
  BuildingStorefrontIcon,
  RectangleStackIcon,
  UserGroupIcon,
  UserIcon,
  KeyIcon,
  Cog6ToothIcon,
  XMarkIcon,
  CheckCircleIcon,
  ExclamationTriangleIcon,
} from '@heroicons/vue/24/outline';

const isSidebarOpen = ref(true);
const isMobileOpen = ref(false);

const toggleSidebar = () => {
  isSidebarOpen.value = !isSidebarOpen.value;
};

const toggleMobileSidebar = () => {
  isMobileOpen.value = !isMobileOpen.value;
};

const page = usePage();
const user = computed(() => (page.props.auth ? (page.props.auth as any).user : null));

// Account Management Modal state
const showAccountModal = ref(false);
const activeAccountTab = ref<'profile' | 'password'>('profile');

const profileForm = useForm({
  name: '',
  username: '',
  email: '',
  phone: '',
});

const passwordForm = useForm({
  current_password: '',
  password: '',
  password_confirmation: '',
});

const openAccountModal = () => {
  if (user.value) {
    profileForm.name = user.value.name || '';
    profileForm.username = user.value.username || '';
    profileForm.email = user.value.email || '';
    profileForm.phone = user.value.phone || '';
  }
  showAccountModal.value = true;
};

const submitProfileUpdate = () => {
  profileForm.post('/profile/update', {
    preserveScroll: true,
    onSuccess: () => {
      // Profile updated successfully
    },
  });
};

const submitPasswordUpdate = () => {
  passwordForm.post('/profile/password', {
    preserveScroll: true,
    onSuccess: () => {
      passwordForm.reset();
    },
  });
};
</script>

<template>
  <div class="min-h-screen bg-slate-950 text-slate-100 flex">
    <!-- Sidebar -->
    <aside
      class="fixed inset-y-0 left-0 z-40 bg-slate-900 border-r border-slate-800/80 transition-all duration-300 flex flex-col justify-between"
      :class="[
        isSidebarOpen ? 'w-64' : 'w-[72px]',
        isMobileOpen ? 'translate-x-0' : '-translate-x-full lg:translate-x-0'
      ]"
    >
      <!-- Logo Header -->
      <div class="h-16 px-4 flex items-center justify-between border-b border-slate-800/80 shrink-0">
        <Link href="/" class="flex items-center gap-3 overflow-hidden">
          <img
            src="/favicon.ico"
            alt="ComicRealm"
            class="w-9 h-9 rounded-2xl object-contain shadow-lg shrink-0"
          />
          <div v-if="isSidebarOpen" class="flex flex-col truncate">
            <span class="font-brand font-black text-white text-base tracking-wider uppercase">ComicRealm</span>
            <span class="text-[10px] text-sky-400 font-bold uppercase tracking-widest">Creator Studio</span>
          </div>
        </Link>

        <button
          @click="toggleSidebar"
          class="hidden lg:flex text-slate-400 hover:text-white p-1 rounded-lg hover:bg-slate-800 transition"
        >
          <ChevronLeftIcon v-if="isSidebarOpen" class="w-5 h-5" />
          <ChevronRightIcon v-else class="w-5 h-5" />
        </button>
      </div>

      <!-- Navigation Links -->
      <nav class="p-3 space-y-1 overflow-y-auto flex-1 custom-scrollbar">
        <!-- ── Super Admin Navigation ─────────────────────────────────────── -->
        <template v-if="user && user.role === 'admin'">
          <p class="px-3 pb-2 pt-1 text-[10px] font-extrabold uppercase text-amber-400/80 tracking-widest">
            <span v-if="isSidebarOpen">Super Admin</span>
            <span v-else class="block w-full border-t border-slate-800/80 my-1" />
          </p>

          <!-- Dashboard -->
          <Link
            href="/admin/dashboard"
            class="flex items-center gap-3 px-3 py-2.5 rounded-xl text-sm font-semibold transition-all"
            :class="page.url === '/admin/dashboard'
              ? 'bg-amber-500/15 text-amber-400 border border-amber-500/25 shadow-sm'
              : 'text-slate-400 hover:bg-slate-800/70 hover:text-white'"
          >
            <ChartBarIcon class="w-5 h-5 shrink-0" />
            <span v-if="isSidebarOpen">Dashboard Overview</span>
          </Link>

          <!-- Kelola Studio -->
          <Link
            href="/admin/publishers"
            class="flex items-center gap-3 px-3 py-2.5 rounded-xl text-sm font-semibold transition-all"
            :class="page.url.startsWith('/admin/publishers')
              ? 'bg-amber-500/15 text-amber-400 border border-amber-500/25 shadow-sm'
              : 'text-slate-400 hover:bg-slate-800/70 hover:text-white'"
          >
            <BuildingStorefrontIcon class="w-5 h-5 shrink-0" />
            <span v-if="isSidebarOpen">Kelola Studio</span>
          </Link>

          <!-- Kelola Komik -->
          <Link
            href="/admin/comics"
            class="flex items-center gap-3 px-3 py-2.5 rounded-xl text-sm font-semibold transition-all"
            :class="page.url.startsWith('/admin/comics')
              ? 'bg-amber-500/15 text-amber-400 border border-amber-500/25 shadow-sm'
              : 'text-slate-400 hover:bg-slate-800/70 hover:text-white'"
          >
            <BookOpenIcon class="w-5 h-5 shrink-0" />
            <span v-if="isSidebarOpen">Kelola Komik</span>
          </Link>

          <!-- Kelola Chapter -->
          <Link
            href="/admin/chapters"
            class="flex items-center gap-3 px-3 py-2.5 rounded-xl text-sm font-semibold transition-all"
            :class="page.url.startsWith('/admin/chapters')
              ? 'bg-amber-500/15 text-amber-400 border border-amber-500/25 shadow-sm'
              : 'text-slate-400 hover:bg-slate-800/70 hover:text-white'"
          >
            <RectangleStackIcon class="w-5 h-5 shrink-0" />
            <span v-if="isSidebarOpen">Kelola Chapter</span>
          </Link>

          <!-- Kelola Komentar -->
          <Link
            href="/admin/comments"
            class="flex items-center gap-3 px-3 py-2.5 rounded-xl text-sm font-semibold transition-all"
            :class="page.url.startsWith('/admin/comments')
              ? 'bg-amber-500/15 text-amber-400 border border-amber-500/25 shadow-sm'
              : 'text-slate-400 hover:bg-slate-800/70 hover:text-white'"
          >
            <ChatBubbleLeftRightIcon class="w-5 h-5 shrink-0" />
            <span v-if="isSidebarOpen">Kelola Komentar</span>
          </Link>

          <!-- Kelola User -->
          <Link
            href="/admin/users"
            class="flex items-center gap-3 px-3 py-2.5 rounded-xl text-sm font-semibold transition-all"
            :class="page.url.startsWith('/admin/users')
              ? 'bg-amber-500/15 text-amber-400 border border-amber-500/25 shadow-sm'
              : 'text-slate-400 hover:bg-slate-800/70 hover:text-white'"
          >
            <UserGroupIcon class="w-5 h-5 shrink-0" />
            <span v-if="isSidebarOpen">Kelola User</span>
          </Link>

          <!-- Transaksi -->
          <Link
            href="/admin/transactions"
            class="flex items-center gap-3 px-3 py-2.5 rounded-xl text-sm font-semibold transition-all"
            :class="page.url.startsWith('/admin/transactions')
              ? 'bg-amber-500/15 text-amber-400 border border-amber-500/25 shadow-sm'
              : 'text-slate-400 hover:bg-slate-800/70 hover:text-white'"
          >
            <CreditCardIcon class="w-5 h-5 shrink-0" />
            <span v-if="isSidebarOpen">Transaksi & Payout</span>
          </Link>
        </template>

        <!-- ── Publisher / Creator Navigation ─────────────────────────────── -->
        <template v-else>
          <p class="px-3 pb-2 pt-1 text-[10px] font-extrabold uppercase text-sky-400/70 tracking-widest">
            <span v-if="isSidebarOpen">Creator Studio</span>
            <span v-else class="block w-full border-t border-slate-800/80 my-1" />
          </p>

          <!-- Studio Dashboard -->
          <Link
            href="/publisher/dashboard"
            class="flex items-center gap-3 px-3 py-2.5 rounded-xl text-sm font-semibold transition-all"
            :class="page.url === '/publisher/dashboard'
              ? 'bg-sky-600/20 text-sky-400 border border-sky-500/25 shadow-sm'
              : 'text-slate-400 hover:bg-slate-800/70 hover:text-white'"
          >
            <ChartBarIcon class="w-5 h-5 shrink-0" />
            <span v-if="isSidebarOpen">Studio Dashboard</span>
          </Link>

          <!-- Kelola Studio -->
          <Link
            href="/publisher/profile"
            class="flex items-center gap-3 px-3 py-2.5 rounded-xl text-sm font-semibold transition-all"
            :class="page.url.startsWith('/publisher/profile')
              ? 'bg-sky-600/20 text-sky-400 border border-sky-500/25 shadow-sm'
              : 'text-slate-400 hover:bg-slate-800/70 hover:text-white'"
          >
            <BuildingStorefrontIcon class="w-5 h-5 shrink-0" />
            <span v-if="isSidebarOpen">Kelola Studio</span>
          </Link>

          <!-- Kelola Komik & Chapter -->
          <Link
            href="/publisher/comics"
            class="flex items-center gap-3 px-3 py-2.5 rounded-xl text-sm font-semibold transition-all"
            :class="page.url.startsWith('/publisher/comics') && !page.url.startsWith('/publisher/comics/create')
              ? 'bg-sky-600/20 text-sky-400 border border-sky-500/25 shadow-sm'
              : 'text-slate-400 hover:bg-slate-800/70 hover:text-white'"
          >
            <BookOpenIcon class="w-5 h-5 shrink-0" />
            <span v-if="isSidebarOpen">Kelola Komik & Chapter</span>
          </Link>

          <!-- Cashflow & Wallet -->
          <Link
            href="/publisher/wallet"
            class="flex items-center gap-3 px-3 py-2.5 rounded-xl text-sm font-semibold transition-all"
            :class="page.url.startsWith('/publisher/wallet')
              ? 'bg-sky-600/20 text-sky-400 border border-sky-500/25 shadow-sm'
              : 'text-slate-400 hover:bg-slate-800/70 hover:text-white'"
          >
            <BanknotesIcon class="w-5 h-5 shrink-0" />
            <span v-if="isSidebarOpen">Cashflow & Wallet</span>
          </Link>

          <!-- Ke Admin Panel -->
          <div v-if="user && user.role === 'admin'" class="pt-3 mt-3 border-t border-slate-800/60">
            <Link
              href="/admin/dashboard"
              class="flex items-center gap-3 px-3 py-2.5 rounded-xl text-sm font-semibold text-amber-400 hover:bg-amber-500/10 transition-all"
            >
              <ShieldCheckIcon class="w-5 h-5 shrink-0" />
              <span v-if="isSidebarOpen">Admin Control</span>
            </Link>
          </div>
        </template>
      </nav>

      <!-- User Footer (Klik Avatar / Logo untuk Kelola Akun) -->
      <div class="p-3 border-t border-slate-800/80 flex items-center justify-between shrink-0 bg-slate-900/50">
        <button
          @click="openAccountModal"
          class="flex items-center gap-3 overflow-hidden min-w-0 flex-1 text-left p-1.5 rounded-xl hover:bg-slate-800/80 transition group"
          title="Klik untuk Kelola Akun Saya"
        >
          <div class="w-9 h-9 rounded-xl bg-gradient-to-br from-sky-500 to-indigo-600 text-white font-bold flex items-center justify-center text-sm shrink-0 shadow-md group-hover:ring-2 group-hover:ring-sky-500/50 transition">
            {{ user ? user.name.charAt(0).toUpperCase() : 'U' }}
          </div>
          <div v-if="isSidebarOpen" class="truncate min-w-0 flex-1">
            <h4 class="text-xs font-bold text-white truncate flex items-center justify-between">
              <span>{{ user ? user.name : 'Creator' }}</span>
              <Cog6ToothIcon class="w-3.5 h-3.5 text-slate-500 group-hover:text-sky-400 shrink-0 ml-1 transition" />
            </h4>
            <span class="text-[10px] text-slate-400 capitalize flex items-center gap-1">
              {{ user ? user.role : 'Publisher' }} • <span class="text-sky-400 font-medium">Edit Akun</span>
            </span>
          </div>
        </button>

      </div>
    </aside>

    <!-- Mobile Backdrop -->
    <div
      v-if="isMobileOpen"
      @click="toggleMobileSidebar"
      class="fixed inset-0 z-30 bg-black/60 backdrop-blur-sm lg:hidden"
    />

    <!-- Main Content -->
    <div
      class="flex-1 flex flex-col min-w-0 transition-all duration-300"
      :class="[isSidebarOpen ? 'lg:pl-64' : 'lg:pl-[72px]']"
    >
      <!-- Top Header -->
      <header class="h-16 bg-slate-950/80 backdrop-blur-md border-b border-slate-800/80 px-4 lg:px-8 flex items-center justify-between sticky top-0 z-20">
        <div class="flex items-center gap-3">
          <button @click="toggleMobileSidebar" class="lg:hidden text-slate-400 hover:text-white p-1.5 rounded-lg hover:bg-slate-800 transition">
            <Bars3Icon class="w-5 h-5" />
          </button>
          <span class="text-xs text-slate-500 font-medium hidden sm:block">The ComicRealm Creator Platform</span>
        </div>

        <div class="flex items-center gap-3">
          <button
            @click="openAccountModal"
            class="flex items-center gap-2 text-xs font-semibold px-3 py-1.5 rounded-xl bg-slate-900 border border-slate-800 text-slate-300 hover:text-white hover:border-slate-700 transition"
          >
            <Cog6ToothIcon class="w-3.5 h-3.5 text-sky-400" />
            Kelola Akun
          </button>

          <a
            href="/"
            target="_blank"
            class="flex items-center gap-1.5 text-xs font-semibold px-3 py-1.5 rounded-xl bg-slate-900 border border-slate-800 text-slate-400 hover:text-white hover:border-slate-700 transition"
          >
            <ArrowTopRightOnSquareIcon class="w-3.5 h-3.5" />
            Public Website
          </a>
        </div>
      </header>

      <!-- Page Content -->
      <main class="p-4 lg:p-8 flex-1">
        <slot />
      </main>
    </div>

    <!-- Modal Kelola Akun Saya (Account Settings Modal) -->
    <div v-if="showAccountModal" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/75 backdrop-blur-sm">
      <div class="bg-slate-900 border border-slate-800 rounded-3xl max-w-lg w-full shadow-2xl overflow-hidden flex flex-col">
        <!-- Modal Header -->
        <div class="px-6 py-4 border-b border-slate-800 bg-slate-950 flex items-center justify-between">
          <div class="flex items-center gap-3">
            <div class="w-10 h-10 rounded-2xl bg-gradient-to-br from-sky-500 to-indigo-600 text-white font-extrabold flex items-center justify-center text-base shadow-lg">
              {{ user ? user.name.charAt(0).toUpperCase() : 'U' }}
            </div>
            <div>
              <h3 class="text-base font-extrabold text-white flex items-center gap-2">
                Kelola Akun Saya
              </h3>
              <p class="text-xs text-slate-400">{{ user ? user.email : '' }} • <span class="capitalize text-sky-400 font-semibold">{{ user ? user.role : '' }}</span></p>
            </div>
          </div>
          <button @click="showAccountModal = false" class="text-slate-400 hover:text-white p-1 rounded-lg transition">
            <XMarkIcon class="w-5 h-5" />
          </button>
        </div>

        <!-- Modal Nav Tabs -->
        <div class="flex border-b border-slate-800 bg-slate-950/60 px-6 pt-3 gap-2">
          <button
            @click="activeAccountTab = 'profile'"
            class="px-4 py-2 text-xs font-bold rounded-t-xl transition border-b-2"
            :class="activeAccountTab === 'profile'
              ? 'border-sky-500 text-sky-400 bg-slate-900'
              : 'border-transparent text-slate-400 hover:text-slate-200'"
          >
            <UserIcon class="w-3.5 h-3.5 inline mr-1" /> Informasi Profil
          </button>
          <button
            @click="activeAccountTab = 'password'"
            class="px-4 py-2 text-xs font-bold rounded-t-xl transition border-b-2"
            :class="activeAccountTab === 'password'
              ? 'border-sky-500 text-sky-400 bg-slate-900'
              : 'border-transparent text-slate-400 hover:text-slate-200'"
          >
            <KeyIcon class="w-3.5 h-3.5 inline mr-1" /> Ubah Password
          </button>
        </div>

        <!-- Modal Body -->
        <div class="p-6 space-y-4">
          <!-- TAB 1: EDIT PROFIL -->
          <form v-if="activeAccountTab === 'profile'" @submit.prevent="submitProfileUpdate" class="space-y-4">
            <div class="space-y-1">
              <label class="block text-xs font-semibold text-slate-300">Nama Lengkap *</label>
              <input
                v-model="profileForm.name"
                type="text"
                required
                class="w-full bg-slate-950 border border-slate-800 rounded-xl px-4 py-2.5 text-xs text-white focus:outline-none focus:ring-1 focus:ring-sky-500 font-semibold"
              />
              <span v-if="profileForm.errors.name" class="text-[11px] text-rose-400">{{ profileForm.errors.name }}</span>
            </div>

            <div class="space-y-1">
              <label class="block text-xs font-semibold text-slate-300">Username *</label>
              <div class="relative">
                <span class="absolute left-3.5 top-2.5 text-xs text-slate-500 font-mono">@</span>
                <input
                  v-model="profileForm.username"
                  type="text"
                  required
                  class="w-full bg-slate-950 border border-slate-800 rounded-xl pl-8 pr-4 py-2.5 text-xs text-white font-mono focus:outline-none focus:ring-1 focus:ring-sky-500"
                />
              </div>
              <span v-if="profileForm.errors.username" class="text-[11px] text-rose-400">{{ profileForm.errors.username }}</span>
            </div>

            <div class="space-y-1">
              <label class="block text-xs font-semibold text-slate-300">Email Utama *</label>
              <input
                v-model="profileForm.email"
                type="email"
                required
                class="w-full bg-slate-950 border border-slate-800 rounded-xl px-4 py-2.5 text-xs text-white focus:outline-none focus:ring-1 focus:ring-sky-500"
              />
              <span v-if="profileForm.errors.email" class="text-[11px] text-rose-400">{{ profileForm.errors.email }}</span>
            </div>

            <div class="space-y-1">
              <label class="block text-xs font-semibold text-slate-300">Nomor Telepon / WhatsApp</label>
              <input
                v-model="profileForm.phone"
                type="text"
                placeholder="08123456789"
                class="w-full bg-slate-950 border border-slate-800 rounded-xl px-4 py-2.5 text-xs text-white focus:outline-none focus:ring-1 focus:ring-sky-500 font-mono"
              />
              <span v-if="profileForm.errors.phone" class="text-[11px] text-rose-400">{{ profileForm.errors.phone }}</span>
            </div>

            <div class="pt-2 flex items-center justify-end gap-3">
              <button
                type="submit"
                :disabled="profileForm.processing"
                class="px-6 py-2.5 rounded-xl text-xs font-bold text-white bg-sky-600 hover:bg-sky-500 disabled:opacity-50 transition shadow-lg shadow-sky-600/30"
              >
                {{ profileForm.processing ? 'Memproses...' : 'Simpan Perubahan Profil →' }}
              </button>
            </div>
          </form>

          <!-- TAB 2: UBAH PASSWORD -->
          <form v-else @submit.prevent="submitPasswordUpdate" class="space-y-4">
            <div class="space-y-1">
              <label class="block text-xs font-semibold text-slate-300">Password Saat Ini *</label>
              <input
                v-model="passwordForm.current_password"
                type="password"
                required
                class="w-full bg-slate-950 border border-slate-800 rounded-xl px-4 py-2.5 text-xs text-white focus:outline-none focus:ring-1 focus:ring-sky-500"
              />
              <span v-if="passwordForm.errors.current_password" class="text-[11px] text-rose-400">{{ passwordForm.errors.current_password }}</span>
            </div>

            <div class="space-y-1">
              <label class="block text-xs font-semibold text-slate-300">Password Baru *</label>
              <input
                v-model="passwordForm.password"
                type="password"
                required
                class="w-full bg-slate-950 border border-slate-800 rounded-xl px-4 py-2.5 text-xs text-white focus:outline-none focus:ring-1 focus:ring-sky-500"
              />
              <span v-if="passwordForm.errors.password" class="text-[11px] text-rose-400">{{ passwordForm.errors.password }}</span>
            </div>

            <div class="space-y-1">
              <label class="block text-xs font-semibold text-slate-300">Konfirmasi Password Baru *</label>
              <input
                v-model="passwordForm.password_confirmation"
                type="password"
                required
                class="w-full bg-slate-950 border border-slate-800 rounded-xl px-4 py-2.5 text-xs text-white focus:outline-none focus:ring-1 focus:ring-sky-500"
              />
            </div>

            <div class="pt-2 flex items-center justify-end gap-3">
              <button
                type="submit"
                :disabled="passwordForm.processing"
                class="px-6 py-2.5 rounded-xl text-xs font-bold text-white bg-sky-600 hover:bg-sky-500 disabled:opacity-50 transition shadow-lg shadow-sky-600/30"
              >
                {{ passwordForm.processing ? 'Memproses...' : 'Ubah Password →' }}
              </button>
            </div>
          </form>
        </div>

        <!-- Modal Footer / Logout Option -->
        <div class="px-6 py-3 border-t border-slate-800 bg-slate-950 flex items-center justify-between">
          <Link
            href="/logout"
            method="post"
            as="button"
            class="text-xs font-bold text-rose-400 hover:text-rose-300 flex items-center gap-1.5 py-1 px-2 rounded-lg hover:bg-rose-500/10 transition"
          >
            <ArrowRightOnRectangleIcon class="w-4 h-4" /> Keluar Akun (Logout)
          </Link>
          <button @click="showAccountModal = false" class="px-4 py-1.5 rounded-xl text-xs font-semibold text-slate-400 hover:text-white">
            Tutup
          </button>
        </div>
      </div>
    </div>
  </div>
</template>
