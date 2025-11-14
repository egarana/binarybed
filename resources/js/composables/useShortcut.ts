import { onMounted, onUnmounted } from 'vue';

export interface ShortcutOptions {
    /**
     * Daftar kombinasi tombol yang mau dipantau.
     * Contoh: ['ctrl+s', 'meta+s', 'ctrl+enter']
     */
    keys: string[];

    /**
     * Fungsi callback yang akan dijalankan saat shortcut ditekan.
     */
    callback: (e: KeyboardEvent) => void;

    /**
     * (Opsional) Kalau true, event default browser akan dicegah (preventDefault()).
     * Default: true
     */
    preventDefault?: boolean;
}

/**
 * Composable untuk mendeteksi shortcut keyboard seperti Ctrl+S atau Meta+S.
 *
 * @example
 * useShortcut({
 *     keys: ['ctrl+s', 'meta+s'],
 *     callback: (e) => submitForm(),
 * });
 */
export function useShortcut({ keys, callback, preventDefault = true }: ShortcutOptions) {
    const handler = (e: KeyboardEvent) => {
        // pastikan e.key ada dan berupa string
        const key = typeof e.key === 'string' ? e.key.toLowerCase() : '';

        const pressed = [
            e.ctrlKey ? 'ctrl' : '',
            e.metaKey ? 'meta' : '',
            e.shiftKey ? 'shift' : '',
            e.altKey ? 'alt' : '',
            key,
        ]
            .filter(Boolean)
            .join('+');

        if (keys.includes(pressed)) {
            if (preventDefault) e.preventDefault();
            callback(e);
        }
    };

    onMounted(() => {
        window.addEventListener('keydown', handler);
    });

    onUnmounted(() => {
        window.removeEventListener('keydown', handler);
    });
}
