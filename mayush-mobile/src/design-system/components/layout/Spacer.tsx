/**
 * Mayush Design System - Spacer Component
 */

import React from 'react';
import { View } from 'react-native';
import { spacing } from '../../tokens/spacing';

export interface SpacerProps {
  size?: keyof typeof spacing;
  horizontal?: boolean;
}

export const Spacer: React.FC<SpacerProps> = ({
  size = 'md',
  horizontal = false,
}) => {
  const dim = spacing[size];
  return (
    <View
      style={{
        width: horizontal ? dim : undefined,
        height: horizontal ? undefined : dim,
      }}
    />
  );
};
