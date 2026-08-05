/**
 * Mayush Design System - PriceText Component
 */

import React from 'react';
import { Inline } from '../layout/Inline';
import { MayushText } from './MayushText';
import { colors } from '../../tokens/colors';

export interface PriceTextProps {
  priceFormatted: string;
  originalPriceFormatted?: string;
  hasDiscount?: boolean;
  size?: 'regular' | 'large';
}

export const PriceText: React.FC<PriceTextProps> = ({
  priceFormatted,
  originalPriceFormatted,
  hasDiscount = false,
  size = 'regular',
}) => {
  const variant = size === 'large' ? 'priceLarge' : 'priceRegular';

  return (
    <Inline space="xs" align="center">
      <MayushText variant={variant} color={colors.brand.orange500}>
        {priceFormatted}
      </MayushText>

      {hasDiscount && originalPriceFormatted ? (
        <MayushText
          variant="smallBody"
          color={colors.neutral.gray500}
          style={{ textDecorationLine: 'line-through' }}
        >
          {originalPriceFormatted}
        </MayushText>
      ) : null}
    </Inline>
  );
};
