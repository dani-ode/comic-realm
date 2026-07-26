<script setup lang="ts">
import { ref } from 'vue';
import { Head, useForm, router, Link } from '@inertiajs/vue3';
import {
  ClockIcon,
  CheckBadgeIcon,
  NoSymbolIcon,
  XCircleIcon,
  EyeIcon,
  CheckIcon,
  TrashIcon,
  PencilSquareIcon,
  BookOpenIcon,
  BanknotesIcon,
  ArrowRightIcon,
  BuildingStorefrontIcon,
  LockOpenIcon,
  XMarkIcon,
  UserIcon,
} from '@heroicons/vue/24/outline';
import AdminLayout from '@/Layouts/AdminLayout.vue';

interface Publisher {
  id: number;
  user_id: number;
  brand_name: string;
  slug: string;
  bio?: string;
  bank_name?: string;
  bank_account_number?: string;
  bank_account_name?: string;
  verification_status: string;
  rejection_reason?: string;
  created_at: string;
  comics_count?: number;
  user?: { name: string; email: string };
  wallet?: { balance: number; total_earned: number; total_withdrawn: number };
}

interface Comic {
  id: number;
  title: string;
  cover_image?: string;
  status: string;
  chapters_count?: number;
  created_at: string;
}

interface Paginator {
  data: Publisher[];
  links: any[];
}

const props = defineProps<{
  publishers: Paginator;
  filters?: { status?: string; search?: string };
}>();

const search = ref(props.filters?.search || '');
const statusFilter = ref(props.filters?.status || '');

// Actions forms
const actionForm = useForm({
  rejection_reason: '',
});

// Modal state for Reject
const showRejectModal = ref(false);
const selectedPublisherId = ref<number | null>(null);

// Modal state for Studio Detail (Pendapatan & Komik)
const showDetailModal = ref(false);
const detailLoading = ref(false);
const detailPublisher = ref<Publisher | null>(null);
const detailComics = ref<Comic[]>([]);

const handleSearch = () => {
  router.get('/admin/publishers', {
    search: search.value,
    status: statusFilter.value,
  }, { preserveState: true, replace: true });
};

const filterByStatus = (status: string) => {
  statusFilter.value = status;
  handleSearch();
};

const approvePublisher = (id: number) => {
  if (confirm('Apakah Anda yakin ingin menyetujui studio ini?')) {
    actionForm.post(`/admin/publishers/${id}/approve`);
  }
};

const openRejectModal = (id: number) => {
  selectedPublisherId.value = id;
  actionForm.rejection_reason = '';
  showRejectModal.value = true;
};

const submitReject = () => {
  if (!selectedPublisherId.value) return;
  actionForm.post(`/admin/publishers/${selectedPublisherId.value}/reject`, {
    onSuccess: () => {
      showRejectModal.value = false;
      actionForm.reset();
    },
  });
};

const blockPublisher = (id: number) => {
  if (confirm('Apakah Anda yakin ingin MEMBLOKIR studio ini? Studio tidak akan dapat mempublikasikan komik.')) {
    actionForm.post(`/admin/publishers/${id}/block`);
  }
};

const unblockPublisher = (id: number) => {
  if (confirm('Apakah Anda yakin ingin MEMBUKA BLOKIR studio ini?')) {
    actionForm.post(`/admin/publishers/${id}/unblock`);
  }
};

const openDetailModal = async (id: number) => {
  showDetailModal.value = true;
  detailLoading.value = true;
  detailPublisher.value = null;
  detailComics.value = [];

  try {
    const res = await fetch(`/admin/publishers/${id}`);
    const data = await res.json();
    detailPublisher.value = data.publisher;
    detailComics.value = data.comics || [];
  } catch (e) {
    console.error('Failed to load studio details', e);
  } finally {
    detailLoading.value = false;
  }
};
</script>

<template>
  <Head title="Kelola Studio - Admin Panel" />

  <AdminLayout>
    <div class="space-y-8">
      <!-- Page Header -->
      <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
        <div>
          <span class="text-xs text-amber-400 font-bold uppercase tracking-wider">Super Admin</span>
          <h1 class="text-3xl font-extrabold text-white flex items-center gap-3">
            <BuildingStorefrontIcon class="w-8 h-8 text-amber-400 shrink-0" />
            Kelola Studio Publisher
          </h1>
          <p class="text-sm text-slate-400 mt-1">Setujui pengajuan, blokir/buka blokir studio, pantau pendapatan, dan lihat komik yang diterbitkan</p>
        </div>
      </div>

      <!-- Filters & Search -->
      <div class="flex flex-col sm:flex-row items-center justify-between gap-4 bg-slate-900 border border-slate-800 p-4 rounded-2xl">
        <div class="flex items-center gap-2 overflow-x-auto w-full sm:w-auto">
          <button
            @click="filterByStatus('')"
            class="px-4 py-2 rounded-xl text-xs font-bold transition whitespace-nowrap"
            :class="!statusFilter ? 'bg-amber-500 text-slate-950 shadow-md shadow-amber-500/20' : 'bg-slate-950 text-slate-400 hover:text-white border border-slate-800'"
          >
            Semua Studio
          </button>
          <button
            @click="filterByStatus('pending')"
            class="inline-flex items-center gap-1.5 px-4 py-2 rounded-xl text-xs font-bold transition whitespace-nowrap"
            :class="statusFilter === 'pending' ? 'bg-amber-500 text-slate-950 shadow-md shadow-amber-500/20' : 'bg-slate-950 text-slate-400 hover:text-white border border-slate-800'"
          >
            <ClockIcon class="w-3.5 h-3.5" /> Menunggu Approval
          </button>
          <button
            @click="filterByStatus('approved')"
            class="inline-flex items-center gap-1.5 px-4 py-2 rounded-xl text-xs font-bold transition whitespace-nowrap"
            :class="statusFilter === 'approved' ? 'bg-amber-500 text-slate-950 shadow-md shadow-amber-500/20' : 'bg-slate-950 text-slate-400 hover:text-white border border-slate-800'"
          >
            <CheckBadgeIcon class="w-3.5 h-3.5" /> Approved
          </button>
          <button
            @click="filterByStatus('blocked')"
            class="inline-flex items-center gap-1.5 px-4 py-2 rounded-xl text-xs font-bold transition whitespace-nowrap"
            :class="statusFilter === 'blocked' ? 'bg-amber-500 text-slate-950 shadow-md shadow-amber-500/20' : 'bg-slate-950 text-slate-400 hover:text-white border border-slate-800'"
          >
            <NoSymbolIcon class="w-3.5 h-3.5" /> Blocked
          </button>
        </div>

        <div class="w-full sm:w-72">
          <input
            v-model="search"
            @keyup.enter="handleSearch"
            type="text"
            placeholder="Cari nama studio / user..."
            class="w-full bg-slate-950 border border-slate-800 rounded-xl px-4 py-2 text-xs text-white placeholder-slate-500 focus:outline-none focus:border-amber-500"
          />
        </div>
      </div>

      <!-- Publishers Table -->
      <div v-if="publishers.data && publishers.data.length" class="bg-slate-900 border border-slate-800 rounded-2xl overflow-hidden shadow-xl">
        <div class="overflow-x-auto">
          <table class="w-full text-left text-sm text-slate-300">
            <thead class="text-xs uppercase bg-slate-950 text-slate-400 border-b border-slate-800">
              <tr>
                <th class="px-6 py-4 flex items-center gap-1.5"><BuildingStorefrontIcon class="w-3.5 h-3.5" /> Brand / Studio</th>
                <th class="px-6 py-4"><UserIcon class="w-3.5 h-3.5 inline mr-1" /> Pemilik (User)</th>
                <th class="px-6 py-4"><BookOpenIcon class="w-3.5 h-3.5 inline mr-1" /> Komik Published</th>
                <th class="px-6 py-4"><BanknotesIcon class="w-3.5 h-3.5 inline mr-1" /> Saldo Real (Siap Payout)</th>
                <th class="px-6 py-4"><CheckBadgeIcon class="w-3.5 h-3.5 inline mr-1" /> Status</th>
                <th class="px-6 py-4 text-right"><Cog6ToothIcon class="w-3.5 h-3.5 inline mr-1" /> Aksi & Detail</th>
              </tr>
            </thead>
            <tbody class="divide-y divide-slate-800/60">
              <tr v-for="pub in publishers.data" :key="pub.id" class="hover:bg-slate-800/30 transition">
                <td class="px-6 py-4">
                  <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-xl bg-slate-800 border border-slate-700 flex items-center justify-center text-lg font-bold text-amber-400 shrink-0">
                      {{ pub.brand_name.charAt(0).toUpperCase() }}
                    </div>
                    <div>
                      <h3 class="font-bold text-white text-sm hover:text-amber-400 cursor-pointer" @click="openDetailModal(pub.id)">
                        {{ pub.brand_name }}
                      </h3>
                      <p class="text-xs text-slate-500 line-clamp-1">Bank: {{ pub.bank_name || '-' }} ({{ pub.bank_account_number || '-' }})</p>
                    </div>
                  </div>
                </td>

                <td class="px-6 py-4 text-xs">
                  <span class="text-white font-semibold">{{ pub.user ? pub.user.name : 'User' }}</span>
                  <p class="text-slate-400">{{ pub.user ? pub.user.email : '' }}</p>
                </td>

                <td class="px-6 py-4 text-xs font-bold">
                  <Link
                    :href="`/admin/comics?publisher_id=${pub.user_id}`"
                    class="text-purple-400 hover:text-purple-300 underline flex items-center gap-1"
                  >
                    <BookOpenIcon class="w-3.5 h-3.5" />
                    {{ pub.comics_count || 0 }} Komik
                    <ArrowRightIcon class="w-3 h-3" />
                  </Link>
                </td>

                <td class="px-6 py-4 text-xs">
                  <div class="text-emerald-400 font-extrabold text-sm">
                    Rp {{ pub.wallet?.balance !== undefined ? pub.wallet.balance.toLocaleString() : '0' }}
                  </div>
                  <p class="text-[10px] text-slate-400 mt-0.5">
                    Masuk: Rp {{ pub.wallet?.total_earned ? pub.wallet.total_earned.toLocaleString() : '0' }}
                    <span v-if="pub.wallet?.total_withdrawn" class="text-sky-400"> | Ditarik: Rp {{ pub.wallet.total_withdrawn.toLocaleString() }}</span>
                  </p>
                </td>

                <td class="px-6 py-4">
                  <span
                    class="px-2.5 py-1 text-[10px] font-extrabold rounded-lg uppercase tracking-wider"
                    :class="[
                      pub.verification_status === 'approved' ? 'bg-emerald-500/10 text-emerald-400 border border-emerald-500/30' :
                      pub.verification_status === 'pending' ? 'bg-amber-500/10 text-amber-400 border border-amber-500/30' :
                      pub.verification_status === 'blocked' ? 'bg-rose-500/10 text-rose-400 border border-rose-500/30' :
                      'bg-slate-800 text-slate-400 border border-slate-700'
                    ]"
                  >
                    {{ pub.verification_status }}
                  </span>
                </td>

                <td class="px-6 py-4 text-right space-x-2 whitespace-nowrap">
                  <!-- Detail Button -->
                  <button
                    @click="openDetailModal(pub.id)"
                    class="px-3 py-1.5 rounded-lg text-xs font-bold text-slate-300 bg-slate-800 hover:bg-slate-700 hover:text-white transition border border-slate-700"
                  >
                    <EyeIcon class="w-3.5 h-3.5" /> Detail
                  </button>

                  <!-- Approve Button -->
                  <button
                    v-if="pub.verification_status !== 'approved' && pub.verification_status !== 'blocked'"
                    @click="approvePublisher(pub.id)"
                    class="inline-flex items-center gap-1 px-3 py-1.5 rounded-lg text-xs font-bold text-white bg-emerald-600 hover:bg-emerald-500 transition shadow-md shadow-emerald-600/30"
                  >
                    <CheckIcon class="w-3.5 h-3.5" /> Approve
                  </button>

                  <!-- Reject Button -->
                  <button
                    v-if="pub.verification_status === 'pending'"
                    @click="openRejectModal(pub.id)"
                    class="inline-flex items-center gap-1 px-3 py-1.5 rounded-lg text-xs font-bold text-rose-300 bg-rose-950/60 hover:bg-rose-900 border border-rose-800 transition"
                  >
                    <XMarkIcon class="w-3.5 h-3.5" /> Reject
                  </button>

                  <!-- Block / Unblock Button -->
                  <button
                    v-if="pub.verification_status === 'approved'"
                    @click="blockPublisher(pub.id)"
                    class="inline-flex items-center gap-1 px-3 py-1.5 rounded-lg text-xs font-bold text-rose-400 bg-rose-950/40 border border-rose-800/80 hover:bg-rose-900 transition"
                  >
                    <NoSymbolIcon class="w-3.5 h-3.5" /> Block
                  </button>

                  <button
                    v-if="pub.verification_status === 'blocked'"
                    @click="unblockPublisher(pub.id)"
                    class="inline-flex items-center gap-1 px-3 py-1.5 rounded-lg text-xs font-bold text-emerald-400 bg-emerald-950/40 border border-emerald-800/80 hover:bg-emerald-900 transition"
                  >
                    <LockOpenIcon class="w-3.5 h-3.5" /> Unblock
                  </button>
                </td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>

      <div v-else class="bg-slate-900 border border-slate-800 rounded-2xl p-12 text-center text-slate-400">
        Belum ada studio publisher yang terdaftar atau sesuai kriteria pencarian.
      </div>
    </div>

    <!-- Reject Reason Modal -->
    <div v-if="showRejectModal" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/70 backdrop-blur-sm">
      <div class="bg-slate-900 border border-slate-800 rounded-2xl max-w-md w-full p-6 space-y-4 shadow-2xl">
        <h3 class="text-lg font-bold text-white">Tolak Pengajuan Studio</h3>
        <p class="text-xs text-slate-400">Berikan alasan penolakan untuk pemilik studio.</p>

        <textarea
          v-model="actionForm.rejection_reason"
          rows="3"
          placeholder="Contoh: Dokumen atau data rekening belum valid."
          class="w-full bg-slate-950 border border-slate-800 rounded-xl p-3 text-xs text-white placeholder-slate-500 focus:outline-none focus:border-rose-500"
        ></textarea>

        <div class="flex items-center justify-end gap-3 pt-2">
          <button @click="showRejectModal = false" class="px-4 py-2 rounded-xl text-xs font-semibold text-slate-400 hover:text-white">
            Batal
          </button>
          <button @click="submitReject" class="px-4 py-2 rounded-xl text-xs font-bold text-white bg-rose-600 hover:bg-rose-500 shadow-lg shadow-rose-600/30">
            Tolak Studio
          </button>
        </div>
      </div>
    </div>

    <!-- Studio Detail Modal (Lihat Pendapatan & Komik) -->
    <div v-if="showDetailModal" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/70 backdrop-blur-sm">
      <div class="bg-slate-900 border border-slate-800 rounded-2xl max-w-3xl w-full max-h-[85vh] flex flex-col shadow-2xl overflow-hidden">
        <!-- Modal Header -->
        <div class="px-6 py-4 border-b border-slate-800 flex items-center justify-between bg-slate-950">
          <div>
            <h3 class="text-lg font-bold text-white">Detail Studio & Ringkasan Performa</h3>
            <p class="text-xs text-slate-400">Statistik pendapatan dan komik yang dipublikasikan</p>
          </div>
          <button @click="showDetailModal = false" class="text-slate-400 hover:text-white text-xl font-bold p-1">
            ✕
          </button>
        </div>

        <!-- Modal Body -->
        <div class="p-6 overflow-y-auto space-y-6 flex-1">
          <div v-if="detailLoading" class="py-12 text-center text-slate-400">
            Memuat data studio...
          </div>

          <template v-else-if="detailPublisher">
            <!-- Studio Header Card -->
            <div class="bg-slate-950 border border-slate-800 p-5 rounded-xl flex flex-col md:flex-row md:items-center justify-between gap-4">
              <div>
                <span class="text-[10px] font-extrabold uppercase text-amber-400 tracking-wider">Publisher Profile</span>
                <h2 class="text-xl font-extrabold text-white">{{ detailPublisher.brand_name }}</h2>
                <p class="text-xs text-slate-400 mt-1">Pemilik: {{ detailPublisher.user?.name }} ({{ detailPublisher.user?.email }})</p>
                <p class="text-xs text-slate-500 mt-1">Bio: {{ detailPublisher.bio || 'Tidak ada bio' }}</p>
              </div>

              <div class="text-right">
                <span
                  class="px-3 py-1 text-xs font-extrabold rounded-lg uppercase"
                  :class="[
                    detailPublisher.verification_status === 'approved' ? 'bg-emerald-500/10 text-emerald-400 border border-emerald-500/30' :
                    detailPublisher.verification_status === 'blocked' ? 'bg-rose-500/10 text-rose-400 border border-rose-500/30' :
                    'bg-amber-500/10 text-amber-400 border border-amber-500/30'
                  ]"
                >
                  {{ detailPublisher.verification_status }}
                </span>
              </div>
            </div>

            <!-- Financial Statistics (Lihat Pendapatan) -->
            <div class="space-y-3">
              <h4 class="text-sm font-bold text-white flex items-center gap-2">
                <BanknotesIcon class="w-4 h-4 text-slate-400" /> Pendapatan & Saldo Wallet Studio
              </h4>
              <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <div class="bg-slate-950 border border-slate-800 p-4 rounded-xl space-y-1">
                  <span class="text-[10px] text-slate-400 uppercase font-semibold">Total Pendapatan Royalti</span>
                  <div class="text-lg font-extrabold text-emerald-400">
                    Rp {{ detailPublisher.wallet?.total_earned ? detailPublisher.wallet.total_earned.toLocaleString() : '0' }}
                  </div>
                </div>

                <div class="bg-slate-950 border border-slate-800 p-4 rounded-xl space-y-1">
                  <span class="text-[10px] text-slate-400 uppercase font-semibold">Saldo Wallet Saat Ini</span>
                  <div class="text-lg font-extrabold text-sky-400">
                    Rp {{ detailPublisher.wallet?.balance ? detailPublisher.wallet.balance.toLocaleString() : '0' }}
                  </div>
                </div>

                <div class="bg-slate-950 border border-slate-800 p-4 rounded-xl space-y-1">
                  <span class="text-[10px] text-slate-400 uppercase font-semibold">Total Payout / Ditarik</span>
                  <div class="text-lg font-extrabold text-purple-400">
                    Rp {{ detailPublisher.wallet?.total_withdrawn ? detailPublisher.wallet.total_withdrawn.toLocaleString() : '0' }}
                  </div>
                </div>
              </div>
              <div class="bg-slate-950 border border-slate-800 p-3 rounded-xl text-xs text-slate-400 flex items-center justify-between">
                <span>Rekening Pembayaran: <strong class="text-white">{{ detailPublisher.bank_name || 'BCA' }} - {{ detailPublisher.bank_account_number || '-' }}</strong> a.n. {{ detailPublisher.bank_account_name || '-' }}</span>
              </div>
            </div>
          </template>
        </div>

        <!-- Modal Footer -->
        <div class="px-6 py-4 border-t border-slate-800 bg-slate-950 flex items-center justify-end">
          <button @click="showDetailModal = false" class="px-5 py-2 rounded-xl text-xs font-bold text-white bg-slate-800 hover:bg-slate-700">
            Tutup
          </button>
        </div>
      </div>
    </div>
  </AdminLayout>
</template>
