<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import { ref } from 'vue';
import axios from 'axios';
import PublicLayout from '@/Layouts/PublicLayout.vue';
import {
  CreditCardIcon,
  ShieldCheckIcon,
  ShoppingBagIcon,
  ArrowRightIcon,
  Squares2X2Icon,
  InboxIcon,
} from '@heroicons/vue/24/outline';
import { useToast } from '@/composables/useToast';

interface CartItem {
  id: number;
  chapter_id: number;
  price: number;
  chapter: {
    id: number;
    title: string;
    chapter_number: number;
    price: number;
    comic: {
      id: number;
      title: string;
      cover_image: string;
    };
  };
}

interface Cart {
  id: number;
  total_amount: number;
  items: CartItem[];
}

const props = defineProps<{
  cart?: Cart | null;
}>();

const { error: toastError } = useToast();
const isLoading = ref(false);

const submitOrder = async () => {
  isLoading.value = true;
  try {
    const res = await axios.post('/api/checkout/process');
    if (res.data && res.data.redirect_url) {
      window.location.href = res.data.redirect_url;
    }
  } catch (err: any) {
    const msg = err.response?.data?.message || 'Gagal memproses checkout. Silakan coba lagi.';
    toastError(msg);
  } finally {
    isLoading.value = false;
  }
};
</script>

<template>
  <Head title="Checkout Order - ComicRealm" />

  <PublicLayout minimal title="Checkout Summary" backUrl="/cart">
    <main class="max-w-4xl mx-auto px-4 lg:px-8 py-10 w-full flex-1 space-y-8">
      <!-- Header Title -->
      <div>
        <!-- <h1 class="text-3xl font-extrabold text-white flex items-center gap-3">
          <span class="w-9 h-9 rounded-xl bg-sky-500/10 border border-sky-500/30 flex items-center justify-center shrink-0">
            <CreditCardIcon class="w-5 h-5 text-sky-400" />
          </span>
          Checkout Summary
        </h1> -->
        <p class="text-sm text-slate-400 mt-1">Review items and confirm order to select payment channel (QRIS, VA, E-Wallet)</p>
      </div>

      <!-- Checkout Container -->
      <div v-if="cart && cart.items && cart.items.length" class="bg-slate-900 border border-slate-800 rounded-3xl p-6 sm:p-8 space-y-6 shadow-2xl">
        <div class="flex items-center justify-between border-b border-slate-800 pb-4">
          <h2 class="text-lg font-bold text-white flex items-center gap-2">
            <ShoppingBagIcon class="w-5 h-5 text-sky-400" />
            Selected Chapters ({{ cart.items.length }})
          </h2>
          <Link href="/cart" class="text-xs text-sky-400 hover:underline font-semibold">
            Edit Cart
          </Link>
        </div>

        <!-- Items List -->
        <div class="divide-y divide-slate-800/80">
          <div
            v-for="item in cart.items"
            :key="item.id"
            class="py-3.5 flex items-center justify-between gap-4"
          >
            <div class="flex items-center gap-3.5 min-w-0">
              <img
                :src="item.chapter.comic.cover_image"
                :alt="item.chapter.comic.title"
                class="w-12 h-16 object-cover rounded-xl border border-slate-800 shrink-0 bg-slate-950"
              />
              <div class="min-w-0">
                <span class="text-xs text-sky-400 font-medium block truncate">{{ item.chapter.comic.title }}</span>
                <h3 class="font-bold text-white text-xs sm:text-sm truncate">
                  Chapter {{ item.chapter.chapter_number }}: {{ item.chapter.title }}
                </h3>
                <span class="text-[10px] text-slate-500 block mt-0.5">Digital Access Entitlement</span>
              </div>
            </div>

            <div class="font-bold text-amber-400 text-xs sm:text-sm shrink-0">
              Rp {{ item.price ? item.price.toLocaleString() : '5,000' }}
            </div>
          </div>
        </div>

        <!-- Summary & Total -->
        <div class="pt-4 border-t border-slate-800 space-y-3 text-xs sm:text-sm">
          <div class="flex justify-between text-slate-400">
            <span>Subtotal ({{ cart.items.length }} items)</span>
            <span class="text-white">Rp {{ cart.total_amount ? cart.total_amount.toLocaleString() : '0' }}</span>
          </div>
          <div class="flex justify-between text-slate-400">
            <span class="flex items-center gap-1">
              <ShieldCheckIcon class="w-4 h-4 text-emerald-400" />
              Secure TriPay Gateway Fee
            </span>
            <span class="text-emerald-400 font-medium">Calculated at Payment</span>
          </div>

          <div class="pt-3 border-t border-slate-800 flex justify-between text-base font-extrabold">
            <span class="text-white">Total Payable</span>
            <span class="text-amber-400 text-lg sm:text-xl">Rp {{ cart.total_amount ? cart.total_amount.toLocaleString() : '0' }}</span>
          </div>
        </div>

        <!-- Confirm CTA Button — Responsive Labels for Mobile HP vs Desktop -->
        <button
          @click="submitOrder"
          :disabled="isLoading"
          class="w-full py-4 px-6 rounded-2xl text-sm font-bold text-white bg-gradient-to-r from-sky-600 to-indigo-600 hover:from-sky-500 hover:to-indigo-500 disabled:opacity-50 transition shadow-xl shadow-sky-600/30 active:scale-[0.98] flex items-center justify-center gap-2.5"
        >
          <CreditCardIcon class="w-5 h-5 shrink-0" />
          <span v-if="isLoading">Processing Order Invoice...</span>
          <template v-else>
            <!-- Mobile label -->
            <span class="sm:hidden">Confirm & Pay (Rp {{ cart.total_amount ? cart.total_amount.toLocaleString() : '0' }})</span>
            <!-- Desktop label -->
            <span class="hidden sm:inline">Confirm Order & Select Payment Method</span>
            <ArrowRightIcon class="w-4 h-4 shrink-0" />
          </template>
        </button>
      </div>

      <!-- Empty State -->
      <div v-else class="bg-slate-900/40 border border-slate-800 rounded-3xl p-16 text-center space-y-4">
        <div class="w-16 h-16 bg-slate-950 border border-slate-800 rounded-2xl flex items-center justify-center mx-auto">
          <InboxIcon class="w-8 h-8 text-slate-500" />
        </div>
        <h2 class="text-xl font-bold text-white">No Items Selected for Checkout</h2>
        <p class="text-sm text-slate-400 max-w-md mx-auto">
          Your cart is currently empty. Explore our comic catalog to add webcomic chapters.
        </p>
        <div class="pt-2">
          <Link href="/comics" class="inline-flex items-center gap-2 px-6 py-3 rounded-xl bg-sky-600 hover:bg-sky-500 text-white font-semibold text-sm transition">
            <Squares2X2Icon class="w-4 h-4" />
            Explore Comic Catalog
          </Link>
        </div>
      </div>
    </main>
  </PublicLayout>
</template>
