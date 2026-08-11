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

export interface MyAddressesListV2ScreenProps {
  onNavigateTab: (tab: TabKey) => void;
  onBack: () => void;
  onAddAddress: () => void;
  onEditAddress: (address: SavedAddress) => void;
  language?: 'fr' | 'ar';
}

/**
 * Figma node 309:763 — 08-my-addresses-list-v2-fr
 *
 * V2 variant of the address list: card-based layout with radio-style
 * selection and compact address display — no label pills. This is
 * the "populated addresses with selectable cards" view.
 */
export const MyAddressesListV2Screen: React.FC<MyAddressesListV2ScreenProps> = ({
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
        <View style={[styles.titleArea, isRTL && styles.rowReverse]}>
          <View style={styles.flex1}>
            <MayushText variant="pageTitle" color={colors.brand.navy900} style={[styles.sectionTitle, isRTL && styles.rtlText]}>
              {language === 'ar' ? 'عناوين التوصيل' : 'Adresses de livraison'}
            </MayushText>
            <MayushText variant="caption" color={colors.neutral.gray500} style={isRTL && styles.rtlText}>
              {language === 'ar' ? 'اختر أو أضف عنوان توصيل.' : 'Sélectionnez ou ajoutez une adresse de livraison.'}
            </MayushText>
          </View>
          <View style={styles.pinCircle}>
            <MayushIcon name="map-pin" size={24} color={colors.brand.navy900} />
          </View>
        </View>

        {addresses.map((address) => (
          <View key={address.id} style={styles.v2Card}>
            {/* Card heading */}
            <View style={[styles.cardHeading, isRTL && styles.rowReverse]}>
              <View style={styles.homeCircle}>
                <MayushIcon name="home" size={24} color={colors.brand.navy900} />
              </View>
              <View style={styles.flex1}>
                <View style={[styles.nameRow, isRTL && styles.rowReverse]}>
                  <MayushText variant="sectionTitle" color={colors.brand.navy900} style={isRTL && styles.rtlText}>
                    {address.name}
                  </MayushText>
                  <TouchableOpacity accessibilityRole="button" onPress={() => handleSetDefault(address.id)}>
                    <View style={[styles.radio, address.isDefault && styles.radioActive]}>
                      {address.isDefault && <View style={styles.radioInner} />}
                    </View>
                  </TouchableOpacity>
                </View>
                <View style={[styles.phoneRow, isRTL && styles.rowReverse]}>
                  <MayushIcon name="phone" size={13} color={colors.neutral.gray500} />
                  <MayushText variant="body" color={colors.neutral.gray700}>{address.phone}</MayushText>
                </View>
              </View>
            </View>

            {address.isDefault && (
              <View style={styles.defaultPill}>
                <MayushIcon name="star" size={12} color={colors.brand.orange500} />
                <MayushText variant="caption" color={colors.brand.orange500} style={styles.defaultLabel}>
                  {language === 'ar' ? 'العنوان الافتراضي' : 'Adresse par défaut'}
                </MayushText>
              </View>
            )}

            <View style={styles.divider} />

            <View style={[styles.detailRow, isRTL && styles.rowReverse]}>
              <MayushIcon name="map" size={14} color={colors.brand.navy900} />
              <MayushText variant="caption" color={colors.neutral.gray700} style={styles.detailText}>
                {address.addressLine}
              </MayushText>
            </View>
            <View style={[styles.detailRow, isRTL && styles.rowReverse]}>
              <MayushIcon name="briefcase" size={14} color={colors.brand.navy900} />
              <MayushText variant="caption" color={colors.neutral.gray700} style={styles.detailText}>
                {`${address.city}, ${address.postcode}`}
              </MayushText>
            </View>
            <View style={[styles.detailRow, isRTL && styles.rowReverse]}>
              <MayushIcon name="map-pin" size={14} color={colors.brand.orange500} />
              <MayushText variant="caption" color={colors.brand.orange500} style={styles.detailText}>
                {language === 'ar' ? `منطقة التوصيل : ${address.zone}` : `Zone de livraison : ${address.zone}`}
              </MayushText>
            </View>

            {/* Actions row */}
            <View style={[styles.actionsRow, isRTL && styles.rowReverse]}>
              <TouchableOpacity accessibilityRole="button" onPress={() => onEditAddress(address)} style={styles.editBtn}>
                <MayushIcon name="edit-2" size={15} color={colors.brand.navy900} />
                <MayushText variant="button" color={colors.brand.navy900} style={styles.actionLabel}>
                  {language === 'ar' ? 'تعديل' : 'Modifier'}
                </MayushText>
              </TouchableOpacity>
              <TouchableOpacity accessibilityRole="button" onPress={() => handleOpenDelete(address)} style={styles.deleteBtn}>
                <MayushIcon name="trash-2" size={15} color={colors.semantic.error} />
                <MayushText variant="button" color={colors.semantic.error} style={styles.actionLabel}>
                  {language === 'ar' ? 'حذف' : 'Supprimer'}
                </MayushText>
              </TouchableOpacity>
            </View>
          </View>
        ))}

        {/* Add Address CTA */}
        <TouchableOpacity accessibilityRole="button" onPress={onAddAddress} style={[styles.addCta, isRTL && styles.rowReverse]}>
          <MayushIcon name="plus" size={18} color={colors.brand.orange500} />
          <MayushText variant="button" color={colors.brand.orange500}>
            {language === 'ar' ? 'إضافة عنوان جديد' : 'Ajouter une nouvelle adresse'}
          </MayushText>
        </TouchableOpacity>

        <View style={[styles.securityBar, isRTL && styles.rowReverse]}>
          <MayushIcon name="shield" size={14} color={colors.brand.orange500} />
          <MayushText variant="caption" color={colors.neutral.gray700}>
            {language === 'ar' ? 'معلوماتك آمنة وسرية.' : 'Vos informations sont sécurisées et confidentielles.'}
          </MayushText>
        </View>
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
  screen: { flex: 1, backgroundColor: colors.surface.white },
  header: {
    height: 56, paddingHorizontal: spacing.md, flexDirection: 'row', alignItems: 'center',
    justifyContent: 'space-between', borderBottomWidth: 1, borderBottomColor: '#E7DED3',
  },
  backButton: { padding: 4 },
  headerTitle: { fontSize: 18, fontWeight: '700' },
  headerRightPlaceholder: { width: 32 },
  rowReverse: { flexDirection: 'row-reverse' },
  rtlText: { writingDirection: 'rtl' },
  flex1: { flex: 1 },
  content: { padding: spacing.md, paddingBottom: 100, gap: 12 },
  titleArea: { flexDirection: 'row', gap: 12, marginBottom: 4 },
  sectionTitle: { fontSize: 22, lineHeight: 28 },
  pinCircle: {
    width: 37, height: 37, borderRadius: 10, alignItems: 'center',
    justifyContent: 'center', backgroundColor: '#FFF7EF',
  },
  v2Card: {
    borderWidth: 1, borderColor: '#F0E3D7', borderRadius: 13,
    padding: 13, backgroundColor: colors.surface.white,
    shadowColor: colors.brand.navy900, shadowOpacity: 0.05,
    shadowRadius: 8, shadowOffset: { width: 0, height: 2 }, elevation: 2,
  },
  cardHeading: { flexDirection: 'row', gap: 12 },
  homeCircle: {
    width: 50, height: 50, borderRadius: 25, alignItems: 'center',
    justifyContent: 'center', backgroundColor: '#FFF7EF',
  },
  nameRow: { flexDirection: 'row', justifyContent: 'space-between', alignItems: 'center' },
  phoneRow: { flexDirection: 'row', alignItems: 'center', gap: 6, marginTop: 3 },
  radio: {
    width: 19, height: 19, borderRadius: 10, borderWidth: 1.5,
    borderColor: colors.brand.navy900, alignItems: 'center', justifyContent: 'center',
  },
  radioActive: { borderColor: colors.brand.orange500 },
  radioInner: { width: 11, height: 11, borderRadius: 6, backgroundColor: colors.brand.orange500 },
  defaultPill: {
    alignSelf: 'flex-start', marginTop: 6, minHeight: 22, borderRadius: 5,
    paddingHorizontal: 8, flexDirection: 'row', alignItems: 'center',
    gap: 5, backgroundColor: '#FFF6E8',
  },
  defaultLabel: { fontSize: 10, fontWeight: '700' },
  divider: { height: 1, marginVertical: 10, backgroundColor: '#F0E3D7' },
  detailRow: { flexDirection: 'row', alignItems: 'flex-start', gap: 8, marginTop: 4 },
  detailText: { flex: 1, fontSize: 10, lineHeight: 14 },
  actionsRow: { flexDirection: 'row', gap: 10, marginTop: 12 },
  editBtn: {
    flex: 1, height: 33, borderRadius: 7, borderWidth: 1, borderColor: colors.brand.navy900,
    flexDirection: 'row', alignItems: 'center', justifyContent: 'center', gap: 7,
  },
  deleteBtn: {
    flex: 1, height: 33, borderRadius: 7, borderWidth: 1, borderColor: colors.semantic.error,
    flexDirection: 'row', alignItems: 'center', justifyContent: 'center', gap: 7,
    backgroundColor: '#FFEEEC',
  },
  actionLabel: { fontSize: 11 },
  addCta: {
    height: 42, marginTop: 6, borderWidth: 1, borderColor: colors.brand.orange500,
    borderRadius: 8, flexDirection: 'row', alignItems: 'center',
    justifyContent: 'center', gap: 8, backgroundColor: '#FFFCF7',
  },
  securityBar: {
    marginTop: 10, alignSelf: 'center', flexDirection: 'row', alignItems: 'center', gap: 6,
  },
});
