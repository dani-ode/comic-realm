<script setup lang="ts">
import { Link, usePage, router } from "@inertiajs/vue3";
import { ref, computed, onMounted, onUnmounted, watch, nextTick } from "vue";
import { useToast } from "@/composables/useToast";
import ToastContainer from "@/Components/UI/ToastContainer.vue";
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
    MagnifyingGlassIcon,
    XMarkIcon,
    ArrowLeftIcon,
    ShieldCheckIcon,
} from "@heroicons/vue/24/outline";

const props = withDefaults(
    defineProps<{
        minimal?: boolean;
        title?: string;
        backUrl?: string;
    }>(),
    {
        minimal: false,
        title: "",
        backUrl: "",
    }
);

const handleBack = () => {
    if (props.backUrl) {
        router.get(props.backUrl);
    } else if (typeof window !== "undefined" && window.history.length > 1) {
        window.history.back();
    } else {
        router.get("/cart");
    }
};

const page = usePage();
const isDropdownOpen = ref(false);
const dropdownRef = ref<HTMLElement | null>(null);

const isSearchModalOpen = ref(false);
const searchInputRef = ref<HTMLInputElement | null>(null);
const searchQuery = ref("");

const openSearchModal = () => {
    isSearchModalOpen.value = true;
    nextTick(() => {
        if (searchInputRef.value) {
            searchInputRef.value.focus();
        }
    });
};

const handleSearchSubmit = () => {
    const q = searchQuery.value.trim();
    if (!q) return;
    isSearchModalOpen.value = false;
    router.get('/comics', { search: q });
};

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

const { success, error } = useToast();

// Watch Inertia flash messages and display as toasts
watch(
    () => (page.props as any).flash,
    (flash) => {
        if (flash?.success) success(flash.success);
        if (flash?.error) error(flash.error);
    },
    { deep: true, immediate: true },
);
const topGenres = computed(() => {
    return ((page.props as any).topGenres as Array<{ id: number; name: string; slug: string }>) || [];
});
</script>

<template>
    <div class="min-h-screen bg-slate-950 text-slate-100 flex flex-col">
        <!-- Global Toast Notifications -->
        <ToastContainer />

        <!-- Minimal Checkout Header -->
        <header
            v-if="minimal"
            class="sticky top-0 z-50 bg-slate-950/90 backdrop-blur-md border-b border-slate-800/80 px-4 lg:px-8 py-3.5 flex items-center justify-between"
        >
            <div class="flex items-center gap-3">
                <!-- Back Button -->
                <button
                    @click="handleBack"
                    class="flex items-center justify-center w-9 h-9 rounded-xl bg-slate-900 border border-slate-800 text-slate-300 hover:text-white hover:border-slate-700 transition active:scale-95 shrink-0"
                    title="Kembali"
                >
                    <ArrowLeftIcon class="w-4 h-4" />
                </button>

                <div class="flex items-center gap-2.5">
                    <Link href="/" class="shrink-0">
                        <img
                            src="/favicon.ico"
                            alt="ComicRealm"
                            class="w-7 h-7 rounded-xl object-contain shadow-md shadow-sky-500/20"
                        />
                    </Link>
                    <div>
                        <h1 class="text-sm sm:text-base font-extrabold text-white leading-none">
                            {{ title || "Checkout Order" }}
                        </h1>
                        <p class="text-[10px] text-slate-400 mt-0.5">The ComicRealm Transaction</p>
                    </div>
                </div>
            </div>

            <!-- Secure Badge -->
            <div class="flex items-center gap-1.5 text-xs font-semibold text-emerald-400 bg-emerald-500/10 border border-emerald-500/30 px-3 py-1.5 rounded-xl shrink-0">
                <ShieldCheckIcon class="w-4 h-4 shrink-0" />
                <span class="hidden sm:inline">Pembayaran Aman</span>
            </div>
        </header>

        <!-- Standard Navbar -->
        <header
            v-else
            class="sticky top-0 z-50 bg-slate-950/80 backdrop-blur-md border-b border-slate-800/80 px-4 lg:px-8 py-3.5 flex items-center justify-between gap-4"
        >
            <!-- Logo -->
            <Link href="/" class="flex items-center gap-2 shrink-0">
                <img
                    src="/favicon.ico"
                    alt="ComicRealm"
                    class="w-8 h-8 rounded-2xl object-contain shadow-lg shadow-sky-500/20"
                />
                <span
                    class="font-brand text-[21px] font-extrabold bg-gradient-to-r from-sky-400 via-indigo-400 to-purple-400 bg-clip-text text-transparent tracking-wide"
                >
                    ComicRealm
                </span>
            </Link>

            <!-- Nav Links (Studio List + Catalog + Top 10 Most Used Genres) -->
            <nav class="hidden md:flex items-center gap-3 text-xs font-semibold overflow-x-auto scrollbar-none py-1">
                <Link
                    href="/studios"
                    class="text-slate-300 hover:text-white transition whitespace-nowrap shrink-0 px-2.5 py-1 rounded-lg"
                    :class="
                        page.url.startsWith('/studios')
                            ? 'text-sky-400 font-extrabold bg-sky-500/10 border border-sky-500/20'
                            : ''
                    "
                >
                    All Studios
                </Link>
                <Link
                    href="/comics"
                    class="text-slate-300 hover:text-white transition whitespace-nowrap shrink-0 px-2.5 py-1 rounded-lg"
                    :class="
                        page.url === '/comics'
                            ? 'text-sky-400 font-extrabold bg-sky-500/10 border border-sky-500/20'
                            : ''
                    "
                >
                    Catalog
                </Link>
                <Link
                    v-for="genre in topGenres"
                    :key="genre.id"
                    :href="`/comics?genre=${genre.slug}`"
                    class="text-slate-300 hover:text-white transition whitespace-nowrap shrink-0 px-2.5 py-1 rounded-lg"
                    :class="
                        page.url.includes(`genre=${genre.slug}`)
                            ? 'text-sky-400 font-extrabold bg-sky-500/10 border border-sky-500/20'
                            : ''
                    "
                >
                    {{ genre.name }}
                </Link>
            </nav>

            <div class="flex items-center gap-3">
                <!-- Search Button -->
                <button
                    @click="openSearchModal"
                    class="flex items-center justify-center w-9 h-9 rounded-xl bg-slate-900 border border-slate-800 text-slate-300 hover:text-white hover:border-slate-700 transition active:scale-95 shrink-0"
                    title="Search Comics"
                >
                    <MagnifyingGlassIcon class="w-4 h-4" />
                </button>

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
                                href="/profile"
                                @click="isDropdownOpen = false"
                                class="flex items-center gap-2.5 px-4 py-2 text-slate-300 hover:bg-slate-800 hover:text-white transition"
                            >
                                <UserCircleIcon
                                    class="w-4 h-4 shrink-0 text-slate-400"
                                />
                                My Profile & Settings
                            </Link>

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
                                    class="w-4 h-4 shrink-0 text-slate-400"
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
                        class="text-xs font-bold px-4 py-2 rounded-xl bg-sky-600 hover:bg-sky-500 text-white transition"
                        >Sign In</Link
                    >
                </div>
            </div>
        </header>

        <!-- Mobile Nav Bar (Studio List + Catalog + Top 10 Most Used Genres) -->
        <nav v-if="!minimal" class="md:hidden bg-slate-950/90 backdrop-blur-md border-b border-slate-800/80 px-3 py-2 flex items-center gap-2 text-xs font-medium overflow-x-auto shrink-0 sticky top-[57px] z-40 scrollbar-none">
            <Link
                href="/studios"
                class="px-3 py-1 rounded-lg transition whitespace-nowrap shrink-0"
                :class="page.url.startsWith('/studios') ? 'bg-sky-500/10 text-sky-400 font-bold border border-sky-500/20' : 'text-slate-400 hover:text-white'"
            >
                All Studios
            </Link>
            <Link
                href="/comics"
                class="px-3 py-1 rounded-lg transition whitespace-nowrap shrink-0"
                :class="page.url === '/comics' ? 'bg-sky-500/10 text-sky-400 font-bold border border-sky-500/20' : 'text-slate-400 hover:text-white'"
            >
                Catalog
            </Link>
            <Link
                v-for="genre in topGenres"
                :key="genre.id"
                :href="`/comics?genre=${genre.slug}`"
                class="px-3 py-1 rounded-lg transition whitespace-nowrap shrink-0"
                :class="page.url.includes(`genre=${genre.slug}`) ? 'bg-sky-500/10 text-sky-400 font-bold border border-sky-500/20' : 'text-slate-400 hover:text-white'"
            >
                {{ genre.name }}
            </Link>
        </nav>

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
                <link rel="icon" href="/favicon.ico" type="image/x-icon" />
                <Link href="/comics" class="hover:underline"
                    >Browse Comics</Link
                >
                <Link href="/publisher/apply" class="hover:underline"
                    >Publisher Studio</Link
                >
            </div>
        </footer>

        <!-- Search Popup Modal -->
        <Teleport to="body">
            <div
                v-if="isSearchModalOpen"
                class="fixed inset-0 z-[99999] bg-slate-950/80 backdrop-blur-md flex items-start justify-center pt-24 px-4"
                @click.self="isSearchModalOpen = false"
            >
                <div class="bg-slate-900 border border-slate-800 rounded-3xl p-5 sm:p-6 shadow-2xl w-full max-w-lg space-y-4 relative">
                    <!-- Modal Header -->
                    <div class="flex items-center justify-between">
                        <span class="text-xs font-bold text-sky-400 flex items-center gap-1.5">
                            <MagnifyingGlassIcon class="w-4 h-4" /> Search Comic Catalog
                        </span>
                        <button
                            @click="isSearchModalOpen = false"
                            class="text-slate-400 hover:text-white transition p-1"
                        >
                            <XMarkIcon class="w-5 h-5" />
                        </button>
                    </div>

                    <!-- Search Input Form -->
                    <form @submit.prevent="handleSearchSubmit" class="relative">
                        <input
                            ref="searchInputRef"
                            v-model="searchQuery"
                            type="text"
                            placeholder="Enter comic title, author, or keyword..."
                            class="w-full rounded-2xl bg-slate-950 border border-slate-700 pl-11 pr-24 py-3.5 text-xs sm:text-sm text-white placeholder-slate-500 focus:border-sky-500 focus:outline-none focus:ring-2 focus:ring-sky-500/20"
                            @keydown.escape="isSearchModalOpen = false"
                        />
                        <MagnifyingGlassIcon class="w-5 h-5 text-slate-500 absolute left-3.5 top-3.5" />
                        <button
                            type="submit"
                            class="absolute right-2 top-2 bottom-2 px-4 rounded-xl bg-sky-600 hover:bg-sky-500 text-white font-bold text-xs transition flex items-center gap-1"
                        >
                            <span>Search</span>
                        </button>
                    </form>

                    <div class="text-[11px] text-slate-500 flex items-center justify-between pt-1">
                        <span>Press <kbd class="px-1.5 py-0.5 rounded bg-slate-950 border border-slate-800 text-slate-300 font-mono text-[10px]">Enter</kbd> to view results</span>
                        <span>Press <kbd class="px-1.5 py-0.5 rounded bg-slate-950 border border-slate-800 text-slate-300 font-mono text-[10px]">ESC</kbd> to close</span>
                    </div>
                </div>
            </div>
        </Teleport>
    </div>
</template>
