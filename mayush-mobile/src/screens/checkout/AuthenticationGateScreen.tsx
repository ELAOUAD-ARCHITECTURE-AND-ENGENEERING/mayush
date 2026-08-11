/**
 * Alias re-export for 309:613 (04-welcome-sign-in-create-account-guest-fr).
 * Unifies checkout authentication gate with the primary AuthenticationWelcomeScreen.
 */

import React from 'react';
import { AuthenticationWelcomeScreen, AuthenticationWelcomeScreenProps } from '../auth/AuthenticationWelcomeScreen';

export type AuthenticationGateScreenProps = AuthenticationWelcomeScreenProps;
export const AuthenticationGateScreen: React.FC<AuthenticationGateScreenProps> = (props) => (
  <AuthenticationWelcomeScreen {...props} />
);
