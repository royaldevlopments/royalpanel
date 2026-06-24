import React, { useState, useEffect } from 'react';

export default () => {
    const [show, setShow] = useState(false);
    const [dismissed, setDismissed] = useState(() => {
        try { return localStorage.getItem('discord_banner_dismissed') === 'true'; }
        catch { return false; }
    });

    useEffect(() => {
        if (dismissed) return;
        const path = window.location.pathname;
        if (path.startsWith('/auth') || path.startsWith('/admin')) return;

        fetch('/api/client/bot/link/status')
            .then(r => r.json())
            .then(d => { if (!d.linked) setShow(true); })
            .catch(() => {});
    }, [dismissed]);

    const dismiss = () => {
        setShow(false);
        setDismissed(true);
        try { localStorage.setItem('discord_banner_dismissed', 'true'); }
        catch {}
    };

    if (!show) return null;

    return (
        <div css={`
            background: linear-gradient(90deg, rgba(168,85,247,0.12), rgba(236,72,153,0.08));
            border-bottom: 0.5px solid rgba(168,85,247,0.2);
            padding: 8px 16px;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 12px;
            font-size: 13px;
            color: #c4b5fd;
            position: relative;
            z-index: 100;
        `}>
            <span>🔗 Connect your Discord account to use bot commands</span>
            <a
                href={'/account'}
                css={`
                    color: #a855f7; text-decoration: none; font-weight: 500;
                    &:hover { text-decoration: underline; }
                `}
            >
                Settings &rarr;
            </a>
            <button
                onClick={dismiss}
                css={`
                    background: none; border: none; color: #6b7280; cursor: pointer;
                    font-size: 16px; padding: 2px 6px;
                    &:hover { color: #9ca3af; }
                `}
            >
                &times;
            </button>
        </div>
    );
};
