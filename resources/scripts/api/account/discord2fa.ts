import http from '@/api/http';

export interface Discord2FAStatus {
    linked: boolean;
    enabled: boolean;
}

export const getDiscord2FAStatus = (): Promise<Discord2FAStatus> => {
    return new Promise((resolve, reject) => {
        http.get('/api/client/bot/2fa/status')
            .then((response) => resolve(response.data))
            .catch(reject);
    });
};

export const toggleDiscord2FA = (enabled: boolean): Promise<void> => {
    return new Promise((resolve, reject) => {
        http.post('/api/client/bot/2fa/toggle', { enabled })
            .then(() => resolve())
            .catch(reject);
    });
};

export const generateDiscordLinkCode = (): Promise<{ code: string; expires_at: string }> => {
    return new Promise((resolve, reject) => {
        http.post('/api/client/bot/link/generate')
            .then((response) => resolve(response.data))
            .catch(reject);
    });
};

export const sendLoginDiscord2FACode = (): Promise<{ success: boolean; expires_at: string }> => {
    return new Promise((resolve, reject) => {
        http.post('/auth/login/checkpoint/discord-send')
            .then((response) => resolve(response.data))
            .catch(reject);
    });
};
