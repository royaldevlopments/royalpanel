import http from '@/api/http';
import { LoginResponse } from '@/api/auth/login';

interface CheckpointData {
    token: string;
    code?: string;
    recoveryToken?: string;
    discordCode?: string;
}

export default (data: CheckpointData): Promise<LoginResponse> => {
    return new Promise((resolve, reject) => {
        const payload: Record<string, string | undefined> = {
            confirmation_token: data.token,
            authentication_code: data.code,
            recovery_token: data.recoveryToken && data.recoveryToken.length > 0 ? data.recoveryToken : undefined,
            discord_2fa_code: data.discordCode,
        };

        http.post('/auth/login/checkpoint', payload)
            .then((response) =>
                resolve({
                    complete: response.data.data.complete,
                    intended: response.data.data.intended || undefined,
                })
            )
            .catch(reject);
    });
};
