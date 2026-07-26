<script setup lang="ts">
import { Head, useForm, Link } from "@inertiajs/vue3";
import {
  UserIcon,
  EnvelopeIcon,
  LockClosedIcon,
  PhoneIcon,
  ArrowRightIcon,
} from "@heroicons/vue/24/outline";

const form = useForm({
    name: "",
    username: "",
    email: "",
    phone: "",
    password: "",
    password_confirmation: "",
});

const submit = () => {
    form.post("/register", {
        onFinish: () => form.reset("password", "password_confirmation"),
    });
};
</script>

<template>
    <Head title="Create Account - ComicRealm" />

    <div class="min-h-screen bg-slate-950 text-slate-100 flex flex-col justify-center items-center py-12 px-4 sm:px-6 lg:px-8 relative overflow-hidden">
        <!-- Ambient Glowing Background Orbs -->
        <div class="absolute -top-40 -right-40 w-96 h-96 bg-purple-600/15 rounded-full blur-3xl pointer-events-none"></div>
        <div class="absolute -bottom-40 -left-40 w-96 h-96 bg-sky-600/15 rounded-full blur-3xl pointer-events-none"></div>

        <div class="w-full max-w-md space-y-6 relative z-10">
            <!-- Header Brand -->
            <div class="text-center space-y-3">
                <Link href="/" class="inline-flex items-center gap-3 group">
                    <img
                        src="/favicon.ico"
                        alt="ComicRealm"
                        class="w-12 h-12 rounded-2xl object-contain shadow-xl shadow-sky-500/20 group-hover:scale-105 transition duration-300 border border-slate-800"
                    />
                    <span class="font-brand text-3xl font-extrabold bg-gradient-to-r from-sky-400 via-indigo-300 to-purple-400 bg-clip-text text-transparent tracking-wide">
                        ComicRealm
                    </span>
                </Link>
                <div>
                    <h2 class="text-2xl font-black text-white tracking-tight">Claim Your Place</h2>
                    <p class="text-xs text-slate-400 mt-1">
                        Already a legend?
                        <Link href="/login" class="font-bold text-sky-400 hover:text-sky-300 hover:underline">
                            Return to Sign In
                        </Link>
                    </p>
                </div>
            </div>

            <!-- Glass Card Container -->
            <div class="bg-slate-900/80 backdrop-blur-xl border border-slate-800/80 rounded-3xl p-6 sm:p-8 shadow-2xl space-y-5">
                <form class="space-y-4" @submit.prevent="submit">
                    <!-- Full Name -->
                    <div>
                        <label class="block text-xs font-bold text-slate-300 mb-1">Full Name *</label>
                        <div class="relative">
                            <input
                                v-model="form.name"
                                type="text"
                                required
                                class="w-full rounded-xl bg-slate-950 border border-slate-800 pl-10 pr-4 py-2.5 text-xs text-white placeholder-slate-500 focus:border-sky-500 focus:outline-none focus:ring-1 focus:ring-sky-500 transition"
                                placeholder="e.g. Sung Jinwoo"
                            />
                            <UserIcon class="w-4 h-4 text-slate-500 absolute left-3.5 top-3" />
                        </div>
                        <p v-if="form.errors.name" class="mt-1 text-xs text-rose-400 font-medium">
                            {{ form.errors.name }}
                        </p>
                    </div>

                    <!-- Username -->
                    <div>
                        <label class="block text-xs font-bold text-slate-300 mb-1">Username *</label>
                        <div class="relative">
                            <input
                                v-model="form.username"
                                type="text"
                                required
                                class="w-full rounded-xl bg-slate-950 border border-slate-800 pl-10 pr-4 py-2.5 text-xs text-white placeholder-slate-500 focus:border-sky-500 focus:outline-none focus:ring-1 focus:ring-sky-500 transition"
                                placeholder="jinwoo"
                            />
                            <span class="text-xs font-bold text-slate-500 absolute left-3.5 top-2.5">@</span>
                        </div>
                        <p v-if="form.errors.username" class="mt-1 text-xs text-rose-400 font-medium">
                            {{ form.errors.username }}
                        </p>
                    </div>

                    <!-- Email Address -->
                    <div>
                        <label class="block text-xs font-bold text-slate-300 mb-1">Email Address *</label>
                        <div class="relative">
                            <input
                                v-model="form.email"
                                type="email"
                                required
                                class="w-full rounded-xl bg-slate-950 border border-slate-800 pl-10 pr-4 py-2.5 text-xs text-white placeholder-slate-500 focus:border-sky-500 focus:outline-none focus:ring-1 focus:ring-sky-500 transition"
                                placeholder="jinwoo@example.com"
                            />
                            <EnvelopeIcon class="w-4 h-4 text-slate-500 absolute left-3.5 top-3" />
                        </div>
                        <p v-if="form.errors.email" class="mt-1 text-xs text-rose-400 font-medium">
                            {{ form.errors.email }}
                        </p>
                    </div>

                    <!-- Password -->
                    <div>
                        <label class="block text-xs font-bold text-slate-300 mb-1">Password *</label>
                        <div class="relative">
                            <input
                                v-model="form.password"
                                type="password"
                                required
                                class="w-full rounded-xl bg-slate-950 border border-slate-800 pl-10 pr-4 py-2.5 text-xs text-white placeholder-slate-500 focus:border-sky-500 focus:outline-none focus:ring-1 focus:ring-sky-500 transition"
                                placeholder="••••••••"
                            />
                            <LockClosedIcon class="w-4 h-4 text-slate-500 absolute left-3.5 top-3" />
                        </div>
                        <p v-if="form.errors.password" class="mt-1 text-xs text-rose-400 font-medium">
                            {{ form.errors.password }}
                        </p>
                    </div>

                    <!-- Confirm Password -->
                    <div>
                        <label class="block text-xs font-bold text-slate-300 mb-1">Confirm Password *</label>
                        <div class="relative">
                            <input
                                v-model="form.password_confirmation"
                                type="password"
                                required
                                class="w-full rounded-xl bg-slate-950 border border-slate-800 pl-10 pr-4 py-2.5 text-xs text-white placeholder-slate-500 focus:border-sky-500 focus:outline-none focus:ring-1 focus:ring-sky-500 transition"
                                placeholder="••••••••"
                            />
                            <LockClosedIcon class="w-4 h-4 text-slate-500 absolute left-3.5 top-3" />
                        </div>
                    </div>

                    <!-- Submit Button -->
                    <button
                        type="submit"
                        :disabled="form.processing"
                        class="w-full py-3 px-4 rounded-xl text-xs font-bold text-white bg-gradient-to-r from-sky-600 to-indigo-600 hover:from-sky-500 hover:to-indigo-500 focus:outline-none disabled:opacity-50 transition shadow-lg shadow-sky-600/30 active:scale-[0.98] flex items-center justify-center gap-2 mt-2"
                    >
                        <span>{{ form.processing ? "Creating Account..." : "Create Account & Join Realm" }}</span>
                        <ArrowRightIcon class="w-4 h-4" />
                    </button>
                </form>
            </div>
        </div>
    </div>
</template>
