<script setup lang="ts">
import { Head, Link, router, usePage } from '@inertiajs/vue3';
import { ref } from 'vue';
import axios from 'axios';
import PublicLayout from '@/Layouts/PublicLayout.vue';
import { ShoppingCartIcon, Squares2X2Icon } from '@heroicons/vue/24/outline';
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
      slug: string;
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

const page = usePage();
const { success: toastSuccess } = useToast();

const cartData = ref<Cart | null>(props.cart || null);
const isLoading = ref(false);

const removeItem = async (chapterId: number) => {
  isLoading.value = true;
  try {
    const res = await axios.delete(`/api/cart/items/${chapterId}`);
    if (res.data && res.data.cart) {
      cartData.value = res.data.cart;
      (page.props as any).cartCount = res.data.cart.items.length;
      router.reload({ only: ['cartCount', 'cartChapterIds'] });
      toastSuccess('Item berhasil dihapus dari keranjang.');
    }
  } catch (err) {
    //
  } finally {
    isLoading.value = false;
  }
};

const clearAll = async () => {
  if (!confirm('Apakah Anda yakin ingin mengosongkan keranjang?')) return;
  isLoading.value = true;
  try {
    await axios.delete('/api/cart/clear');
    if (cartData.value) {
      cartData.value.items = [];
      cartData.value.total_amount = 0;
    }
    (page.props as any).cartCount = 0;
    router.reload({ only: ['cartCount', 'cartChapterIds'] });
    toastSuccess('Keranjang belanja telah dikosongkan.');
  } catch (err) {
    //
  } finally {
    isLoading.value = false;
  }
};

const proceedToCheckout = () => {
  router.get('/checkout');
};
</script>

<template>
  <Head title="Shopping Cart - The ComicRealm" />

  <PublicLayout>
    <main class="max-w-5xl mx-auto px-4 lg:px-8 py-10 w-full flex-1 space-y-8">
      <div>
        <h1 class="text-3xl font-extrabold text-white flex items-center gap-3">
          <span class="w-9 h-9 rounded-xl bg-sky-500/10 border border-sky-500/30 flex items-center justify-center shrink-0">
            <ShoppingCartIcon class="w-5 h-5 text-sky-400" />
          </span>
          Shopping Cart
        </h1>
        <p class="text-sm text-slate-400 mt-1">Review your selected comic chapters before checkout</p>
      </div>

      <div v-if="cartData && cartData.items && cartData.items.length" class="grid grid-cols-1 lg:grid-cols-3 gap-8">
        <!-- Item List -->
        <div class="lg:col-span-2 space-y-4">
          <div class="flex items-center justify-between pb-2 border-b border-slate-800 text-xs text-slate-400">
            <span>{{ cartData.items.length }} Items Selected</span>
            <button @click="clearAll" class="text-rose-400 hover:underline">Clear All</button>
          </div>

          <div
            v-for="item in cartData.items"
            :key="item.id"
            class="bg-slate-900 border border-slate-800 rounded-2xl p-4 flex items-center justify-between gap-4 transition hover:border-slate-700"
          >
            <div class="flex items-center gap-4 min-w-0">
              <!-- Comic Cover Image Link -->
              <Link
                v-if="item.chapter.comic.slug"
                :href="`/comics/${item.chapter.comic.slug}`"
                class="shrink-0 group"
              >
                <img
                  :src="item.chapter.comic.cover_image"
                  :alt="item.chapter.comic.title"
                  class="w-16 h-24 object-cover rounded-xl border border-slate-800 bg-slate-950 group-hover:border-sky-500/50 group-hover:scale-105 transition duration-300"
                />
              </Link>
              <img
                v-else
                :src="item.chapter.comic.cover_image"
                :alt="item.chapter.comic.title"
                class="w-16 h-24 object-cover rounded-xl border border-slate-800 shrink-0 bg-slate-950"
              />

              <div class="min-w-0">
                <Link
                  v-if="item.chapter.comic.slug"
                  :href="`/comics/${item.chapter.comic.slug}`"
                  class="text-xs text-sky-400 font-medium hover:underline block truncate"
                >
                  {{ item.chapter.comic.title }}
                </Link>
                <span v-else class="text-xs text-sky-400 font-medium block truncate">{{ item.chapter.comic.title }}</span>

                <h3 class="text-sm sm:text-base font-bold text-white mt-0.5 truncate">
                  Chapter {{ item.chapter.chapter_number }}: {{ item.chapter.title }}
                </h3>
                <p class="text-xs text-slate-400 mt-1">Digital Access Entitlement</p>
              </div>
            </div>

            <div class="text-right shrink-0">
              <div class="text-base font-bold text-amber-400">
                Rp {{ item.price ? item.price.toLocaleString() : '5,000' }}
              </div>
              <button
                @click="removeItem(item.chapter_id)"
                :disabled="isLoading"
                class="text-xs text-rose-400 hover:text-rose-300 mt-2 block ml-auto font-medium"
              >
                Remove
              </button>
            </div>
          </div>
        </div>

        <!-- Order Summary Card -->
        <div class="bg-slate-900 border border-slate-800 rounded-2xl p-6 h-fit space-y-6">
          <h2 class="text-lg font-bold text-white border-b border-slate-800 pb-3">Order Summary</h2>

          <div class="space-y-3 text-sm">
            <div class="flex justify-between text-slate-400">
              <span>Subtotal ({{ cartData.items.length }} chapters)</span>
              <span class="text-white">Rp {{ cartData.total_amount ? cartData.total_amount.toLocaleString() : '0' }}</span>
            </div>

            <div class="flex justify-between text-slate-400">
              <span>Payment Gateway Fee</span>
              <span class="text-emerald-400 font-medium">Calculated at Checkout</span>
            </div>

            <div class="pt-3 border-t border-slate-800 flex justify-between text-base font-bold">
              <span class="text-white">Total Amount</span>
              <span class="text-amber-400">Rp {{ cartData.total_amount ? cartData.total_amount.toLocaleString() : '0' }}</span>
            </div>
          </div>

          <button
            @click="proceedToCheckout"
            class="w-full py-3.5 px-4 rounded-xl text-sm font-bold text-white bg-sky-600 hover:bg-sky-500 transition shadow-lg shadow-sky-600/30 flex items-center justify-center gap-2"
          >
            <span>Proceed to Checkout →</span>
          </button>
        </div>
      </div>

      <!-- Empty Cart State -->
      <div v-else class="bg-slate-900/40 border border-slate-800 rounded-2xl p-16 text-center space-y-4">
        <div class="w-16 h-16 bg-slate-950 border border-slate-800 rounded-2xl flex items-center justify-center mx-auto">
          <ShoppingCartIcon class="w-8 h-8 text-slate-500" />
        </div>
        <h2 class="text-xl font-bold text-white">Your Cart is Empty</h2>
        <p class="text-sm text-slate-400 max-w-md mx-auto">
          Explore our comic catalog and add paid webcomic chapters to your shopping cart to continue.
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
