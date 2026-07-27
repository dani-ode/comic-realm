<script setup lang="ts">
import { Head, useForm, Link } from '@inertiajs/vue3';
import { ref } from 'vue';
import axios from 'axios';
import {
  ArrowLeftIcon,
  PencilSquareIcon,
  DocumentDuplicateIcon,
  TrashIcon,
  PlusIcon,
  CheckCircleIcon,
  ArrowLeftIcon as MoveLeftIcon,
  ArrowRightIcon as MoveRightIcon,
  PhotoIcon,
  ArrowDownTrayIcon,
  ArrowPathIcon,
  ExclamationTriangleIcon,
} from '@heroicons/vue/24/outline';

interface ChapterPage {
  id: number;
  page_number: number;
  image_url: string;
  formatted_url?: string;
  image_path?: string;
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

const pagesList = ref<ChapterPage[]>(props.chapter.pages ? [...props.chapter.pages] : []);
const isUploading = ref<boolean>(false);
const isProcessing = ref<boolean>(false);
const statusMessage = ref<string | null>(null);
const errorMessage = ref<string | null>(null);

const activeInsertAfter = ref<number | null>(null);
const insertFileInput = ref<HTMLInputElement | null>(null);
const appendFileInput = ref<HTMLInputElement | null>(null);

// Form khusus metadata (Nomor bab, Judul bab, Harga)
const form = useForm({
  title: props.chapter.title,
  chapter_number: props.chapter.chapter_number,
  is_free: props.chapter.is_free,
  price: props.chapter.price,
});

const submitUpdateMetadata = () => {
  form.post(`/publisher/comics/${props.comic.id}/chapters/${props.chapter.id}/update`);
};

// Realtime Delete Page
const deletePageRealtime = async (pageId: number, pageNum: number) => {
  if (!confirm(`Apakah Anda yakin ingin menghapus Halaman #${pageNum}?`)) return;

  isProcessing.value = true;
  errorMessage.value = null;
  statusMessage.value = null;

  try {
    const res = await axios.delete(
      `/publisher/comics/${props.comic.id}/chapters/${props.chapter.id}/pages/${pageId}`
    );
    if (res.data.success) {
      pagesList.value = res.data.pages;
      statusMessage.value = `Halaman #${pageNum} berhasil dihapus.`;
    }
  } catch (err: any) {
    errorMessage.value = err.response?.data?.message || 'Gagal menghapus halaman.';
  } finally {
    isProcessing.value = false;
  }
};

// Realtime Delete All Pages
const deleteAllPagesRealtime = async () => {
  if (!confirm(`Apakah Anda yakin ingin menghapus SEMUA (${pagesList.value.length}) halaman gambar pada bab ini? Tindakan ini tidak dapat dibatalkan.`)) return;

  isProcessing.value = true;
  errorMessage.value = null;
  statusMessage.value = null;

  try {
    const res = await axios.delete(
      `/publisher/comics/${props.comic.id}/chapters/${props.chapter.id}/pages-all`
    );
    if (res.data.success) {
      pagesList.value = [];
      statusMessage.value = 'Semua halaman gambar berhasil dihapus.';
    }
  } catch (err: any) {
    errorMessage.value = err.response?.data?.message || 'Gagal menghapus semua halaman.';
  } finally {
    isProcessing.value = false;
  }
};

// Realtime Move Page (Left/Right)
const movePagePosition = async (index: number, direction: 'left' | 'right') => {
  const targetIndex = direction === 'left' ? index - 1 : index + 1;
  if (targetIndex < 0 || targetIndex >= pagesList.value.length) return;

  // Swap in local array
  const temp = pagesList.value[index];
  pagesList.value[index] = pagesList.value[targetIndex];
  pagesList.value[targetIndex] = temp;

  // Sync reorder to backend
  isProcessing.value = true;
  errorMessage.value = null;
  statusMessage.value = null;

  try {
    const pageIdsInOrder = pagesList.value.map((p) => p.id);
    const res = await axios.post(
      `/publisher/comics/${props.comic.id}/chapters/${props.chapter.id}/pages/reorder`,
      { page_ids: pageIdsInOrder }
    );
    if (res.data.success) {
      pagesList.value = res.data.pages;
      statusMessage.value = 'Urutan halaman berhasil diperbarui.';
    }
  } catch (err: any) {
    errorMessage.value = 'Gagal menyimpan urutan baru.';
  } finally {
    isProcessing.value = false;
  }
};

// Trigger Insert Modal / File Picker
const triggerInsertAfter = (afterPageNumber: number) => {
  activeInsertAfter.value = afterPageNumber;
  if (insertFileInput.value) {
    insertFileInput.value.click();
  }
};

// Handle File Selection for Upload / Insertion
const handleFileSelect = async (event: Event, insertAfterNum: number | null = null) => {
  const target = event.target as HTMLInputElement;
  if (!target.files || !target.files.length) return;

  const filesArray = Array.from(target.files);
  isUploading.value = true;
  errorMessage.value = null;
  statusMessage.value = null;

  const formData = new FormData();
  filesArray.forEach((file) => {
    formData.append('pages[]', file);
  });

  if (insertAfterNum !== null) {
    formData.append('insert_after', insertAfterNum.toString());
  }

  try {
    const res = await axios.post(
      `/publisher/comics/${props.comic.id}/chapters/${props.chapter.id}/pages/upload`,
      formData,
      { headers: { 'Content-Type': 'multipart/form-data' } }
    );

    if (res.data.success) {
      pagesList.value = res.data.pages;
      statusMessage.value = insertAfterNum !== null
        ? `Gambar baru berhasil disisipkan setelah Halaman #${insertAfterNum}.`
        : `Berhasil mengunggah ${filesArray.length} gambar baru di akhir bab.`;
    }
  } catch (err: any) {
    errorMessage.value = err.response?.data?.message || 'Gagal mengunggah gambar.';
  } finally {
    isUploading.value = false;
    activeInsertAfter.value = null;
    target.value = '';
  }
};
</script>

<template>
  <Head :title="`Edit Chapter ${chapter.chapter_number} - ${comic.title}`" />

  <div class="min-h-screen bg-slate-950 text-slate-100 flex flex-col justify-center py-12 px-4 sm:px-6 lg:px-8">
    <div class="max-w-5xl mx-auto w-full space-y-8">
      <!-- Header -->
      <div class="text-center space-y-2">
        <Link href="/publisher/comics" class="inline-flex items-center gap-1.5 text-xs font-bold text-sky-400 hover:underline">
          <ArrowLeftIcon class="w-3.5 h-3.5" /> Kembali ke Kelola Komik & Chapter
        </Link>
        <h1 class="text-3xl font-extrabold text-white flex items-center justify-center gap-3">
          <PencilSquareIcon class="w-8 h-8 text-sky-400" />
          Edit Chapter {{ chapter.chapter_number }}: {{ comic.title }}
        </h1>
        <p class="text-sm text-slate-400">Pengaturan metadata bab (nomor, judul, harga) & manajemen gambar halaman (realtime CRUD & sisip gambar)</p>
      </div>

      <!-- Notification Alerts -->
      <div v-if="statusMessage" class="p-4 bg-emerald-500/10 border border-emerald-500/30 rounded-xl text-emerald-400 text-sm font-medium flex items-center gap-2">
        <CheckCircleIcon class="w-5 h-5 shrink-0" />
        {{ statusMessage }}
      </div>
      <div v-if="errorMessage" class="p-4 bg-rose-500/10 border border-rose-500/30 rounded-xl text-rose-400 text-sm font-medium flex items-center gap-2">
        <ExclamationTriangleIcon class="w-5 h-5 shrink-0" />
        {{ errorMessage }}
      </div>

      <!-- Card 1: Form Update Metadata Chapter (Nomor, Judul, Harga) -->
      <div class="bg-slate-900 border border-slate-800 rounded-2xl p-6 sm:p-8 space-y-6 shadow-xl">
        <h2 class="text-base font-bold text-white border-b border-slate-800 pb-3 flex items-center gap-2">
          <PencilSquareIcon class="w-5 h-5 text-sky-400" />
          Detail & Informasi Bab (Metadata Only)
        </h2>

        <form class="space-y-5" @submit.prevent="submitUpdateMetadata">
          <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
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
          </div>

          <!-- Status Gratis / Berbayar -->
          <div class="p-4 bg-slate-950 border border-slate-800 rounded-xl space-y-4">
            <div class="flex items-center justify-between">
              <div>
                <label class="text-xs font-bold text-white block">Akses Bab Gratis?</label>
                <p class="text-[11px] text-slate-400">Centang jika pembaca dapat membaca bab ini secara gratis tanpa pembelian.</p>
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

          <div class="flex justify-end">
            <button
              type="submit"
              :disabled="form.processing"
              class="px-6 py-2.5 rounded-xl text-xs font-bold text-white bg-sky-600 hover:bg-sky-500 disabled:opacity-50 transition shadow-lg shadow-sky-600/20"
            >
              {{ form.processing ? 'Menyimpan Detail...' : 'Simpan Detail Bab (Nomor & Harga) →' }}
            </button>
          </div>
        </form>
      </div>

      <!-- Card 2: Manajemen Gambar Realtime (CRUD & Sisip Gambar) -->
      <div class="bg-slate-900 border border-slate-800 rounded-2xl p-6 sm:p-8 space-y-6 shadow-2xl">
        <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4 border-b border-slate-800 pb-4">
          <div>
            <h2 class="text-base font-bold text-white flex items-center gap-2">
              <DocumentDuplicateIcon class="w-5 h-5 text-purple-400" />
              Kelola Gambar Halaman Chapter (Realtime)
            </h2>
            <p class="text-xs text-slate-400 mt-0.5">
              Total <strong class="text-white font-extrabold">{{ pagesList.length }} Halaman</strong>. Hapus, geser urutan, atau sisipkan gambar baru di antara halaman secara langsung.
            </p>
          </div>

          <!-- Hidden File Inputs -->
          <input
            ref="insertFileInput"
            type="file"
            multiple
            accept="image/*,.txt,text/plain"
            class="hidden"
            @change="(e) => handleFileSelect(e, activeInsertAfter)"
          />
          <input
            ref="appendFileInput"
            type="file"
            multiple
            accept="image/*,.txt,text/plain"
            class="hidden"
            @change="(e) => handleFileSelect(e, null)"
          />

          <div class="flex items-center gap-2 shrink-0">
            <button
              v-if="pagesList.length"
              @click="deleteAllPagesRealtime"
              :disabled="isProcessing || isUploading"
              class="inline-flex items-center gap-1.5 px-3.5 py-2.5 rounded-xl text-xs font-bold text-rose-400 bg-rose-950/40 border border-rose-800/80 hover:bg-rose-900 hover:text-white disabled:opacity-50 transition shadow-md"
            >
              <TrashIcon class="w-4 h-4" />
              Hapus Semua Gambar
            </button>

            <button
              @click="appendFileInput?.click()"
              :disabled="isUploading"
              class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl text-xs font-bold text-white bg-indigo-600 hover:bg-indigo-500 disabled:opacity-50 transition shadow-md shadow-indigo-600/20"
            >
              <PlusIcon class="w-4 h-4" />
              + Unggah Gambar / File .txt Link
            </button>
          </div>
        </div>

        <!-- Progress Spinner -->
        <div v-if="isUploading || isProcessing" class="p-3 bg-sky-500/10 border border-sky-500/30 rounded-xl text-sky-400 text-xs font-semibold flex items-center justify-center gap-2">
          <ArrowPathIcon class="w-4 h-4 animate-spin" />
          {{ isUploading ? 'Mengunggah & memproses gambar...' : 'Memperbarui urutan halaman...' }}
        </div>

        <!-- Grid Halaman Gambar Realtime -->
        <div v-if="pagesList.length" class="space-y-6">
          <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-4">
            <div
              v-for="(pageItem, idx) in pagesList"
              :key="pageItem.id"
              class="bg-slate-950 border border-slate-800 rounded-2xl overflow-hidden group flex flex-col justify-between hover:border-slate-700 transition"
            >
              <!-- Gambar Halaman -->
              <div class="relative aspect-[2/3] bg-slate-900">
                <img
                  :src="pageItem.image_url || pageItem.formatted_url"
                  :alt="`Halaman ${pageItem.page_number}`"
                  class="w-full h-full object-cover"
                />
                <span class="absolute top-2 left-2 px-2 py-0.5 rounded-md bg-black/80 text-white font-extrabold text-xs shadow-md">
                  #{{ pageItem.page_number }}
                </span>
              </div>

              <!-- Baris Tombol Aksi Halaman -->
              <div class="p-3 bg-slate-900 border-t border-slate-800/80 space-y-2">
                <div class="flex items-center justify-between gap-1">
                  <!-- Move Left / Up -->
                  <button
                    @click="movePagePosition(idx, 'left')"
                    :disabled="idx === 0 || isProcessing"
                    title="Geser Ke Atas/Kiri"
                    class="p-1.5 rounded-lg bg-slate-950 border border-slate-800 text-slate-400 hover:text-white disabled:opacity-30"
                  >
                    <MoveLeftIcon class="w-4 h-4" />
                  </button>

                  <!-- Move Right / Down -->
                  <button
                    @click="movePagePosition(idx, 'right')"
                    :disabled="idx === pagesList.length - 1 || isProcessing"
                    title="Geser Ke Bawah/Kanan"
                    class="p-1.5 rounded-lg bg-slate-950 border border-slate-800 text-slate-400 hover:text-white disabled:opacity-30"
                  >
                    <MoveRightIcon class="w-4 h-4" />
                  </button>

                  <!-- Realtime Delete Button -->
                  <button
                    @click="deletePageRealtime(pageItem.id, pageItem.page_number)"
                    :disabled="isProcessing"
                    title="Hapus Halaman Ini"
                    class="px-2.5 py-1.5 rounded-lg text-xs font-bold text-rose-400 bg-rose-950/40 border border-rose-800/80 hover:bg-rose-900 transition flex items-center gap-1"
                  >
                    <TrashIcon class="w-3.5 h-3.5" /> Hapus
                  </button>
                </div>

                <!-- Insert Page After Button -->
                <button
                  @click="triggerInsertAfter(pageItem.page_number)"
                  :disabled="isUploading"
                  class="w-full py-1.5 rounded-lg text-[11px] font-bold text-sky-400 bg-sky-950/40 border border-sky-800/60 hover:bg-sky-900 transition flex items-center justify-center gap-1"
                >
                  <PlusIcon class="w-3 h-3" />
                  + Sisipkan Gambar Setelah Ini
                </button>
              </div>
            </div>
          </div>
        </div>

        <!-- State Kosong -->
        <div v-else class="bg-slate-950 border border-slate-800 rounded-2xl p-12 text-center space-y-3">
          <PhotoIcon class="w-12 h-12 text-slate-600 mx-auto" />
          <h3 class="text-base font-bold text-white">Belum Ada Halaman Gambar</h3>
          <p class="text-xs text-slate-400">Silakan unggah gambar halaman komik untuk bab ini.</p>
          <div class="pt-2">
            <button
              @click="appendFileInput?.click()"
              class="px-5 py-2.5 rounded-xl text-xs font-bold text-white bg-sky-600 hover:bg-sky-500 transition"
            >
              + Unggah Gambar Sekarang
            </button>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>
