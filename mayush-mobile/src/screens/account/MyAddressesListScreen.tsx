import React, { useEffect, useState } from 'react';
import { ScrollView, StyleSheet, TouchableOpacity, View } from 'react-native';
import { SavedAddress } from '../../commerce/checkoutState';
import { authState } from '../../commerce/authState';
import { BottomTabBar, TabKey } from '../../design-system/components/navigation/BottomTabBar';
import { MayushIcon } from '../../design-system/components/navigation/MayushIcon';
import { MayushText } from '../../design-system/components/typography/MayushText';
import { colors } from '../../design-system/tokens/colors';
import { spacing } from '../../design-system/tokens/spacing';
import { DeleteAddressModal } from './DeleteAddressModal';

export interface MyAddressesListScreenProps {
  onNavigateTab: (tab: TabKey) => void;
  onBack: () => void;
  onAddAddress: () => void;
  onEditAddress: (address: SavedAddress) => void;
  onNavigateV2?: () => void;
  language?: 'fr' | 'ar';
}

const LABEL_ICONS: Record<string, 'home' | 'briefcase' | 'map-pin'> = {
  Maison: 'home',
  Bureau: 'briefcase',
  Autre: 'map-pin',
};

export const MyAddressesListScreen: React.FC<MyAddressesListScreenProps> = ({
  onNavigateTab,
  onBack,
  onAddAddress,
  onEditAddress,
  language = 'fr',
}) => {
  const isRTL = language === 'ar';
  const [addresses, setAddresses] = useState<SavedAddress[]>(authState.getSavedAddresses());
  const [addressToDelete, setAddressToDelete] = useState<SavedAddress | null>(null);

  useEffect(() => {
    const unsub = authState.subscribe(() => {
      setAddresses(authState.getSavedAddresses());
      setAddressToDelete(authState.getAddressToDelete());
    });
    return unsub;
  }, []);

  const handleSetDefault = (id: string) => {
    authState.setDefaultAddress(id);
  };

  const handleOpenDelete = (address: SavedAddress) => {
    authState.setAddressToDelete(address);
  };

  const handleConfirmDelete = () => {
    if (addressToDelete) {
      authState.deleteAddress(addressToDelete.id);
    }
  };

  const handleCancelDelete = () => {
    authState.setAddressToDelete(null);
  };

  return (
    <View style={styles.screen}>
      {/* Header */}
      <View style={[styles.header, isRTL && styles.rowReverse]}>
        <TouchableOpacity accessibilityRole="button" onPress={onBack} style={styles.backButton}>
          <MayushIcon name={isRTL ? 'chevron-right' : 'chevron-left'} size={24} color={colors.brand.navy900} />
        </TouchableOpacity>
        <MayushText variant="pageTitle" color={colors.brand.navy900} style={styles.headerTitle}>
          {language === 'ar' ? 'عناويني' : 'Mes adresses'}
        </MayushText>
        <View style={styles.headerRightPlaceholder} />
      </View>

      <ScrollView contentContainerStyle={styles.content} showsVerticalScrollIndicator={false}>
        {/* Section label */}
        <MayushText variant="caption" color={colors.neutral.gray500} style={[styles.sectionLabel, isRTL && styles.rtlText]}>
          {language === 'ar' ? 'عناوين التوصيل المحفوظة' : 'ADRESSES DE LIVRAISON ENREGISTRÉES'}
        </MayushText>

        {addresses.length === 0 ? (
          <View style={styles.emptyCard}>
            <MayushIcon name="map-pin" size={32} color={colors.neutral.gray300} />
            <MayushText variant="cardTitle" color={colors.brand.navy900} style={isRTL && styles.rtlText}>
              {language === 'ar' ? 'لا توجد عناوين محفوظة' : 'Aucune adresse enregistrée'}
            </MayushText>
            <MayushText variant="body" color={colors.neutral.gray500} style={isRTL && styles.rtlText}>
              {language === 'ar' ? 'أضف عنوانك الأول لتسريع التوصيل.' : 'Ajoutez votre première adresse pour accélérer la livraison.'}
            </MayushText>
          </View>
        ) : (
          addresses.map((address) => {
            const labelKey = (address as any).label || 'Maison';
            const labelIcon = LABEL_ICONS[labelKey] || 'map-pin';
            return (
              <View key={address.id} style={[styles.addressCard, address.isDefault && styles.defaultCard]}>
                {/* Label + Default Badge Row */}
                <View style={[styles.topRow, isRTL && styles.rowReverse]}>
                  <View style={[styles.labelPill, isRTL && styles.rowReverse]}>
                    <MayushIcon name={labelIcon} size={14} color={colors.brand.orange500} />
                    <MayushText variant="caption" color={colors.brand.orange500} style={styles.labelText}>
                      {labelKey}
                    </MayushText>
                  </View>
                  {address.isDefault && (
                    <View style={styles.defaultBadge}>
                      <MayushIcon name="star" size={12} color={colors.brand.orange500} />
                      <MayushText variant="caption" color={colors.brand.orange500} style={styles.defaultText}>
                        {language === 'ar' ? 'افتراضي' : 'Par défaut'}
                      </MayushText>
                    </View>
                  )}
                </View>

                {/* Name & Phone */}
                <MayushText variant="cardTitle" color={colors.brand.navy900} style={[styles.addressName, isRTL && styles.rtlText]}>
                  {address.name}
                </MayushText>
                <View style={[styles.phoneRow, isRTL && styles.rowReverse]}>
                  <MayushIcon name="phone" size={14} color={colors.neutral.gray500} />
                  <MayushText variant="body" color={colors.neutral.gray700}>{address.phone}</MayushText>
                </View>

                {/* Address Details */}
                <View style={styles.divider} />
                <View style={[styles.detailRow, isRTL && styles.rowReverse]}>
                  <MayushIcon name="map" size={14} color={colors.brand.navy900} />
                  <MayushText variant="caption" color={colors.neutral.gray700} style={styles.detailText}>{address.addressLine}</MayushText>
                </View>
                <View style={[styles.detailRow, isRTL && styles.rowReverse]}>
                  <MayushIcon name="briefcase" size={14} color={colors.brand.navy900} />
                  <MayushText variant="caption" color={colors.neutral.gray700} style={styles.detailText}>{`${address.city}, ${address.postcode}`}</MayushText>
                </View>
                <View style={[styles.detailRow, isRTL && styles.rowReverse]}>
                  <MayushIcon name="map-pin" size={14} color={colors.brand.orange500} />
                  <MayushText variant="caption" color={colors.brand.orange500} style={styles.detailText}>
                    {language === 'ar' ? `منطقة التوصيل : ${address.zone}` : `Zone de livraison : ${address.zone}`}
                  </MayushText>
                </View>

                {/* Actions */}
                <View style={[styles.actionsRow, isRTL && styles.rowReverse]}>
                  {!address.isDefault && (
                    <TouchableOpacity accessibilityRole="button" onPress={() => handleSetDefault(address.id)} style={styles.actionOutline}>
                      <MayushIcon name="star" size={14} color={colors.brand.navy900} />
                      <MayushText variant="caption" color={colors.brand.navy900}>
                        {language === 'ar' ? 'تعيين' : 'Par défaut'}
                      </MayushText>
                    </TouchableOpacity>
                  )}
                  <TouchableOpacity accessibilityRole="button" onPress={() => onEditAddress(address)} style={styles.actionOutline}>
                    <MayushIcon name="edit-2" size={14} color={colors.brand.navy900} />
                    <MayushText variant="caption" color={colors.brand.navy900}>
                      {language === 'ar' ? 'تعديل' : 'Modifier'}
                    </MayushText>
                  </TouchableOpacity>
                  <TouchableOpacity accessibilityRole="button" onPress={() => handleOpenDelete(address)} style={styles.actionDanger}>
                    <MayushIcon name="trash-2" size={14} color={colors.semantic.error} />
                    <MayushText variant="caption" color={colors.semantic.error}>
                      {language === 'ar' ? 'حذف' : 'Supprimer'}
                    </MayushText>
                  </TouchableOpacity>
                </View>
              </View>
            );
          })
        )}

        {/* Add Address CTA */}
        <TouchableOpacity accessibilityRole="button" onPress={onAddAddress} style={[styles.addButton, isRTL && styles.rowReverse]}>
          <MayushIcon name="plus" size={18} color={colors.brand.orange500} />
          <MayushText variant="button" color={colors.brand.orange500}>
            {language === 'ar' ? 'إضافة عنوان جديد' : 'Ajouter une nouvelle adresse'}
          </MayushText>
        </TouchableOpacity>
      </ScrollView>

      <DeleteAddressModal
        visible={Boolean(addressToDelete)}
        address={addressToDelete}
        onCancel={handleCancelDelete}
        onConfirm={handleConfirmDelete}
        language={language}
      />

      <BottomTabBar activeTab="account" onTabPress={onNavigateTab} />
    </View>
  );
};

const styles = StyleSheet.create({
  screen: { flex: 1, backgroundColor: colors.surface.creamLight },
  header: {
    height: 56, paddingHorizontal: spacing.md, flexDirection: 'row', alignItems: 'center',
    justifyContent: 'space-between', backgroundColor: colors.surface.white,
    borderBottomWidth: 1, borderBottomColor: '#E7DED3',
  },
  backButton: { padding: 4 },
  headerTitle: { fontSize: 18, fontWeight: '700' },
  headerRightPlaceholder: { width: 32 },
  rowReverse: { flexDirection: 'row-reverse' },
  rtlText: { writingDirection: 'rtl' },
  content: { padding: spacing.md, paddingBottom: 100, gap: 14 },
  sectionLabel: { fontSize: 11, fontWeight: '700', marginTop: 4 },
  emptyCard: {
    padding: 32, borderRadius: 18, backgroundColor: colors.surface.white,
    borderWidth: 1, borderColor: '#E7DED3', alignItems: 'center', gap: 10,
  },
  addressCard: {
    padding: 16, borderRadius: 16, backgroundColor: colors.surface.white,
    borderWidth: 1, borderColor: '#E7DED3',
  },
  defaultCard: { borderColor: colors.brand.orange500 },
  topRow: { flexDirection: 'row', alignItems: 'center', justifyContent: 'space-between', marginBottom: 8 },
  labelPill: {
    flexDirection: 'row', alignItems: 'center', gap: 4,
    paddingHorizontal: 10, paddingVertical: 3, borderRadius: 8, backgroundColor: '#FFF6E8',
  },
  labelText: { fontSize: 11, fontWeight: '700' },
  defaultBadge: {
    flexDirection: 'row', alignItems: 'center', gap: 4,
    paddingHorizontal: 8, paddingVertical: 2, borderRadius: 6, backgroundColor: '#FFF6E8',
  },
  defaultText: { fontSize: 10, fontWeight: '700' },
  addressName: { fontSize: 15, fontWeight: '700' },
  phoneRow: { flexDirection: 'row', alignItems: 'center', gap: 6, marginTop: 4 },
  divider: { height: 1, backgroundColor: '#F0E8DF', marginVertical: 10 },
  detailRow: { flexDirection: 'row', alignItems: 'flex-start', gap: 8, marginTop: 4 },
  detailText: { flex: 1, fontSize: 11, lineHeight: 15 },
  actionsRow: { flexDirection: 'row', gap: 8, marginTop: 12 },
  actionOutline: {
    flex: 1, height: 34, borderRadius: 8, borderWidth: 1, borderColor: colors.brand.navy900,
    flexDirection: 'row', alignItems: 'center', justifyContent: 'center', gap: 5,
  },
  actionDanger: {
    flex: 1, height: 34, borderRadius: 8, borderWidth: 1, borderColor: colors.semantic.error,
    flexDirection: 'row', alignItems: 'center', justifyContent: 'center', gap: 5,
    backgroundColor: '#FFEEEC',
  },
  addButton: {
    height: 48, marginTop: 4, borderWidth: 1.5, borderColor: colors.brand.orange500,
    borderRadius: 12, flexDirection: 'row', alignItems: 'center', justifyContent: 'center',
    gap: 8, backgroundColor: '#FFFCF7',
  },
});
