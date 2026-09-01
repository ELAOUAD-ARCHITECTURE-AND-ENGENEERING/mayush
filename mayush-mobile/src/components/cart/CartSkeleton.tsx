/**
 * CartSkeleton Component (Figma Node 309:667 - 05-cart-skeleton-loading-state)
 * Native animated placeholder loader cards for cart loading state.
 */

import React from 'react';
import { StyleSheet, View } from 'react-native';
import { colors } from '../../design-system/tokens/colors';
import { radii } from '../../design-system/tokens/radii';

export const CartSkeleton: React.FC = () => {
  return (
    <View style={styles.container} accessibilityLabel="Cart Skeleton Loading State">
      {/* Header bar skeleton */}
      <View style={styles.headerBar}>
        <View style={[styles.skeletonBlock, { width: 120, height: 24 }]} />
        <View style={[styles.skeletonBlock, { width: 60, height: 20 }]} />
      </View>

      {/* Progress bar skeleton */}
      <View style={styles.progressCard}>
        <View style={[styles.skeletonBlock, { width: '80%', height: 16 }]} />
        <View style={[styles.skeletonBlock, { width: '100%', height: 8, marginTop: 8 }]} />
      </View>

      {/* Cart item card skeletons */}
      {[1, 2].map((key) => (
        <View key={key} style={styles.cardSkeleton}>
          <View style={styles.thumbSkeleton} />
          <View style={styles.metaSkeleton}>
            <View style={[styles.skeletonBlock, { width: '85%', height: 16 }]} />
            <View style={[styles.skeletonBlock, { width: '50%', height: 12, marginTop: 6 }]} />
            <View style={styles.bottomRow}>
              <View style={[styles.skeletonBlock, { width: 80, height: 18 }]} />
              <View style={[styles.skeletonBlock, { width: 70, height: 28, borderRadius: radii.md }]} />
            </View>
          </View>
        </View>
      ))}

      {/* Summary card skeleton */}
      <View style={styles.summarySkeleton}>
        <View style={[styles.skeletonBlock, { width: 140, height: 20 }]} />
        <View style={[styles.skeletonBlock, { width: '100%', height: 14, marginTop: 12 }]} />
        <View style={[styles.skeletonBlock, { width: '100%', height: 14, marginTop: 8 }]} />
        <View style={[styles.skeletonBlock, { width: '100%', height: 24, marginTop: 12 }]} />
      </View>
    </View>
  );
};

const styles = StyleSheet.create({
  container: { flex: 1, padding: 16, gap: 14, backgroundColor: colors.surface.white },
  headerBar: { flexDirection: 'row', justifyContent: 'space-between', alignItems: 'center', marginBottom: 4 },
  skeletonBlock: { backgroundColor: colors.surface.creamLight, borderRadius: radii.sm },
  progressCard: { padding: 14, borderRadius: radii.xl, borderWidth: 1, borderColor: colors.surface.borderWarm },
  cardSkeleton: {
    flexDirection: 'row',
    padding: 12,
    borderRadius: radii.xl,
    borderWidth: 1,
    borderColor: colors.surface.borderWarm,
    backgroundColor: colors.surface.white,
  },
  thumbSkeleton: { width: 80, height: 80, borderRadius: radii.lg, backgroundColor: colors.surface.creamLight, marginRight: 12 },
  metaSkeleton: { flex: 1, justifyContent: 'space-between' },
  bottomRow: { flexDirection: 'row', justifyContent: 'space-between', alignItems: 'center', marginTop: 10 },
  summarySkeleton: { padding: 16, borderRadius: radii.xl, borderWidth: 1, borderColor: colors.surface.borderWarm },
});
