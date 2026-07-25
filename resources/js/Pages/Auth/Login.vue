<script setup lang="ts">
import { Head, useForm, Link, usePage } from '@inertiajs/vue3';
import { computed } from 'vue';

const page = usePage();
const flashError = computed(() => (page.props as any).flash?.error);
const pageErrors = computed(() => (page.props as any).errors || {});

const form = useForm({
  login: '',
  password: '',
  remember: false,
});

const submit = () => {
  form.post('/login', {
    onFinish: () => form.reset('password'),
  });
};
</script>

<template>
  <Head title="Sign In" />

  <div class="min-h-screen bg-slate-950 flex flex-col justify-center py-12 sm:px-6 lg:px-8">
    <div class="sm:mx-auto sm:w-full sm:max-w-md text-center">
      <Link href="/" class="text-3xl font-extrabold bg-gradient-to-r from-sky-400 to-indigo-400 bg-clip-text text-transparent">
        The ComicRealm
      </Link>
      <h2 class="mt-4 text-2xl font-bold tracking-tight text-white">Sign in to your account</h2>
      <p class="mt-2 text-sm text-slate-400">
        Don't have an account?
        <Link href="/register" class="font-medium text-sky-400 hover:text-sky-300">Create one now</Link>
      </p>
    </div>

    <div class="mt-8 sm:mx-auto sm:w-full sm:max-w-md space-y-4">
      <div class="bg-slate-900 border border-slate-800 py-8 px-4 shadow-xl sm:rounded-2xl sm:px-10 space-y-6">
        <!-- Prominent Error Alert Banner -->
        <div
          v-if="form.hasErrors || Object.keys(pageErrors).length > 0 || flashError"
          class="p-4 bg-rose-500/10 border border-rose-500/30 rounded-xl text-rose-400 text-xs font-medium space-y-1"
        >
          <div class="flex items-center gap-2 font-bold text-sm">
            <span>⚠️</span> Sign In Failed
          </div>
          <p v-if="form.errors.login || pageErrors.login">{{ form.errors.login || pageErrors.login }}</p>
          <p v-if="form.errors.password || pageErrors.password">{{ form.errors.password || pageErrors.password }}</p>
          <p v-if="flashError">{{ flashError }}</p>
        </div>

        <form class="space-y-5" @submit.prevent="submit">
          <div>
            <label class="block text-sm font-medium text-slate-300">Email or Username</label>
            <input
              v-model="form.login"
              type="text"
              required
              class="mt-1 block w-full rounded-xl bg-slate-950 border border-slate-800 px-3.5 py-2.5 text-white placeholder-slate-500 focus:border-sky-500 focus:outline-none focus:ring-1 focus:ring-sky-500 text-sm"
              placeholder="username or email@example.com"
            />
            <p v-if="form.errors.login || pageErrors.login" class="mt-1 text-xs text-rose-400">
              {{ form.errors.login || pageErrors.login }}
            </p>
          </div>

          <div>
            <label class="block text-sm font-medium text-slate-300">Password</label>
            <input
              v-model="form.password"
              type="password"
              required
              class="mt-1 block w-full rounded-xl bg-slate-950 border border-slate-800 px-3.5 py-2.5 text-white placeholder-slate-500 focus:border-sky-500 focus:outline-none focus:ring-1 focus:ring-sky-500 text-sm"
              placeholder="••••••••"
            />
            <p v-if="form.errors.password || pageErrors.password" class="mt-1 text-xs text-rose-400">
              {{ form.errors.password || pageErrors.password }}
            </p>
          </div>

          <div class="flex items-center justify-between">
            <div class="flex items-center">
              <input
                id="remember-me"
                v-model="form.remember"
                type="checkbox"
                class="h-4 w-4 rounded bg-slate-950 border-slate-800 text-sky-600 focus:ring-sky-500"
              />
              <label for="remember-me" class="ml-2 block text-sm text-slate-300">Remember me</label>
            </div>
          </div>

          <div>
            <button
              type="submit"
              :disabled="form.processing"
              class="w-full flex justify-center py-3 px-4 border border-transparent rounded-xl text-sm font-semibold text-white bg-sky-600 hover:bg-sky-500 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-sky-500 disabled:opacity-50 transition shadow-lg shadow-sky-600/30"
            >
              {{ form.processing ? 'Signing in...' : 'Sign In' }}
            </button>
          </div>
        </form>

        <!-- Demo Accounts Hint -->
        <div class="pt-4 border-t border-slate-800/80 space-y-1.5 text-xs text-slate-400">
          <p class="font-bold text-slate-300 flex items-center gap-1.5">
            <span>🔑</span> Demo Accounts (Password: <code class="text-sky-400">password123</code>)
          </p>
          <div class="bg-slate-950/80 border border-slate-800 rounded-xl p-3 space-y-1 font-mono text-[11px]">
            <p>• Reader: <span class="text-sky-400">user@comicrealm.test</span></p>
            <p>• Publisher: <span class="text-sky-400">publisher@comicrealm.test</span></p>
            <p>• Admin: <span class="text-sky-400">admin@comicrealm.test</span></p>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>
