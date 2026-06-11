import http from '@/api/http';

export default (uuid: string): Promise<any> => {
    return new Promise((resolve, reject) => {
        http.get(`/api/client/servers/${uuid}/settings/discord-webhook`)
            .then(({ data }) => resolve(data))
            .catch(reject);
    });
};
