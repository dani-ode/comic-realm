<script setup lang="ts">
import { Link, usePage } from '@inertiajs/vue3';
import { computed } from 'vue';

const page = usePage();
const user = computed(() => (page.props.auth as any)?.user);
const cartCount = computed(() => (page.props as any)?.cartCount || 0);
</script>

<template>
  <div class="min-h-screen bg-slate-950 text-slate-100 flex flex-col">
    <!-- Navbar Header -->
    <header class="sticky top-0 z-50 bg-slate-950/80 backdrop-blur-md border-b border-slate-800/80 px-4 lg:px-8 py-3.5 flex items-center justify-between">
      <Link href="/" class="text-xl font-extrabold bg-gradient-to-r from-sky-400 via-indigo-400 to-purple-400 bg-clip-text text-transparent">
        The ComicRealm
      </Link>

      <nav class="hidden md:flex items-center gap-6 text-sm font-medium">
        <Link href="/" class="text-slate-300 hover:text-white transition" :class="page.url === '/' ? 'text-sky-400 font-bold' : ''">Home</Link>
        <Link href="/comics" class="text-slate-300 hover:text-white transition" :class="page.url.startsWith('/comics') ? 'text-sky-400 font-bold' : ''">Catalog</Link>
        <Link href="/library" class="text-slate-300 hover:text-white transition" :class="page.url.startsWith('/library') ? 'text-sky-400 font-bold' : ''">My Library 📚</Link>
      </nav>

      <div class="flex items-center gap-3">
        <Link href="/cart" class="relative text-xs font-semibold px-4 py-2 rounded-xl bg-slate-900 border border-slate-800 text-slate-300 hover:text-white transition">
          Cart 🛒
          <span v-if="cartCount > 0" class="absolute -top-1.5 -right-1.5 px-1.5 py-0.5 text-[10px] font-bold rounded-full bg-sky-500 text-white">
            {{ cartCount }}
          </span>
        </Link>

        <div v-if="user" class="flex items-center gap-2">
          <Link href="/publisher/dashboard" class="text-xs font-semibold px-3 py-1.5 rounded-xl bg-sky-600/20 text-sky-400 border border-sky-500/30 hover:bg-sky-600/30 transition">
            Studio 🎨
          </Link>
        </div>
        <div v-else class="flex items-center gap-2">
          <Link href="/login" class="text-xs font-semibold px-3 py-1.5 text-slate-300 hover:text-white">Sign In</Link>
          <Link href="/register" class="text-xs font-bold px-4 py-2 rounded-xl bg-sky-600 hover:bg-sky-500 text-white transition">Register</Link>
        </div>
      </div>
    </header>

    <!-- Main Content Area -->
    <main class="flex-1">
      <slot />
    </main>

    <!-- Footer -->
    <footer class="border-t border-slate-800/80 bg-slate-950 py-8 px-4 lg:px-8 text-center text-xs text-slate-500 space-y-2">
      <p>© 2026 The ComicRealm — Premium Webcomic Streaming & TriPay Monetization Platform</p>
      <div class="flex justify-center gap-4 text-slate-400">
        <Link href="/comics" class="hover:underline">Browse Comics</Link>
        <Link href="/publisher/apply" class="hover:underline">Publisher Studio</Link>
      </div>
    </footer>
  </div>
</template>
