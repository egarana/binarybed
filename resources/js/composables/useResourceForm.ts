import { ref } from 'vue';
import { Form } from '@inertiajs/vue3';
import { useShortcut } from '@/composables/useShortcut';
import { notifyActionResult } from '@/helpers/notifyActionResult';
import { capitalizeFirst } from '@/helpers/string';

interface UseResourceFormConfig {
    resourceName: string;
    action: 'create' | 'update';
    successDescription?: string;
    errorDescription?: string;
}

export function useResourceForm(config: UseResourceFormConfig) {
    const formRef = ref<InstanceType<typeof Form> | null>(null);

    // Setup keyboard shortcut for form submission (Ctrl+S / Cmd+S)
    useShortcut({
        keys: ['ctrl+s', 'meta+s'],
        callback: () => {
            formRef.value?.$el?.requestSubmit?.();
        },
    });

    // Success handler
    const onSuccess = (payload: any) => {
        notifyActionResult(
            'success',
            config.action,
            capitalizeFirst(config.resourceName),
            payload,
            {
                successDescription: config.successDescription ||
                    `The ${config.resourceName} has been ${config.action}d successfully.`,
            }
        );
    };

    // Error handler
    const onError = (payload: any) => {
        notifyActionResult(
            'error',
            config.action,
            config.resourceName,
            payload,
            {
                errorDescription: config.errorDescription ||
                    `An unexpected error occurred while ${config.action}ing the ${config.resourceName}. Please check your input and try again.`,
            }
        );
    };

    return {
        formRef,
        onSuccess,
        onError
    };
}
