<script setup lang="ts">
import { Head, useForm, Link } from '@inertiajs/vue3';
import { ref } from 'vue';
import {
  ArrowLeftIcon,
  PencilSquareIcon,
  DocumentDuplicateIcon,
  TrashIcon,
  PlusIcon,
  CheckCircleIcon,
} from '@heroicons/vue/24/outline';

interface ChapterPage {
  id: number;
  page_number: number;
  image_url: string;
}

interface Chapter {
  id: number;
  comic_id: number;
  chapter_number: number;
  title: string;
  is_free: boolean;
  price: number;
  pages?: ChapterPage[];
}

interface Comic {
  id: number;
  title: string;
  slug: string;
}

const props = defineProps<{
  comic: Comic;
  chapter: Chapter;
}>();

const form = useForm({
  title: props.chapter.title,
  chapter_number: props.chapter.chapter_number,
  is_free: props.chapter.is_free,
  price: props.chapter.price,
});

const submitUpdate = () => {
  form.post(`/publisher/comics/${props.comic.id}/chapters/${props.chapter.id}/update`);
};
</script>

<template>
  <Head :title="`Edit Chapter ${chapter.chapter_number} - ${comic.title}`" />

  <div class="min-h-screen bg-slate-950 text-slate-100 flex flex-col justify-center py-12 sm:px-6 lg:px-8">
    <div class="sm:mx-auto sm:w-full sm:max-w-2xl text-center">
      <Link href="/publisher/comics" class="inline-flex items-center gap-1.5 text-xs font-bold text-sky-400 hover:underline">
        <ArrowLeftIcon class="w-3.5 h-3.5" /> Kembali ke Kelola Komik
      </Link>
      <h2 class="mt-2 text-3xl font-extrabold tracking-tight text-white flex items-center justify-center gap-3">
        <PencilSquareIcon class="w-8 h-8 text-sky-400" />
        Edit Chapter {{ chapter.chapter_number }}: {{ comic.title }}
      </h2>
      <p class="text-sm text-slate-400 mt-1">Ubah judul bab, nomor urut bab, status akses gratis/berbayar, dan harga chapter</p>
    </div>

    <div class="mt-8 sm:mx-auto sm:w-full sm:max-w-2xl">
      <div class="bg-slate-900 border border-slate-800 py-8 px-6 shadow-2xl sm:rounded-2xl sm:px-10">
        <form class="space-y-6" @submit.prevent="submitUpdate">
          <!-- Nomor Urut Bab -->
          <div>
            <label class="block text-xs font-bold text-slate-300">Nomor Bab *</label>
            <input
              v-model="form.chapter_number"
              type="number"
              step="0.1"
              required
              class="mt-1 block w-full rounded-xl bg-slate-950 border border-slate-800 px-3.5 py-2.5 text-white focus:border-sky-500 focus:outline-none text-sm font-extrabold font-mono"
            />
          </div>

          <!-- Judul Bab -->
          <div>
            <label class="block text-xs font-bold text-slate-300">Judul Bab / Chapter Title *</label>
            <input
              v-model="form.title"
              type="text"
              required
              class="mt-1 block w-full rounded-xl bg-slate-950 border border-slate-800 px-3.5 py-2.5 text-white focus:border-sky-500 focus:outline-none text-sm font-semibold"
            />
          </div>

          <!-- Status Gratis / Berbayar -->
          <div class="p-4 bg-slate-950 border border-slate-800 rounded-xl space-y-4">
            <div class="flex items-center justify-between">
              <div>
                <label class="text-xs font-bold text-white block">Akses Bab Gratis?</label>
                <p class="text-[11px] text-slate-400">Centang jika pembaca dapat membaca bab ini tanpa biaya koin/pembelian.</p>
              </div>
              <input
                type="checkbox"
                v-model="form.is_free"
                class="w-5 h-5 rounded bg-slate-900 border-slate-800 text-sky-600 focus:ring-sky-500 cursor-pointer"
              />
            </div>

            <!-- Harga (Jika Berbayar) -->
            <div v-if="!form.is_free" class="pt-2 border-t border-slate-800 space-y-1">
              <label class="block text-xs font-bold text-slate-300">Harga Bab (Rp) *</label>
              <input
                v-model="form.price"
                type="number"
                min="0"
                step="500"
                required
                placeholder="5000"
                class="block w-full rounded-xl bg-slate-900 border border-slate-800 px-3.5 py-2.5 text-amber-400 font-extrabold font-mono text-sm focus:border-amber-500 focus:outline-none"
              />
            </div>
          </div>

          <!-- Info Halaman Gambar yang Terdaftar -->
          <div v-if="chapter.pages && chapter.pages.length" class="space-y-2 p-4 bg-slate-950 border border-slate-800 rounded-xl">
            <h3 class="text-xs font-bold text-slate-300 flex items-center gap-2">
              <DocumentDuplicateIcon class="w-4 h-4 text-purple-400" />
              Jumlah Halaman Komik: <span class="text-white font-extrabold">{{ chapter.pages.length }} Halaman</span>
            </h3>
            <div class="grid grid-cols-6 gap-2 pt-2">
              <div v-for="p in chapter.pages.slice(0, 12)" :key="p.id" class="aspect-[2/3] bg-slate-900 rounded-lg overflow-hidden border border-slate-800 relative">
                <img :src="p.image_url" :alt="`Page ${p.page_number}`" class="w-full h-full object-cover" />
                <span class="absolute bottom-0 right-0 bg-black/70 text-white text-[9px] px-1 font-bold">#{{ p.page_number }}</span>
              </div>
            </div>
          </div>

          <button
            type="submit"
            :disabled="form.processing"
            class="w-full flex justify-center py-3.5 px-4 rounded-xl text-sm font-bold text-white bg-sky-600 hover:bg-sky-500 disabled:opacity-50 transition shadow-lg shadow-sky-600/30"
          >
            {{ form.processing ? 'Menyimpan Bab...' : 'Simpan Perubahan Bab →' }}
          </button>
        </form>
      </div>
    </div>
  </div>
</template>
