<script setup lang="ts">
import { ref, computed } from 'vue';
import { Head, useForm, Link } from '@inertiajs/vue3';
import { InformationCircleIcon, DocumentTextIcon, PhotoIcon, LinkIcon } from '@heroicons/vue/24/outline';

interface Comic {
  id: number;
  title: string;
  slug: string;
}

interface LatestChapter {
  id: number;
  title: string;
  chapter_number: number;
}

const props = defineProps<{
  comic: Comic;
  latestChapter?: LatestChapter | null;
}>();

const nextChapterNumber = props.latestChapter
  ? Number((props.latestChapter.chapter_number + 1).toFixed(1))
  : 1.0;

const uploadMode = ref<'txt' | 'urls' | 'files'>('txt');
const txtLineCount = ref(0);

const form = useForm({
  title: '',
  chapter_number: nextChapterNumber,
  is_free: true,
  price: 0,
  pages: [] as File[],
  txt_file: null as File | null,
  url_list: '',
});

const handleFileChange = (e: Event) => {
  const target = e.target as HTMLInputElement;
  if (target.files) {
    form.pages = Array.from(target.files);
  }
};

const handleTxtFileChange = (e: Event) => {
  const target = e.target as HTMLInputElement;
  if (target.files && target.files[0]) {
    const file = target.files[0];
    form.txt_file = file;
    const reader = new FileReader();
    reader.onload = (evt) => {
      const content = (evt.target?.result as string) || '';
      const lines = content
        .split('\n')
        .map((l) => l.trim())
        .filter((l) => l.startsWith('http://') || l.startsWith('https://'));
      txtLineCount.value = lines.length;
    };
    reader.readAsText(file);
  } else {
    form.txt_file = null;
    txtLineCount.value = 0;
  }
};

const parsedUrlCount = computed(() => {
  if (!form.url_list) return 0;
  return form.url_list
    .split('\n')
    .map((l) => l.trim())
    .filter((l) => l.startsWith('http://') || l.startsWith('https://')).length;
});

const submit = () => {
  if (uploadMode.value !== 'files') form.pages = [];
  if (uploadMode.value !== 'txt') form.txt_file = null;
  if (uploadMode.value !== 'urls') form.url_list = '';

  form.post(`/publisher/comics/${props.comic.id}/chapters`);
};
</script>

<template>
  <Head :title="`Add Chapter - ${comic.title}`" />

  <div class="min-h-screen bg-slate-950 text-slate-100 flex flex-col justify-center py-12 sm:px-6 lg:px-8">
    <div class="sm:mx-auto sm:w-full sm:max-w-2xl text-center">
      <Link href="/publisher/dashboard" class="text-xs font-semibold text-sky-400 hover:underline">← Back to Dashboard</Link>
      <h2 class="mt-2 text-3xl font-extrabold tracking-tight text-white">Publish New Chapter</h2>
      <p class="text-sm text-slate-400 mt-1">Series: <strong class="text-white">{{ comic.title }}</strong></p>
    </div>

    <div class="mt-8 sm:mx-auto sm:w-full sm:max-w-2xl space-y-4">
      <!-- Informasi Chapter Terakhir -->
      <div v-if="latestChapter" class="bg-slate-900 border border-sky-500/30 p-4 rounded-2xl flex items-center justify-between gap-4 shadow-lg shadow-sky-950/20">
        <div class="flex items-center gap-3">
          <div class="w-10 h-10 rounded-xl bg-sky-500/15 border border-sky-500/30 text-sky-400 font-extrabold flex items-center justify-center text-sm shrink-0">
            #{{ latestChapter.chapter_number }}
          </div>
          <div>
            <span class="text-[10px] font-extrabold uppercase text-sky-400 tracking-wider flex items-center gap-1">
              <InformationCircleIcon class="w-3.5 h-3.5" /> Chapter Terakhir Terbit
            </span>
            <h4 class="text-sm font-bold text-white">Bab {{ latestChapter.chapter_number }}: {{ latestChapter.title }}</h4>
          </div>
        </div>
        <div class="text-right hidden sm:block">
          <span class="text-[10px] text-slate-400 uppercase font-semibold">Rekomendasi Bab Baru</span>
          <p class="text-xs font-extrabold text-amber-400">Bab {{ nextChapterNumber }}</p>
        </div>
      </div>

      <div class="bg-slate-900 border border-slate-800 py-8 px-6 shadow-xl sm:rounded-2xl sm:px-10">
        <form class="space-y-5" @submit.prevent="submit">
          <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
            <div>
              <label class="block text-xs font-medium text-slate-400">Chapter Number</label>
              <input
                v-model="form.chapter_number"
                type="number"
                step="0.1"
                required
                class="mt-1 block w-full rounded-xl bg-slate-950 border border-slate-800 px-3.5 py-2.5 text-white font-mono font-bold text-sm focus:outline-none focus:border-sky-500"
                placeholder="1.0"
              />
            </div>

            <div>
              <label class="block text-xs font-medium text-slate-400">Chapter Title</label>
              <input
                v-model="form.title"
                type="text"
                required
                class="mt-1 block w-full rounded-xl bg-slate-950 border border-slate-800 px-3.5 py-2.5 text-white text-sm focus:outline-none focus:border-sky-500"
                placeholder="misal: Pertarungan Sengit di Kota"
              />
            </div>
          </div>

          <!-- Free vs Paid Pricing -->
          <div class="p-4 bg-slate-950 border border-slate-800 rounded-xl space-y-3">
            <label class="flex items-center gap-2 text-sm font-bold text-white cursor-pointer">
              <input
                v-model="form.is_free"
                type="checkbox"
                class="rounded bg-slate-900 border-slate-800 text-sky-600 focus:ring-sky-500"
              />
              <span>Free Chapter (Available to All Readers)</span>
            </label>

            <div v-if="!form.is_free" class="pt-2">
              <label class="block text-xs font-medium text-slate-400">Chapter Price (IDR)</label>
              <input
                v-model="form.price"
                type="number"
                min="1000"
                step="1000"
                required
                class="mt-1 block w-full rounded-xl bg-slate-900 border border-slate-800 px-3.5 py-2 text-amber-400 font-bold text-sm"
                placeholder="5000"
              />
            </div>
          </div>

          <!-- Input Mode Selector (TXT Link vs Direct URLs vs Image Files) -->
          <div class="space-y-3">
            <label class="block text-sm font-bold text-slate-200">
              Metode Unggah Halaman Komik
            </label>
            <div class="grid grid-cols-3 gap-2 bg-slate-950 p-1.5 rounded-xl border border-slate-800 text-xs font-bold">
              <button
                type="button"
                @click="uploadMode = 'txt'"
                :class="uploadMode === 'txt' ? 'bg-sky-600 text-white shadow-md' : 'text-slate-400 hover:text-slate-200'"
                class="py-2.5 rounded-lg transition flex items-center justify-center gap-1.5"
              >
                <DocumentTextIcon class="w-4 h-4" /> File .txt Link
              </button>
              <button
                type="button"
                @click="uploadMode = 'urls'"
                :class="uploadMode === 'urls' ? 'bg-sky-600 text-white shadow-md' : 'text-slate-400 hover:text-slate-200'"
                class="py-2.5 rounded-lg transition flex items-center justify-center gap-1.5"
              >
                <LinkIcon class="w-4 h-4" /> Tempel URL
              </button>
              <button
                type="button"
                @click="uploadMode = 'files'"
                :class="uploadMode === 'files' ? 'bg-sky-600 text-white shadow-md' : 'text-slate-400 hover:text-slate-200'"
                class="py-2.5 rounded-lg transition flex items-center justify-center gap-1.5"
              >
                <PhotoIcon class="w-4 h-4" /> Gambar WebP/PNG
              </button>
            </div>

            <!-- Mode 1: Upload File .txt -->
            <div v-if="uploadMode === 'txt'" class="p-4 bg-slate-950 border border-slate-800 rounded-xl space-y-2">
              <label class="block text-xs font-bold text-slate-300">
                Upload File `.txt` (Berisi Link Gambar CDN/Server Lain)
              </label>
              <input
                type="file"
                accept=".txt,text/plain"
                @change="handleTxtFileChange"
                class="block w-full text-xs text-slate-400 file:mr-4 file:py-2 file:px-3 file:rounded-lg file:border-0 file:text-xs file:font-semibold file:bg-sky-600 file:text-white hover:file:bg-sky-500 bg-slate-900 border border-slate-800 rounded-xl p-2"
              />
              <p v-if="txtLineCount > 0" class="text-xs font-bold text-emerald-400 flex items-center gap-1">
                ✓ Terdeteksi {{ txtLineCount }} link URL gambar siap di-import.
              </p>
              <p class="text-[11px] text-slate-500">
                Format file: 1 link URL per baris (contoh: <code>https://cdn.example.com/ch1/001.webp</code>).
              </p>
            </div>

            <!-- Mode 2: Tempel URL Textarea -->
            <div v-else-if="uploadMode === 'urls'" class="p-4 bg-slate-950 border border-slate-800 rounded-xl space-y-2">
              <label class="block text-xs font-bold text-slate-300">
                Tempelkan Daftar Link URL Gambar (1 Link Per Baris)
              </label>
              <textarea
                v-model="form.url_list"
                rows="6"
                placeholder="https://cdn.example.com/ch1/001.webp&#10;https://cdn.example.com/ch1/002.webp&#10;https://cdn.example.com/ch1/003.webp"
                class="w-full bg-slate-900 border border-slate-800 rounded-xl p-3 text-xs font-mono text-slate-200 focus:outline-none focus:border-sky-500"
              ></textarea>
              <p v-if="parsedUrlCount > 0" class="text-xs font-bold text-emerald-400">
                ✓ Terdeteksi {{ parsedUrlCount }} link URL valid.
              </p>
            </div>

            <!-- Mode 3: Upload Image Files -->
            <div v-else class="p-4 bg-slate-950 border border-slate-800 rounded-xl space-y-2">
              <label class="block text-xs font-bold text-slate-300">
                Upload File Gambar (WebP/PNG/JPG)
              </label>
              <input
                type="file"
                multiple
                accept="image/webp,image/png,image/jpeg"
                @change="handleFileChange"
                class="block w-full text-xs text-slate-400 file:mr-4 file:py-2 file:px-3 file:rounded-lg file:border-0 file:text-xs file:font-semibold file:bg-sky-600 file:text-white hover:file:bg-sky-500 bg-slate-900 border border-slate-800 rounded-xl p-2"
              />
              <p class="text-xs text-slate-500">
                Memilih {{ form.pages.length }} file gambar.
              </p>
            </div>
          </div>

          <button
            type="submit"
            :disabled="form.processing"
            class="w-full flex justify-center py-3.5 px-4 rounded-xl text-sm font-bold text-white bg-sky-600 hover:bg-sky-500 transition shadow-lg shadow-sky-600/30"
          >
            {{ form.processing ? 'Publishing Chapter & Saving Pages...' : 'Publish Chapter →' }}
          </button>
        </form>
      </div>
    </div>
  </div>
</template>
