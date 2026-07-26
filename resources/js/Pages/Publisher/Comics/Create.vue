<script setup lang="ts">
import { Head, useForm, Link, router } from '@inertiajs/vue3';
import { ref, computed } from 'vue';
import axios from 'axios';
import {
  PhotoIcon,
  ArrowLeftIcon,
  InformationCircleIcon,
  CheckCircleIcon,
  SwatchIcon,
  PlusIcon,
  XMarkIcon,
} from '@heroicons/vue/24/outline';

interface Genre {
  id: number;
  name: string;
  slug: string;
}

const props = defineProps<{
  genres: Genre[];
}>();

const coverPreview = ref<string>('');
const bannerPreview = ref<string>('');
const originalCoverDim = ref<{ width: number; height: number } | null>(null);
const originalBannerDim = ref<{ width: number; height: number } | null>(null);

const form = useForm({
  title: '',
  description: '',
  cover_image: '',
  banner_image: '',
  author_name: '',
  artist_name: '',
  status: 'ongoing',
  genres: [] as number[],
});

// Cover Image Auto-Crop (Ratio 2:3 -> 400x600 px)
const handleCoverFileSelect = (event: Event) => {
  const target = event.target as HTMLInputElement;
  if (!target.files || !target.files[0]) return;

  const file = target.files[0];
  const reader = new FileReader();

  reader.onload = (e) => {
    const img = new Image();
    img.onload = () => {
      originalCoverDim.value = { width: img.width, height: img.height };

      const targetWidth = 400;
      const targetHeight = 600;
      const targetRatio = targetWidth / targetHeight;

      const canvas = document.createElement('canvas');
      canvas.width = targetWidth;
      canvas.height = targetHeight;
      const ctx = canvas.getContext('2d');

      if (!ctx) return;

      const imgRatio = img.width / img.height;
      let srcX = 0, srcY = 0, srcWidth = img.width, srcHeight = img.height;

      if (imgRatio > targetRatio) {
        srcWidth = img.height * targetRatio;
        srcX = (img.width - srcWidth) / 2;
      } else {
        srcHeight = img.width / targetRatio;
        srcY = (img.height - srcHeight) / 2;
      }

      ctx.drawImage(img, srcX, srcY, srcWidth, srcHeight, 0, 0, targetWidth, targetHeight);

      const croppedUrl = canvas.toDataURL('image/webp', 0.92);
      form.cover_image = croppedUrl;
      coverPreview.value = croppedUrl;
    };
    img.src = e.target?.result as string;
  };
  reader.readAsDataURL(file);
};

// Banner Image Auto-Crop (Ratio 3:1 -> 1200x400 px)
const handleBannerFileSelect = (event: Event) => {
  const target = event.target as HTMLInputElement;
  if (!target.files || !target.files[0]) return;

  const file = target.files[0];
  const reader = new FileReader();

  reader.onload = (e) => {
    const img = new Image();
    img.onload = () => {
      originalBannerDim.value = { width: img.width, height: img.height };

      const targetWidth = 1200;
      const targetHeight = 400;
      const targetRatio = targetWidth / targetHeight;

      const canvas = document.createElement('canvas');
      canvas.width = targetWidth;
      canvas.height = targetHeight;
      const ctx = canvas.getContext('2d');

      if (!ctx) return;

      const imgRatio = img.width / img.height;
      let srcX = 0, srcY = 0, srcWidth = img.width, srcHeight = img.height;

      if (imgRatio > targetRatio) {
        srcWidth = img.height * targetRatio;
        srcX = (img.width - srcWidth) / 2;
      } else {
        srcHeight = img.width / targetRatio;
        srcY = (img.height - srcHeight) / 2;
      }

      ctx.drawImage(img, srcX, srcY, srcWidth, srcHeight, 0, 0, targetWidth, targetHeight);

      const croppedUrl = canvas.toDataURL('image/webp', 0.90);
      form.banner_image = croppedUrl;
      bannerPreview.value = croppedUrl;
    };
    img.src = e.target?.result as string;
  };
  reader.readAsDataURL(file);
};

// Max 3 genres
const MAX_GENRES = 3;
const genreList = ref<Genre[]>(props.genres);

const canAddMoreGenres = computed(() => form.genres.length < MAX_GENRES);

const toggleGenre = (id: number) => {
  const idx = form.genres.indexOf(id);
  if (idx >= 0) {
    form.genres.splice(idx, 1);
  } else if (form.genres.length < MAX_GENRES) {
    form.genres.push(id);
  }
};

// Create new genre inline
const newGenreName = ref('');
const isCreatingGenre = ref(false);
const genreError = ref('');

const createGenre = async () => {
  const name = newGenreName.value.trim();
  if (!name) return;
  isCreatingGenre.value = true;
  genreError.value = '';
  try {
    const res = await axios.post('/publisher/genres', { name });
    if (res.data?.genre) {
      genreList.value.push(res.data.genre);
      if (form.genres.length < MAX_GENRES) {
        form.genres.push(res.data.genre.id);
      }
      newGenreName.value = '';
    }
  } catch (err: any) {
    genreError.value = err.response?.data?.message || 'Gagal membuat genre baru.';
  } finally {
    isCreatingGenre.value = false;
  }
};

const submit = () => {
  form.post('/publisher/comics');
};
</script>

<template>
  <Head title="Buat Serial Komik Baru - Creator Studio" />

  <div class="min-h-screen bg-slate-950 text-slate-100 flex flex-col justify-center py-12 sm:px-6 lg:px-8">
    <div class="sm:mx-auto sm:w-full sm:max-w-2xl text-center">
      <Link href="/publisher/comics" class="inline-flex items-center gap-1.5 text-xs font-bold text-sky-400 hover:underline">
        <ArrowLeftIcon class="w-3.5 h-3.5" /> Kembali ke Kelola Komik
      </Link>
      <h2 class="mt-2 text-3xl font-extrabold tracking-tight text-white flex items-center justify-center gap-3">
        <SwatchIcon class="w-8 h-8 text-sky-400" />
        Buat Serial Komik Baru
      </h2>
      <p class="text-sm text-slate-400 mt-1">Daftarkan serial webcomic baru karya studio Anda ke platform ComicRealm</p>
    </div>

    <div class="mt-8 sm:mx-auto sm:w-full sm:max-w-2xl">
      <div class="bg-slate-900 border border-slate-800 py-8 px-6 shadow-2xl sm:rounded-2xl sm:px-10">
        <form class="space-y-6" @submit.prevent="submit">
          <!-- Judul Komik -->
          <div>
            <label class="block text-xs font-bold text-slate-300">Judul Serial Komik *</label>
            <input
              v-model="form.title"
              type="text"
              required
              class="mt-1 block w-full rounded-xl bg-slate-950 border border-slate-800 px-3.5 py-2.5 text-white placeholder-slate-500 focus:border-sky-500 focus:outline-none text-sm font-semibold"
              placeholder="Contoh: The Legend of Realm Master"
            />
          </div>

          <!-- Sinopsis / Deskripsi -->
          <div>
            <label class="block text-xs font-bold text-slate-300">Sinopsis / Deskripsi *</label>
            <textarea
              v-model="form.description"
              rows="4"
              required
              class="mt-1 block w-full rounded-xl bg-slate-950 border border-slate-800 px-3.5 py-2.5 text-white placeholder-slate-500 focus:border-sky-500 focus:outline-none text-sm"
              placeholder="Tuliskan ringkasan alur cerita webcomic Anda yang menarik pembaca..."
            ></textarea>
          </div>

          <!-- Upload Cover Image & Auto-Crop Rules -->
          <div class="space-y-3 p-5 bg-slate-950 border border-slate-800 rounded-2xl">
            <div class="flex items-start gap-2 text-xs text-sky-400">
              <InformationCircleIcon class="w-5 h-5 shrink-0" />
              <div>
                <strong class="font-extrabold text-white block">Aturan & Spesifikasi Cover Komik:</strong>
                <ul class="list-disc list-inside text-slate-400 space-y-0.5 mt-1 text-[11px]">
                  <li>Rasio Aspek Resmi: <span class="text-sky-300 font-bold">2:3 (Vertikal / Poster)</span></li>
                  <li>Dimensi Standar: <span class="text-sky-300 font-bold">400 x 600 px</span></li>
                  <li><strong class="text-amber-400">Auto-Crop:</strong> Jika ukuran tidak sesuai, sistem otomatis melakukan center crop ke 400x600 px.</li>
                </ul>
              </div>
            </div>

            <div class="pt-2 flex flex-col sm:flex-row items-center gap-6">
              <div class="relative w-28 h-40 bg-slate-900 rounded-xl border-2 border-dashed border-slate-700 flex flex-col items-center justify-center overflow-hidden shrink-0 shadow-lg">
                <img
                  v-if="coverPreview || form.cover_image"
                  :src="coverPreview || form.cover_image"
                  alt="Cover Preview"
                  class="w-full h-full object-cover"
                />
                <div v-else class="text-center p-2 text-slate-500">
                  <PhotoIcon class="w-8 h-8 mx-auto mb-1 text-slate-600" />
                  <span class="text-[10px] font-bold">Cover Preview (2:3)</span>
                </div>
              </div>

              <div class="flex-1 space-y-2 w-full">
                <label class="block text-xs font-bold text-slate-300">Pilih File Cover Komik *</label>
                <input
                  type="file"
                  accept="image/png,image/jpeg,image/webp"
                  @change="handleCoverFileSelect"
                  class="block w-full text-xs text-slate-400 file:mr-4 file:py-2.5 file:px-4 file:rounded-xl file:border-0 file:text-xs file:font-semibold file:bg-sky-600 file:text-white hover:file:bg-sky-500 bg-slate-900 border border-slate-800 rounded-xl p-2 cursor-pointer"
                />

                <div v-if="originalCoverDim" class="text-[11px] p-2.5 rounded-xl bg-slate-900 border border-slate-800">
                  <p class="text-emerald-400 font-bold flex items-center gap-1">
                    <CheckCircleIcon class="w-4 h-4" /> Hasil Crop Otomatis Cover: 400 x 600 px
                  </p>
                </div>
              </div>
            </div>
          </div>

          <!-- Upload Banner Image & Auto-Crop Rules -->
          <div class="space-y-3 p-5 bg-slate-950 border border-slate-800 rounded-2xl">
            <div class="flex items-start gap-2 text-xs text-indigo-400">
              <InformationCircleIcon class="w-5 h-5 shrink-0" />
              <div>
                <strong class="font-extrabold text-white block">Aturan & Spesifikasi Banner Header Komik:</strong>
                <ul class="list-disc list-inside text-slate-400 space-y-0.5 mt-1 text-[11px]">
                  <li>Rasio Aspek Resmi: <span class="text-indigo-300 font-bold">3:1 (Landscape Header)</span></li>
                  <li>Dimensi Standar: <span class="text-indigo-300 font-bold">1200 x 400 px</span></li>
                  <li><strong class="text-amber-400">Auto-Crop:</strong> Jika ukuran tidak sesuai, sistem otomatis melakukan center crop ke 1200x400 px.</li>
                </ul>
              </div>
            </div>

            <div class="pt-2 space-y-3">
              <div class="relative w-full h-28 bg-slate-900 rounded-xl border-2 border-dashed border-slate-700 flex items-center justify-center overflow-hidden shadow-lg">
                <img
                  v-if="bannerPreview || form.banner_image"
                  :src="bannerPreview || form.banner_image"
                  alt="Banner Preview"
                  class="w-full h-full object-cover"
                />
                <div v-else class="text-center p-2 text-slate-500">
                  <PhotoIcon class="w-8 h-8 mx-auto mb-1 text-slate-600" />
                  <span class="text-[10px] font-bold">Banner Header Preview (3:1)</span>
                </div>
              </div>

              <div>
                <label class="block text-xs font-bold text-slate-300 mb-1">Pilih File Banner Header Komik (Opsional)</label>
                <input
                  type="file"
                  accept="image/png,image/jpeg,image/webp"
                  @change="handleBannerFileSelect"
                  class="block w-full text-xs text-slate-400 file:mr-4 file:py-2.5 file:px-4 file:rounded-xl file:border-0 file:text-xs file:font-semibold file:bg-indigo-600 file:text-white hover:file:bg-indigo-500 bg-slate-900 border border-slate-800 rounded-xl p-2 cursor-pointer"
                />
                <div v-if="originalBannerDim" class="text-[11px] p-2.5 rounded-xl bg-slate-900 border border-slate-800 mt-2">
                  <p class="text-emerald-400 font-bold flex items-center gap-1">
                    <CheckCircleIcon class="w-4 h-4" /> Hasil Crop Otomatis Banner: 1200 x 400 px
                  </p>
                </div>
              </div>
            </div>
          </div>

          <!-- Author & Artist -->
          <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
            <div>
              <label class="block text-xs font-bold text-slate-300">Nama Penulis / Author</label>
              <input
                v-model="form.author_name"
                type="text"
                class="mt-1 block w-full rounded-xl bg-slate-950 border border-slate-800 px-3.5 py-2.5 text-white text-xs"
                placeholder="Contoh: Chugong"
              />
            </div>

            <div>
              <label class="block text-xs font-bold text-slate-300">Nama Artis / Illustrator</label>
              <input
                v-model="form.artist_name"
                type="text"
                class="mt-1 block w-full rounded-xl bg-slate-950 border border-slate-800 px-3.5 py-2.5 text-white text-xs"
                placeholder="Contoh: DUBU (REDICE)"
              />
            </div>
          </div>

          <!-- Genres -->
          <div>
            <div class="flex items-center justify-between mb-1.5">
              <label class="block text-xs font-bold text-slate-300">Pilih Genre Komik * <span class="text-slate-500 font-normal">(maksimal {{ MAX_GENRES }})</span></label>
              <span class="text-xs font-bold" :class="form.genres.length >= MAX_GENRES ? 'text-amber-400' : 'text-slate-500'">
                {{ form.genres.length }}/{{ MAX_GENRES }}
              </span>
            </div>

            <!-- Genre Chips -->
            <div class="flex flex-wrap gap-2 mb-3">
              <button
                v-for="g in genreList"
                :key="g.id"
                type="button"
                @click="toggleGenre(g.id)"
                :disabled="!form.genres.includes(g.id) && !canAddMoreGenres"
                class="px-3 py-1.5 rounded-xl text-xs font-semibold border transition select-none"
                :class="form.genres.includes(g.id)
                  ? 'bg-sky-600 border-sky-500 text-white'
                  : canAddMoreGenres
                    ? 'bg-slate-950 border-slate-700 text-slate-300 hover:border-slate-600'
                    : 'bg-slate-950 border-slate-800 text-slate-600 cursor-not-allowed opacity-50'
                "
              >
                {{ g.name }}
              </button>
            </div>

            <!-- Create New Genre -->
            <div class="flex items-center gap-2 mt-1">
              <input
                v-model="newGenreName"
                type="text"
                placeholder="Nama genre baru..."
                class="flex-1 rounded-xl bg-slate-950 border border-slate-800 px-3 py-2 text-xs text-white placeholder-slate-500 focus:outline-none focus:ring-1 focus:ring-sky-500"
                @keyup.enter="createGenre"
              />
              <button
                type="button"
                @click="createGenre"
                :disabled="!newGenreName.trim() || isCreatingGenre"
                class="flex items-center gap-1.5 px-3 py-2 rounded-xl bg-emerald-600 hover:bg-emerald-500 text-white text-xs font-bold transition disabled:opacity-50"
              >
                <PlusIcon class="w-3.5 h-3.5" />
                {{ isCreatingGenre ? 'Membuat...' : 'Buat Genre' }}
              </button>
            </div>
            <p v-if="genreError" class="text-xs text-rose-400 mt-1">{{ genreError }}</p>
            <p v-if="form.genres.length >= MAX_GENRES" class="text-xs text-amber-400 mt-1">
              Maksimal {{ MAX_GENRES }} genre sudah dipilih.
            </p>
          </div>

          <button
            type="submit"
            :disabled="form.processing || !form.cover_image"
            class="w-full flex justify-center py-3.5 px-4 rounded-xl text-sm font-bold text-white bg-sky-600 hover:bg-sky-500 disabled:opacity-50 transition shadow-lg shadow-sky-600/30"
          >
            {{ form.processing ? 'Menerbitkan Serial Komik...' : 'Terbitkan Serial Komik →' }}
          </button>
        </form>
      </div>
    </div>
  </div>
</template>
