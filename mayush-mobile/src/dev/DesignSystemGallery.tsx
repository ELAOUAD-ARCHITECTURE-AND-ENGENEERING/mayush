/**
 * Mayush Design System - Development QA Gallery Component
 * Isolated visual QA canvas for reviewing primitives, tokens, and LTR/RTL behavior.
 */

import React, { useState } from 'react';
import { ScrollView, View } from 'react-native';
import { ThemeProvider } from '../design-system/theme/ThemeProvider';
import { useTheme } from '../design-system/theme/useTheme';
import { Stack } from '../design-system/components/layout/Stack';
import { Inline } from '../design-system/components/layout/Inline';
import { Card } from '../design-system/components/layout/Card';
import { Spacer } from '../design-system/components/layout/Spacer';
import { Divider } from '../design-system/components/layout/Divider';
import { MayushText } from '../design-system/components/typography/MayushText';
import { PriceText } from '../design-system/components/typography/PriceText';
import { MayushLogo } from '../design-system/components/brand/MayushLogo';
import { PrimaryButton } from '../design-system/components/actions/PrimaryButton';
import { SecondaryButton } from '../design-system/components/actions/SecondaryButton';
import { OutlineButton } from '../design-system/components/actions/OutlineButton';
import { TextField } from '../design-system/components/forms/TextField';
import { QuantityStepper } from '../design-system/components/forms/QuantityStepper';
import { BottomTabBar, TabKey } from '../design-system/components/navigation/BottomTabBar';
import { ProductCard } from '../design-system/components/commerce/ProductCard';
import { VariantChip } from '../design-system/components/commerce/VariantChip';
import { PaymentOptionCard } from '../design-system/components/commerce/PaymentOptionCard';
import { Skeleton } from '../design-system/components/feedback/Skeleton';
import { colors } from '../design-system/tokens/colors';
import { MvpAppLanguage } from '../contracts/api/dto';

const GalleryContent: React.FC = () => {
  const { language, setLanguage, isRTL } = useTheme();
  const [activeTab, setActiveTab] = useState<TabKey>('home');
  const [qty, setQty] = useState<number>(1);
  const [chipSelected, setChipSelected] = useState<boolean>(true);
  const [paymentSelected, setPaymentSelected] = useState<'cmi' | 'cod'>('cmi');

  return (
    <ScrollView style={{ flex: 1, backgroundColor: colors.surface.cream }}>
      <View style={{ padding: 16, paddingTop: 48 }}>
        <Stack space="lg">
          {/* Header & Language Toggle */}
          <Inline space="md" align="center" justify="space-between">
            <MayushLogo width={130} height={36} />
            <Inline space="xs">
              <OutlineButton
                label="FR"
                fullWidth={false}
                disabled={language === 'fr'}
                onPress={() => setLanguage('fr')}
              />
              <OutlineButton
                label="AR"
                fullWidth={false}
                disabled={language === 'ar'}
                onPress={() => setLanguage('ar')}
              />
            </Inline>
          </Inline>

          <MayushText variant="pageTitle">
            {language === 'ar' ? 'معرض عناصر التصميم' : 'Galerie du Design System'}
          </MayushText>

          <Divider />

          {/* Typography Scale */}
          <Card padding="md">
            <Stack space="xs">
              <MayushText variant="display">Display Title</MayushText>
              <MayushText variant="sectionTitle">Section Title</MayushText>
              <MayushText variant="body">Body text in {language.toUpperCase()} (RTL: {isRTL ? 'Yes' : 'No'})</MayushText>
              <PriceText priceFormatted="250.00 MAD" originalPriceFormatted="350.00 MAD" hasDiscount={true} size="large" />
            </Stack>
          </Card>

          {/* Buttons */}
          <Card padding="md">
            <Stack space="md">
              <MayushText variant="strongBody">Action Buttons</MayushText>
              <PrimaryButton label="Primary Action" onPress={() => {}} />
              <SecondaryButton label="Secondary Action" onPress={() => {}} />
              <OutlineButton label="Outline Action" onPress={() => {}} />
            </Stack>
          </Card>

          {/* Form Controls */}
          <Card padding="md">
            <Stack space="md">
              <MayushText variant="strongBody">Form Inputs</MayushText>
              <TextField label="Nom complet" placeholder="Entrez votre nom" />
              <Inline space="md" align="center" justify="space-between">
                <MayushText variant="smallBody">Quantité:</MayushText>
                <QuantityStepper value={qty} onIncrement={() => setQty(qty + 1)} onDecrement={() => setQty(Math.max(1, qty - 1))} />
              </Inline>
              <Inline space="xs">
                <VariantChip label="Noir" selected={chipSelected} onPress={() => setChipSelected(!chipSelected)} />
                <VariantChip label="Blanc" selected={!chipSelected} onPress={() => setChipSelected(!chipSelected)} />
              </Inline>
            </Stack>
          </Card>

          {/* Payment Cards */}
          <Card padding="md">
            <Stack space="sm">
              <MayushText variant="strongBody">Payment Method Options</MayushText>
              <PaymentOptionCard
                title="Carte Bancaire CMI"
                description="Paiement sécurisé par carte marocaine"
                selected={paymentSelected === 'cmi'}
                onSelect={() => setPaymentSelected('cmi')}
              />
              <PaymentOptionCard
                title="Paiement à la livraison"
                description="Payer en espèces à la réception"
                selected={paymentSelected === 'cod'}
                onSelect={() => setPaymentSelected('cod')}
              />
            </Stack>
          </Card>

          {/* Product Card & Skeleton */}
          <Card padding="md">
            <Stack space="md">
              <MayushText variant="strongBody">Commerce Product Card & Skeleton</MayushText>
              <Inline space="md" align="flex-start">
                <ProductCard
                  name="Canapé Design Mayush"
                  thumbnailUrl="https://via.placeholder.com/150"
                  currentPriceFormatted="1 490.00 MAD"
                  originalPriceFormatted="1 990.00 MAD"
                  hasDiscount={true}
                  discountPercentage="25%"
                  onPress={() => {}}
                />
                <Stack space="xs" style={{ width: 140 }}>
                  <Skeleton height={140} borderRadius="lg" />
                  <Skeleton height={16} width="80%" />
                  <Skeleton height={16} width="50%" />
                </Stack>
              </Inline>
            </Stack>
          </Card>

          <Spacer size="xl" />
        </Stack>
      </View>

      {/* Bottom Tab Bar */}
      <BottomTabBar activeTab={activeTab} onTabPress={setActiveTab} cartBadgeCount={3} />
    </ScrollView>
  );
};

export const DesignSystemGallery: React.FC = () => {
  return (
    <ThemeProvider initialLanguage="fr">
      <GalleryContent />
    </ThemeProvider>
  );
};
