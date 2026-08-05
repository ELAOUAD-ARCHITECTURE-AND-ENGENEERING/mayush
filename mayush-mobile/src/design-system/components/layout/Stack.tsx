/**
 * Mayush Design System - Stack Component (Vertical Layout)
 */

import React from 'react';
import { View, ViewStyle, StyleProp } from 'react-native';
import { spacing } from '../../tokens/spacing';

export interface StackProps {
  space?: keyof typeof spacing;
  align?: 'flex-start' | 'center' | 'flex-end' | 'stretch';
  justify?: 'flex-start' | 'center' | 'flex-end' | 'space-between' | 'space-around';
  style?: StyleProp<ViewStyle>;
  children: React.ReactNode;
}

export const Stack: React.FC<StackProps> = ({
  space = 'none',
  align = 'stretch',
  justify = 'flex-start',
  style,
  children,
}) => {
  return (
    <View
      style={[
        {
          flexDirection: 'column',
          alignItems: align,
          justifyContent: justify,
          rowGap: spacing[space],
        },
        style,
      ]}
    >
      {children}
    </View>
  );
};
