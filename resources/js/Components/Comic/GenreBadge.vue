<script setup lang="ts">
defineProps<{
  name: string;
  slug?: string;
}>();
</script>

<template>
  <span class="inline-block px-2.5 py-1 text-xs font-semibold rounded-lg bg-sky-500/10 text-sky-400 border border-sky-500/20">
    {{ name }}
  </span>
</template>
