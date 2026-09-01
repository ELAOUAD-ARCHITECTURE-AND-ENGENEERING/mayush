import React, { useEffect, useRef } from 'react';
import { View, StyleSheet, ScrollView, Animated } from 'react-native';
import { colors } from '../../design-system/tokens/colors';
import { spacing } from '../../design-system/tokens/spacing';

interface SettingsSkeletonLoadingStateScreenProps {
  onSimulateComplete?: () => void;
}

export const SettingsSkeletonLoadingStateScreen: React.FC<SettingsSkeletonLoadingStateScreenProps> = ({
  onSimulateComplete,
}) => {
  const pulseAnim = useRef(new Animated.Value(0.4)).current;

  useEffect(() => {
    const animation = Animated.loop(
      Animated.sequence([
        Animated.timing(pulseAnim, {
          toValue: 1,
          duration: 750,
          useNativeDriver: true,
        }),
        Animated.timing(pulseAnim, {
          toValue: 0.4,
          duration: 750,
          useNativeDriver: true,
        }),
      ])
    );
    animation.start();

    if (onSimulateComplete) {
      const timer = setTimeout(onSimulateComplete, 2000);
      return () => {
        clearTimeout(timer);
        animation.stop();
      };
    }

    return () => animation.stop();
  }, [pulseAnim, onSimulateComplete]);

  return (
    <View style={styles.container}>
      {/* Header Skeleton */}
      <View style={styles.header}>
        <Animated.View style={[styles.headerTitleSkeleton, { opacity: pulseAnim }]} />
      </View>

      <ScrollView contentContainerStyle={styles.scrollContent} showsVerticalScrollIndicator={false}>
        {/* User Profile Card Skeleton */}
        <Animated.View style={[styles.profileCardSkeleton, { opacity: pulseAnim }]}>
          <View style={styles.avatarSkeleton} />
          <View style={styles.profileTextCol}>
            <View style={styles.nameSkeletonBar} />
            <View style={styles.emailSkeletonBar} />
          </View>
        </Animated.View>

        {/* Section 1 Header Skeleton */}
        <Animated.View style={[styles.sectionTitleSkeletonBar, { opacity: pulseAnim }]} />

        {/* 3 Menu Item Row Skeletons */}
        {[1, 2, 3].map((key) => (
          <Animated.View key={key} style={[styles.menuRowSkeleton, { opacity: pulseAnim }]}>
            <View style={styles.iconCircleSkeleton} />
            <View style={styles.rowLabelSkeletonBar} />
            <View style={styles.chevronSkeleton} />
          </Animated.View>
        ))}

        {/* Section 2 Header Skeleton */}
        <Animated.View style={[styles.sectionTitleSkeletonBar, { opacity: pulseAnim, marginTop: spacing.lg }]} />

        {/* 2 Toggle Item Row Skeletons */}
        {[4, 5].map((key) => (
          <Animated.View key={key} style={[styles.menuRowSkeleton, { opacity: pulseAnim }]}>
            <View style={styles.iconCircleSkeleton} />
            <View style={styles.rowLabelSkeletonBar} />
            <View style={styles.toggleSwitchSkeleton} />
          </Animated.View>
        ))}
      </ScrollView>
    </View>
  );
};

const styles = StyleSheet.create({
  container: { flex: 1, backgroundColor: colors.neutral.gray100 },
  header: {
    paddingHorizontal: spacing.md,
    paddingTop: 48,
    paddingBottom: spacing.sm,
    backgroundColor: colors.neutral.white,
    borderBottomWidth: 1,
    borderBottomColor: 'rgba(0,0,0,0.05)',
  },
  headerTitleSkeleton: {
    width: 140,
    height: 24,
    borderRadius: 6,
    backgroundColor: '#CBD5E1',
  },
  scrollContent: { padding: spacing.md },
  profileCardSkeleton: {
    flexDirection: 'row',
    alignItems: 'center',
    gap: spacing.sm,
    backgroundColor: colors.neutral.white,
    borderRadius: 16,
    padding: spacing.md,
    marginBottom: spacing.lg,
    borderWidth: 1,
    borderColor: 'rgba(0,0,0,0.05)',
  },
  avatarSkeleton: {
    width: 48,
    height: 48,
    borderRadius: 24,
    backgroundColor: '#CBD5E1',
  },
  profileTextCol: { flex: 1, gap: 8 },
  nameSkeletonBar: { width: '60%', height: 16, borderRadius: 4, backgroundColor: '#CBD5E1' },
  emailSkeletonBar: { width: '40%', height: 12, borderRadius: 4, backgroundColor: '#E2E8F0' },
  sectionTitleSkeletonBar: {
    width: 120,
    height: 14,
    borderRadius: 4,
    backgroundColor: '#CBD5E1',
    marginBottom: spacing.xs,
  },
  menuRowSkeleton: {
    flexDirection: 'row',
    alignItems: 'center',
    gap: spacing.sm,
    backgroundColor: colors.neutral.white,
    borderRadius: 14,
    padding: spacing.md,
    marginBottom: spacing.xs,
    borderWidth: 1,
    borderColor: 'rgba(0,0,0,0.05)',
  },
  iconCircleSkeleton: {
    width: 32,
    height: 32,
    borderRadius: 16,
    backgroundColor: '#E2E8F0',
  },
  rowLabelSkeletonBar: {
    flex: 1,
    height: 14,
    borderRadius: 4,
    backgroundColor: '#E2E8F0',
  },
  chevronSkeleton: {
    width: 16,
    height: 16,
    borderRadius: 8,
    backgroundColor: '#E2E8F0',
  },
  toggleSwitchSkeleton: {
    width: 36,
    height: 20,
    borderRadius: 10,
    backgroundColor: '#E2E8F0',
  },
});
