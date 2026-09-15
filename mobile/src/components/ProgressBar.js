import { StyleSheet, View } from 'react-native';
import { colors } from '../theme';

export default function ProgressBar({ value = 0, max = 1 }) {
  const pct = Math.max(0, Math.min(1, max ? Number(value) / Number(max) : 0));
  return (
    <View style={styles.track}>
      <View style={[styles.fill, { width: `${Math.round(pct * 100)}%` }]} />
    </View>
  );
}

const styles = StyleSheet.create({
  track: { height: 8, backgroundColor: colors.accentSoft, borderRadius: 99, overflow: 'hidden' },
  fill: { height: 8, backgroundColor: colors.accent, borderRadius: 99 },
});
