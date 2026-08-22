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
  | 'heart-filled'
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
  | 'trash'
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
  | 'truck-outline'
  | 'trending-down'
  | 'trending-up'
  | 'truck'
  | 'refresh-cw'
  | 'shield-check'
  | 'desk'
  | 'vase-outline'
  | 'bookshelf'
  | 'table-chair'
  | 'file-text'
  | 'thumbs-up'
  | 'x-circle'
  | 'alert-circle'
  | 'lock'
  | 'eye'
  | 'eye-off'
  | 'circle'
  | 'credit-card'
  | 'globe'
  | 'log-out'
  | 'wallet'
  | 'help-circle'
  | 'chevron-up'
  | 'life-buoy'
  | 'rotate-ccw'
  | 'box'
  | 'mail'
  | 'message-circle'
  | 'headphones'
  | 'thumbs-down'
  | 'external-link'
  | 'inbox'
  | 'send'
  | 'paperclip'
  | 'edit-3'
  | 'sun'
  | 'message-square'
  | 'align-left'
  | 'image'
  | 'plus-circle'
  | 'alert-triangle'
  | 'star-filled'
  | 'download'
  | 'list'
  | 'wrench'
  | 'zap'
  | 'wifi-off'
  | 'moon'
  | 'smartphone';

interface MayushIconProps {
  name: MayushIconName;
  size?: number;
  color?: ColorValue;
  strokeWidth?: number;
  style?: any;
}

const MATERIAL_MAP: Record<string, keyof typeof MaterialCommunityIcons.glyphMap> = {
  'arrow-down-up': 'arrow-up-down',
  'sliders-horizontal': 'tune-variant',
  sofa: 'sofa-outline',
  bed: 'bed-outline',
  lamp: 'lamp-outline',
  'table-furniture': 'table-furniture',
  rug: 'rug',
  'package-variant-closed': 'package-variant-closed',
  'chair-rolling': 'chair-rolling',
  'outdoor-lamp': 'outdoor-lamp',
  'truck-outline': 'truck-outline',
  'shield-check': 'shield-check-outline',
  desk: 'desk',
  'vase-outline': 'flower-tulip-outline',
  bookshelf: 'bookshelf',
  'table-chair': 'table-chair',
  wallet: 'wallet-outline',
  'star-filled': 'star',
  'heart-filled': 'heart',
};

export const MayushIcon: React.FC<MayushIconProps> = ({ name, size = 24, color = '#1F2A3A', strokeWidth, style }) => {
  const resolvedName = (name as string) === 'close' ? 'x' : name;
  const materialGlyph = MATERIAL_MAP[resolvedName];
  if (materialGlyph) {
    return <MaterialCommunityIcons name={materialGlyph} size={size} color={color as string} style={style} />;
  }

  return (
    <Feather
      name={resolvedName as keyof typeof Feather.glyphMap}
      size={size}
      color={color as string}
      strokeWidth={strokeWidth}
      style={style}
    />
  );
};
