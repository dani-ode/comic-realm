<script setup lang="ts">
import { Link, usePage, router } from "@inertiajs/vue3";
import { ref, onMounted, onUnmounted } from "vue";
import {
    BookOpenIcon,
    ShoppingCartIcon,
    ChevronDownIcon,
    CreditCardIcon,
    SwatchIcon,
    BookmarkIcon,
    HeartIcon,
    RectangleGroupIcon,
    ArrowRightOnRectangleIcon,
    UserCircleIcon,
    BuildingStorefrontIcon,
} from "@heroicons/vue/24/outline";

const page = usePage();
const isDropdownOpen = ref(false);
const dropdownRef = ref<HTMLElement | null>(null);

const toggleDropdown = () => {
    isDropdownOpen.value = !isDropdownOpen.value;
};

const handleLogout = () => {
    isDropdownOpen.value = false;
    router.post("/logout");
};

const closeDropdown = (e: MouseEvent) => {
    if (dropdownRef.value && !dropdownRef.value.contains(e.target as Node)) {
        isDropdownOpen.value = false;
    }
};

onMounted(() => {
    window.addEventListener("click", closeDropdown);
});
onUnmounted(() => {
    window.removeEventListener("click", closeDropdown);
});
</script>

<template>
    <div class="min-h-screen bg-slate-950 text-slate-100 flex flex-col">
        <!-- Navbar -->
        <header
            class="sticky top-0 z-50 bg-slate-950/80 backdrop-blur-md border-b border-slate-800/80 px-4 lg:px-8 py-3.5 flex items-center justify-between"
        >
            <!-- Logo -->
            <Link href="/" class="flex items-center gap-2">
                <div
                    class="w-7 h-7 rounded-lg bg-gradient-to-br from-sky-400 to-indigo-500 flex items-center justify-center shadow-lg shadow-sky-500/20"
                >
                    <SwatchIcon class="w-4 h-4 text-white" />
                </div>
                <span
                    class="font-brand text-[21px] font-extrabold bg-gradient-to-r from-sky-400 via-indigo-400 to-purple-400 bg-clip-text text-transparent tracking-wide"
                >
                    ComicRealm
                </span>
            </Link>

            <!-- Nav Links -->
            <nav class="hidden md:flex items-center gap-6 text-sm font-medium">
                <Link
                    href="/"
                    class="text-slate-300 hover:text-white transition"
                    :class="
                        usePage().url === '/' ? 'text-sky-400 font-bold' : ''
                    "
                >
                    Home
                </Link>
                <Link
                    href="/comics"
                    class="text-slate-300 hover:text-white transition"
                    :class="
                        usePage().url.startsWith('/comics')
                            ? 'text-sky-400 font-bold'
                            : ''
                    "
                >
                    Catalog
                </Link>
                <Link
                    href="/library"
                    class="flex items-center gap-1.5 text-slate-300 hover:text-white transition"
                    :class="
                        usePage().url.startsWith('/library')
                            ? 'text-sky-400 font-bold'
                            : ''
                    "
                >
                    <BookOpenIcon class="w-4 h-4" />
                    My Library
                </Link>
                <Link
                    href="/bookmarks"
                    class="flex items-center gap-1.5 text-slate-300 hover:text-white transition"
                    :class="
                        usePage().url.startsWith('/bookmarks')
                            ? 'text-sky-400 font-bold'
                            : ''
                    "
                >
                    <HeartIcon class="w-4 h-4 text-rose-400" />
                    Bookmarks
                </Link>
            </nav>

            <div class="flex items-center gap-3">
                <!-- Cart Button -->
                <Link
                    href="/cart"
                    class="relative flex items-center gap-1.5 text-xs font-semibold px-3.5 py-2 rounded-xl bg-slate-900 border border-slate-800 text-slate-300 hover:text-white hover:border-slate-700 transition"
                >
                    <ShoppingCartIcon class="w-4 h-4" />
                    <span class="hidden sm:inline">Cart</span>
                    <span
                        v-if="($page.props as any).cartCount > 0"
                        class="absolute -top-1.5 -right-1.5 px-1.5 py-0.5 text-[10px] font-bold rounded-full bg-sky-500 text-white"
                    >
                        {{ ($page.props as any).cartCount }}
                    </span>
                </Link>

                <!-- Authenticated Dropdown -->
                <div
                    v-if="($page.props.auth as any)?.user"
                    ref="dropdownRef"
                    class="relative"
                >
                    <button
                        @click.stop="toggleDropdown"
                        class="flex items-center gap-2 px-2.5 py-1.5 rounded-xl bg-slate-900 border border-slate-800 hover:border-slate-700 text-slate-200 transition focus:outline-none"
                    >
                        <!-- Avatar -->
                        <img
                            v-if="($page.props.auth as any).user.avatar"
                            :src="($page.props.auth as any).user.avatar"
                            :alt="($page.props.auth as any).user.name"
                            class="w-7 h-7 rounded-full object-cover border border-slate-700 shrink-0"
                        />
                        <div
                            v-else
                            class="w-7 h-7 rounded-full bg-gradient-to-tr from-sky-500 to-indigo-600 text-white font-bold text-xs flex items-center justify-center border border-sky-400/30 shrink-0"
                        >
                            {{
                                ($page.props.auth as any).user.name
                                    ? ($page.props.auth as any).user.name
                                          .charAt(0)
                                          .toUpperCase()
                                    : "U"
                            }}
                        </div>

                        <span
                            class="hidden sm:inline-block text-xs font-semibold text-white max-w-[120px] truncate"
                        >
                            {{ ($page.props.auth as any).user.name }}
                        </span>

                        <ChevronDownIcon
                            class="w-3.5 h-3.5 text-slate-400 transition-transform"
                            :class="isDropdownOpen ? 'rotate-180' : ''"
                        />
                    </button>

                    <!-- Dropdown -->
                    <div
                        v-if="isDropdownOpen"
                        class="absolute right-0 mt-2 w-56 bg-slate-900 border border-slate-800 rounded-2xl shadow-2xl py-2 z-50 divide-y divide-slate-800 text-xs"
                    >
                        <!-- User Info -->
                        <div class="px-4 py-2.5">
                            <p class="font-bold text-white truncate">
                                {{ ($page.props.auth as any).user.name }}
                            </p>
                            <p class="text-[11px] text-slate-400 truncate">
                                @{{
                                    ($page.props.auth as any).user.username ||
                                    ($page.props.auth as any).user.email
                                }}
                            </p>
                        </div>

                        <!-- Links -->
                        <div class="py-1">
                            <Link
                                href="/orders"
                                @click="isDropdownOpen = false"
                                class="flex items-center gap-2.5 px-4 py-2 text-slate-300 hover:bg-slate-800 hover:text-white transition"
                            >
                                <CreditCardIcon
                                    class="w-4 h-4 shrink-0 text-slate-400"
                                />
                                My Orders & Transactions
                            </Link>

                            <Link
                                v-if="($page.props.auth as any).user.role === 'admin'"
                                href="/admin/dashboard"
                                @click="isDropdownOpen = false"
                                class="flex items-center gap-2.5 px-4 py-2 text-sky-400 font-semibold hover:bg-slate-800 transition"
                            >
                                <RectangleGroupIcon
                                    class="w-4 h-4 shrink-0 text-sky-400"
                                />
                                Admin Dashboard
                            </Link>
                            <Link
                                v-else-if="($page.props.auth as any).user.role === 'publisher'"
                                href="/publisher/dashboard"
                                @click="isDropdownOpen = false"
                                class="flex items-center gap-2.5 px-4 py-2 text-slate-300 hover:bg-slate-800 hover:text-white transition"
                            >
                                <SwatchIcon
                                    class="w-4 h-4 shrink-0 text-slate-400"
                                />
                                Publisher Dashboard
                            </Link>
                            <Link
                                v-else
                                href="/publisher/dashboard"
                                @click="isDropdownOpen = false"
                                class="flex items-center gap-2.5 px-4 py-2 text-slate-300 hover:bg-slate-800 hover:text-white transition"
                            >
                                <SwatchIcon
                                    class="w-4 h-4 shrink-0 text-slate-400"
                                />
                                Publish Your Webcomic
                            </Link>

                            <Link
                                href="/bookmarks"
                                @click="isDropdownOpen = false"
                                class="flex items-center gap-2.5 px-4 py-2 text-slate-300 hover:bg-slate-800 hover:text-white transition"
                            >
                                <HeartIcon
                                    class="w-4 h-4 shrink-0 text-rose-400"
                                />
                                My Bookmarks
                            </Link>

                            <Link
                                href="/library"
                                @click="isDropdownOpen = false"
                                class="flex items-center gap-2.5 px-4 py-2 text-slate-300 hover:bg-slate-800 hover:text-white transition"
                            >
                                <BookOpenIcon
                                    class="w-4 h-4 shrink-0 text-slate-400"
                                />
                                My Library
                            </Link>
                        </div>

                        <!-- Logout -->
                        <div class="py-1">
                            <button
                                @click="handleLogout"
                                class="w-full text-left flex items-center gap-2.5 px-4 py-2 text-rose-400 hover:bg-rose-500/10 transition"
                            >
                                <ArrowRightOnRectangleIcon
                                    class="w-4 h-4 shrink-0"
                                />
                                Logout
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Guest Links -->
                <div v-else class="flex items-center gap-2">
                    <Link
                        href="/login"
                        class="text-xs font-semibold px-3 py-1.5 text-slate-300 hover:text-white"
                        >Sign In</Link
                    >
                    <Link
                        href="/register"
                        class="text-xs font-bold px-4 py-2 rounded-xl bg-sky-600 hover:bg-sky-500 text-white transition"
                        >Register</Link
                    >
                </div>
            </div>
        </header>

        <!-- Page Content -->
        <main class="flex-1">
            <slot />
        </main>

        <!-- Footer -->
        <footer
            class="border-t border-slate-800/80 bg-slate-950 py-8 px-4 lg:px-8 text-center text-xs text-slate-500 space-y-2"
        >
            <p>
                © 2026 The ComicRealm — Premium Webcomic Streaming & TriPay
                Monetization Platform
            </p>
            <div class="flex justify-center gap-4 text-slate-400">
                <Link href="/comics" class="hover:underline"
                    >Browse Comics</Link
                >
                <Link href="/publisher/apply" class="hover:underline"
                    >Publisher Studio</Link
                >
            </div>
        </footer>
    </div>
</template>
