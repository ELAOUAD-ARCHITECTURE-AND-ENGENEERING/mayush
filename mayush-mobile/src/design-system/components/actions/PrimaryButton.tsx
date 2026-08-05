/**
 * Mayush Design System - PrimaryButton Component
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

export interface PrimaryButtonProps {
  label: string;
  onPress: () => void;
  loading?: boolean;
  disabled?: boolean;
  fullWidth?: boolean;
  style?: StyleProp<ViewStyle>;
}

export const PrimaryButton: React.FC<PrimaryButtonProps> = ({
  label,
  onPress,
  loading = false,
  disabled = false,
  fullWidth = true,
  style,
}) => {
  const isInactive = disabled || loading;
  const backgroundColor = isInactive
    ? colors.interactive.primaryDisabled
    : colors.brand.orange500;

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
        <ActivityIndicator color={colors.surface.white} size="small" />
      ) : (
        <MayushText variant="button" color={colors.surface.white} align="center">
          {label}
        </MayushText>
      )}
    </TouchableOpacity>
  );
};
