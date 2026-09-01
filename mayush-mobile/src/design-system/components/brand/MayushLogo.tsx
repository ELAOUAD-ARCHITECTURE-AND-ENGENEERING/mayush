/**
 * Mayush Design System - MayushLogo Component
 * Dynamically resolves logo image from backend API setting (URI) with local bundled asset fallback.
 * Scaled to elegant, proportioned dimensions matching Mayush reference headers.
 */

import React, { useState } from 'react';
import { Image, ImageStyle, StyleProp } from 'react-native';
import { useTheme } from '../../theme/useTheme';

const LOCAL_LOGO_ASSET = require('../../../../assets/brand/logo-transparent.png');

export interface MayushLogoProps {
  width?: number;
  height?: number;
  uri?: string;
  style?: StyleProp<ImageStyle>;
}

export const MayushLogo: React.FC<MayushLogoProps> = ({
  width = 108,
  height = 30,
  uri,
  style,
}) => {
  const { theme } = useTheme();
  const [imageError, setImageError] = useState(false);

  const activeUri = uri || theme.logoUrl;

  const imageSource = (activeUri && !imageError)
    ? { uri: activeUri }
    : LOCAL_LOGO_ASSET;

  return (
    <Image
      source={imageSource}
      onError={() => setImageError(true)}
      style={[
        {
          width,
          height,
          resizeMode: 'contain',
        },
        style,
      ]}
    />
  );
};
