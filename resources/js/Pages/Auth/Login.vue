<script setup lang="ts">
import { Head, useForm, Link, usePage } from "@inertiajs/vue3";
import { ExclamationTriangleIcon, KeyIcon, ClipboardDocumentIcon } from '@heroicons/vue/24/outline';
import { computed, ref } from "vue";
import { useToast } from "@/composables/useToast";
import ToastContainer from "@/Components/UI/ToastContainer.vue";

const page = usePage();

const errorMessage = ref("");

const activeError = computed(() => {
    if (errorMessage.value) return errorMessage.value;
    if (form.errors.login) return form.errors.login;
    if (form.errors.password) return form.errors.password;
    const pageProps = page.props as any;
    if (pageProps?.errors?.login) return pageProps.errors.login;
    if (pageProps?.errors?.password) return pageProps.errors.password;
    if (pageProps?.flash?.error) return pageProps.flash.error;
    return "";
});

const form = useForm({
    login: "",
    password: "",
    remember: false,
});

const { error: toastError } = useToast();

const submit = (e?: Event) => {
    if (e) e.preventDefault();
    console.log("[Login.vue] Form submitted via Inertia AJAX:", form.login);
    errorMessage.value = "";

    form.post("/login", {
        preserveState: true,
        preserveScroll: true,
        onStart: () => console.log("[Login.vue] Inertia POST starting..."),
        onSuccess: () => console.log("[Login.vue] Inertia POST success"),
        onError: (errors: Record<string, string>) => {
            console.error("[Login.vue] Inertia POST returned errors:", errors);
            const msg =
                errors.login ||
                errors.password ||
                Object.values(errors)[0] ||
                "Email/Username atau password yang Anda masukkan tidak sesuai.";
            errorMessage.value = msg;
            toastError(msg);
        },
        onFinish: () => {
            console.log("[Login.vue] Inertia POST finished.");
            form.reset("password");
        },
    });
};
function copyToClipboard(email: string) {
  const text = `${email}`;
  navigator.clipboard.writeText(text).then(() => {
    console.log('Demo credentials copied to clipboard');
  }).catch(err => {
    console.error('Failed to copy demo credentials: ', err);
  });
}
</script>

<template>
    <Head title="Sign In" />

    <!-- Toast for login errors -->
    <ToastContainer />

    <div
        class="min-h-screen bg-slate-950 flex flex-col justify-center py-12 sm:px-6 lg:px-8"
    >
        <div class="sm:mx-auto sm:w-full sm:max-w-md text-center">
            <Link
                href="/"
                class="text-3xl font-extrabold bg-gradient-to-r from-sky-400 to-indigo-400 bg-clip-text text-transparent"
            >
                Comic Realm
            </Link>
            <h2 class="mt-4 text-2xl font-bold tracking-tight text-white">
                Return to the realm
            </h2>
            <p class="mt-2 text-sm text-slate-400">
                Not yet part of the tale?
                <Link
                    href="/register"
                    class="font-medium text-sky-400 hover:text-sky-300"
                    >Step into the legend</Link
                >
            </p>
        </div>

        <div class="mt-8 sm:mx-auto sm:w-full sm:max-w-md space-y-4">
            <div
                class="bg-slate-900 border border-slate-800 py-8 px-4 shadow-xl sm:rounded-2xl sm:px-10 space-y-6"
            >
                <!-- Prominent Error Alert Banner -->
                <div
                    v-if="activeError"
                    class="p-4 bg-rose-500/10 border border-rose-500/30 rounded-xl text-rose-400 text-xs font-medium space-y-1"
                >
                    <div class="flex items-center gap-2 font-bold text-sm">
                        <ExclamationTriangleIcon class="w-4 h-4" /> Sign In Failed
                    </div>
                    <p>{{ activeError }}</p>
                </div>

                <form class="space-y-5" @submit.prevent="submit">
                    <div>
                        <label class="block text-sm font-medium text-slate-300"
                            >Email or Username</label
                        >
                        <input
                            v-model="form.login"
                            type="text"
                            required
                            class="mt-1 block w-full rounded-xl bg-slate-950 border border-slate-800 px-3.5 py-2.5 text-white placeholder-slate-500 focus:border-sky-500 focus:outline-none focus:ring-1 focus:ring-sky-500 text-sm"
                            placeholder="username or email@example.com"
                        />
                        <p
                            v-if="form.errors.login"
                            class="mt-1 text-xs text-rose-400"
                        >
                            {{ form.errors.login }}
                        </p>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-slate-300"
                            >Password</label
                        >
                        <input
                            v-model="form.password"
                            type="password"
                            required
                            class="mt-1 block w-full rounded-xl bg-slate-950 border border-slate-800 px-3.5 py-2.5 text-white placeholder-slate-500 focus:border-sky-500 focus:outline-none focus:ring-1 focus:ring-sky-500 text-sm"
                            placeholder="••••••••"
                        />
                        <p
                            v-if="form.errors.password"
                            class="mt-1 text-xs text-rose-400"
                        >
                            {{ form.errors.password }}
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
                            <label
                                for="remember-me"
                                class="ml-2 block text-sm text-slate-300"
                                >Remember me</label
                            >
                        </div>
                    </div>

                    <div>
                        <button
                            type="button"
                            @click="submit"
                            :disabled="form.processing"
                            class="w-full flex justify-center py-3 px-4 border border-transparent rounded-xl text-sm font-semibold text-white bg-sky-600 hover:bg-sky-500 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-sky-500 disabled:opacity-50 transition shadow-lg shadow-sky-600/30"
                        >
                            {{ form.processing ? "Signing in..." : "Sign In" }}
                        </button>
                    </div>
                </form>

                <!-- Demo Accounts Hint -->
                <div
                    class="pt-4 border-t border-slate-800/80 space-y-1.5 text-xs text-slate-400"
                >
                    <p class="font-bold text-slate-300 flex items-center gap-1.5">
                        <KeyIcon class="w-4 h-4" /> Demo Accounts ( Password :
                        <span class="text-sky-400">password123</span>  <button @click="copyToClipboard('password123')" class="inline-flex items-center gap-1 text-slate-400 hover:text-sky-400">
                            <ClipboardDocumentIcon class="w-4 h-4" /> Copy
                        </button>)
                    </p>
                    <div class="flex items-center justify-between">
                        <p class="flex items-center gap-1">
                            • User: <span class="text-sky-400">reader@comicrealm.test</span>
                        </p>
                        <button @click="copyToClipboard('reader@comicrealm.test')" class="inline-flex items-center gap-1 text-slate-400 hover:text-sky-400">
                            <ClipboardDocumentIcon class="w-4 h-4" /> Copy
                        </button>
                    </div>
                    <div class="flex items-center justify-between">
                        <p class="flex items-center gap-1">
                            • Publisher: <span class="text-sky-400">publisher@comicrealm.test</span>
                        </p>
                        <button @click="copyToClipboard('publisher@comicrealm.test')" class="inline-flex items-center gap-1 text-slate-400 hover:text-sky-400">
                            <ClipboardDocumentIcon class="w-4 h-4" /> Copy
                        </button>
                    </div>
                    <div class="flex items-center justify-between">
                        <p class="flex items-center gap-1">
                            • Admin: <span class="text-sky-400">admin@comicrealm.test</span>
                        </p>
                        <button @click="copyToClipboard('admin@comicrealm.test')" class="inline-flex items-center gap-1 text-slate-400 hover:text-sky-400">
                            <ClipboardDocumentIcon class="w-4 h-4" /> Copy
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>
