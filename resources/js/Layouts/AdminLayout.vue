<script setup lang="ts">
import { ref } from 'vue';
import { Link, usePage } from '@inertiajs/vue3';
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
  SwatchIcon,
  ArrowTopRightOnSquareIcon,
  BanknotesIcon,
  BuildingStorefrontIcon,
  RectangleStackIcon,
  UserGroupIcon,
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
const user = page.props.auth ? (page.props.auth as any).user : null;
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
      <div class="h-16 flex items-center justify-between px-4 border-b border-slate-800/80 shrink-0">
        <Link href="/" class="flex items-center gap-2.5 overflow-hidden min-w-0">
          <div class="w-8 h-8 rounded-xl bg-gradient-to-br from-amber-400 to-orange-500 flex items-center justify-center shrink-0 shadow-lg shadow-amber-500/20">
            <SwatchIcon class="w-4 h-4 text-white" />
          </div>
          <span v-if="isSidebarOpen" class="font-extrabold text-base bg-gradient-to-r from-amber-400 to-orange-400 bg-clip-text text-transparent truncate">
            ComicRealm
          </span>
        </Link>

        <button
          @click="toggleSidebar"
          class="hidden lg:flex items-center justify-center w-6 h-6 rounded-lg text-slate-500 hover:text-white hover:bg-slate-800 transition"
        >
          <ChevronLeftIcon v-if="isSidebarOpen" class="w-4 h-4" />
          <ChevronRightIcon v-else class="w-4 h-4" />
        </button>
      </div>

      <!-- Navigation -->
      <nav class="flex-1 px-2.5 py-5 space-y-0.5 overflow-y-auto">

        <!-- ── Admin Navigation ─────────────────────────────────── -->
        <template v-if="page.url.startsWith('/admin')">
          <p class="px-3 pb-2 pt-1 text-[10px] font-extrabold uppercase text-amber-400/70 tracking-widest">
            <span v-if="isSidebarOpen">Admin Panel</span>
            <span v-else class="block w-full border-t border-slate-800/80 my-1" />
          </p>

          <!-- Dashboard -->
          <Link
            href="/admin/dashboard"
            class="flex items-center gap-3 px-3 py-2.5 rounded-xl text-sm font-semibold transition-all group"
            :class="page.url === '/admin/dashboard'
              ? 'bg-amber-500/15 text-amber-400 border border-amber-500/25 shadow-sm'
              : 'text-slate-400 hover:bg-slate-800/70 hover:text-white'"
          >
            <ShieldCheckIcon class="w-5 h-5 shrink-0" />
            <span v-if="isSidebarOpen">Dashboard Admin</span>
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
            :class="page.url.startsWith('/publisher/dashboard')
              ? 'bg-sky-600/20 text-sky-400 border border-sky-500/25 shadow-sm'
              : 'text-slate-400 hover:bg-slate-800/70 hover:text-white'"
          >
            <ChartBarIcon class="w-5 h-5 shrink-0" />
            <span v-if="isSidebarOpen">Studio Dashboard</span>
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

          <!-- New Comic -->
          <Link
            href="/publisher/comics/create"
            class="flex items-center gap-3 px-3 py-2.5 rounded-xl text-sm font-semibold transition-all"
            :class="page.url.startsWith('/publisher/comics/create')
              ? 'bg-sky-600/20 text-sky-400 border border-sky-500/25 shadow-sm'
              : 'text-slate-400 hover:bg-slate-800/70 hover:text-white'"
          >
            <PlusCircleIcon class="w-5 h-5 shrink-0" />
            <span v-if="isSidebarOpen">New Comic Series</span>
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

      <!-- User Footer -->
      <div class="p-3 border-t border-slate-800/80 flex items-center justify-between shrink-0">
        <div class="flex items-center gap-3 overflow-hidden min-w-0">
          <div class="w-8 h-8 rounded-xl bg-gradient-to-br from-sky-500 to-indigo-600 text-white font-bold flex items-center justify-center text-sm shrink-0 shadow-md">
            {{ user ? user.name.charAt(0).toUpperCase() : 'U' }}
          </div>
          <div v-if="isSidebarOpen" class="truncate min-w-0">
            <h4 class="text-xs font-bold text-white truncate">{{ user ? user.name : 'Creator' }}</h4>
            <span class="text-[10px] text-slate-400 capitalize">{{ user ? user.role : 'Publisher' }}</span>
          </div>
        </div>

        <!-- Link ke site publik -->
        <a
          v-if="isSidebarOpen"
          href="/"
          target="_blank"
          class="shrink-0 text-slate-500 hover:text-white transition"
          title="Buka website publik"
        >
          <ArrowTopRightOnSquareIcon class="w-4 h-4" />
        </a>
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
  </div>
</template>
