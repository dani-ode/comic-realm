<script setup lang="ts">
import { Head, useForm } from '@inertiajs/vue3';
import PublicLayout from '@/Layouts/PublicLayout.vue';
import {
  UserCircleIcon,
  KeyIcon,
  ShieldCheckIcon,
  IdentificationIcon,
  EnvelopeIcon,
  PhoneIcon,
  UserIcon,
} from '@heroicons/vue/24/outline';

interface User {
  id: number;
  name: string;
  username: string;
  email: string;
  phone?: string;
  avatar?: string;
  role: string;
  created_at: string;
  last_login_at?: string;
}

const props = defineProps<{
  user: User;
}>();

const profileForm = useForm({
  name: props.user.name || '',
  username: props.user.username || '',
  email: props.user.email || '',
  phone: props.user.phone || '',
});

const passwordForm = useForm({
  current_password: '',
  password: '',
  password_confirmation: '',
});

const updateProfile = () => {
  profileForm.post('/profile/update', {
    preserveScroll: true,
  });
};

const updatePassword = () => {
  passwordForm.post('/profile/password', {
    preserveScroll: true,
    onSuccess: () => passwordForm.reset(),
  });
};
</script>

<template>
  <Head title="My Profile & Settings" />

  <PublicLayout>
    <main class="max-w-4xl mx-auto px-4 lg:px-8 py-10 w-full flex-1 space-y-8">
      <!-- Header Title -->
      <div>
        <h1 class="text-3xl font-extrabold text-white flex items-center gap-3">
          <span class="w-9 h-9 rounded-xl bg-sky-500/10 border border-sky-500/30 flex items-center justify-center shrink-0">
            <UserCircleIcon class="w-5 h-5 text-sky-400" />
          </span>
          My Profile & Account
        </h1>
        <p class="text-sm text-slate-400 mt-1">Manage your personal information and account security settings</p>
      </div>

      <!-- User Card Banner -->
      <div class="bg-slate-900 border border-slate-800 rounded-3xl p-6 sm:p-8 shadow-xl flex flex-col sm:flex-row items-center sm:items-start gap-6">
        <!-- Avatar -->
        <div class="relative shrink-0">
          <img
            v-if="user.avatar"
            :src="user.avatar"
            :alt="user.name"
            class="w-24 h-24 rounded-2xl object-cover border-2 border-sky-500/50 shadow-lg"
          />
          <div
            v-else
            class="w-24 h-24 rounded-2xl bg-gradient-to-br from-sky-600 to-indigo-700 flex items-center justify-center text-white font-black text-3xl shadow-lg border-2 border-sky-500/30"
          >
            {{ user.name ? user.name.charAt(0).toUpperCase() : 'U' }}
          </div>
          <!-- Role Badge Floating -->
          <span class="absolute -bottom-2 -right-2 px-2.5 py-0.5 rounded-full text-[10px] font-extrabold tracking-wider uppercase bg-slate-950 border border-slate-700 text-sky-400">
            {{ user.role }}
          </span>
        </div>

        <!-- Details -->
        <div class="flex-1 space-y-2 text-center sm:text-left">
          <div class="flex flex-wrap items-center justify-center sm:justify-start gap-3">
            <h2 class="text-2xl font-bold text-white">{{ user.name }}</h2>
            <span class="px-3 py-1 rounded-full text-xs font-semibold bg-sky-500/10 border border-sky-500/30 text-sky-400">
              @{{ user.username || user.email }}
            </span>
          </div>

          <p class="text-xs text-slate-400 flex flex-wrap items-center justify-center sm:justify-start gap-x-4 gap-y-1">
            <span>Email: <strong class="text-slate-200">{{ user.email }}</strong></span>
            <span v-if="user.phone">Phone: <strong class="text-slate-200">{{ user.phone }}</strong></span>
          </p>

          <div class="pt-2 text-[11px] text-slate-500 flex flex-wrap items-center justify-center sm:justify-start gap-4 border-t border-slate-800/80">
            <span>Joined: {{ new Date(user.created_at).toLocaleDateString('id-ID', { year: 'numeric', month: 'long', day: 'numeric' }) }}</span>
            <span v-if="user.last_login_at">Last active: {{ new Date(user.last_login_at).toLocaleDateString('id-ID') }}</span>
          </div>
        </div>
      </div>

      <!-- Settings Grid -->
      <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
        <!-- Edit Profile Form -->
        <div class="bg-slate-900 border border-slate-800 rounded-3xl p-6 sm:p-8 space-y-6 shadow-xl">
          <div class="flex items-center gap-2 border-b border-slate-800 pb-4">
            <IdentificationIcon class="w-5 h-5 text-sky-400" />
            <h3 class="text-lg font-bold text-white">Personal Information</h3>
          </div>

          <form @submit.prevent="updateProfile" class="space-y-4">
            <div>
              <label class="block text-xs font-bold text-slate-300 mb-1">Full Name</label>
              <div class="relative">
                <input
                  v-model="profileForm.name"
                  type="text"
                  required
                  class="w-full rounded-xl bg-slate-950 border border-slate-800 pl-10 pr-4 py-2.5 text-xs text-white placeholder-slate-500 focus:border-sky-500 focus:outline-none focus:ring-1 focus:ring-sky-500"
                  placeholder="Your full name"
                />
                <UserIcon class="w-4 h-4 text-slate-500 absolute left-3.5 top-3" />
              </div>
              <p v-if="profileForm.errors.name" class="text-xs text-rose-400 mt-1">{{ profileForm.errors.name }}</p>
            </div>

            <div>
              <label class="block text-xs font-bold text-slate-300 mb-1">Username</label>
              <div class="relative">
                <input
                  v-model="profileForm.username"
                  type="text"
                  required
                  class="w-full rounded-xl bg-slate-950 border border-slate-800 pl-10 pr-4 py-2.5 text-xs text-white placeholder-slate-500 focus:border-sky-500 focus:outline-none focus:ring-1 focus:ring-sky-500"
                  placeholder="username"
                />
                <span class="text-xs font-bold text-slate-500 absolute left-3.5 top-2.5">@</span>
              </div>
              <p v-if="profileForm.errors.username" class="text-xs text-rose-400 mt-1">{{ profileForm.errors.username }}</p>
            </div>

            <div>
              <label class="block text-xs font-bold text-slate-300 mb-1">Email Address</label>
              <div class="relative">
                <input
                  v-model="profileForm.email"
                  type="email"
                  required
                  class="w-full rounded-xl bg-slate-950 border border-slate-800 pl-10 pr-4 py-2.5 text-xs text-white placeholder-slate-500 focus:border-sky-500 focus:outline-none focus:ring-1 focus:ring-sky-500"
                  placeholder="your@email.com"
                />
                <EnvelopeIcon class="w-4 h-4 text-slate-500 absolute left-3.5 top-3" />
              </div>
              <p v-if="profileForm.errors.email" class="text-xs text-rose-400 mt-1">{{ profileForm.errors.email }}</p>
            </div>

            <div>
              <label class="block text-xs font-bold text-slate-300 mb-1">Phone Number (Optional)</label>
              <div class="relative">
                <input
                  v-model="profileForm.phone"
                  type="text"
                  class="w-full rounded-xl bg-slate-950 border border-slate-800 pl-10 pr-4 py-2.5 text-xs text-white placeholder-slate-500 focus:border-sky-500 focus:outline-none focus:ring-1 focus:ring-sky-500"
                  placeholder="+62 812 3456 7890"
                />
                <PhoneIcon class="w-4 h-4 text-slate-500 absolute left-3.5 top-3" />
              </div>
              <p v-if="profileForm.errors.phone" class="text-xs text-rose-400 mt-1">{{ profileForm.errors.phone }}</p>
            </div>

            <button
              type="submit"
              :disabled="profileForm.processing"
              class="w-full py-3 px-4 rounded-xl text-xs font-bold text-white bg-sky-600 hover:bg-sky-500 disabled:opacity-50 transition shadow-lg shadow-sky-600/30"
            >
              {{ profileForm.processing ? 'Saving Changes...' : 'Save Profile Changes' }}
            </button>
          </form>
        </div>

        <!-- Security & Password Form -->
        <div class="bg-slate-900 border border-slate-800 rounded-3xl p-6 sm:p-8 space-y-6 shadow-xl">
          <div class="flex items-center gap-2 border-b border-slate-800 pb-4">
            <ShieldCheckIcon class="w-5 h-5 text-emerald-400" />
            <h3 class="text-lg font-bold text-white">Security & Password</h3>
          </div>

          <form @submit.prevent="updatePassword" class="space-y-4">
            <div>
              <label class="block text-xs font-bold text-slate-300 mb-1">Current Password</label>
              <div class="relative">
                <input
                  v-model="passwordForm.current_password"
                  type="password"
                  required
                  class="w-full rounded-xl bg-slate-950 border border-slate-800 pl-10 pr-4 py-2.5 text-xs text-white placeholder-slate-500 focus:border-sky-500 focus:outline-none focus:ring-1 focus:ring-sky-500"
                  placeholder="••••••••"
                />
                <KeyIcon class="w-4 h-4 text-slate-500 absolute left-3.5 top-3" />
              </div>
              <p v-if="passwordForm.errors.current_password" class="text-xs text-rose-400 mt-1">
                {{ passwordForm.errors.current_password }}
              </p>
            </div>

            <div>
              <label class="block text-xs font-bold text-slate-300 mb-1">New Password</label>
              <div class="relative">
                <input
                  v-model="passwordForm.password"
                  type="password"
                  required
                  class="w-full rounded-xl bg-slate-950 border border-slate-800 pl-10 pr-4 py-2.5 text-xs text-white placeholder-slate-500 focus:border-sky-500 focus:outline-none focus:ring-1 focus:ring-sky-500"
                  placeholder="••••••••"
                />
                <KeyIcon class="w-4 h-4 text-slate-500 absolute left-3.5 top-3" />
              </div>
              <p v-if="passwordForm.errors.password" class="text-xs text-rose-400 mt-1">
                {{ passwordForm.errors.password }}
              </p>
            </div>

            <div>
              <label class="block text-xs font-bold text-slate-300 mb-1">Confirm New Password</label>
              <div class="relative">
                <input
                  v-model="passwordForm.password_confirmation"
                  type="password"
                  required
                  class="w-full rounded-xl bg-slate-950 border border-slate-800 pl-10 pr-4 py-2.5 text-xs text-white placeholder-slate-500 focus:border-sky-500 focus:outline-none focus:ring-1 focus:ring-sky-500"
                  placeholder="••••••••"
                />
                <KeyIcon class="w-4 h-4 text-slate-500 absolute left-3.5 top-3" />
              </div>
              <p v-if="passwordForm.errors.password_confirmation" class="text-xs text-rose-400 mt-1">
                {{ passwordForm.errors.password_confirmation }}
              </p>
            </div>

            <button
              type="submit"
              :disabled="passwordForm.processing"
              class="w-full py-3 px-4 rounded-xl text-xs font-bold text-white bg-slate-800 hover:bg-slate-700 disabled:opacity-50 transition border border-slate-700"
            >
              {{ passwordForm.processing ? 'Updating Password...' : 'Update Password' }}
            </button>
          </form>
        </div>
      </div>
    </main>
  </PublicLayout>
</template>
