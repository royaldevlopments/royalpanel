import React, { useRef, useState, useCallback } from 'react';
import axios, { AxiosProgressEvent } from 'axios';
import getFileUploadUrl from '@/api/server/files/getFileUploadUrl';
import { ServerContext } from '@/state/server';
import { useStoreState } from 'easy-peasy';
import { CloudUploadIcon, XCircleIcon, CheckCircleIcon } from '@heroicons/react/outline';
import ServerContentBlock from '@/components/elements/ServerContentBlock';
import { Button } from '@/components/elements/button/index';
import Spinner from '@/components/elements/Spinner';
import { useFlashKey } from '@/plugins/useFlash';
import FlashMessageRender from '@/components/FlashMessageRender';
import { bytesToString } from '@/lib/formatters';

interface UploadEntry {
    name: string;
    size: number;
    loaded: number;
    status: 'pending' | 'uploading' | 'done' | 'error';
    error?: string;
    abort?: AbortController;
}

export default () => {
    const uuid = ServerContext.useStoreState((state) => state.server.data!.uuid);
    const directory = ServerContext.useStoreState((state) => state.files.directory);
    const setDirectory = ServerContext.useStoreActions((actions) => actions.files.setDirectory);
    const { clearAndAddHttpError } = useFlashKey('upload');
    const fileInputRef = useRef<HTMLInputElement>(null);
    const [uploads, setUploads] = useState<UploadEntry[]>([]);
    const [dragging, setDragging] = useState(false);
    const [totalLoaded, setTotalLoaded] = useState(0);
    const [totalSize, setTotalSize] = useState(0);

    const addFiles = useCallback((files: FileList | File[]) => {
        const list = Array.from(files);
        const entries: UploadEntry[] = list.map((f) => ({
            name: f.name,
            size: f.size,
            loaded: 0,
            status: 'pending' as const,
        }));
        setUploads((prev) => [...prev, ...entries]);

        const totalBytes = list.reduce((acc, f) => acc + f.size, 0);
        setTotalSize((prev) => prev + totalBytes);

        list.forEach((file, i) => {
            const controller = new AbortController();
            setUploads((prev) => {
                const copy = [...prev];
                const idx = prev.length - list.length + i;
                copy[idx] = { ...copy[idx], status: 'uploading', abort: controller };
                return copy;
            });

            getFileUploadUrl(uuid)
                .then((url) =>
                    axios.post(url, { files: file }, {
                        signal: controller.signal,
                        headers: { 'Content-Type': 'multipart/form-data' },
                        params: { directory },
                        onUploadProgress: (data: AxiosProgressEvent) => {
                            setTotalLoaded((prev) => prev - (file.size - data.loaded));
                            setUploads((prev) => {
                                const copy = [...prev];
                                const idx = prev.findIndex((u) => u.name === file.name && u.status === 'uploading');
                                if (idx !== -1) copy[idx] = { ...copy[idx], loaded: data.loaded };
                                return copy;
                            });
                        },
                    })
                )
                .then(() => {
                    setUploads((prev) => prev.map((u) => u.name === file.name ? { ...u, status: 'done', loaded: u.size, abort: undefined } : u));
                    setTotalLoaded((prev) => prev + file.size);
                })
                .catch((error) => {
                    setUploads((prev) => prev.map((u) => u.name === file.name ? { ...u, status: 'error', error: error.message, abort: undefined } : u));
                });
        });
    }, [uuid, directory]);

    const handleDrop = useCallback((e: React.DragEvent) => {
        e.preventDefault();
        e.stopPropagation();
        setDragging(false);
        if (e.dataTransfer?.files.length) addFiles(e.dataTransfer.files);
    }, [addFiles]);

    const handleDragOver = (e: React.DragEvent) => {
        e.preventDefault();
        e.stopPropagation();
        setDragging(true);
    };

    const handleDragLeave = (e: React.DragEvent) => {
        e.preventDefault();
        e.stopPropagation();
        setDragging(false);
    };

    const handleFileSelect = (e: React.ChangeEvent<HTMLInputElement>) => {
        if (e.currentTarget.files?.length) addFiles(e.currentTarget.files);
        if (fileInputRef.current) fileInputRef.current.value = '';
    };

    const removeEntry = (name: string) => {
        const entry = uploads.find((u) => u.name === name);
        if (entry?.abort) entry.abort.abort();
        const removed = uploads.filter((u) => u.name === name)[0];
        setUploads((prev) => prev.filter((u) => u.name !== name));
        if (removed) {
            setTotalLoaded((prev) => prev - removed.loaded);
            setTotalSize((prev) => prev - removed.size);
        }
    };

    const clearDone = () => {
        const done = uploads.filter((u) => u.status === 'done');
        const doneSize = done.reduce((acc, u) => acc + u.size, 0);
        const doneLoaded = done.reduce((acc, u) => acc + u.loaded, 0);
        setUploads((prev) => prev.filter((u) => u.status !== 'done'));
        setTotalSize((prev) => prev - doneSize);
        setTotalLoaded((prev) => prev - doneLoaded);
    };

    const activeUploads = uploads.filter((u) => u.status === 'pending' || u.status === 'uploading').length;
    const doneCount = uploads.filter((u) => u.status === 'done').length;
    const errorCount = uploads.filter((u) => u.status === 'error').length;

    const overallProgress = totalSize > 0 ? Math.round((totalLoaded / totalSize) * 100) : 0;

    return (
        <ServerContentBlock title={'Upload Files'} showFlashKey={'upload'} icon={CloudUploadIcon}>
            <FlashMessageRender byKey={'upload'} className={'mb-4'} />

            <div className={'grid grid-cols-1 lg:grid-cols-3 gap-6 mb-6'}>
                <div className={'lg:col-span-2'}>
                    <div
                        className={`border-2 border-dashed rounded-box p-12 text-center cursor-pointer transition-colors duration-200 ${
                            dragging ? 'border-cyan-400 bg-cyan-500/10' : 'border-neutral-500 hover:border-neutral-400'
                        }`}
                        onDrop={handleDrop}
                        onDragOver={handleDragOver}
                        onDragLeave={handleDragLeave}
                        onClick={() => fileInputRef.current?.click()}
                    >
                        <input
                            ref={fileInputRef}
                            type={'file'}
                            multiple
                            className={'hidden'}
                            onChange={handleFileSelect}
                        />
                        <CloudUploadIcon className={'w-16 h-16 mx-auto text-neutral-400 mb-4'} />
                        <p className={'text-lg text-neutral-200 font-semibold'}>Drop files here or click to browse</p>
                        <p className={'text-sm text-neutral-400 mt-1'}>No file size limit — upload any size</p>
                    </div>
                </div>
                <div>
                    <div className={'bg-gray-700 backdrop rounded-box p-5 space-y-3'}>
                        <h3 className={'text-sm font-semibold text-neutral-200'}>Upload Queue</h3>
                        <div className={'text-xs text-neutral-400 space-y-1'}>
                            <div className={'flex justify-between'}>
                                <span>Total files</span>
                                <span className={'font-mono'}>{uploads.length}</span>
                            </div>
                            <div className={'flex justify-between'}>
                                <span>Uploading</span>
                                <span className={'font-mono text-yellow-400'}>{activeUploads}</span>
                            </div>
                            <div className={'flex justify-between'}>
                                <span>Completed</span>
                                <span className={'font-mono text-green-400'}>{doneCount}</span>
                            </div>
                            <div className={'flex justify-between'}>
                                <span>Failed</span>
                                <span className={'font-mono text-red-400'}>{errorCount}</span>
                            </div>
                            <div className={'flex justify-between'}>
                                <span>Total size</span>
                                <span className={'font-mono'}>{bytesToString(totalSize)}</span>
                            </div>
                        </div>
                        {totalSize > 0 && (
                            <div>
                                <div className={'flex justify-between text-xs text-neutral-400 mb-1'}>
                                    <span>{bytesToString(totalLoaded)} / {bytesToString(totalSize)}</span>
                                    <span>{overallProgress}%</span>
                                </div>
                                <div className={'w-full bg-neutral-600 rounded-full h-2'}>
                                    <div
                                        className={'bg-cyan-500 h-2 rounded-full transition-all duration-300'}
                                        style={{ width: `${overallProgress}%` }}
                                    />
                                </div>
                            </div>
                        )}
                        {doneCount > 0 && (
                            <Button.Text onClick={clearDone} className={'w-full'}>
                                Clear Completed
                            </Button.Text>
                        )}
                    </div>
                </div>
            </div>

            {uploads.length > 0 && (
                <div className={'bg-gray-700 backdrop rounded-box overflow-hidden'}>
                    <div className={'px-6 py-3 bg-gray-600 flex items-center'}>
                        <div className={'flex-1'}><span className={'text-xs text-gray-300 uppercase tracking-wide'}>File</span></div>
                        <div className={'w-28 text-right'}><span className={'text-xs text-gray-300 uppercase tracking-wide'}>Size</span></div>
                        <div className={'w-32 text-right'}><span className={'text-xs text-gray-300 uppercase tracking-wide'}>Progress</span></div>
                        <div className={'w-20 text-right'}><span className={'text-xs text-gray-300 uppercase tracking-wide'}>Status</span></div>
                        <div className={'w-10'}></div>
                    </div>
                    {uploads.map((entry) => {
                        const progress = entry.size > 0 ? Math.round((entry.loaded / entry.size) * 100) : 0;
                        return (
                            <div key={entry.name} className={'flex items-center px-6 py-3 border-b border-neutral-600 last:border-0'}>
                                <div className={'flex-1 truncate text-sm text-neutral-200 pr-4'}>{entry.name}</div>
                                <div className={'w-28 text-right text-sm text-neutral-400 font-mono'}>{bytesToString(entry.size)}</div>
                                <div className={'w-32 text-right'}>
                                    {entry.status === 'uploading' || entry.status === 'done' ? (
                                        <div className={'flex items-center gap-2 justify-end'}>
                                            <div className={'w-20 bg-neutral-600 rounded-full h-1.5'}>
                                                <div
                                                    className={`h-1.5 rounded-full transition-all duration-300 ${
                                                        entry.status === 'done' ? 'bg-green-500' : 'bg-cyan-500'
                                                    }`}
                                                    style={{ width: `${progress}%` }}
                                                />
                                            </div>
                                            <span className={'text-xs text-neutral-400 w-10 text-right'}>{progress}%</span>
                                        </div>
                                    ) : (
                                        <span className={'text-xs text-neutral-500'}>—</span>
                                    )}
                                </div>
                                <div className={'w-20 text-right'}>
                                    {entry.status === 'pending' && <span className={'text-xs text-neutral-400'}>Pending</span>}
                                    {entry.status === 'uploading' && <Spinner size={'small'} />}
                                    {entry.status === 'done' && <CheckCircleIcon className={'w-5 text-green-400 inline'} />}
                                    {entry.status === 'error' && (
                                        <span className={'text-xs text-red-400'} title={entry.error}>Failed</span>
                                    )}
                                </div>
                                <div className={'w-10 text-right'}>
                                    {(entry.status === 'pending' || entry.status === 'uploading') && (
                                        <button onClick={() => removeEntry(entry.name)} className={'text-neutral-400 hover:text-red-400 transition-colors'}>
                                            <XCircleIcon className={'w-4'} />
                                        </button>
                                    )}
                                </div>
                            </div>
                        );
                    })}
                </div>
            )}
        </ServerContentBlock>
    );
};
