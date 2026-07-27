<script setup lang="ts">
import { Head, useForm, Link } from '@inertiajs/vue3';
import { ref } from 'vue';
import {
  PhotoIcon,
  ArrowLeftIcon,
  InformationCircleIcon,
  CheckCircleIcon,
  PencilSquareIcon,
} from '@heroicons/vue/24/outline';

interface Genre {
  id: number;
  name: string;
  slug: string;
}

interface Comic {
  id: number;
  title: string;
  description: string;
  cover_image: string;
  banner_image?: string;
  author_name?: string;
  artist_name?: string;
  status: string;
  genres?: Genre[];
}

const props = defineProps<{
  comic: Comic;
  genres: Genre[];
}>();

const coverPreview = ref<string>(props.comic.cover_image || '');
const bannerPreview = ref<string>(props.comic.banner_image || '');
const originalCoverDim = ref<{ width: number; height: number } | null>(null);
const originalBannerDim = ref<{ width: number; height: number } | null>(null);

const initialGenreIds = props.comic.genres ? props.comic.genres.map((g) => g.id) : [];

const form = useForm({
  title: props.comic.title,
  description: props.comic.description,
  cover_image: props.comic.cover_image,
  banner_image: props.comic.banner_image || '',
  author_name: props.comic.author_name || '',
  artist_name: props.comic.artist_name || '',
  status: props.comic.status || 'ongoing',
  genres: initialGenreIds,
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
    img.onerror = () => {
      const rawUrl = (e.target?.result as string) || '';
      form.cover_image = rawUrl;
      coverPreview.value = rawUrl;
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
    img.onerror = () => {
      const rawUrl = (e.target?.result as string) || '';
      form.banner_image = rawUrl;
      bannerPreview.value = rawUrl;
    };
    img.src = e.target?.result as string;
  };
  reader.readAsDataURL(file);
};

const submit = () => {
  form.post(`/publisher/comics/${props.comic.id}/update`);
};
</script>

<template>
  <Head :title="`Edit Komik: ${comic.title} - Creator Studio`" />

  <div class="min-h-screen bg-slate-950 text-slate-100 flex flex-col justify-center py-12 sm:px-6 lg:px-8">
    <div class="sm:mx-auto sm:w-full sm:max-w-2xl text-center">
      <Link href="/publisher/comics" class="inline-flex items-center gap-1.5 text-xs font-bold text-sky-400 hover:underline">
        <ArrowLeftIcon class="w-3.5 h-3.5" /> Kembali ke Kelola Komik
      </Link>
      <h2 class="mt-2 text-3xl font-extrabold tracking-tight text-white flex items-center justify-center gap-3">
        <PencilSquareIcon class="w-8 h-8 text-sky-400" />
        Edit Detail Serial Komik
      </h2>
      <p class="text-sm text-slate-400 mt-1">Perbarui judul, sinopsis, status terbitan, dan gambar cover/banner komik</p>
    </div>

    <div class="mt-8 sm:mx-auto sm:w-full sm:max-w-2xl">
      <div class="bg-slate-900 border border-slate-800 py-8 px-6 shadow-2xl sm:rounded-2xl sm:px-10">
        <form class="space-y-6" @submit.prevent="submit">
          <!-- Status Komik -->
          <div>
            <label class="block text-xs font-bold text-slate-300">Status Serial Komik *</label>
            <select
              v-model="form.status"
              class="mt-1 block w-full rounded-xl bg-slate-950 border border-slate-800 px-3.5 py-2.5 text-white focus:border-sky-500 focus:outline-none text-sm font-semibold"
            >
              <option value="ongoing">Ongoing (Sedang Berjalan)</option>
              <option value="completed">Completed (Tamat)</option>
              <option value="hiatus">Hiatus (Diberhentikan Sementara)</option>
            </select>
          </div>

          <!-- Judul Komik -->
          <div>
            <label class="block text-xs font-bold text-slate-300">Judul Serial Komik *</label>
            <input
              v-model="form.title"
              type="text"
              required
              class="mt-1 block w-full rounded-xl bg-slate-950 border border-slate-800 px-3.5 py-2.5 text-white placeholder-slate-500 focus:border-sky-500 focus:outline-none text-sm font-semibold"
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
            ></textarea>
          </div>

          <!-- Upload Cover Image & Auto-Crop Rules -->
          <div class="space-y-3 p-5 bg-slate-950 border border-slate-800 rounded-2xl">
            <div class="flex items-start gap-2 text-xs text-sky-400">
              <InformationCircleIcon class="w-5 h-5 shrink-0" />
              <div>
                <strong class="font-extrabold text-white block">Aturan Cover Komik:</strong>
                <p class="text-slate-400 text-[11px] mt-0.5">
                  Rasio aspek resmi <strong class="text-sky-300">2:3 (400 x 600 px)</strong>. Gambar baru akan otomatis di-crop paksa jika ukuran tidak sesuai.
                </p>
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
                <label class="block text-xs font-bold text-slate-300">Ubah File Cover Komik</label>
                <input
                  type="file"
                  accept="image/png,image/jpeg,image/webp,image/avif,image/*"
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
                <strong class="font-extrabold text-white block">Aturan Banner Header Komik:</strong>
                <p class="text-slate-400 text-[11px] mt-0.5">
                  Rasio aspek resmi <strong class="text-indigo-300">3:1 (1200 x 400 px)</strong>. Gambar baru akan otomatis di-crop paksa jika ukuran tidak sesuai.
                </p>
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
                <label class="block text-xs font-bold text-slate-300 mb-1">Ubah File Banner Header Komik</label>
                <input
                  type="file"
                  accept="image/png,image/jpeg,image/webp,image/avif,image/*"
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
              />
            </div>

            <div>
              <label class="block text-xs font-bold text-slate-300">Nama Artis / Illustrator</label>
              <input
                v-model="form.artist_name"
                type="text"
                class="mt-1 block w-full rounded-xl bg-slate-950 border border-slate-800 px-3.5 py-2.5 text-white text-xs"
              />
            </div>
          </div>

          <!-- Genres -->
          <div>
            <label class="block text-xs font-bold text-slate-300 mb-2">Pilih Genre Komik *</label>
            <div class="flex flex-wrap gap-2">
              <label
                v-for="g in genres"
                :key="g.id"
                class="flex items-center gap-1.5 px-3 py-1.5 rounded-xl bg-slate-950 border border-slate-800 text-xs cursor-pointer select-none hover:border-slate-700 transition"
              >
                <input
                  type="checkbox"
                  :value="g.id"
                  v-model="form.genres"
                  class="rounded bg-slate-900 border-slate-800 text-sky-600 focus:ring-sky-500"
                />
                <span class="text-slate-300 font-semibold">{{ g.name }}</span>
              </label>
            </div>
          </div>

          <button
            type="submit"
            :disabled="form.processing"
            class="w-full flex justify-center py-3.5 px-4 rounded-xl text-sm font-bold text-white bg-sky-600 hover:bg-sky-500 disabled:opacity-50 transition shadow-lg shadow-sky-600/30"
          >
            {{ form.processing ? 'Menyimpan Perubahan...' : 'Simpan Perubahan Komik →' }}
          </button>
        </form>
      </div>
    </div>
  </div>
</template>
