import React, { useState, useCallback } from 'react';
import { Link, RouteComponentProps, useLocation } from 'react-router-dom';
import loginCheckpoint from '@/api/auth/loginCheckpoint';
import { sendLoginDiscord2FACode } from '@/api/account/discord2fa';
import LoginFormContainer from '@/components/auth/LoginFormContainer';
import { ActionCreator } from 'easy-peasy';
import { StaticContext } from 'react-router';
import { useFormikContext, withFormik } from 'formik';
import useFlash from '@/plugins/useFlash';
import { FlashStore } from '@/state/flashes';
import Field from '@/components/elements/Field';
import tw from 'twin.macro';
import { Button } from '@/components/elements/button/index';
import { DotsHorizontalIcon } from '@heroicons/react/solid';
import { useTranslation } from 'react-i18next';

interface Values {
    code: string;
    recoveryCode: string;
    discordCode: string;
}

type OwnProps = RouteComponentProps<
    Record<string, string | undefined>,
    StaticContext,
    { token?: string; hasDiscord2FA?: boolean }
>;

type Props = OwnProps & {
    clearAndAddHttpError: ActionCreator<FlashStore['clearAndAddHttpError']['payload']>;
};

interface LocationState {
    token?: string;
    hasDiscord2FA?: boolean;
}

const LoginCheckpointContainer = () => {
    const { t } = useTranslation('auth');
    const { isSubmitting, setFieldValue } = useFormikContext<Values>();
    const location = useLocation<LocationState>();
    const [isMissingDevice, setIsMissingDevice] = useState(false);
    const [useDiscord, setUseDiscord] = useState(false);
    const [discordSent, setDiscordSent] = useState(false);
    const [discordSending, setDiscordSending] = useState(false);

    const { clearAndAddHttpError } = useFlash();

    const hasDiscord2FA = location.state?.hasDiscord2FA || false;

    const sendDiscordCode = useCallback(() => {
        setDiscordSending(true);
        sendLoginDiscord2FACode()
            .then(() => {
                setDiscordSent(true);
                setDiscordSending(false);
            })
            .catch((error) => {
                setDiscordSending(false);
                clearAndAddHttpError({ error });
            });
    }, []);

    return (
        <LoginFormContainer title={t('checkpoint.title')} css={tw`w-full flex`}>
            <div css={tw`mt-3`}>
                {useDiscord ? (
                    <Field
                        icon={DotsHorizontalIcon}
                        name={'discordCode'}
                        title={'Discord 2FA Code'}
                        description={'Enter the 6-digit code sent to your Discord DM'}
                        type={'text'}
                        autoComplete={'one-time-code'}
                        autoFocus
                    />
                ) : (
                    <Field
                        icon={DotsHorizontalIcon}
                        name={isMissingDevice ? 'recoveryCode' : 'code'}
                        title={isMissingDevice ? t('checkpoint.recovery-code') : t('checkpoint.auth-code')}
                        description={isMissingDevice ? t('checkpoint.is-missing') : t('checkpoint.is-not-missing')}
                        type={'text'}
                        autoComplete={'one-time-code'}
                        autoFocus
                    />
                )}
            </div>
            {useDiscord && !discordSent && (
                <div css={tw`mt-3`}>
                    <Button
                        css={tw`w-full !py-3`}
                        type={'button'}
                        disabled={discordSending}
                        onClick={sendDiscordCode}
                    >
                        {discordSending ? 'Sending...' : 'Send Code via Discord'}
                    </Button>
                </div>
            )}
            <div css={tw`mt-3`}>
                <Button css={tw`w-full !py-3`} type={'submit'} disabled={isSubmitting}>
                    {t('checkpoint.button')}
                </Button>
            </div>
            {!useDiscord && hasDiscord2FA && (
                <div css={tw`mt-3 text-center`}>
                    <span
                        onClick={() => {
                            setFieldValue('code', '');
                            setFieldValue('recoveryCode', '');
                            setFieldValue('discordCode', '');
                            setUseDiscord(true);
                            setDiscordSent(false);
                            setIsMissingDevice(false);
                        }}
                        css={tw`cursor-pointer text-sm text-neutral-300 tracking-wide no-underline hover:text-neutral-200`}
                    >
                        Use Discord 2FA Instead
                    </span>
                </div>
            )}
            {useDiscord && (
                <div css={tw`mt-3 text-center`}>
                    <span
                        onClick={() => {
                            setFieldValue('code', '');
                            setFieldValue('recoveryCode', '');
                            setFieldValue('discordCode', '');
                            setUseDiscord(false);
                        }}
                        css={tw`cursor-pointer text-sm text-neutral-300 tracking-wide no-underline hover:text-neutral-200`}
                    >
                        Use Authenticator App Instead
                    </span>
                </div>
            )}
            {!useDiscord && (
                <div css={tw`mt-3 text-center`}>
                    <span
                        onClick={() => {
                            setFieldValue('code', '');
                            setFieldValue('recoveryCode', '');
                            setFieldValue('discordCode', '');
                            setIsMissingDevice((s) => !s);
                        }}
                        css={tw`cursor-pointer text-sm text-neutral-300 tracking-wide no-underline hover:text-neutral-200`}
                    >
                        {!isMissingDevice ? t('checkpoint.lost-device') : t('checkpoint.not-lost-device')}
                    </span>
                </div>
            )}
            <div css={tw`mt-3 text-center`}>
                <Link
                    to={'/auth/login'}
                    css={tw`text-sm text-neutral-300 tracking-wide no-underline hover:text-neutral-200`}
                >
                    {t('return')}
                </Link>
            </div>
        </LoginFormContainer>
    );
};

const EnhancedForm = withFormik<Props, Values>({
    handleSubmit: ({ code, recoveryCode, discordCode }, { setSubmitting, props: { clearAndAddHttpError, location } }) => {
        const token = location.state?.token || '';

        if (discordCode) {
            loginCheckpoint({ token, discordCode })
                .then((response) => {
                    if (response.complete) {
                        // @ts-expect-error this is valid
                        window.location = response.intended || '/';
                        return;
                    }
                    setSubmitting(false);
                })
                .catch((error) => {
                    console.error(error);
                    setSubmitting(false);
                    clearAndAddHttpError({ error });
                });
        } else {
            loginCheckpoint({ token, code, recoveryToken: recoveryCode })
                .then((response) => {
                    if (response.complete) {
                        // @ts-expect-error this is valid
                        window.location = response.intended || '/';
                        return;
                    }
                    setSubmitting(false);
                })
                .catch((error) => {
                    console.error(error);
                    setSubmitting(false);
                    clearAndAddHttpError({ error });
                });
        }
    },

    mapPropsToValues: () => ({
        code: '',
        recoveryCode: '',
        discordCode: '',
    }),
})(LoginCheckpointContainer);

export default ({ history, location, ...props }: OwnProps) => {
    const { clearAndAddHttpError } = useFlash();

    if (!location.state?.token) {
        history.replace('/auth/login');
        return null;
    }

    return (
        <EnhancedForm clearAndAddHttpError={clearAndAddHttpError} history={history} location={location} {...props} />
    );
};
