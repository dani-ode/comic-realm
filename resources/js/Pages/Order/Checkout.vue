<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import { ref } from 'vue';
import axios from 'axios';

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

const isLoading = ref(false);
const errorMessage = ref('');

const submitOrder = async () => {
  isLoading.value = true;
  errorMessage.value = '';
  try {
    const res = await axios.post('/api/checkout/process');
    if (res.data && res.data.redirect_url) {
      window.location.href = res.data.redirect_url;
    }
  } catch (err: any) {
    if (err.response && err.response.data && err.response.data.message) {
      errorMessage.value = err.response.data.message;
    } else {
      errorMessage.value = 'Failed to process checkout. Please try again.';
    }
  } finally {
    isLoading.value = false;
  }
};
</script>

<template>
  <Head title="Checkout Order - The ComicRealm" />

  <div class="min-h-screen bg-slate-950 text-slate-100 flex flex-col">
    <!-- Navbar Header -->
    <header class="sticky top-0 z-50 bg-slate-950/80 backdrop-blur-md border-b border-slate-800/80 px-4 lg:px-8 py-3.5 flex items-center justify-between">
      <Link href="/" class="text-xl font-extrabold bg-gradient-to-r from-sky-400 via-indigo-400 to-purple-400 bg-clip-text text-transparent">
        The ComicRealm
      </Link>

      <nav class="hidden md:flex items-center gap-6 text-sm font-medium">
        <Link href="/cart" class="text-slate-300 hover:text-white transition">← Back to Cart</Link>
        <span class="text-sky-400 font-bold">Checkout</span>
      </nav>
    </header>

    <main class="max-w-4xl mx-auto px-4 lg:px-8 py-10 w-full flex-1 space-y-8">
      <div>
        <h1 class="text-3xl font-extrabold text-white">Checkout Summary</h1>
        <p class="text-sm text-slate-400 mt-1">Review items and confirm order to select payment channel</p>
      </div>

      <div v-if="errorMessage" class="p-4 bg-rose-500/10 border border-rose-500/30 rounded-xl text-rose-400 text-sm">
        {{ errorMessage }}
      </div>

      <div v-if="cart && cart.items && cart.items.length" class="bg-slate-900 border border-slate-800 rounded-2xl p-6 space-y-6">
        <h2 class="text-lg font-bold text-white border-b border-slate-800 pb-3">Selected Chapters</h2>

        <div class="space-y-3">
          <div v-for="item in cart.items" :key="item.id" class="flex items-center justify-between py-2 border-b border-slate-800/60 text-sm">
            <div>
              <span class="text-xs text-sky-400 font-medium">{{ item.chapter.comic.title }}</span>
              <h3 class="font-bold text-white">Chapter {{ item.chapter.chapter_number }}: {{ item.chapter.title }}</h3>
            </div>
            <div class="font-bold text-amber-400">
              Rp {{ item.price ? item.price.toLocaleString() : '5,000' }}
            </div>
          </div>
        </div>

        <div class="pt-4 border-t border-slate-800 space-y-2 text-sm">
          <div class="flex justify-between text-slate-400">
            <span>Subtotal</span>
            <span class="text-white">Rp {{ cart.total_amount ? cart.total_amount.toLocaleString() : '0' }}</span>
          </div>
          <div class="flex justify-between text-lg font-bold text-white pt-2 border-t border-slate-800">
            <span>Total Payable</span>
            <span class="text-amber-400">Rp {{ cart.total_amount ? cart.total_amount.toLocaleString() : '0' }}</span>
          </div>
        </div>

        <button
          @click="submitOrder"
          :disabled="isLoading"
          class="w-full py-4 rounded-xl text-base font-bold text-white bg-sky-600 hover:bg-sky-500 disabled:opacity-50 transition shadow-xl shadow-sky-600/30"
        >
          {{ isLoading ? 'Generating Order Invoice...' : 'Confirm Order & Select Payment Method →' }}
        </button>
      </div>

      <div v-else class="bg-slate-900/40 border border-slate-800 rounded-2xl p-12 text-center text-slate-400">
        No active items in cart for checkout.
        <div class="pt-4">
          <Link href="/comics" class="text-sky-400 hover:underline">Back to Catalog</Link>
        </div>
      </div>
    </main>
  </div>
</template>
