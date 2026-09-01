/**
 * Mayush Design System - OutlineButton Component
 */

import React from 'react';
import {
  TouchableOpacity,
  ActivityIndicator,
  ViewStyle,
  StyleProp,
} from 'react-native';
import { MayushText } from '../typography/MayushText';
import { colors } from '../../tokens/colors';
import { radii } from '../../tokens/radii';
import { sizing } from '../../tokens/sizing';
import { spacing } from '../../tokens/spacing';

export interface OutlineButtonProps {
  label: string;
  onPress: () => void;
  loading?: boolean;
  disabled?: boolean;
  fullWidth?: boolean;
  style?: StyleProp<ViewStyle>;
}

export const OutlineButton: React.FC<OutlineButtonProps> = ({
  label,
  onPress,
  loading = false,
  disabled = false,
  fullWidth = true,
  style,
}) => {
  const isInactive = disabled || loading;

  return (
    <TouchableOpacity
      activeOpacity={0.8}
      onPress={onPress}
      disabled={isInactive}
      style={[
        {
          height: sizing.buttonHeight,
          backgroundColor: 'transparent',
          borderColor: isInactive ? colors.neutral.gray300 : colors.brand.orange500,
          borderWidth: 1.5,
          borderRadius: radii.xl,
          alignItems: 'center',
          justifyContent: 'center',
          paddingHorizontal: spacing.xl,
          alignSelf: fullWidth ? 'stretch' : 'auto',
        },
        style,
      ]}
      accessibilityRole="button"
      accessibilityState={{ disabled: isInactive, busy: loading }}
    >
      {loading ? (
        <ActivityIndicator color={colors.brand.orange500} size="small" />
      ) : (
        <MayushText
          variant="button"
          color={isInactive ? colors.neutral.gray500 : colors.brand.orange500}
          align="center"
        >
          {label}
        </MayushText>
      )}
    </TouchableOpacity>
  );
};
