export type AdminToastType = 'success' | 'error' | 'info';

export interface AdminToastPayload {
    type: AdminToastType;
    message: string;
}

export function notify(payload: AdminToastPayload): void {
    window.dispatchEvent(new CustomEvent<AdminToastPayload>('admin:toast', {detail: payload}));
}
