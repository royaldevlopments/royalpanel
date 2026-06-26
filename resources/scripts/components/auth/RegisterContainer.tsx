import React, { useEffect, useRef, useState } from 'react';
import { Link } from 'react-router-dom';
import register from '@/api/auth/register';
import RegisterFormContainer from '@/components/auth/LoginFormContainer';
import { useStoreState } from 'easy-peasy';
import { ApplicationStore } from '@/state';
import { Formik, FormikHelpers } from 'formik';
import { object, string } from 'yup';
import Field from '@/components/elements/Field';
import tw from 'twin.macro';
import { Button } from '@/components/elements/button/index';
import { UserCircleIcon, AtSymbolIcon } from '@heroicons/react/outline';
import { FaDiscord, FaGithub, FaGoogle } from 'react-icons/fa';
import Reaptcha from 'reaptcha';
import useFlash from '@/plugins/useFlash';
import Turnstile, { useTurnstile } from "react-turnstile";
import { useTranslation } from 'react-i18next';

interface Values {
    email: string;
    username: string;
    firstname: string;
    lastname: string;
}

const RegisterContainer = () => {
    const ref = useRef<Reaptcha>(null);
    const turnstile = useTurnstile();
    const [token, setToken] = useState('');
    const { t } = useTranslation('royal/auth');

    const { clearFlashes, clearAndAddHttpError, addFlash } = useFlash();
    const { recaptcha: recaptchaSettings, turnstile: turnstileSettings } = useStoreState((state) => state.settings.data!);
    const oauthDiscord = useStoreState((state: ApplicationStore) => state.settings.data!.royal.oauthDiscordEnabled);
    const oauthGithub = useStoreState((state: ApplicationStore) => state.settings.data!.royal.oauthGithubEnabled);
    const oauthGoogle = useStoreState((state: ApplicationStore) => state.settings.data!.royal.oauthGoogleEnabled);

    useEffect(() => {
        clearFlashes();
    }, []);

    const onSubmit = (values: Values, { setSubmitting }: FormikHelpers<Values>) => {
        clearFlashes();

        if (recaptchaSettings.enabled && recaptchaSettings.method && !token) {
            if (recaptchaSettings.method === 'recaptcha') {
            ref.current!.execute().catch((error) => {
                console.error(error);

                setSubmitting(false);
                clearAndAddHttpError({ error });
            });
            } else if (recaptchaSettings.method === 'turnstile') {
                turnstile.execute().catch((error: unknown) => {
                    console.error(error);

                    setSubmitting(false);
                    clearAndAddHttpError({ error: error as Error });
                });
            }

            return;
        }

        register({ ...values, recaptchaData: token })
            .then((response) => {
                if (response.complete) {
                    addFlash({
                        type: 'success',
                        title: 'Success',
                        message: t('register.success-message'),
                    });

                    setSubmitting(false);
                }
            })
            .catch((error) => {
                console.error(error);

                setToken('');
                if (recaptchaSettings.enabled && recaptchaSettings.method) {
                    if (recaptchaSettings.method === 'recaptcha') {
                        ref.current!.reset();
                    } else if (recaptchaSettings.method === 'turnstile') {
                        turnstile.reset();
                    }
                }

                const data = JSON.parse(error.config.data);

                if (!/^[a-zA-Z0-9][a-zA-Z0-9_.-]*[a-zA-Z0-9]$/.test(data.username))
                    error =
                        t('register.valid-username-required');
                if (!/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(data.email)) error = t('register.valid-email-required');

                setSubmitting(false);
                if (typeof error === 'string') {
                    addFlash({
                        type: 'error',
                        title: 'Error',
                        message: error || '',
                    });
                } else {
                    clearAndAddHttpError({ error });
                }
            });
    };

    return (
        <Formik
            onSubmit={onSubmit}
            initialValues={{ email: '', username: '', firstname: '', lastname: '' }}
            validationSchema={object().shape({
                email: string().required(t('register.email-required')),
                username: string().required(t('register.username-required')),
                firstname: string().required(t('register.firstname-required')),
                lastname: string().required(t('register.lastname-required')),
            })}
        >
            {({ isSubmitting, setSubmitting, submitForm }) => (
                <RegisterFormContainer title={t('register.title')} css={tw`w-full flex flex-col`}>
                    <div css={tw`grid lg:grid-cols-2 gap-4 w-full`}>
                        <div>
                            <p css={tw`text-xs font-bold text-gray-300 mb-1`}>First Name</p>
                            <Field type={'text'} name={'firstname'} placeholder={t('register.firstname')} disabled={isSubmitting} icon={UserCircleIcon} />
                        </div>
                        <div>
                            <p css={tw`text-xs font-bold text-gray-300 mb-1`}>Last Name</p>
                            <Field type={'text'} name={'lastname'} placeholder={t('register.lastname')} disabled={isSubmitting} icon={UserCircleIcon} />
                        </div>
                    </div>
                    <p css={tw`text-xs font-bold text-gray-300 mb-1 mt-4`}>Username</p>
                    <Field type={'text'} name={'username'} placeholder={t('register.username')} disabled={isSubmitting} icon={UserCircleIcon} />
                    <p css={tw`text-xs font-bold text-gray-300 mb-1 mt-4`}>Email</p>
                    <Field type={'email'} name={'email'} placeholder={t('register.email')} disabled={isSubmitting} icon={AtSymbolIcon} />
                    <div css={tw`mt-4 z-50 relative`}>
                        {recaptchaSettings.enabled && recaptchaSettings.method && (
                            recaptchaSettings.method === 'recaptcha' ? (
                            <Reaptcha
                                ref={ref}
                                size={'invisible'}
                                sitekey={recaptchaSettings.siteKey || '_invalid_key'}
                                onVerify={(response) => {
                                    setToken(response);
                                    submitForm();
                                }}
                                onExpire={() => {
                                    setSubmitting(false);
                                    setToken('');
                                }}
                            />
                            ) : recaptchaSettings.method === 'turnstile' && (
                                <Turnstile
                                    sitekey={turnstileSettings.siteKey || '_invalid_key'}
                                    execution="render"
                                    appearance="always"
                                    onVerify={(response) => {
                                        setToken(response);
                                    }}
                                    onExpire={() => {
                                        setSubmitting(false);
                                        setToken('');
                                    }}
                                />
                            )
                        )}
                    </div>
                    <div css={tw`mt-3`}>
                        <Button type={'submit'} className={'w-full !py-3'} disabled={isSubmitting}>
                            {t('register.register')}
                        </Button>
                    </div>
                    {(String(oauthDiscord) === 'true' || String(oauthGithub) === 'true' || String(oauthGoogle) === 'true') && (
                        <div css={tw`mt-4 flex flex-col gap-2`}>
                            <div css={tw`flex items-center gap-3`}>
                                <div css={tw`flex-1 h-px bg-neutral-700`} />
                                <span css={tw`text-xs text-neutral-500 uppercase tracking-wide`}>Or register with</span>
                                <div css={tw`flex-1 h-px bg-neutral-700`} />
                            </div>
                            <div css={tw`flex gap-2`}>
                                {String(oauthDiscord) === 'true' && (
                                    <a href="/auth/oauth/discord" css={tw`flex-1 flex items-center justify-center gap-2 py-2.5 rounded-lg bg-[#5865F2] hover:bg-[#4752C4] text-white text-sm font-medium transition-colors no-underline`}>
                                        <FaDiscord size={18} /> Discord
                                    </a>
                                )}
                                {String(oauthGithub) === 'true' && (
                                    <a href="/auth/oauth/github" css={tw`flex-1 flex items-center justify-center gap-2 py-2.5 rounded-lg bg-[#24292F] hover:bg-[#1B1F24] text-white text-sm font-medium transition-colors no-underline`}>
                                        <FaGithub size={18} /> GitHub
                                    </a>
                                )}
                                {String(oauthGoogle) === 'true' && (
                                    <a href="/auth/oauth/google" css={tw`flex-1 flex items-center justify-center gap-2 py-2.5 rounded-lg bg-[#EA4335] hover:bg-[#D33426] text-white text-sm font-medium transition-colors no-underline`}>
                                        <FaGoogle size={18} /> Google
                                    </a>
                                )}
                            </div>
                        </div>
                    )}
                    <div css={tw`mt-6 text-center`}>
                        <Link
                            to={'/auth/login'}
                            css={tw`text-xs text-neutral-300 tracking-wide uppercase no-underline hover:text-neutral-200`}
                        >
                            {t('register.already-have-account')}
                        </Link>
                    </div>
                </RegisterFormContainer>
            )}
        </Formik>
    );
};

export default RegisterContainer;