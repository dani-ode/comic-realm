<script setup lang="ts">
import { ref } from 'vue';
import { Link, usePage } from '@inertiajs/vue3';
import AppHeader from '@/Components/Admin/layout/AppHeader.vue';
import AppSidebar from '@/Components/Admin/layout/AppSidebar.vue';
import Backdrop from '@/Components/Admin/layout/Backdrop.vue';

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
    <!-- Sidebar for Admin & Creator Studio -->
    <aside
      class="fixed inset-y-0 left-0 z-40 bg-slate-900 border-r border-slate-800/80 transition-all duration-300 flex flex-col justify-between"
      :class="[
        isSidebarOpen ? 'w-64' : 'w-20',
        isMobileOpen ? 'translate-x-0' : '-translate-x-full lg:translate-x-0'
      ]"
    >
      <!-- Logo Header -->
      <div class="h-16 flex items-center justify-between px-4 border-b border-slate-800/80">
        <Link href="/" class="flex items-center gap-2 overflow-hidden">
          <span class="text-2xl">🎨</span>
          <span v-if="isSidebarOpen" class="font-extrabold text-lg bg-gradient-to-r from-sky-400 via-indigo-400 to-purple-400 bg-clip-text text-transparent truncate">
            ComicRealm
          </span>
        </Link>

        <button @click="toggleSidebar" class="hidden lg:block text-slate-400 hover:text-white p-1">
          ‹
        </button>
      </div>

      <!-- Navigation Links -->
      <nav class="flex-1 px-3 py-6 space-y-1 overflow-y-auto">
        <Link
          href="/publisher/dashboard"
          class="flex items-center gap-3 px-3 py-2.5 rounded-xl text-sm font-semibold transition"
          :class="page.url.startsWith('/publisher/dashboard') ? 'bg-sky-600 text-white shadow-lg shadow-sky-600/30' : 'text-slate-400 hover:bg-slate-800/60 hover:text-white'"
        >
          <span class="text-lg">📊</span>
          <span v-if="isSidebarOpen">Studio Dashboard</span>
        </Link>

        <Link
          href="/publisher/comics/create"
          class="flex items-center gap-3 px-3 py-2.5 rounded-xl text-sm font-semibold transition"
          :class="page.url.startsWith('/publisher/comics/create') ? 'bg-sky-600 text-white shadow-lg shadow-sky-600/30' : 'text-slate-400 hover:bg-slate-800/60 hover:text-white'"
        >
          <span class="text-lg">➕</span>
          <span v-if="isSidebarOpen">New Comic Series</span>
        </Link>

        <div v-if="user && user.role === 'admin'" class="pt-4 border-t border-slate-800/80 space-y-1">
          <span v-if="isSidebarOpen" class="px-3 text-[10px] font-extrabold uppercase text-slate-500 tracking-wider">
            Super Admin
          </span>
          <Link
            href="/admin/dashboard"
            class="flex items-center gap-3 px-3 py-2.5 rounded-xl text-sm font-semibold text-amber-400 hover:bg-amber-500/10 transition"
          >
            <span class="text-lg">🛡️</span>
            <span v-if="isSidebarOpen">Admin Control</span>
          </Link>
        </div>
      </nav>

      <!-- User Footer Profile -->
      <div class="p-3 border-t border-slate-800/80 flex items-center justify-between">
        <div class="flex items-center gap-3 overflow-hidden">
          <div class="w-9 h-9 rounded-xl bg-sky-600 text-white font-bold flex items-center justify-center text-sm shrink-0">
            {{ user ? user.name.charAt(0).toUpperCase() : 'U' }}
          </div>
          <div v-if="isSidebarOpen" class="truncate">
            <h4 class="text-xs font-bold text-white truncate">{{ user ? user.name : 'Creator' }}</h4>
            <span class="text-[10px] text-sky-400 capitalize">{{ user ? user.role : 'Publisher' }}</span>
          </div>
        </div>
      </div>
    </aside>

    <!-- Backdrop for Mobile Sidebar -->
    <div v-if="isMobileOpen" @click="toggleMobileSidebar" class="fixed inset-0 z-30 bg-black/60 backdrop-blur-sm lg:hidden"></div>

    <!-- Main Content Area -->
    <div
      class="flex-1 flex flex-col min-w-0 transition-all duration-300"
      :class="[isSidebarOpen ? 'lg:pl-64' : 'lg:pl-20']"
    >
      <!-- Top App Header -->
      <header class="h-16 bg-slate-950/80 backdrop-blur-md border-b border-slate-800/80 px-4 lg:px-8 flex items-center justify-between sticky top-0 z-20">
        <div class="flex items-center gap-3">
          <button @click="toggleMobileSidebar" class="lg:hidden text-slate-400 hover:text-white p-2">
            ☰
          </button>
          <span class="text-xs text-slate-400 font-medium">The ComicRealm Creator Platform</span>
        </div>

        <div class="flex items-center gap-4">
          <Link href="/" class="text-xs font-semibold px-3 py-1.5 rounded-xl bg-slate-900 border border-slate-800 text-slate-300 hover:text-white">
            Public Website →
          </Link>
        </div>
      </header>

      <!-- Main Page Slot Content -->
      <main class="p-4 lg:p-8 flex-1">
        <slot />
      </main>
    </div>
  </div>
</template>
