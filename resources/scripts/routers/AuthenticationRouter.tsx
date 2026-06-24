import React from 'react';
import { Route, Switch, useRouteMatch } from 'react-router-dom';
import LoginContainer from '@/components/auth/LoginContainer';
import ForgotPasswordContainer from '@/components/auth/ForgotPasswordContainer';
import ResetPasswordContainer from '@/components/auth/ResetPasswordContainer';
import LoginCheckpointContainer from '@/components/auth/LoginCheckpointContainer';
import RegisterContainer from '@/components/auth/RegisterContainer';
import { NotFound } from '@/components/elements/ScreenBlock';
import { useHistory, useLocation } from 'react-router';
import { useStoreState } from 'easy-peasy';
import { ApplicationStore } from '@/state';

const Switches = () => {
    const { path } = useRouteMatch();
    const history = useHistory();
    const location = useLocation();
    const registration = useStoreState((state: ApplicationStore) => state.settings.data!.royal.registration);

    return (
        <Switch location={location}>
            <Route path={`${path}/login`} component={LoginContainer} exact />
            {String(registration) === 'true' && (
                <Route path={`${path}/register`} component={RegisterContainer} exact />
            )}
            <Route path={`${path}/login/checkpoint`} component={LoginCheckpointContainer} />
            <Route path={`${path}/password`} component={ForgotPasswordContainer} exact />
            <Route path={`${path}/password/reset/:token`} component={ResetPasswordContainer} />
            <Route path={`${path}/checkpoint`} />
            <Route path={'*'}>
                <NotFound onBack={() => history.push('/auth/login')} />
            </Route>
        </Switch>
    );
};

const AuthContainer = () => <Switches />;

export default AuthContainer;
