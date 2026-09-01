import React, { useState, useEffect } from 'react';
import {
  View,
  StyleSheet,
  ScrollView,
  TouchableOpacity,
  TextInput,
} from 'react-native';
import { MayushText } from '../../design-system/components/typography/MayushText';
import { MayushIcon } from '../../design-system/components/navigation/MayushIcon';
import { colors } from '../../design-system/tokens/colors';
import { spacing } from '../../design-system/tokens/spacing';
import { supportState, SupportRequest } from '../../commerce/supportState';
import { accountPreferencesState } from '../../commerce/accountPreferencesState';
import { TabKey } from '../../design-system/components/navigation/BottomTabBar';

interface MySupportTicketsListScreenProps {
  onNavigateTab: (tab: TabKey) => void;
  onBack: () => void;
  onSelectTicket: (ticketId: string) => void;
  onNavigateContactForm: () => void;
  onNavigateEmptyState?: () => void;
}

type FilterTab = 'toutes' | 'ouvertes' | 'attente' | 'resolues';

export const MySupportTicketsListScreen: React.FC<MySupportTicketsListScreenProps> = ({
  onNavigateTab,
  onBack,
  onSelectTicket,
  onNavigateContactForm,
  onNavigateEmptyState,
}) => {
  const [requests, setRequests] = useState<SupportRequest[]>(supportState.getSupportRequests());
  const [selectedTab, setSelectedTab] = useState<FilterTab>('toutes');
  const [searchQuery, setSearchQuery] = useState('');
  const [language, setLanguage] = useState(accountPreferencesState.getLanguage());

  useEffect(() => {
    const unsubSupport = supportState.subscribe(() => {
      setRequests(supportState.getSupportRequests());
    });
    const unsubPref = accountPreferencesState.subscribe(() => {
      setLanguage(accountPreferencesState.getLanguage());
    });
    return () => {
      unsubSupport();
      unsubPref();
    };
  }, []);

  const isRTL = language === 'ar';
  const filteredRequests = supportState.filterSupportRequests(selectedTab, searchQuery);

  const getStatusBadge = (status: string) => {
    switch (status) {
      case 'open':
        return {
          label: isRTL ? 'مفتوح' : 'Ouvert',
          bg: 'rgba(232,125,62,0.12)',
          text: colors.brand.orange500,
          icon: 'clock' as const,
        };
      case 'in-progress':
        return {
          label: isRTL ? 'قيد الانتظار' : 'En attente',
          bg: '#FFF8E6',
          text: '#D97706',
          icon: 'clock' as const,
        };
      case 'resolved':
      case 'closed':
        return {
          label: isRTL ? 'تم الحل' : 'Résolu',
          bg: '#EDFCF2',
          text: '#0D894F',
          icon: 'check-circle' as const,
        };
      default:
        return {
          label: status,
          bg: colors.neutral.gray100,
          text: colors.neutral.gray700,
          icon: 'help-circle' as const,
        };
    }
  };

  return (
    <View style={styles.container}>
      {/* Header */}
      <View style={styles.header}>
        <TouchableOpacity onPress={onBack} style={styles.backBtn} activeOpacity={0.7}>
          <MayushIcon name={isRTL ? 'arrow-right' : 'arrow-left'} size={22} color={colors.brand.navy900} />
        </TouchableOpacity>
        <MayushText variant="cardTitle" style={styles.headerLogo}>
          MAYUSH<MayushText variant="cardTitle" color={colors.brand.orange500}> DESIGN</MayushText>
        </MayushText>
        <TouchableOpacity onPress={onNavigateContactForm} style={styles.bellBtn} activeOpacity={0.7}>
          <MayushIcon name="bell" size={20} color={colors.brand.navy900} />
        </TouchableOpacity>
      </View>

      <ScrollView style={styles.scrollView} contentContainerStyle={styles.scrollContent} showsVerticalScrollIndicator={false}>
        {/* Title Section */}
        <View style={styles.titleSection}>
          <View style={styles.headsetCircle}>
            <MayushIcon name="headphones" size={28} color={colors.brand.orange500} />
          </View>
          <MayushText variant="pageTitle" color={colors.brand.navy900} style={[styles.title, isRTL && styles.rtlText]}>
            {isRTL ? 'تذاكر الدعم الخاصة بي' : 'Mes tickets de support'}
          </MayushText>
          <MayushText variant="body" color={colors.neutral.gray500} style={[styles.subtitle, isRTL && styles.rtlText]}>
            {isRTL
              ? 'اطّلع على سجل جميع تذاكر الدعم الخاصة بك.'
              : 'Consultez l’historique de tous vos tickets de support.'}
          </MayushText>
        </View>

        {/* Search Bar */}
        <View style={styles.searchBox}>
          <MayushIcon name="search" size={18} color={colors.neutral.gray500} />
          <TextInput
            style={[styles.searchInput, isRTL && styles.rtlText]}
            placeholder={isRTL ? 'البحث عن طريق المرجع أو الموضوع' : 'Rechercher par référence ou sujet'}
            placeholderTextColor={colors.neutral.gray500}
            value={searchQuery}
            onChangeText={setSearchQuery}
          />
          {searchQuery ? (
            <TouchableOpacity onPress={() => setSearchQuery('')}>
              <MayushIcon name="x" size={16} color={colors.neutral.gray500} />
            </TouchableOpacity>
          ) : null}
        </View>

        {/* Filter Tabs */}
        <View style={styles.tabsRow}>
          {[
            { key: 'toutes', label: isRTL ? 'الكل' : 'Toutes' },
            { key: 'ouvertes', label: isRTL ? 'المفتوحة' : 'Ouvertes' },
            { key: 'attente', label: isRTL ? 'قيد الانتظار' : 'En attente' },
            { key: 'resolues', label: isRTL ? 'المغلقة' : 'Résolues' },
          ].map((tab) => {
            const isActive = selectedTab === tab.key;
            return (
              <TouchableOpacity
                key={tab.key}
                style={[styles.tabChip, isActive && styles.tabChipActive]}
                onPress={() => setSelectedTab(tab.key as FilterTab)}
                activeOpacity={0.7}
              >
                <MayushText
                  variant="strongBody"
                  color={isActive ? colors.brand.orange500 : colors.neutral.gray700}
                  style={styles.tabText}
                >
                  {tab.label}
                </MayushText>
              </TouchableOpacity>
            );
          })}
        </View>

        {/* Ticket List */}
        {filteredRequests.length === 0 ? (
          <View style={styles.emptyCard}>
            <MayushIcon name="inbox" size={32} color={colors.neutral.gray500} />
            <MayushText variant="strongBody" color={colors.brand.navy900} style={{ marginTop: spacing.xs }}>
              {isRTL ? 'لا توجد تذاكر دعم' : 'Aucun ticket trouvé'}
            </MayushText>
            <TouchableOpacity style={styles.createBtn} onPress={onNavigateContactForm} activeOpacity={0.85}>
              <MayushText variant="strongBody" color={colors.neutral.white}>
                {isRTL ? 'إنشاء تذكرة جديدة' : 'Créer un nouveau ticket'}
              </MayushText>
            </TouchableOpacity>
          </View>
        ) : (
          filteredRequests.map((ticket) => {
            const badge = getStatusBadge(ticket.status);
            return (
              <TouchableOpacity
                key={ticket.id}
                style={styles.ticketCard}
                onPress={() => {
                  supportState.setSelectedSupportRequestId(ticket.id);
                  onSelectTicket(ticket.id);
                }}
                activeOpacity={0.85}
              >
                <View style={styles.cardHeader}>
                  <View style={styles.cardRefRow}>
                    <View style={styles.fileIconCircle}>
                      <MayushIcon name="file-text" size={18} color={colors.brand.orange500} />
                    </View>
                    <View style={{ flex: 1 }}>
                      <MayushText variant="strongBody" color={colors.brand.navy900}>
                        {ticket.reference}
                      </MayushText>
                      <MayushText variant="body" color={colors.brand.navy900} style={[{ fontSize: 13, marginTop: 2 }, isRTL && styles.rtlText]}>
                        {isRTL ? ticket.titleAr || ticket.title : ticket.title}
                      </MayushText>
                    </View>
                  </View>

                  <View style={[styles.badge, { backgroundColor: badge.bg }]}>
                    <MayushIcon name={badge.icon} size={12} color={badge.text} />
                    <MayushText variant="smallBody" color={badge.text} style={{ fontWeight: '600', marginLeft: 4 }}>
                      {badge.label}
                    </MayushText>
                  </View>
                </View>

                {ticket.relatedOrderId ? (
                  <View style={styles.orderLinkRow}>
                    <MayushIcon name="shopping-bag" size={14} color={colors.neutral.gray500} />
                    <MayushText variant="smallBody" color={colors.neutral.gray700}>
                      {isRTL ? 'الطلب : ' : 'Commande : '}{ticket.relatedOrderId}
                    </MayushText>
                  </View>
                ) : null}

                <View style={styles.cardFooter}>
                  <View style={styles.dateRow}>
                    <MayushIcon name="clock" size={14} color={colors.neutral.gray500} />
                    <MayushText variant="smallBody" color={colors.neutral.gray500}>
                      {isRTL ? 'آخر تحديث : ' : 'Dernière mise à jour : '}{ticket.date}
                    </MayushText>
                  </View>

                  <View style={styles.cardActionRight}>
                    {ticket.unreadCount && ticket.unreadCount > 0 ? (
                      <View style={styles.unreadBadge}>
                        <MayushText variant="smallBody" color={colors.neutral.white} style={{ fontSize: 11, fontWeight: '700' }}>
                          {ticket.unreadCount} {isRTL ? 'جديد' : (ticket.unreadCount > 1 ? 'Nouveaux' : 'Nouveau')}
                        </MayushText>
                      </View>
                    ) : null}

                    <TouchableOpacity
                      style={styles.detailsBtn}
                      onPress={() => {
                        supportState.setSelectedSupportRequestId(ticket.id);
                        onSelectTicket(ticket.id);
                      }}
                      activeOpacity={0.7}
                    >
                      <MayushText variant="smallBody" color={colors.brand.orange500} style={{ fontWeight: '600' }}>
                        {isRTL ? 'عرض التفاصيل' : 'Voir les détails'}
                      </MayushText>
                      <MayushIcon name={isRTL ? 'chevron-left' : 'chevron-right'} size={14} color={colors.brand.orange500} />
                    </TouchableOpacity>
                  </View>
                </View>
              </TouchableOpacity>
            );
          })
        )}
      </ScrollView>
    </View>
  );
};

const styles = StyleSheet.create({
  container: { flex: 1, backgroundColor: '#FAF8F5' },
  header: {
    flexDirection: 'row', alignItems: 'center', justifyContent: 'space-between',
    paddingHorizontal: spacing.md, paddingTop: 48, paddingBottom: spacing.sm,
    backgroundColor: '#FAF8F5',
  },
  backBtn: { padding: spacing.xs },
  headerLogo: { fontSize: 18, fontWeight: '800', color: colors.brand.navy900 },
  bellBtn: { padding: spacing.xs },
  scrollView: { flex: 1 },
  scrollContent: { padding: spacing.md, paddingBottom: 40 },
  titleSection: { alignItems: 'center', marginBottom: spacing.md },
  headsetCircle: {
    width: 56, height: 56, borderRadius: 28, backgroundColor: 'rgba(232,125,62,0.1)',
    alignItems: 'center', justifyContent: 'center', marginBottom: spacing.xs,
  },
  title: { textAlign: 'center', marginBottom: 4 },
  subtitle: { textAlign: 'center', fontSize: 13, lineHeight: 18 },
  searchBox: {
    flexDirection: 'row', alignItems: 'center', gap: spacing.xs,
    backgroundColor: colors.neutral.white, borderWidth: 1, borderColor: colors.neutral.gray300,
    borderRadius: 12, paddingHorizontal: spacing.sm, paddingVertical: 10, marginBottom: spacing.md,
  },
  searchInput: { flex: 1, fontSize: 13, color: colors.brand.navy900 },
  tabsRow: { flexDirection: 'row', gap: spacing.xs, marginBottom: spacing.md },
  tabChip: {
    flex: 1, paddingVertical: 8, alignItems: 'center', borderRadius: 8,
    borderBottomWidth: 2, borderBottomColor: 'transparent',
  },
  tabChipActive: { borderBottomColor: colors.brand.orange500 },
  tabText: { fontSize: 12 },
  emptyCard: {
    backgroundColor: colors.neutral.white, borderRadius: 16, padding: 24,
    alignItems: 'center', borderWidth: 1, borderColor: 'rgba(0,0,0,0.05)', marginTop: spacing.md,
  },
  createBtn: {
    backgroundColor: colors.brand.orange500, borderRadius: 12, paddingHorizontal: 20,
    paddingVertical: 12, marginTop: spacing.md,
  },
  ticketCard: {
    backgroundColor: colors.neutral.white, borderRadius: 16, padding: spacing.md,
    marginBottom: spacing.md, borderWidth: 1, borderColor: 'rgba(0,0,0,0.05)',
  },
  cardHeader: { flexDirection: 'row', justifyContent: 'space-between', alignItems: 'flex-start' },
  cardRefRow: { flexDirection: 'row', gap: spacing.xs, flex: 1, paddingRight: spacing.xs },
  fileIconCircle: {
    width: 36, height: 36, borderRadius: 18, backgroundColor: 'rgba(232,125,62,0.1)',
    alignItems: 'center', justifyContent: 'center',
  },
  badge: {
    flexDirection: 'row', alignItems: 'center', paddingHorizontal: 8, paddingVertical: 4,
    borderRadius: 12,
  },
  orderLinkRow: {
    flexDirection: 'row', alignItems: 'center', gap: 6, marginTop: 10,
    paddingTop: 10, borderTopWidth: 1, borderTopColor: colors.neutral.gray300,
  },
  cardFooter: {
    flexDirection: 'row', justifyContent: 'space-between', alignItems: 'center',
    marginTop: 10, paddingTop: 10, borderTopWidth: 1, borderTopColor: colors.neutral.gray300,
  },
  dateRow: { flexDirection: 'row', alignItems: 'center', gap: 4 },
  cardActionRight: { flexDirection: 'row', alignItems: 'center', gap: spacing.xs },
  unreadBadge: {
    backgroundColor: colors.brand.orange500, borderRadius: 10, paddingHorizontal: 6, paddingVertical: 2,
  },
  detailsBtn: { flexDirection: 'row', alignItems: 'center', gap: 2 },
  rtlText: { textAlign: 'right' },
});
