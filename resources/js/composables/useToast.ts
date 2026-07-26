import { ref } from 'vue';

export type ToastType = 'success' | 'error' | 'warning' | 'info';

export interface Toast {
  id: number;
  type: ToastType;
  message: string;
  duration?: number;
}

const toasts = ref<Toast[]>([]);
let counter = 0;

export function useToast() {
  const push = (message: string, type: ToastType = 'info', duration = 4000) => {
    const id = ++counter;
    toasts.value.push({ id, type, message, duration });

    if (duration > 0) {
      setTimeout(() => dismiss(id), duration);
    }
  };

  const dismiss = (id: number) => {
    const idx = toasts.value.findIndex((t) => t.id === id);
    if (idx !== -1) toasts.value.splice(idx, 1);
  };

  return {
    toasts,
    push,
    dismiss,
    success: (msg: string, duration?: number) => push(msg, 'success', duration),
    error: (msg: string, duration?: number) => push(msg, 'error', duration),
    warning: (msg: string, duration?: number) => push(msg, 'warning', duration),
    info: (msg: string, duration?: number) => push(msg, 'info', duration),
  };
}
