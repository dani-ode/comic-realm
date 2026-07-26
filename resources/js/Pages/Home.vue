<script setup lang="ts">
import { Head } from '@inertiajs/vue3';
import { RocketLaunchIcon } from '@heroicons/vue/24/outline';

defineProps<{
  appName?: string;
}>();
</script>

<template>
  <Head title="Home - Read. Create. Publish." />

  <div class="min-h-screen bg-slate-950 text-white flex flex-col items-center justify-center p-6 text-center">
    <div class="max-w-2xl">
      <div class="inline-block px-4 py-1.5 rounded-full bg-sky-500/10 border border-sky-500/30 text-sky-400 text-sm font-medium mb-6">
        <RocketLaunchIcon class="w-5 h-5 inline-block mr-1"/> The ComicRealm Engine Initialized
      </div>
      <h1 class="text-4xl sm:text-6xl font-extrabold tracking-tight mb-4 bg-gradient-to-r from-sky-400 via-indigo-400 to-purple-400 bg-clip-text text-transparent">
        The ComicRealm
      </h1>
      <p class="text-slate-400 text-lg sm:text-xl mb-8">
        Webcomic Marketplace, Continuous Vertical Reader & Creator Economy Platform built with Laravel 13, Inertia.js, Vue 3, TypeScript & TriPay.
      </p>
      <div class="flex flex-wrap items-center justify-center gap-4">
        <a href="#" class="px-6 py-3 rounded-xl bg-sky-600 hover:bg-sky-500 font-semibold transition shadow-lg shadow-sky-600/30">
          Browse Catalog
        </a>
        <a href="#" class="px-6 py-3 rounded-xl bg-slate-800 hover:bg-slate-700 border border-slate-700 font-semibold transition">
          Become Publisher
        </a>
      </div>
    </div>
  </div>
</template>
