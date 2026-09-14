import { Pressable, ScrollView, StyleSheet, Text, View } from 'react-native';
import { useAuth } from '../context/AuthContext';
import { colors } from '../theme';

function todayDay(plan) {
  if (!plan?.days) return null;
  const n = new Date().getDate();
  return plan.days.find((d) => Number(d.day) === n) || plan.days[0];
}

export default function HomeScreen({ navigation }) {
  const { member, profile, plan } = useAuth();
  const today = todayDay(plan);
  const calories = plan?.meals ? null : plan?.calories_target;

  return (
    <ScrollView style={styles.wrap} contentContainerStyle={{ padding: 20, paddingTop: 64 }}>
      <Text style={styles.kicker}>WELCOME BACK</Text>
      <Text style={styles.h1}>{member?.display_name || 'Athlete'}</Text>
      <Text style={styles.p}>{plan?.title || 'Your coach will publish this month’s plan soon.'}</Text>

      <View style={styles.row}>
        <Stat label="Weight" value={profile?.weight_kg ? `${profile.weight_kg} kg` : '—'} />
        <Stat label="Goal" value={(profile?.goal || 'general').replace('_', ' ')} />
        <Stat label="Calories" value={plan?.calories_target ? `${plan.calories_target}` : '—'} />
      </View>

      {today?.workout ? (
        <Pressable style={styles.card} onPress={() => navigation.navigate('Day', { day: today })}>
          <Text style={styles.kicker}>TODAY</Text>
          <Text style={styles.h2}>{today.workout.title}</Text>
          <Text style={styles.p}>{today.workout.focus} · {today.workout.duration_min} min</Text>
          <Text style={styles.link}>Open session →</Text>
        </Pressable>
      ) : null}

      <View style={styles.card}>
        <Text style={styles.kicker}>TARGETS</Text>
        {(plan?.targets || []).map((t) => (
          <View key={t.id || t.label} style={styles.target}>
            <Text style={styles.targetLabel}>{t.label}</Text>
            <Text style={styles.targetVal}>{t.current_value} → {t.target_value} {t.unit}</Text>
          </View>
        ))}
      </View>
    </ScrollView>
  );
}

function Stat({ label, value }) {
  return (
    <View style={styles.stat}>
      <Text style={styles.statVal}>{value}</Text>
      <Text style={styles.statLab}>{label}</Text>
    </View>
  );
}

const styles = StyleSheet.create({
  wrap: { flex: 1, backgroundColor: colors.bg },
  kicker: { color: colors.accent, fontSize: 11, fontWeight: '800', letterSpacing: 1.4, marginBottom: 6 },
  h1: { color: colors.text, fontSize: 32, fontWeight: '900', marginBottom: 8 },
  h2: { color: colors.text, fontSize: 24, fontWeight: '800' },
  p: { color: colors.muted, lineHeight: 20, marginTop: 6 },
  row: { flexDirection: 'row', gap: 8, marginVertical: 18 },
  stat: { flex: 1, backgroundColor: colors.card, borderRadius: 14, padding: 12 },
  statVal: { color: colors.text, fontWeight: '800', textTransform: 'capitalize' },
  statLab: { color: colors.muted, fontSize: 11, marginTop: 4, textTransform: 'uppercase' },
  card: { backgroundColor: colors.card, borderRadius: 18, padding: 18, marginBottom: 14 },
  link: { color: colors.accent, marginTop: 12, fontWeight: '700' },
  target: { flexDirection: 'row', justifyContent: 'space-between', paddingVertical: 8, borderBottomWidth: 1, borderBottomColor: colors.line },
  targetLabel: { color: colors.text },
  targetVal: { color: colors.muted },
});
