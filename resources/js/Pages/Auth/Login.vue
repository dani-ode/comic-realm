<script setup lang="ts">
import { Head, useForm, Link, usePage } from "@inertiajs/vue3";
import { computed, ref } from "vue";
import {
  KeyIcon,
  ClipboardDocumentIcon,
  EnvelopeIcon,
  LockClosedIcon,
  UserIcon,
  ArrowRightIcon,
  CheckIcon,
} from "@heroicons/vue/24/outline";
import { useToast } from "@/composables/useToast";
import ToastContainer from "@/Components/UI/ToastContainer.vue";

const props = defineProps<{
    redirect?: string;
}>();

const form = useForm({
    login: "",
    password: "",
    remember: false,
    redirect: props.redirect || (typeof window !== 'undefined' ? new URLSearchParams(window.location.search).get('redirect') || '' : ''),
});

const { error: toastError } = useToast();
const copiedAccount = ref<string | null>(null);

const submit = () => {
    form.post("/login", {
        preserveState: true,
        preserveScroll: true,
        onError: (errors: Record<string, string>) => {
            const msg =
                errors.login ||
                errors.password ||
                Object.values(errors)[0] ||
                "Email/Username atau password yang Anda masukkan tidak sesuai.";
            toastError(msg);
        },
        onFinish: () => {
            form.reset("password");
        },
    });
};

function fillDemo(loginVal: string) {
  form.login = loginVal;
  form.password = "password123";
  copiedAccount.value = loginVal;
  navigator.clipboard.writeText(loginVal);
  setTimeout(() => {
    copiedAccount.value = null;
  }, 2500);
}
</script>

<template>
    <Head title="Sign In - ComicRealm" />

    <!-- Toast Container -->
    <ToastContainer />

    <div class="min-h-screen bg-slate-950 text-slate-100 flex flex-col justify-center items-center py-12 px-4 sm:px-6 lg:px-8 relative overflow-hidden">
        <!-- Ambient Glowing Background Orbs -->
        <div class="absolute -top-40 -left-40 w-96 h-96 bg-sky-600/15 rounded-full blur-3xl pointer-events-none"></div>
        <div class="absolute -bottom-40 -right-40 w-96 h-96 bg-indigo-600/15 rounded-full blur-3xl pointer-events-none"></div>

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
                    <h2 class="text-2xl font-black text-white tracking-tight">Return to the Realm</h2>
                    <p class="text-xs text-slate-400 mt-1">
                        Not yet part of the tale?
                        <Link href="/register" class="font-bold text-sky-400 hover:text-sky-300 hover:underline">
                            Create an account
                        </Link>
                    </p>
                </div>
            </div>

            <!-- Glass Card Container -->
            <div class="bg-slate-900/80 backdrop-blur-xl border border-slate-800/80 rounded-3xl p-6 sm:p-8 shadow-2xl space-y-6">
                <form class="space-y-4" @submit.prevent="submit">
                    <!-- Login Input -->
                    <div>
                        <label class="block text-xs font-bold text-slate-300 mb-1">Email or Username</label>
                        <div class="relative">
                            <input
                                v-model="form.login"
                                type="text"
                                required
                                class="w-full rounded-xl bg-slate-950 border border-slate-800 pl-10 pr-4 py-2.5 text-xs text-white placeholder-slate-500 focus:border-sky-500 focus:outline-none focus:ring-1 focus:ring-sky-500 transition"
                                placeholder="username or email@example.com"
                            />
                            <EnvelopeIcon class="w-4 h-4 text-slate-500 absolute left-3.5 top-3" />
                        </div>
                        <p v-if="form.errors.login" class="mt-1 text-xs text-rose-400 font-medium">
                            {{ form.errors.login }}
                        </p>
                    </div>

                    <!-- Password Input -->
                    <div>
                        <label class="block text-xs font-bold text-slate-300 mb-1">Password</label>
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

                    <!-- Remember Me -->
                    <div class="flex items-center justify-between pt-1">
                        <label class="flex items-center gap-2 text-xs text-slate-300 cursor-pointer select-none">
                            <input
                                v-model="form.remember"
                                type="checkbox"
                                class="rounded bg-slate-950 border-slate-800 text-sky-600 focus:ring-sky-500 w-4 h-4"
                            />
                            <span>Remember me</span>
                        </label>
                    </div>

                    <!-- Submit Button -->
                    <button
                        type="submit"
                        :disabled="form.processing"
                        class="w-full py-3 px-4 rounded-xl text-xs font-bold text-white bg-gradient-to-r from-sky-600 to-indigo-600 hover:from-sky-500 hover:to-indigo-500 focus:outline-none disabled:opacity-50 transition shadow-lg shadow-sky-600/30 active:scale-[0.98] flex items-center justify-center gap-2"
                    >
                        <span>{{ form.processing ? "Signing in..." : "Sign In to Realm" }}</span>
                        <ArrowRightIcon class="w-4 h-4" />
                    </button>
                </form>

                <!-- Interactive Demo Accounts Panel -->
                <div class="pt-4 border-t border-slate-800/80 space-y-2.5">
                    <div class="flex items-center justify-between text-xs text-slate-400">
                        <span class="font-bold text-slate-300 flex items-center gap-1.5">
                            <KeyIcon class="w-3.5 h-3.5 text-sky-400" /> Demo Accounts
                        </span>
                        <span class="text-[11px] text-slate-500">Click to fill</span>
                    </div>

                    <div class="grid grid-cols-3 gap-2">
                        <button
                            type="button"
                            @click="fillDemo('reader@comicrealm.test')"
                            class="p-2.5 rounded-xl bg-slate-950 border border-slate-800 hover:border-sky-500/40 text-left transition group"
                        >
                            <p class="text-[11px] font-bold text-sky-400 group-hover:text-sky-300">Reader</p>
                            <p class="text-[9px] text-slate-500 truncate">reader@comicrealm.test</p>
                        </button>

                        <button
                            type="button"
                            @click="fillDemo('publisher@comicrealm.test')"
                            class="p-2.5 rounded-xl bg-slate-950 border border-slate-800 hover:border-sky-500/40 text-left transition group"
                        >
                            <p class="text-[11px] font-bold text-indigo-400 group-hover:text-indigo-300">Publisher</p>
                            <p class="text-[9px] text-slate-500 truncate">publisher@comicrealm.test</p>
                        </button>

                        <button
                            type="button"
                            @click="fillDemo('admin@comicrealm.test')"
                            class="p-2.5 rounded-xl bg-slate-950 border border-slate-800 hover:border-sky-500/40 text-left transition group"
                        >
                            <p class="text-[11px] font-bold text-purple-400 group-hover:text-purple-300">Admin</p>
                            <p class="text-[9px] text-slate-500 truncate">admin@comicrealm.test</p>
                        </button>
                    </div>

                    <p v-if="copiedAccount" class="text-[10px] text-emerald-400 font-semibold flex items-center justify-center gap-1">
                        <CheckIcon class="w-3 h-3" /> Credentials filled for {{ copiedAccount }} (Password: password123)
                    </p>
                </div>
            </div>
        </div>
    </div>
</template>
