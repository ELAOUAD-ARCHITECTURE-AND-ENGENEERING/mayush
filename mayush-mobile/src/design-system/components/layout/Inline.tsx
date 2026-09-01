/**
 * Mayush Design System - Inline Component (Horizontal Layout)
 */

import React from 'react';
import { View, ViewStyle, StyleProp } from 'react-native';
import { spacing } from '../../tokens/spacing';

export interface InlineProps {
  space?: keyof typeof spacing;
  align?: 'flex-start' | 'center' | 'flex-end' | 'stretch';
  justify?: 'flex-start' | 'center' | 'flex-end' | 'space-between' | 'space-around';
  reverseRTL?: boolean;
  isRTL?: boolean;
  style?: StyleProp<ViewStyle>;
  children: React.ReactNode;
}

export const Inline: React.FC<InlineProps> = ({
  space = 'none',
  align = 'center',
  justify = 'flex-start',
  reverseRTL = false,
  isRTL = false,
  style,
  children,
}) => {
  const flexDirection = reverseRTL && isRTL ? 'row-reverse' : 'row';

  return (
    <View
      style={[
        {
          flexDirection,
          alignItems: align,
          justifyContent: justify,
          columnGap: spacing[space],
        },
        style,
      ]}
    >
      {children}
    </View>
  );
};
