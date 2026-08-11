import React, { useState } from 'react';
import { ScrollView, StyleSheet, TouchableOpacity, View } from 'react-native';
import { MayushLogo } from '../../design-system/components/brand/MayushLogo';
import { PrimaryButton } from '../../design-system/components/actions/PrimaryButton';
import { MayushIcon } from '../../design-system/components/navigation/MayushIcon';
import { MayushText } from '../../design-system/components/typography/MayushText';
import { colors } from '../../design-system/tokens/colors';
import { authState } from '../../commerce/authState';

export interface TermsConsentScreenProps {
  onAccept: () => void;
  onDecline: () => void;
}

export const TermsConsentScreen: React.FC<TermsConsentScreenProps> = ({
  onAccept,
  onDecline,
}) => {
  const [agreed, setAgreed] = useState(false);
  const [errorMsg, setErrorMsg] = useState('');

  const handleConsentSubmit = () => {
    if (!agreed) {
      setErrorMsg('Veuillez accepter les conditions pour continuer.');
      return;
    }
    setErrorMsg('');
    authState.updateRegistrationDraft({ agreedToTerms: true });
    authState.completeRegistration();
    onAccept();
  };

  return (
    <View style={styles.container} accessibilityLabel="Conditions d'utilisation et confidentialité">
      <ScrollView contentContainerStyle={styles.scrollContent} showsVerticalScrollIndicator={false}>
        <View style={styles.headerRow}>
          <TouchableOpacity
            onPress={onDecline}
            style={styles.backBtn}
            accessibilityRole="button"
            accessibilityLabel="Retour"
          >
            <MayushIcon name="chevron-left" size={24} color={colors.brand.navy900} />
          </TouchableOpacity>
          <MayushLogo width={160} height={48} />
          <View style={styles.spacer} />
        </View>

        <View style={styles.titleSection}>
          <MayushText variant="pageTitle" color={colors.brand.navy900} style={styles.title}>
            Conditions & Confidentialité
          </MayushText>
          <MayushText variant="caption" color={colors.neutral.gray700} style={styles.subtitle}>
            Veuillez consulter et valider l'accord d'utilisation de la plateforme Mayush Design.
          </MayushText>
        </View>

        <View style={styles.legalBox}>
          <View style={styles.legalSection}>
            <View style={styles.legalHeader}>
              <MayushIcon name="shield" size={18} color={colors.brand.orange500} />
              <MayushText variant="sectionTitle" color={colors.brand.navy900} style={styles.sectionTitleText}>
                1. Conditions d'Utilisation
              </MayushText>
            </View>
            <MayushText variant="caption" color={colors.neutral.gray700} style={styles.legalText}>
              En créant un compte sur Mayush Design, vous acceptez de respecter nos règles d'achat, de livraison et d'interaction avec nos artisans partenaires à travers le Maroc.
            </MayushText>
          </View>

          <View style={styles.legalSection}>
            <View style={styles.legalHeader}>
              <MayushIcon name="lock" size={18} color={colors.brand.orange500} />
              <MayushText variant="sectionTitle" color={colors.brand.navy900} style={styles.sectionTitleText}>
                2. Protection des Données (Loi 09-08)
              </MayushText>
            </View>
            <MayushText variant="caption" color={colors.neutral.gray700} style={styles.legalText}>
              Vos données personnelles (nom, adresse de livraison, téléphone marocain) sont hébergées et traitées de manière sécurisée et ne seront jamais vendues à des tiers.
            </MayushText>
          </View>

          <View style={styles.legalSection}>
            <View style={styles.legalHeader}>
              <MayushIcon name="sofa" size={18} color={colors.brand.orange500} />
              <MayushText variant="sectionTitle" color={colors.brand.navy900} style={styles.sectionTitleText}>
                3. Commandes & Retours
              </MayushText>
            </View>
            <MayushText variant="caption" color={colors.neutral.gray700} style={styles.legalText}>
              Toutes les commandes en MAD sont assorties de la garantie satisfait ou remboursé sous 14 jours selon la réglementation marocaine du commerce électronique.
            </MayushText>
          </View>
        </View>

        {errorMsg ? (
          <View style={styles.errorBox}>
            <MayushIcon name="alert-circle" size={18} color={colors.semantic.error} />
            <MayushText variant="caption" color={colors.semantic.error}>
              {errorMsg}
            </MayushText>
          </View>
        ) : null}

        <TouchableOpacity
          style={styles.checkboxRow}
          onPress={() => {
            setAgreed(!agreed);
            if (errorMsg) setErrorMsg('');
          }}
          accessibilityRole="checkbox"
          accessibilityState={{ checked: agreed }}
        >
          <View style={[styles.checkbox, agreed && styles.checkboxActive]}>
            {agreed ? <MayushIcon name="check" size={14} color={colors.surface.white} /> : null}
          </View>
          <MayushText variant="caption" color={colors.brand.navy900} style={styles.checkboxLabel}>
            J'ai lu et j'accepte les conditions d'utilisation et la politique de confidentialité Mayush Design.
          </MayushText>
        </TouchableOpacity>

        <PrimaryButton
          label="Valider et créer mon compte"
          onPress={handleConsentSubmit}
          disabled={!agreed}
          style={styles.submitBtn}
        />
      </ScrollView>
    </View>
  );
};

const styles = StyleSheet.create({
  container: {
    flex: 1,
    backgroundColor: '#FFF8F0',
  },
  scrollContent: {
    paddingHorizontal: 24,
    paddingTop: 48,
    paddingBottom: 40,
  },
  headerRow: {
    flexDirection: 'row',
    alignItems: 'center',
    justifyContent: 'space-between',
    marginBottom: 24,
  },
  backBtn: {
    width: 40,
    height: 40,
    borderRadius: 20,
    backgroundColor: colors.surface.white,
    alignItems: 'center',
    justifyContent: 'center',
  },
  spacer: {
    width: 40,
  },
  titleSection: {
    marginBottom: 20,
  },
  title: {
    fontSize: 24,
    lineHeight: 30,
    marginBottom: 6,
  },
  subtitle: {
    lineHeight: 18,
  },
  legalBox: {
    backgroundColor: colors.surface.white,
    borderRadius: 20,
    padding: 20,
    gap: 16,
    marginBottom: 20,
    shadowColor: colors.brand.navy900,
    shadowOpacity: 0.06,
    shadowRadius: 10,
    elevation: 2,
  },
  legalSection: {
    gap: 6,
  },
  legalHeader: {
    flexDirection: 'row',
    alignItems: 'center',
    gap: 8,
  },
  sectionTitleText: {
    fontSize: 16,
  },
  legalText: {
    lineHeight: 18,
    paddingLeft: 26,
  },
  errorBox: {
    flexDirection: 'row',
    alignItems: 'center',
    gap: 8,
    backgroundColor: '#FEE2E2',
    padding: 10,
    borderRadius: 10,
    marginBottom: 14,
  },
  checkboxRow: {
    flexDirection: 'row',
    alignItems: 'flex-start',
    gap: 12,
    marginBottom: 24,
    paddingHorizontal: 4,
  },
  checkbox: {
    width: 22,
    height: 22,
    borderRadius: 6,
    borderWidth: 1.5,
    borderColor: colors.surface.borderWarm,
    alignItems: 'center',
    justifyContent: 'center',
    backgroundColor: colors.surface.white,
    marginTop: 2,
  },
  checkboxActive: {
    backgroundColor: colors.brand.orange500,
    borderColor: colors.brand.orange500,
  },
  checkboxLabel: {
    flex: 1,
    lineHeight: 18,
  },
  submitBtn: {
    marginTop: 4,
  },
});
