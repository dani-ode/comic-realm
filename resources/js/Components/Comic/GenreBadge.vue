<script setup lang="ts">
const props = defineProps<{
  name?: string;
  slug?: string;
  genre?: {
    name: string;
    slug?: string;
  };
}>();
</script>

<template>
  <span class="inline-block px-3 py-1.5 text-xs font-semibold rounded-xl bg-sky-500/10 text-sky-400 border border-sky-500/20 whitespace-nowrap">
    {{ genre ? genre.name : name }}
  </span>
</template>
