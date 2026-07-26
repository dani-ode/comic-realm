<script setup lang="ts">
import { ref, onMounted, watch } from 'vue';
import axios from 'axios';

interface Comment {
  id: number;
  comment_text: string;
  is_spoiler: boolean;
  created_at: string;
  user: { name: string; username: string; avatar?: string };
  replies?: Comment[];
}

const props = defineProps<{
  comicId?: number;
  chapterId?: number;
}>();

const comments = ref<Comment[]>([]);
const commentText = ref('');
const isSpoiler = ref(false);
const isLoading = ref(false);
const replyParentId = ref<number | null>(null);
const replyText = ref('');

const fetchComments = async () => {
  if (!props.comicId) return;
  try {
    const params: Record<string, any> = { comic_id: props.comicId };
    if (props.chapterId) {
      params.chapter_id = props.chapterId;
    }
    const res = await axios.get('/api/comments', { params });
    if (res.data && res.data.data) {
      comments.value = res.data.data;
    }
  } catch (err) {
    //
  }
};

onMounted(() => {
  fetchComments();
});

watch(() => [props.comicId, props.chapterId], () => {
  fetchComments();
});

const submitComment = async (parentId: number | null = null) => {
  if (!props.comicId) return;
  const text = parentId ? replyText.value : commentText.value;
  if (!text.trim()) return;

  isLoading.value = true;
  try {
    await axios.post('/api/comments', {
      comic_id: props.comicId,
      chapter_id: props.chapterId || null,
      parent_id: parentId,
      comment_text: text,
      is_spoiler: isSpoiler.value,
    });
    commentText.value = '';
    replyText.value = '';
    replyParentId.value = null;
    fetchComments();
  } catch (err: any) {
    if (err.response && err.response.status === 401) {
      window.location.href = '/login?redirect=' + encodeURIComponent(window.location.pathname + window.location.search);
    }
  } finally {
    isLoading.value = false;
  }
};
</script>

<template>
  <div class="bg-slate-900 border border-slate-800 rounded-2xl p-6 space-y-6">
    <h3 class="text-xl font-bold text-white flex items-center justify-between">
      <span>💬 Discussion & Comments</span>
      <span class="text-xs text-slate-400 font-normal">{{ comments.length }} Threads</span>
    </h3>

    <!-- Post Comment Form -->
    <form @submit.prevent="submitComment(null)" class="space-y-3">
      <textarea
        v-model="commentText"
        rows="3"
        required
        placeholder="Share your thoughts about this webcomic..."
        class="w-full bg-slate-950 border border-slate-800 rounded-xl p-3.5 text-sm text-white placeholder-slate-500 focus:outline-none focus:ring-1 focus:ring-sky-500"
      ></textarea>

      <div class="flex items-center justify-between">
        <label class="flex items-center gap-2 text-xs text-slate-400 cursor-pointer">
          <input v-model="isSpoiler" type="checkbox" class="rounded bg-slate-950 border-slate-800 text-sky-600 focus:ring-sky-500" />
          <span>Mark as Spoiler</span>
        </label>

        <button
          type="submit"
          :disabled="isLoading || !commentText.trim()"
          class="px-4 py-2 rounded-xl text-xs font-semibold bg-sky-600 hover:bg-sky-500 text-white disabled:opacity-50 transition shadow-md shadow-sky-600/20"
        >
          Post Comment
        </button>
      </div>
    </form>

    <!-- Comments List -->
    <div v-if="comments.length" class="space-y-4 pt-2">
      <div v-for="c in comments" :key="c.id" class="bg-slate-950/60 border border-slate-800/80 rounded-xl p-4 space-y-3">
        <div class="flex items-center justify-between text-xs">
          <span class="font-bold text-sky-400">{{ c.user?.name || 'Anonymous' }}</span>
          <span class="text-slate-500">{{ new Date(c.created_at).toLocaleDateString() }}</span>
        </div>

        <p class="text-sm text-slate-200 leading-relaxed" :class="{ 'blur-sm hover:blur-none transition cursor-pointer': c.is_spoiler }">
          {{ c.comment_text }}
        </p>

        <!-- Reply Button -->
        <button
          @click="replyParentId = replyParentId === c.id ? null : c.id"
          class="text-xs text-slate-400 hover:text-white font-medium"
        >
          Reply
        </button>

        <!-- Threaded Reply Input -->
        <div v-if="replyParentId === c.id" class="mt-2 pl-4 border-l-2 border-sky-500 space-y-2">
          <input
            v-model="replyText"
            type="text"
            placeholder="Write a reply..."
            class="w-full bg-slate-900 border border-slate-800 rounded-lg p-2 text-xs text-white placeholder-slate-500 focus:outline-none focus:ring-1 focus:ring-sky-500"
          />
          <button
            @click="submitComment(c.id)"
            :disabled="isLoading || !replyText.trim()"
            class="px-3 py-1 rounded-lg text-xs font-semibold bg-sky-600 text-white disabled:opacity-50"
          >
            Send Reply
          </button>
        </div>

        <!-- Replies List -->
        <div v-if="c.replies && c.replies.length" class="mt-3 pl-4 border-l-2 border-slate-800 space-y-2">
          <div v-for="reply in c.replies" :key="reply.id" class="bg-slate-900/40 p-3 rounded-lg text-xs space-y-1">
            <div class="flex items-center justify-between text-[11px]">
              <span class="font-semibold text-slate-300">{{ reply.user?.name }}</span>
              <span class="text-slate-500">{{ new Date(reply.created_at).toLocaleDateString() }}</span>
            </div>
            <p class="text-slate-300">{{ reply.comment_text }}</p>
          </div>
        </div>
      </div>
    </div>

    <div v-else class="text-center py-6 text-xs text-slate-500">
      No comments yet. Be the first to start the discussion!
    </div>
  </div>
</template>
