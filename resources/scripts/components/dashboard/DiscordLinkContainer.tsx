import React, { useState, useEffect } from 'react';
import { useStoreState } from 'easy-peasy';
import { ApplicationStore } from '@/state';
import MessageBox from '@/components/MessageBox';
import SpinnerOverlay from '@/components/elements/SpinnerOverlay';

export default () => {
    const [loading, setLoading] = useState(true);
    const [linked, setLinked] = useState(false);
    const [discordId, setDiscordId] = useState<string | null>(null);
    const [code, setCode] = useState<string | null>(null);
    const [copied, setCopied] = useState(false);
    const [error, setError] = useState<string | null>(null);
    const [twoFAEnabled, setTwoFAEnabled] = useState(false);
    const [toggling2FA, setToggling2FA] = useState(false);

    useEffect(() => {
        fetch('/api/client/bot/link/status')
            .then(r => r.json())
            .then(d => {
                setLinked(d.linked);
                setDiscordId(d.discord_id);
            })
            .catch(() => setError('Failed to load link status'))
            .finally(() => setLoading(false));

        fetch('/api/client/bot/2fa/status')
            .then(r => r.json())
            .then(d => {
                if (d.linked) setTwoFAEnabled(d.enabled);
            })
            .catch(() => {});
    }, []);

    const generateCode = async () => {
        setError(null);
        setCode(null);
        try {
            const res = await fetch('/api/client/bot/link/generate', { method: 'POST' });
            const d = await res.json();
            if (d.code) {
                setCode(d.code);
            } else {
                setError(d.error || 'Failed to generate code');
            }
        } catch {
            setError('Failed to generate code');
        }
    };

    const unlink = async () => {
        setError(null);
        try {
            const res = await fetch('/api/client/bot/link/unlink', { method: 'POST' });
            const d = await res.json();
            if (d.success) {
                setLinked(false);
                setDiscordId(null);
                setCode(null);
            } else {
                setError(d.error || 'Failed to unlink');
            }
        } catch {
            setError('Failed to unlink');
        }
    };

    const copyCode = () => {
        if (code) {
            navigator.clipboard.writeText(code);
            setCopied(true);
            setTimeout(() => setCopied(false), 2000);
        }
    };

    const toggle2FA = async () => {
        setToggling2FA(true);
        setError(null);
        try {
            const res = await fetch('/api/client/bot/2fa/toggle', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ enabled: !twoFAEnabled }),
            });
            const d = await res.json();
            if (d.success) {
                setTwoFAEnabled(!twoFAEnabled);
            } else {
                setError(d.error || 'Failed to toggle 2FA');
            }
        } catch {
            setError('Failed to toggle 2FA');
        }
        setToggling2FA(false);
    };

    if (loading) {
        return <div className={'bg-gray-700 backdrop rounded-box p-6'}><SpinnerOverlay visible={true} /></div>;
    }

    return (
        <div className={'bg-gray-700 backdrop rounded-box p-6'}>
            <div className={'flex items-center gap-3 mb-4'}>
                <div css={`
                    width: 10px; height: 10px; border-radius: 50%;
                    background: ${linked ? '#22c55e' : '#6b7280'};
                    box-shadow: ${linked ? '0 0 12px rgba(34, 197, 94, 0.5)' : 'none'};
                `} />
                <p className={'text-gray-300 font-medium'}>Discord Connection</p>
            </div>

            {error && <MessageBox title={'Error'} type={'error'}>{error}</MessageBox>}

            {linked ? (
                <div>
                    <p className={'text-gray-400 text-sm mb-3'}>
                        Linked as <span className={'text-gray-200 font-mono'}>{discordId}</span>
                    </p>
                    <div className={'flex gap-2 mb-3'}>
                        <button
                            onClick={toggle2FA}
                            disabled={toggling2FA}
                            className={`px-4 py-2 border rounded-lg text-sm transition-all ${
                                twoFAEnabled
                                    ? 'bg-green-500/10 text-green-400 border-green-500/20 hover:bg-green-500/20'
                                    : 'bg-gray-600/50 text-gray-300 border-gray-500/30 hover:bg-gray-500/30'
                            }`}
                        >
                            {toggling2FA ? '...' : twoFAEnabled ? 'Discord 2FA: ON' : 'Enable Discord 2FA'}
                        </button>
                        <button
                            onClick={unlink}
                            className={'px-4 py-2 bg-red-500/10 text-red-400 border border-red-500/20 rounded-lg text-sm hover:bg-red-500/20 transition-all'}
                        >
                            Unlink
                        </button>
                    </div>
                    {twoFAEnabled && (
                        <p className={'text-gray-500 text-xs'}>
                            Login with Discord DM codes as 2FA. Send code from login page.
                        </p>
                    )}
                </div>
            ) : (
                <div>
                    <p className={'text-gray-400 text-sm mb-3'}>
                        Connect your Discord account to use bot commands.
                    </p>

                    {code ? (
                        <div className={'flex items-center gap-3'}>
                            <div className={'bg-gray-800 px-5 py-3 rounded-lg font-mono text-2xl tracking-[0.3em] text-purple-400 border border-purple-500/20'}>
                                {code}
                            </div>
                            <button
                                onClick={copyCode}
                                className={'px-4 py-3 bg-purple-500/10 text-purple-400 border border-purple-500/20 rounded-lg text-sm hover:bg-purple-500/20 transition-all'}
                            >
                                {copied ? 'Copied!' : 'Copy'}
                            </button>
                            <p className={'text-gray-500 text-xs ml-2'}>Expires in 5 min</p>
                        </div>
                    ) : (
                        <button
                            onClick={generateCode}
                            className={'px-4 py-2 bg-purple-500/10 text-purple-400 border border-purple-500/20 rounded-lg text-sm hover:bg-purple-500/20 transition-all'}
                        >
                            Generate Code
                        </button>
                    )}

                    {code && (
                        <p className={'text-gray-500 text-xs mt-3'}>
                            DM the bot: <code className={'text-purple-400'}>/link {code}</code>
                        </p>
                    )}
                </div>
            )}
        </div>
    );
};
