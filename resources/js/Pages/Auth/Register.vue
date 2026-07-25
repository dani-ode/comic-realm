<script setup lang="ts">
import { Head, useForm, Link } from '@inertiajs/vue3';

const form = useForm({
  name: '',
  username: '',
  email: '',
  phone: '',
  password: '',
  password_confirmation: '',
});

const submit = () => {
  form.post('/register', {
    onFinish: () => form.reset('password', 'password_confirmation'),
  });
};
</script>

<template>
  <Head title="Register Account" />

  <div class="min-h-screen bg-slate-950 flex flex-col justify-center py-12 sm:px-6 lg:px-8">
    <div class="sm:mx-auto sm:w-full sm:max-w-md text-center">
      <Link href="/" class="text-3xl font-extrabold bg-gradient-to-r from-sky-400 to-indigo-400 bg-clip-text text-transparent">
        The ComicRealm
      </Link>
      <h2 class="mt-4 text-2xl font-bold tracking-tight text-white">Create your account</h2>
      <p class="mt-2 text-sm text-slate-400">
        Already have an account?
        <Link href="/login" class="font-medium text-sky-400 hover:text-sky-300">Sign in here</Link>
      </p>
    </div>

    <div class="mt-8 sm:mx-auto sm:w-full sm:max-w-md">
      <div class="bg-slate-900 border border-slate-800 py-8 px-4 shadow-xl sm:rounded-2xl sm:px-10">
        <form class="space-y-5" @submit.prevent="submit">
          <div>
            <label class="block text-sm font-medium text-slate-300">Full Name</label>
            <input
              v-model="form.name"
              type="text"
              required
              class="mt-1 block w-full rounded-xl bg-slate-950 border border-slate-800 px-3.5 py-2.5 text-white placeholder-slate-500 focus:border-sky-500 focus:outline-none focus:ring-1 focus:ring-sky-500 text-sm"
              placeholder="La Ode Mahdani"
            />
            <p v-if="form.errors.name" class="mt-1 text-xs text-rose-400">{{ form.errors.name }}</p>
          </div>

          <div>
            <label class="block text-sm font-medium text-slate-300">Username</label>
            <input
              v-model="form.username"
              type="text"
              required
              class="mt-1 block w-full rounded-xl bg-slate-950 border border-slate-800 px-3.5 py-2.5 text-white placeholder-slate-500 focus:border-sky-500 focus:outline-none focus:ring-1 focus:ring-sky-500 text-sm"
              placeholder="mahdani"
            />
            <p v-if="form.errors.username" class="mt-1 text-xs text-rose-400">{{ form.errors.username }}</p>
          </div>

          <div>
            <label class="block text-sm font-medium text-slate-300">Email Address</label>
            <input
              v-model="form.email"
              type="email"
              required
              class="mt-1 block w-full rounded-xl bg-slate-950 border border-slate-800 px-3.5 py-2.5 text-white placeholder-slate-500 focus:border-sky-500 focus:outline-none focus:ring-1 focus:ring-sky-500 text-sm"
              placeholder="mahdani@example.com"
            />
            <p v-if="form.errors.email" class="mt-1 text-xs text-rose-400">{{ form.errors.email }}</p>
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
            <p v-if="form.errors.password" class="mt-1 text-xs text-rose-400">{{ form.errors.password }}</p>
          </div>

          <div>
            <label class="block text-sm font-medium text-slate-300">Confirm Password</label>
            <input
              v-model="form.password_confirmation"
              type="password"
              required
              class="mt-1 block w-full rounded-xl bg-slate-950 border border-slate-800 px-3.5 py-2.5 text-white placeholder-slate-500 focus:border-sky-500 focus:outline-none focus:ring-1 focus:ring-sky-500 text-sm"
              placeholder="••••••••"
            />
          </div>

          <div>
            <button
              type="submit"
              :disabled="form.processing"
              class="w-full flex justify-center py-3 px-4 border border-transparent rounded-xl text-sm font-semibold text-white bg-sky-600 hover:bg-sky-500 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-sky-500 disabled:opacity-50 transition shadow-lg shadow-sky-600/30"
            >
              {{ form.processing ? 'Registering...' : 'Create Account' }}
            </button>
          </div>
        </form>
      </div>
    </div>
  </div>
</template>
