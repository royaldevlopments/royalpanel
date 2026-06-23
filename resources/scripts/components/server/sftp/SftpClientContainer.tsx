import React, { useEffect, useState, useRef } from 'react';
import { ServerContext } from '@/state/server';
import { useStoreState } from 'easy-peasy';
import CopyOnClick from '@/components/elements/CopyOnClick';
import Input from '@/components/elements/Input';
import Label from '@/components/elements/Label';
import { ip } from '@/lib/formatters';
import { Button } from '@/components/elements/button/index';
import { TerminalIcon, GlobeIcon, StatusOnlineIcon, XCircleIcon, ChevronRightIcon } from '@heroicons/react/outline';
import { useTranslation } from 'react-i18next';
import TitledGreyBox from '@/components/elements/TitledGreyBox';
import ServerContentBlock from '@/components/elements/ServerContentBlock';
import Spinner from '@/components/elements/Spinner';
import { httpErrorToHuman } from '@/api/http';
import { ServerError } from '@/components/elements/ScreenBlock';
import FileObjectRow from '@/components/server/files/FileObjectRow';
import FileManagerBreadcrumbs from '@/components/server/files/FileManagerBreadcrumbs';
import { FileObject } from '@/api/server/files/loadDirectory';
import useFileManagerSwr from '@/plugins/useFileManagerSwr';
import { useLocation, NavLink } from 'react-router-dom';
import { hashToPath } from '@/helpers';
import { Formik } from 'formik';
import Field from '@/components/elements/Field';
import { SearchIcon, CloudUploadIcon } from '@heroicons/react/outline';
import { LuFilePlus, LuFolderPlus } from "react-icons/lu";
import http from '@/api/http';
import createDirectory from '@/api/server/files/createDirectory';

export default () => {
    const { t } = useTranslation('royal/server/settings');
    const { t: tFiles } = useTranslation('royal/server/files');
    const location = useLocation();
    const username = useStoreState((state) => state.user.data!.username);
    const id = ServerContext.useStoreState((state) => state.server.data!.id);
    const uuid = ServerContext.useStoreState((state) => state.server.data!.uuid);
    const sftp = ServerContext.useStoreState((state) => state.server.data!.sftpDetails);
    const directory = ServerContext.useStoreState((state) => state.files.directory);
    const setDirectory = ServerContext.useStoreActions((actions) => actions.files.setDirectory);

    const [status, setStatus] = useState<'checking' | 'online' | 'offline'>('checking');
    const [showDetails, setShowDetails] = useState(false);
    const { data: files, error: filesError, mutate } = useFileManagerSwr();
    const [searchTerm, setSearchTerm] = useState('');
    const [filteredFiles, setFilteredFiles] = useState<FileObject[]>([]);
    const [uploading, setUploading] = useState(false);
    const [uploadProgress, setUploadProgress] = useState(0);
    const [uploadName, setUploadName] = useState('');
    const [newFolderOpen, setNewFolderOpen] = useState(false);
    const [newFolderName, setNewFolderName] = useState('');
    const [newFolderSubmitting, setNewFolderSubmitting] = useState(false);
    const fileInputRef = useRef<HTMLInputElement>(null);

    useEffect(() => {
        setDirectory(hashToPath(location.hash));
    }, [location.hash]);

    useEffect(() => {
        mutate();
    }, [directory]);

    useEffect(() => {
        setFilteredFiles(
            files?.filter((file) => file.name.toLowerCase().includes(searchTerm.toLowerCase())) || []
        );
    }, [files, searchTerm]);

    useEffect(() => {
        fetch('/api/system').then((res) => {
            setStatus(res.status === 401 ? 'online' : 'offline');
        }).catch(() => setStatus('offline'));
    }, []);

    const connectionString = `sftp://${ip(sftp.ip)}:${sftp.port}`;

    const handleUpload = (e: React.ChangeEvent<HTMLInputElement>) => {
        const file = e.currentTarget.files?.[0];
        if (!file) return;
        setUploading(true);
        setUploadName(file.name);
        setUploadProgress(0);

        const formData = new FormData();
        formData.append('files', file);
        formData.append('directory', directory);

        http.post(`/api/client/servers/${uuid}/files/direct-upload`, formData, {
            onUploadProgress: (data: any) => {
                setUploadProgress(Math.round((data.loaded / (data.total || file.size)) * 100));
            },
        })
            .then(() => mutate())
            .catch(() => {})
            .finally(() => {
                setUploading(false);
                setUploadName('');
            });

        if (fileInputRef.current) fileInputRef.current.value = '';
    };

    const handleNewFolder = () => {
        if (!newFolderName.trim()) return;
        setNewFolderSubmitting(true);
        createDirectory(uuid, directory, newFolderName.trim())
            .then(() => mutate())
            .then(() => {
                setNewFolderOpen(false);
                setNewFolderName('');
                setNewFolderSubmitting(false);
            })
            .catch(() => setNewFolderSubmitting(false));
    };

    return (
        <ServerContentBlock title={'SFTP Client'} icon={TerminalIcon}>
            {newFolderOpen && (
                <div className={'fixed inset-0 z-50 flex items-center justify-center bg-black/50'} onClick={() => setNewFolderOpen(false)}>
                    <div className={'bg-neutral-700 rounded-box p-6 w-full max-w-md mx-4'} onClick={(e) => e.stopPropagation()}>
                        <h3 className={'text-lg font-semibold text-neutral-100 mb-4'}>Create New Folder</h3>
                        <input
                            type={'text'}
                            className={'w-full bg-neutral-600 border border-neutral-500 rounded px-3 py-2 text-neutral-100 text-sm mb-4'}
                            placeholder={'Folder name'}
                            value={newFolderName}
                            onChange={(e) => setNewFolderName(e.target.value)}
                            onKeyDown={(e) => e.key === 'Enter' && handleNewFolder()}
                            autoFocus
                        />
                        <div className={'flex justify-end gap-2'}>
                            <Button.Text variant={Button.Variants.Secondary} onClick={() => setNewFolderOpen(false)}>
                                Cancel
                            </Button.Text>
                            <Button onClick={handleNewFolder} disabled={newFolderSubmitting || !newFolderName.trim()}>
                                Create
                            </Button>
                        </div>
                    </div>
                </div>
            )}

            <div className={'flex items-center gap-4 mb-6 flex-wrap'}>
                <div className={'flex items-center gap-3 bg-gray-700 backdrop rounded-box px-5 py-3 flex-1 min-w-0'}>
                    <GlobeIcon className={'w-5 text-cyan-400 shrink-0'} />
                    <div className={'min-w-0 flex-1'}>
                        <p className={'text-sm text-neutral-200 truncate'}>{connectionString}</p>
                        <p className={'text-xs text-neutral-400'}>{username}.{id}</p>
                    </div>
                    <div className={'flex items-center gap-2 shrink-0'}>
                        {status === 'online' ? (
                            <StatusOnlineIcon className={'w-4 text-green-400'} />
                        ) : status === 'offline' ? (
                            <XCircleIcon className={'w-4 text-red-400'} />
                        ) : (
                            <Spinner size={'small'} />
                        )}
                        <span className={'text-xs text-neutral-400'}>
                            {status === 'online' ? 'Connected' : status === 'offline' ? 'Disconnected' : '...'}
                        </span>
                    </div>
                    <button onClick={() => setShowDetails(!showDetails)} className={'text-neutral-400 hover:text-neutral-200'}>
                        <ChevronRightIcon className={`w-4 transition-transform ${showDetails ? 'rotate-90' : ''}`} />
                    </button>
                </div>
                <CopyOnClick text={`sftp://${username}.${id}@${ip(sftp.ip)}:${sftp.port}`}>
                    <Button variant={Button.Variants.Primary} className={'text-sm'}>
                        Copy Connection
                    </Button>
                </CopyOnClick>
            </div>

            {showDetails && (
                <div className={'grid grid-cols-1 md:grid-cols-3 gap-4 mb-6'}>
                    <TitledGreyBox title={t('sftp.server-address')}>
                        <CopyOnClick text={`sftp://${ip(sftp.ip)}:${sftp.port}`}>
                            <Input type={'text'} value={`sftp://${ip(sftp.ip)}:${sftp.port}`} readOnly />
                        </CopyOnClick>
                    </TitledGreyBox>
                    <TitledGreyBox title={t('sftp.username')}>
                        <CopyOnClick text={`${username}.${id}`}>
                            <Input type={'text'} value={`${username}.${id}`} readOnly />
                        </CopyOnClick>
                    </TitledGreyBox>
                    <TitledGreyBox title={'Password'}>
                        <Input type={'text'} value={'Your panel password'} readOnly />
                    </TitledGreyBox>
                </div>
            )}

            <div className={'bg-gray-700 backdrop rounded-box'}>
                <div className={'sticky top-0 bg-gray-700 rounded-box !rounded-b-none backdrop-blur-lg space-y-2 pt-4 px-5 pb-3'}>
                    <div className={'flex gap-3 items-center flex-wrap'}>
                        <div className={'mr-auto flex-1 min-w-0 md:basis-auto'}>
                            <Formik initialValues={{}} onSubmit={() => {}}>
                                <Field
                                    type="text"
                                    icon={SearchIcon}
                                    placeholder={'Search files...'}
                                    name="search"
                                    value={searchTerm}
                                    className={'md:max-w-[300px] w-full !py-2'}
                                    onChange={(e) => setSearchTerm(e.target.value)}
                                />
                            </Formik>
                        </div>
                        <input ref={fileInputRef} type={'file'} className={'hidden'} onChange={handleUpload} />
                        <Button.Text onClick={() => setNewFolderOpen(true)} className={'text-sm'}>
                            <LuFolderPlus className={'w-4 mr-1'} />
                            New Folder
                        </Button.Text>
                        <NavLink to={`/server/${id}/files/new${window.location.hash}`}>
                            <Button.Text className={'text-sm'}>
                                <LuFilePlus className={'w-4 mr-1'} />
                                New File
                            </Button.Text>
                        </NavLink>
                        <Button.Text onClick={() => fileInputRef.current?.click()} disabled={uploading} className={'text-sm'}>
                            <CloudUploadIcon className={'w-4 mr-1'} />
                            {uploading ? `Uploading ${uploadName} (${uploadProgress}%)` : 'Upload'}
                        </Button.Text>
                    </div>
                    {uploading && (
                        <div className={'w-full bg-neutral-600 rounded-full h-1.5'}>
                            <div className={'bg-cyan-500 h-1.5 rounded-full transition-all duration-300'} style={{ width: `${uploadProgress}%` }} />
                        </div>
                    )}
                    <FileManagerBreadcrumbs />
                </div>
                {!files ? (
                    <Spinner size={'large'} centered />
                ) : filesError ? (
                    <ServerError message={httpErrorToHuman(filesError)} onRetry={() => mutate()} />
                ) : (
                    <div className={'overflow-hidden'} style={{ borderBottomLeftRadius: 'var(--radiusBox', borderBottomRightRadius: 'var(--radiusBox' }}>
                        <div className={'hidden sm:flex items-center px-5 py-2.5 bg-gray-600 text-xs text-gray-400 uppercase tracking-wide'}>
                            <div className={'flex-1 ml-5'}>{tFiles('name')}</div>
                            <div className={'w-1/6 mr-4 justify-end flex'}>{tFiles('size')}</div>
                            <div className={'w-1/5 mr-16 justify-end flex'}>{tFiles('date')}</div>
                        </div>
                        {!filteredFiles.length ? (
                            <p className={'text-sm text-neutral-300 text-center py-8'}>This directory is empty.</p>
                        ) : (
                            <div>
                                {filteredFiles.slice(0, 250).map((file) => (
                                    <FileObjectRow key={file.key} file={file} />
                                ))}
                            </div>
                        )}
                    </div>
                )}
            </div>
        </ServerContentBlock>
    );
};
