<script setup lang="ts">
import { Head, useForm, Link } from '@inertiajs/vue3';

interface Comic {
  id: number;
  title: string;
  slug: string;
}

const props = defineProps<{
  comic: Comic;
}>();

const form = useForm({
  title: '',
  chapter_number: 1.0,
  is_free: true,
  price: 0,
  pages: [] as File[],
});

const handleFileChange = (e: Event) => {
  const target = e.target as HTMLInputElement;
  if (target.files) {
    form.pages = Array.from(target.files);
  }
};

const submit = () => {
  form.post(`/publisher/comics/${props.comic.id}/chapters`);
};
</script>

<template>
  <Head :title="`Add Chapter - ${comic.title}`" />

  <div class="min-h-screen bg-slate-950 text-slate-100 flex flex-col justify-center py-12 sm:px-6 lg:px-8">
    <div class="sm:mx-auto sm:w-full sm:max-w-2xl text-center">
      <Link href="/publisher/dashboard" class="text-xs font-semibold text-sky-400">← Back to Dashboard</Link>
      <h2 class="mt-2 text-3xl font-extrabold tracking-tight text-white">Publish New Chapter</h2>
      <p class="text-sm text-slate-400 mt-1">Series: <strong class="text-white">{{ comic.title }}</strong></p>
    </div>

    <div class="mt-8 sm:mx-auto sm:w-full sm:max-w-2xl">
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
                class="mt-1 block w-full rounded-xl bg-slate-950 border border-slate-800 px-3.5 py-2.5 text-white text-sm"
                placeholder="1.0"
              />
            </div>

            <div>
              <label class="block text-xs font-medium text-slate-400">Chapter Title</label>
              <input
                v-model="form.title"
                type="text"
                required
                class="mt-1 block w-full rounded-xl bg-slate-950 border border-slate-800 px-3.5 py-2.5 text-white text-sm"
                placeholder="Chapter 1: The Awakening"
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

          <!-- WebP Image Batch Upload -->
          <div>
            <label class="block text-sm font-medium text-slate-300 mb-1">
              Upload WebP Chapter Pages (Batch Upload)
            </label>
            <input
              type="file"
              multiple
              accept="image/webp,image/png,image/jpeg"
              @change="handleFileChange"
              class="block w-full text-xs text-slate-400 file:mr-4 file:py-2.5 file:px-4 file:rounded-xl file:border-0 file:text-xs file:font-semibold file:bg-sky-600 file:text-white hover:file:bg-sky-500 bg-slate-950 border border-slate-800 rounded-xl p-2"
            />
            <p class="text-xs text-slate-500 mt-1">
              Selected {{ form.pages.length }} images. Files will be ordered by filename and displayed in continuous vertical reader.
            </p>
          </div>

          <button
            type="submit"
            :disabled="form.processing"
            class="w-full flex justify-center py-3.5 px-4 rounded-xl text-sm font-bold text-white bg-sky-600 hover:bg-sky-500 transition shadow-lg shadow-sky-600/30"
          >
            {{ form.processing ? 'Publishing Chapter & Uploading Pages...' : 'Publish Chapter →' }}
          </button>
        </form>
      </div>
    </div>
  </div>
</template>
