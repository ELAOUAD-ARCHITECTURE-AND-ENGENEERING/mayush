import React, { useEffect, useState } from 'react';
import { ScrollView, StyleSheet, TouchableOpacity, View } from 'react-native';
import { ActiveSession, authState } from '../../commerce/authState';
import { BottomTabBar, TabKey } from '../../design-system/components/navigation/BottomTabBar';
import { MayushIcon } from '../../design-system/components/navigation/MayushIcon';
import { MayushText } from '../../design-system/components/typography/MayushText';
import { colors } from '../../design-system/tokens/colors';
import { spacing } from '../../design-system/tokens/spacing';
import { DisconnectSessionModal } from './DisconnectSessionModal';

export interface ActiveSessionsScreenProps {
  onNavigateTab: (tab: TabKey) => void;
  onBack: () => void;
  language?: 'fr' | 'ar';
}

export const ActiveSessionsScreen: React.FC<ActiveSessionsScreenProps> = ({
  onNavigateTab,
  onBack,
  language = 'fr',
}) => {
  const isRTL = language === 'ar';
  const [sessions, setSessions] = useState<ActiveSession[]>(authState.getActiveSessions());
  const [selectedSession, setSelectedSession] = useState<ActiveSession | null>(null);

  useEffect(() => {
    const unsub = authState.subscribe(() => {
      setSessions(authState.getActiveSessions());
      setSelectedSession(authState.getSelectedSession());
    });
    return unsub;
  }, []);

  const currentDevice = sessions.find((s) => s.isCurrent) || {
    id: 'sess-1',
    device: 'iPhone 15 Pro',
    browser: 'Application Mayush iOS 1.0',
    location: 'Casablanca, Maroc',
    lastActive: 'En ce moment',
    isCurrent: true,
  };

  const otherSessions = sessions.filter((s) => !s.isCurrent);

  const handleOpenDisconnectModal = (session: ActiveSession) => {
    authState.setSelectedSession(session);
  };

  const handleCloseModal = () => {
    authState.setSelectedSession(null);
  };

  const handleConfirmDisconnect = () => {
    if (selectedSession) {
      authState.disconnectSession(selectedSession.id);
      authState.setSelectedSession(null);
    }
  };

  return (
    <View style={styles.screen}>
      {/* Top Header */}
      <View style={[styles.header, isRTL && styles.rowReverse]}>
        <TouchableOpacity accessibilityRole="button" onPress={onBack} style={styles.backButton}>
          <MayushIcon name={isRTL ? 'chevron-right' : 'chevron-left'} size={24} color={colors.brand.navy900} />
        </TouchableOpacity>
        <MayushText variant="pageTitle" color={colors.brand.navy900} style={styles.headerTitle}>
          {language === 'ar' ? 'الأجهزة والجلسات النشطة' : 'Appareils connectés'}
        </MayushText>
        <View style={styles.headerRightPlaceholder} />
      </View>

      <ScrollView contentContainerStyle={styles.content} showsVerticalScrollIndicator={false}>
        {/* Section: Current Device */}
        <MayushText variant="caption" color={colors.neutral.gray500} style={[styles.sectionHeader, isRTL && styles.rtlText]}>
          {language === 'ar' ? 'هذا الجهاز (الجلسة الحالية)' : 'CET APPAREIL (SESSION ACTUELLE)'}
        </MayushText>

        <View style={styles.currentCard}>
          <View style={[styles.cardRow, isRTL && styles.rowReverse]}>
            <View style={styles.deviceIconCircle}>
              <MayushIcon name="grid" size={24} color={colors.brand.orange500} />
            </View>
            <View style={styles.cardTextGroup}>
              <View style={[styles.badgeTitleRow, isRTL && styles.rowReverse]}>
                <MayushText variant="cardTitle" color={colors.brand.navy900} style={isRTL && styles.rtlText}>
                  {currentDevice.device}
                </MayushText>
                <View style={styles.activeBadge}>
                  <MayushText variant="caption" color={colors.semantic.success} style={styles.activeBadgeText}>
                    {language === 'ar' ? 'نشط الآن' : 'Session active'}
                  </MayushText>
                </View>
              </View>
              <MayushText variant="body" color={colors.neutral.gray700} style={isRTL && styles.rtlText}>
                {currentDevice.browser}
              </MayushText>
              <MayushText variant="caption" color={colors.neutral.gray500} style={isRTL && styles.rtlText}>
                {`${currentDevice.location} • ${currentDevice.lastActive}`}
              </MayushText>
            </View>
          </View>
        </View>

        {/* Section: Other Active Sessions */}
        <MayushText variant="caption" color={colors.neutral.gray500} style={[styles.sectionHeader, isRTL && styles.rtlText]}>
          {language === 'ar' ? 'أجهزة أخرى متصلة' : 'AUTRES APPAREILS CONNECTÉS'}
        </MayushText>

        {otherSessions.length === 0 ? (
          <View style={styles.emptyCard}>
            <MayushIcon name="check-circle" size={24} color={colors.semantic.success} />
            <MayushText variant="body" color={colors.neutral.gray500} style={isRTL && styles.rtlText}>
              {language === 'ar'
                ? 'لا توجد أجهزة أخرى متصلة بحسابك حالياً.'
                : 'Aucun autre appareil connecté à votre compte.'}
            </MayushText>
          </View>
        ) : (
          otherSessions.map((sess) => (
            <View key={sess.id} style={styles.sessionCard}>
              <View style={[styles.cardRow, isRTL && styles.rowReverse]}>
                <View style={styles.remoteIconCircle}>
                  <MayushIcon name="info" size={22} color={colors.brand.navy900} />
                </View>
                <View style={styles.cardTextGroup}>
                  <MayushText variant="cardTitle" color={colors.brand.navy900} style={isRTL && styles.rtlText}>
                    {sess.device}
                  </MayushText>
                  <MayushText variant="body" color={colors.neutral.gray700} style={isRTL && styles.rtlText}>
                    {sess.browser}
                  </MayushText>
                  <MayushText variant="caption" color={colors.neutral.gray500} style={isRTL && styles.rtlText}>
                    {`${sess.location} • ${sess.lastActive}`}
                  </MayushText>
                </View>
                <TouchableOpacity
                  accessibilityRole="button"
                  onPress={() => handleOpenDisconnectModal(sess)}
                  style={styles.disconnectPill}
                >
                  <MayushText variant="caption" color={colors.semantic.error} style={styles.disconnectText}>
                    {language === 'ar' ? 'إنهاء' : 'Déconnecter'}
                  </MayushText>
                </TouchableOpacity>
              </View>
            </View>
          ))
        )}
      </ScrollView>

      {/* Disconnect Session Modal Confirmation (309:761) */}
      <DisconnectSessionModal
        visible={Boolean(selectedSession)}
        session={selectedSession}
        onCancel={handleCloseModal}
        onConfirm={handleConfirmDisconnect}
        language={language}
      />

      <BottomTabBar activeTab="account" onTabPress={onNavigateTab} />
    </View>
  );
};

const styles = StyleSheet.create({
  screen: { flex: 1, backgroundColor: colors.surface.creamLight },
  header: {
    height: 56,
    paddingHorizontal: spacing.md,
    flexDirection: 'row',
    alignItems: 'center',
    justifyContent: 'space-between',
    backgroundColor: colors.surface.white,
    borderBottomWidth: 1,
    borderBottomColor: '#E7DED3',
  },
  backButton: { padding: 4 },
  headerTitle: { fontSize: 18, fontWeight: '700' },
  headerRightPlaceholder: { width: 32 },
  rowReverse: { flexDirection: 'row-reverse' },
  rtlText: { writingDirection: 'rtl' },
  content: { padding: spacing.md, paddingBottom: 100, gap: 12 },
  sectionHeader: { fontSize: 11, fontWeight: '700', marginTop: 8 },
  currentCard: {
    padding: 16,
    borderRadius: 16,
    backgroundColor: colors.surface.white,
    borderWidth: 1,
    borderColor: colors.brand.orange500,
  },
  sessionCard: {
    padding: 16,
    borderRadius: 16,
    backgroundColor: colors.surface.white,
    borderWidth: 1,
    borderColor: '#E7DED3',
  },
  emptyCard: {
    padding: 20,
    borderRadius: 16,
    backgroundColor: colors.surface.white,
    borderWidth: 1,
    borderColor: '#E7DED3',
    alignItems: 'center',
    gap: 8,
  },
  cardRow: { flexDirection: 'row', alignItems: 'center', gap: 12 },
  deviceIconCircle: {
    width: 44,
    height: 44,
    borderRadius: 22,
    backgroundColor: colors.surface.cream,
    alignItems: 'center',
    justifyContent: 'center',
  },
  remoteIconCircle: {
    width: 44,
    height: 44,
    borderRadius: 22,
    backgroundColor: '#F0F0F5',
    alignItems: 'center',
    justifyContent: 'center',
  },
  cardTextGroup: { flex: 1, gap: 2 },
  badgeTitleRow: { flexDirection: 'row', alignItems: 'center', gap: 8 },
  activeBadge: {
    paddingHorizontal: 8,
    paddingVertical: 2,
    borderRadius: 8,
    backgroundColor: '#E8F5E9',
  },
  activeBadgeText: { fontSize: 10, fontWeight: '700' },
  disconnectPill: {
    paddingHorizontal: 12,
    paddingVertical: 6,
    borderRadius: 12,
    backgroundColor: '#FFEEEC',
  },
  disconnectText: { fontSize: 12, fontWeight: '700' },
});
