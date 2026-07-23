export interface User {
    id: number;
    name: string;
    email: string;
    email_verified_at?: string;
    avatar?: string | null;
    avatar_url?: string;
}

export interface FlashMessages {
    success?: string | null;
    error?: string | null;
    info?: string | null;
}

export interface LearningNotification {
    id: string;
    type: string;
    title: string;
    message: string;
    action_url: string | null;
    action_label: string | null;
    tone: 'info' | 'success' | 'celebration' | string;
    read_at: string | null;
    created_at: string | null;
}

export interface LearningNotifications {
    items: LearningNotification[];
    unread_count: number;
}

export type PageProps<
    T extends Record<string, unknown> = Record<string, unknown>,
> = T & {
    auth: {
        user: User;
    };
    flash?: FlashMessages;
    notifications?: LearningNotifications;
};
