<script setup lang="ts">
defineProps<{
  slotIndex?: number;
}>();
</script>

<template>
  <div class="w-full max-w-3xl mx-auto my-6 p-6 bg-slate-900/80 border border-dashed border-slate-700/60 rounded-2xl text-center flex flex-col items-center justify-center text-slate-400 text-xs gap-1.5 shadow-inner">
    <span class="px-2 py-0.5 rounded bg-slate-800 text-[10px] uppercase tracking-wider text-slate-500 font-bold">Advertisement</span>
    <p class="font-medium text-slate-300">Enjoying the webcomic? Support The ComicRealm</p>
  </div>
</template>
