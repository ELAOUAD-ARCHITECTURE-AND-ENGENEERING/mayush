/**
 * Mayush Design System - SecondaryButton Component
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

export interface SecondaryButtonProps {
  label: string;
  onPress: () => void;
  loading?: boolean;
  disabled?: boolean;
  fullWidth?: boolean;
  style?: StyleProp<ViewStyle>;
}

export const SecondaryButton: React.FC<SecondaryButtonProps> = ({
  label,
  onPress,
  loading = false,
  disabled = false,
  fullWidth = true,
  style,
}) => {
  const isInactive = disabled || loading;
  const backgroundColor = isInactive
    ? colors.surface.creamLight
    : colors.surface.cream;

  return (
    <TouchableOpacity
      activeOpacity={0.8}
      onPress={onPress}
      disabled={isInactive}
      style={[
        {
          height: sizing.buttonHeight,
          backgroundColor,
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
        <MayushText variant="button" color={colors.brand.orange500} align="center">
          {label}
        </MayushText>
      )}
    </TouchableOpacity>
  );
};
