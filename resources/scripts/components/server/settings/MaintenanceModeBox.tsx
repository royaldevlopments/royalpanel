import React, { useState } from 'react';
import { ServerContext } from '@/state/server';
import TitledGreyBox from '@/components/elements/TitledGreyBox';
import toggleMaintenance from '@/api/server/toggleMaintenance';
import { Actions, useStoreActions } from 'easy-peasy';
import { ApplicationStore } from '@/state';
import { httpErrorToHuman } from '@/api/http';
import tw from 'twin.macro';
import { Button } from '@/components/elements/button/index';
import { Dialog } from '@/components/elements/dialog';
import SpinnerOverlay from '@/components/elements/SpinnerOverlay';

export default () => {
    const uuid = ServerContext.useStoreState((state) => state.server.data!.uuid);
    const server = ServerContext.useStoreState((state) => state.server.data!);
    const setServer = ServerContext.useStoreActions((actions) => actions.server.setServer);
    const [modalVisible, setModalVisible] = useState(false);
    const [loading, setLoading] = useState(false);
    const { addFlash, clearFlashes } = useStoreActions((actions: Actions<ApplicationStore>) => actions.flashes);

    const maintenance = server.status === 'maintenance';

    const toggle = () => {
        setLoading(true);
        clearFlashes('settings');
        toggleMaintenance(uuid)
            .then((data) => {
                setServer({ ...server, status: data.maintenance ? 'maintenance' : null });
                addFlash({
                    key: 'settings',
                    type: 'success',
                    message: data.maintenance ? 'Maintenance mode enabled.' : 'Maintenance mode disabled.',
                });
            })
            .catch((error) => {
                addFlash({ key: 'settings', type: 'error', message: httpErrorToHuman(error) });
            })
            .then(() => {
                setLoading(false);
                setModalVisible(false);
            });
    };

    return (
        <TitledGreyBox title={'Maintenance Mode'} css={tw`relative`}>
            <SpinnerOverlay visible={loading} />
            <Dialog.Confirm
                open={modalVisible}
                title={maintenance ? 'Disable Maintenance Mode' : 'Enable Maintenance Mode'}
                confirm={maintenance ? 'Disable' : 'Enable'}
                onClose={() => setModalVisible(false)}
                onConfirmed={toggle}
            >
                {maintenance
                    ? 'Are you sure you want to disable maintenance mode? The server will return to normal operation.'
                    : 'Are you sure you want to enable maintenance mode? Power actions and commands will be blocked for all users.'}
            </Dialog.Confirm>
            <p css={tw`text-sm`}>
                Maintenance mode prevents power actions (start, stop, restart, kill) and console commands
                from being executed. The server console, files, databases, and other settings remain accessible.
            </p>
            <div css={tw`mt-6 text-right`}>
                {maintenance ? (
                    <Button onClick={() => setModalVisible(true)}>
                        Disable Maintenance Mode
                    </Button>
                ) : (
                    <Button.Danger variant={Button.Variants.Secondary} onClick={() => setModalVisible(true)}>
                        Enable Maintenance Mode
                    </Button.Danger>
                )}
            </div>
        </TitledGreyBox>
    );
};
