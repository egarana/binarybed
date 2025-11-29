import { notifyActionResult } from '@/helpers/notifyActionResult';
import { capitalizeFirst } from '@/helpers/string';

interface UseFormNotificationsConfig {
    resourceName: string;
    action: 'create' | 'update' | 'assign';
    successDescription?: string;
    errorDescription?: string;
}

export function useFormNotifications(config: UseFormNotificationsConfig) {
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
        onSuccess,
        onError
    };
}
