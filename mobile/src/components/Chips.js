import { Pressable, StyleSheet, Text, View } from 'react-native';
import { colors } from '../theme';

export default function Chips({ options, value, onChange }) {
  return (
    <View style={styles.wrap}>
      {options.map((opt) => {
        const on = value === opt.id;
        return (
          <Pressable key={opt.id} onPress={() => onChange(opt.id)} style={[styles.chip, on && styles.on]}>
            <Text style={[styles.label, on && styles.labelOn]}>{opt.label}</Text>
          </Pressable>
        );
      })}
    </View>
  );
}

const styles = StyleSheet.create({
  wrap: { flexDirection: 'row', flexWrap: 'wrap', gap: 8, marginBottom: 14 },
  chip: { paddingVertical: 8, paddingHorizontal: 14, borderRadius: 999, backgroundColor: '#fff', borderWidth: 1, borderColor: colors.line },
  on: { backgroundColor: colors.accent, borderColor: colors.accent },
  label: { color: colors.ink, fontWeight: '700', fontSize: 13 },
  labelOn: { color: '#fff' },
});
