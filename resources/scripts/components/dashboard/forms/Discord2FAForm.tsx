import React, { useEffect, useState, useCallback } from 'react';
import { getDiscord2FAStatus, toggleDiscord2FA, generateDiscordLinkCode } from '@/api/account/discord2fa';
import tw from 'twin.macro';
import { Button } from '@/components/elements/button/index';
import { useFlashKey } from '@/plugins/useFlash';
import { useTranslation } from 'react-i18next';

export default () => {
    const { t } = useTranslation('dashboard/account');
    const { clearAndAddHttpError } = useFlashKey('account:discord-2fa');
    const [linked, setLinked] = useState(false);
    const [enabled, setEnabled] = useState(false);
    const [loading, setLoading] = useState(true);
    const [toggling, setToggling] = useState(false);
    const [linkCode, setLinkCode] = useState('');
    const [linkCodeExpiry, setLinkCodeExpiry] = useState('');
    const [generatingCode, setGeneratingCode] = useState(false);

    const fetchStatus = useCallback(() => {
        getDiscord2FAStatus()
            .then((status) => {
                setLinked(status.linked);
                setEnabled(status.enabled);
                setLoading(false);
            })
            .catch((error) => {
                setLoading(false);
                clearAndAddHttpError(error instanceof Error ? error : new Error(String(error)));
            });
    }, []);

    useEffect(() => {
        fetchStatus();
    }, []);

    const handleToggle = () => {
        setToggling(true);
        toggleDiscord2FA(!enabled)
            .then(() => {
                setEnabled(!enabled);
                setToggling(false);
            })
            .catch((error) => {
                setToggling(false);
                clearAndAddHttpError(error instanceof Error ? error : new Error(String(error)));
            });
    };

    const handleGenerateCode = () => {
        setGeneratingCode(true);
        generateDiscordLinkCode()
            .then((res) => {
                setLinkCode(res.code);
                setLinkCodeExpiry(res.expires_at);
                setGeneratingCode(false);
            })
            .catch((error) => {
                setGeneratingCode(false);
                clearAndAddHttpError(error instanceof Error ? error : new Error(String(error)));
            });
    };

    if (loading) {
        return <p css={tw`text-sm text-gray-400`}>Loading...</p>;
    }

    return (
        <div>
            {!linked ? (
                <div>
                    <p css={tw`text-sm mb-2`}>
                        Link your Discord account to enable Discord 2FA.
                    </p>
                    {linkCode ? (
                        <div>
                            <p css={tw`text-sm mb-1`}>
                                Send this code to the bot in Discord: <strong>{linkCode}</strong>
                            </p>
                            <p css={tw`text-xs text-gray-500`}>Expires: {new Date(linkCodeExpiry).toLocaleTimeString()}</p>
                            <Button css={tw`mt-2`} onClick={fetchStatus}>
                                Check Link Status
                            </Button>
                        </div>
                    ) : (
                        <Button onClick={handleGenerateCode} disabled={generatingCode}>
                            {generatingCode ? 'Generating...' : 'Generate Link Code'}
                        </Button>
                    )}
                </div>
            ) : (
                <div>
                    <p css={tw`text-sm`}>
                        Discord 2FA: {enabled ? 'Enabled' : 'Disabled'}
                    </p>
                    <p css={tw`text-xs text-gray-500 mt-1 mb-3`}>
                        When enabled, you can use Discord DM codes as a 2FA method during login.
                    </p>
                    <Button
                        onClick={handleToggle}
                        disabled={toggling}
                        css={enabled ? tw`bg-red-600 hover:bg-red-700` : tw``}
                    >
                        {toggling ? 'Updating...' : enabled ? 'Disable Discord 2FA' : 'Enable Discord 2FA'}
                    </Button>
                </div>
            )}
        </div>
    );
};
