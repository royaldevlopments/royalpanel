import React, { useEffect, useState } from 'react';
import { ServerContext } from '@/state/server';
import TitledGreyBox from '@/components/elements/TitledGreyBox';
import Field from '@/components/elements/Field';
import getDiscordWebhook from '@/api/server/getDiscordWebhook';
import setDiscordWebhook from '@/api/server/setDiscordWebhook';
import { Actions, useStoreActions } from 'easy-peasy';
import { ApplicationStore } from '@/state';
import { httpErrorToHuman } from '@/api/http';
import tw from 'twin.macro';
import { Button } from '@/components/elements/button/index';
import SpinnerOverlay from '@/components/elements/SpinnerOverlay';
import Label from '@/components/elements/Label';

const AVAILABLE_EVENTS = [
    { value: 'power.start', label: 'Server Started' },
    { value: 'power.stop', label: 'Server Stopped' },
    { value: 'power.kill', label: 'Server Killed' },
    { value: 'power.restart', label: 'Server Restarted' },
    { value: 'crash', label: 'Server Crash' },
    { value: 'backup.complete', label: 'Backup Complete' },
    { value: 'backup.failed', label: 'Backup Failed' },
    { value: 'resource.limit', label: 'Resource Limit Reached' },
    { value: 'maintenance.on', label: 'Maintenance Mode ON' },
    { value: 'maintenance.off', label: 'Maintenance Mode OFF' },
];

export default () => {
    const uuid = ServerContext.useStoreState((state) => state.server.data!.uuid);
    const [url, setUrl] = useState('');
    const [events, setEvents] = useState<string[]>([]);
    const [loading, setLoading] = useState(false);
    const [saving, setSaving] = useState(false);
    const { addFlash, clearFlashes } = useStoreActions((actions: Actions<ApplicationStore>) => actions.flashes);

    useEffect(() => {
        setLoading(true);
        getDiscordWebhook(uuid)
            .then((data) => {
                setUrl(data.url || '');
                setEvents(data.events || []);
            })
            .catch((error) => {
                addFlash({ key: 'settings', type: 'error', message: httpErrorToHuman(error) });
            })
            .then(() => setLoading(false));
    }, []);

    const toggleEvent = (event: string) => {
        setEvents((prev) =>
            prev.includes(event) ? prev.filter((e) => e !== event) : [...prev, event]
        );
    };

    const save = () => {
        setSaving(true);
        clearFlashes('settings');
        setDiscordWebhook(uuid, url, events)
            .then(() => {
                addFlash({ key: 'settings', type: 'success', message: 'Discord webhook saved.' });
            })
            .catch((error) => {
                addFlash({ key: 'settings', type: 'error', message: httpErrorToHuman(error) });
            })
            .then(() => setSaving(false));
    };

    return (
        <TitledGreyBox title={'Discord Webhook'} css={tw`relative`}>
            <SpinnerOverlay visible={loading || saving} />
            <p css={tw`text-sm mb-4`}>
                Send server event notifications to a Discord channel via webhook.
            </p>
            <div css={tw`mb-4`}>
                <Label>Webhook URL</Label>
                <input
                    type={'url'}
                    value={url}
                    onChange={(e) => setUrl(e.target.value)}
                    placeholder={'https://discord.com/api/webhooks/...'}
                    css={tw`w-full bg-neutral-800 border border-neutral-600 rounded px-3 py-2 text-sm text-neutral-100`}
                />
            </div>
            <div css={tw`mb-4`}>
                <Label>Notification Events</Label>
                <div css={tw`mt-2 grid grid-cols-2 gap-2`}>
                    {AVAILABLE_EVENTS.map((event) => (
                        <label key={event.value} css={tw`flex items-center gap-2 text-sm cursor-pointer`}>
                            <input
                                type={'checkbox'}
                                checked={events.includes(event.value)}
                                onChange={() => toggleEvent(event.value)}
                                css={tw`rounded border-neutral-600`}
                            />
                            {event.label}
                        </label>
                    ))}
                </div>
            </div>
            <div css={tw`text-right`}>
                <Button onClick={save}>Save Webhook</Button>
            </div>
        </TitledGreyBox>
    );
};
