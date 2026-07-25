<script setup lang="ts">
import { Head, useForm, Link } from '@inertiajs/vue3';

interface Genre {
  id: number;
  name: string;
  slug: string;
}

defineProps<{
  genres: Genre[];
}>();

const form = useForm({
  title: '',
  description: '',
  cover_image: '',
  author_name: '',
  artist_name: '',
  status: 'ongoing',
  genres: [] as number[],
});

const submit = () => {
  form.post('/publisher/comics');
};
</script>

<template>
  <Head title="Create New Comic Series" />

  <div class="min-h-screen bg-slate-950 text-slate-100 flex flex-col justify-center py-12 sm:px-6 lg:px-8">
    <div class="sm:mx-auto sm:w-full sm:max-w-2xl text-center">
      <Link href="/publisher/dashboard" class="text-xs font-semibold text-sky-400">← Back to Dashboard</Link>
      <h2 class="mt-2 text-3xl font-extrabold tracking-tight text-white">Create New Comic Series</h2>
    </div>

    <div class="mt-8 sm:mx-auto sm:w-full sm:max-w-2xl">
      <div class="bg-slate-900 border border-slate-800 py-8 px-6 shadow-xl sm:rounded-2xl sm:px-10">
        <form class="space-y-5" @submit.prevent="submit">
          <div>
            <label class="block text-sm font-medium text-slate-300">Comic Title</label>
            <input
              v-model="form.title"
              type="text"
              required
              class="mt-1 block w-full rounded-xl bg-slate-950 border border-slate-800 px-3.5 py-2.5 text-white placeholder-slate-500 focus:border-sky-500 focus:outline-none text-sm"
              placeholder="e.g. Solo Leveling 2"
            />
          </div>

          <div>
            <label class="block text-sm font-medium text-slate-300">Synopsis / Description</label>
            <textarea
              v-model="form.description"
              rows="4"
              required
              class="mt-1 block w-full rounded-xl bg-slate-950 border border-slate-800 px-3.5 py-2.5 text-white placeholder-slate-500 focus:border-sky-500 focus:outline-none text-sm"
              placeholder="Synopsis of your webcomic series..."
            ></textarea>
          </div>

          <div>
            <label class="block text-sm font-medium text-slate-300">Cover Image URL</label>
            <input
              v-model="form.cover_image"
              type="text"
              required
              class="mt-1 block w-full rounded-xl bg-slate-950 border border-slate-800 px-3.5 py-2.5 text-white placeholder-slate-500 focus:border-sky-500 focus:outline-none text-sm"
              placeholder="https://picsum.photos/400/600"
            />
          </div>

          <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
            <div>
              <label class="block text-xs font-medium text-slate-400">Author Name</label>
              <input
                v-model="form.author_name"
                type="text"
                class="mt-1 block w-full rounded-xl bg-slate-950 border border-slate-800 px-3 py-2 text-white text-xs"
                placeholder="Author"
              />
            </div>

            <div>
              <label class="block text-xs font-medium text-slate-400">Artist Name</label>
              <input
                v-model="form.artist_name"
                type="text"
                class="mt-1 block w-full rounded-xl bg-slate-950 border border-slate-800 px-3 py-2 text-white text-xs"
                placeholder="Artist"
              />
            </div>
          </div>

          <div>
            <label class="block text-sm font-medium text-slate-300 mb-2">Select Genres</label>
            <div class="flex flex-wrap gap-2">
              <label
                v-for="g in genres"
                :key="g.id"
                class="flex items-center gap-1.5 px-3 py-1.5 rounded-xl bg-slate-950 border border-slate-800 text-xs cursor-pointer select-none"
              >
                <input
                  type="checkbox"
                  :value="g.id"
                  v-model="form.genres"
                  class="rounded bg-slate-900 border-slate-800 text-sky-600 focus:ring-sky-500"
                />
                <span class="text-slate-300">{{ g.name }}</span>
              </label>
            </div>
          </div>

          <button
            type="submit"
            :disabled="form.processing"
            class="w-full flex justify-center py-3.5 px-4 rounded-xl text-sm font-bold text-white bg-sky-600 hover:bg-sky-500 transition shadow-lg shadow-sky-600/30"
          >
            {{ form.processing ? 'Creating Series...' : 'Create Comic Series →' }}
          </button>
        </form>
      </div>
    </div>
  </div>
</template>
