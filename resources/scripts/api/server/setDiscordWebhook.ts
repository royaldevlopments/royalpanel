import http from '@/api/http';

export default (uuid: string, url: string, events: string[]): Promise<any> => {
    return new Promise((resolve, reject) => {
        http.post(`/api/client/servers/${uuid}/settings/discord-webhook`, { url, events })
            .then(({ data }) => resolve(data))
            .catch(reject);
    });
};
