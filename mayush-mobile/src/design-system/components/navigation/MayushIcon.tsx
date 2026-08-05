import React from 'react';
import { Feather, MaterialCommunityIcons } from '@expo/vector-icons';
import { ColorValue } from 'react-native';

export type MayushIconName =
  | 'arrow-left'
  | 'arrow-right'
  | 'arrow-down-up'
  | 'bell'
  | 'camera'
  | 'check'
  | 'check-circle'
  | 'chevron-down'
  | 'chevron-left'
  | 'chevron-right'
  | 'grid'
  | 'heart'
  | 'home'
  | 'menu'
  | 'minus'
  | 'more-horizontal'
  | 'plus'
  | 'search'
  | 'share'
  | 'shield'
  | 'sliders'
  | 'shopping-bag'
  | 'shopping-cart'
  | 'sliders-horizontal'
  | 'star'
  | 'tag'
  | 'user'
  | 'x'
  | 'sofa'
  | 'bed'
  | 'lamp'
  | 'table-furniture'
  | 'rug'
  | 'package-variant-closed'
  | 'chair-rolling'
  | 'outdoor-lamp'
  | 'truck-outline';

interface MayushIconProps {
  name: MayushIconName;
  size?: number;
  color?: ColorValue;
  strokeWidth?: number;
}

const materialIcons = new Set<MayushIconName>([
  'sofa',
  'bed',
  'lamp',
  'table-furniture',
  'rug',
  'package-variant-closed',
  'chair-rolling',
  'outdoor-lamp',
  'truck-outline',
]);

export const MayushIcon: React.FC<MayushIconProps> = ({
  name,
  size = 24,
  color = '#101D35',
  strokeWidth = 1.9,
}) => {
  if (materialIcons.has(name)) {
    return <MaterialCommunityIcons name={name as never} size={size} color={color} />;
  }

  const featherName = name === 'sliders-horizontal'
    ? 'sliders'
    : name === 'share'
      ? 'share-2'
      : name === 'arrow-down-up'
        ? 'repeat'
        : name;
  return <Feather name={featherName as never} size={size} color={color} strokeWidth={strokeWidth} />;
};
