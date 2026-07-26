<script setup lang="ts">
import { Head, useForm, Link } from '@inertiajs/vue3';
import { ref } from 'vue';
import {
  PhotoIcon,
  ArrowLeftIcon,
  InformationCircleIcon,
  SparklesIcon,
  CheckCircleIcon,
  SwatchIcon,
} from '@heroicons/vue/24/outline';

interface Genre {
  id: number;
  name: string;
  slug: string;
}

defineProps<{
  genres: Genre[];
}>();

const coverPreview = ref<string>('');
const isCropped = ref<boolean>(false);
const originalDimensions = ref<{ width: number; height: number } | null>(null);

const form = useForm({
  title: '',
  description: '',
  cover_image: '',
  author_name: '',
  artist_name: '',
  status: 'ongoing',
  genres: [] as number[],
});

// Auto-crop logic for cover image (Ratio 2:3 -> 400x600 px)
const handleCoverFileSelect = (event: Event) => {
  const target = event.target as HTMLInputElement;
  if (!target.files || !target.files[0]) return;

  const file = target.files[0];
  const reader = new FileReader();

  reader.onload = (e) => {
    const img = new Image();
    img.onload = () => {
      originalDimensions.value = { width: img.width, height: img.height };

      // Target dimension 400x600 (Aspect ratio 2:3)
      const targetWidth = 400;
      const targetHeight = 600;
      const targetRatio = targetWidth / targetHeight;

      const canvas = document.createElement('canvas');
      canvas.width = targetWidth;
      canvas.height = targetHeight;
      const ctx = canvas.getContext('2d');

      if (!ctx) return;

      // Calculate Center Crop box
      const imgRatio = img.width / img.height;
      let srcX = 0, srcY = 0, srcWidth = img.width, srcHeight = img.height;

      if (imgRatio > targetRatio) {
        // Image is wider -> crop left/right
        srcWidth = img.height * targetRatio;
        srcX = (img.width - srcWidth) / 2;
      } else {
        // Image is taller -> crop top/bottom
        srcHeight = img.width / targetRatio;
        srcY = (img.height - srcHeight) / 2;
      }

      ctx.drawImage(img, srcX, srcY, srcWidth, srcHeight, 0, 0, targetWidth, targetHeight);

      const croppedUrl = canvas.toDataURL('image/webp', 0.92);
      form.cover_image = croppedUrl;
      coverPreview.value = croppedUrl;
      isCropped.value = (img.width !== targetWidth || img.height !== targetHeight);
    };
    img.src = e.target?.result as string;
  };
  reader.readAsDataURL(file);
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
                  <li>Dimensi Standar: <span class="text-sky-300 font-bold">400 x 600 px</span> (atau kelipatannya)</li>
                  <li><strong class="text-amber-400">Fitur Auto-Crop:</strong> Jika ukuran/rasio gambar tidak sesuai, sistem akan otomatis melakukan <em>center crop</em> paksa & penyesuaian ke 400x600 px secara instan.</li>
                </ul>
              </div>
            </div>

            <div class="pt-2 flex flex-col sm:flex-row items-center gap-6">
              <!-- Live Cover Preview -->
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

              <!-- Upload Controls -->
              <div class="flex-1 space-y-2 w-full">
                <label class="block text-xs font-bold text-slate-300">Pilih File Gambar Cover *</label>
                <input
                  type="file"
                  accept="image/png,image/jpeg,image/webp"
                  @change="handleCoverFileSelect"
                  class="block w-full text-xs text-slate-400 file:mr-4 file:py-2.5 file:px-4 file:rounded-xl file:border-0 file:text-xs file:font-semibold file:bg-sky-600 file:text-white hover:file:bg-sky-500 bg-slate-900 border border-slate-800 rounded-xl p-2 cursor-pointer"
                />

                <!-- Crop Status Banner -->
                <div v-if="originalDimensions" class="text-[11px] p-2.5 rounded-xl bg-slate-900 border border-slate-800 space-y-1">
                  <p class="text-slate-400">
                    Ukuran Asli File: <strong class="text-white">{{ originalDimensions.width }} x {{ originalDimensions.height }} px</strong>
                  </p>
                  <p class="text-emerald-400 font-bold flex items-center gap-1">
                    <CheckCircleIcon class="w-4 h-4" /> Hasil Crop Otomatis: 400 x 600 px (Rasio 2:3 Siap Digunakan)
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

          <!-- Status & Genres -->
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
