import React from 'react';
import PluginsManagerContainer from '@/blueprint/extensions/mcplugins/components/PluginsManagerContainer';

interface RouteDefinition {
  path: string;
  name: string | undefined;
  component: React.ComponentType;
  exact?: boolean;
  adminOnly: boolean | false;
  identifier: string;
}
interface ServerRouteDefinition extends RouteDefinition {
  permission: string | string[] | null;
}
interface Routes {
  account: RouteDefinition[];
  server: ServerRouteDefinition[];
}

export default {
  account: [
  ],
  server: [
    {
      path: '/mcplugins',
      permission: null,
      name: 'Plugins',
      component: PluginsManagerContainer,
      exact: true,
      adminOnly: false,
      identifier: 'mcplugins',
    },
  ],
} as Routes;
