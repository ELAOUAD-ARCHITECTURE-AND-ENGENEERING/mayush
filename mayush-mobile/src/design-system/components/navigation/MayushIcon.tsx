import React from 'react';
import { Feather, MaterialCommunityIcons } from '@expo/vector-icons';
import { ColorValue } from 'react-native';

export type MayushIconName =
  | 'arrow-left'
  | 'arrow-right'
  | 'arrow-down-up'
  | 'bell'
  | 'bookmark'
  | 'briefcase'
  | 'calendar'
  | 'camera'
  | 'check'
  | 'check-circle'
  | 'clipboard'
  | 'clock'
  | 'chevron-down'
  | 'chevron-left'
  | 'chevron-right'
  | 'grid'
  | 'heart'
  | 'home'
  | 'info'
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
  | 'trash-2'
  | 'edit-2'
  | 'user'
  | 'x'
  | 'sofa'
  | 'bed'
  | 'lamp'
  | 'map'
  | 'map-pin'
  | 'table-furniture'
  | 'rug'
  | 'package-variant-closed'
  | 'phone'
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
